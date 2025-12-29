import { defineStore } from 'pinia';
import api, { getCsrfCookie } from '../services/api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: null,
        isAuthenticated: false,
    }),

    getters: {
        isAdmin: (state) => {
            if (!state.user) return false;
            return state.user.roles?.some(role => role.name === 'admin') || false;
        },
        hasPermission: (state) => (permission) => {
            if (!state.user) return false;
            if (state.user.roles?.some(role => role.name === 'admin')) return true;
            return state.user.permissions?.some(perm => perm.name === permission) || false;
        },
    },

    actions: {
        async login(credentials) {
            try {
                // Ensure CSRF cookie is fresh before login
                await getCsrfCookie();

                const response = await api.post('/login', credentials);
                // Handle different response structures
                const responseData = response.data?.data || response.data;
                if (responseData && responseData.user && responseData.token) {
                    this.setAuth(responseData);
                    return { success: true, data: responseData };
                } else {
                    // If response doesn't have expected structure, try to extract from response
                    if (response.data?.user && response.data?.token) {
                        this.setAuth(response.data);
                        return { success: true, data: response.data };
                    }
                    throw new Error('Invalid response format from server');
                }
            } catch (error) {
                const errorData = error.response?.data || {};
                const errors = errorData.errors || {};
                const status = error.response?.status;
                const headers = error.response?.headers || {};

                // Handle rate limiting (429)
                if (status === 429) {
                    // Try to get retry-after from various sources
                    let retryAfter = 60; // Default 60 seconds

                    // Try from response body first
                    if (errorData.retry_after) {
                        retryAfter = parseInt(errorData.retry_after, 10);
                    }
                    // Try from headers (axios lowercases header names)
                    else if (headers['retry-after']) {
                        retryAfter = parseInt(headers['retry-after'], 10);
                    }
                    // Try capitalized version
                    else if (headers['Retry-After']) {
                        retryAfter = parseInt(headers['Retry-After'], 10);
                    }
                    // Try from error response headers directly
                    else if (error.response?.headers?.['retry-after']) {
                        retryAfter = parseInt(error.response.headers['retry-after'], 10);
                    }
                    else if (error.response?.headers?.['Retry-After']) {
                        retryAfter = parseInt(error.response.headers['Retry-After'], 10);
                    }

                    const retryAfterSeconds = retryAfter;
                    const retryAfterMinutes = Math.ceil(retryAfterSeconds / 60);

                    return {
                        success: false,
                        message: `Too many login attempts. Please try again in ${retryAfterMinutes} minute${retryAfterMinutes > 1 ? 's' : ''}.`,
                        errors: {},
                        rateLimited: true,
                        retryAfter: retryAfterSeconds,
                    };
                }

                // Handle different error statuses
                if (status === 403) {
                    // Email not verified
                    return {
                        success: false,
                        message: errorData.message || 'Please verify your email address before logging in.',
                        errors: {},
                        requiresVerification: errorData.requires_verification || false,
                    };
                }

                // Extract first error message if available
                let errorMessage = errorData.message;
                if (!errorMessage && errors.email && Array.isArray(errors.email) && errors.email.length > 0) {
                    errorMessage = errors.email[0];
                } else if (!errorMessage && errors.password && Array.isArray(errors.password) && errors.password.length > 0) {
                    errorMessage = errors.password[0];
                } else if (!errorMessage) {
                    errorMessage = 'Login failed. Please check your credentials.';
                }

                return {
                    success: false,
                    message: errorMessage,
                    errors: errors,
                };
            }
        },

        async register(userData) {
            try {
                // Ensure CSRF cookie is fresh before register
                await getCsrfCookie();

                const response = await api.post('/register', userData);
                this.setAuth(response.data);
                return { success: true, data: response.data };
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Registration failed',
                    errors: error.response?.data?.errors,
                };
            }
        },

        async logout() {
            try {
                // Skip 401 handler redirect - logout is intentionally ending session
                await api.post('/logout', {}, { _skipManualRedirect: true });
            } catch (error) {
                // Ignore 401 errors - session may already be expired
                if (error.response?.status !== 401) {
                    console.error('Logout error:', error);
                }
            } finally {
                this.clearAuth();
                // Reset all circuit breaker flags
                window.__isSessionTerminated = false;
                window.__is403Blocked = false;
            }
        },

        async fetchUser() {
            try {
                const response = await api.get('/user');
                const userData = response.data?.data || response.data;
                this.user = userData;
                this.isAuthenticated = true;
                // Update localStorage
                if (userData) {
                    localStorage.setItem('user', JSON.stringify(userData));
                }
                return { success: true, data: userData };
            } catch (error) {
                this.clearAuth();
                return { success: false, message: error.response?.data?.message };
            }
        },

        async forgotPassword(email) {
            try {
                const response = await api.post('/forgot-password', { email });
                return { success: true, message: response.data.message };
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Failed to send reset link',
                };
            }
        },

        async resetPassword(data) {
            try {
                const response = await api.post('/reset-password', data);
                return { success: true, message: response.data.message };
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Password reset failed',
                    errors: error.response?.data?.errors,
                };
            }
        },

        setAuth(data) {
            this.user = data.user;
            this.token = data.token;
            this.isAuthenticated = true;
            localStorage.setItem('auth_token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));
            // Reset circuit breaker flags on successful auth
            window.__isSessionTerminated = false;
            window.__is403Blocked = false;
        },

        clearAuth() {
            this.user = null;
            this.token = null;
            this.isAuthenticated = false;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
        },

        initAuth() {
            try {
                const token = localStorage.getItem('auth_token');
                const user = localStorage.getItem('user');
                if (token && user) {
                    // Try to parse user JSON, clear if invalid
                    try {
                        const parsedUser = JSON.parse(user);
                        this.token = token;
                        this.user = parsedUser;
                        this.isAuthenticated = true;
                    } catch (parseError) {
                        // Invalid JSON in localStorage, clear it
                        // Only log in development mode to reduce console noise
                        if (import.meta.env.DEV) {
                            console.warn('Invalid user data in localStorage, clearing:', parseError);
                        }
                        this.clearAuth();
                    }
                }
            } catch (error) {
                // Only log in development mode to reduce console noise
                if (import.meta.env.DEV) {
                    console.warn('Error initializing auth:', error);
                }
                this.clearAuth();
            }
        },
    },
});
