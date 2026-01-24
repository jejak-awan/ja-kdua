import { defineStore } from 'pinia';
import { AxiosError, AxiosResponse } from 'axios';
import api, { getCsrfCookie } from '../services/api';
import type { User, Role, AuthState, AuthResponse, LoginCredentials } from '../types/auth';

export const ROLE_RANKS: Record<string, number> = {
    'super-admin': 100,
    'admin': 80,
    'editor': 60,
    'author': 40,
    'member': 20,
};

// Define extended window interface for circuit breaker flags
declare global {
    interface Window {
        __isSessionTerminated?: boolean;
        __is403Blocked?: boolean;
    }
}

export const useAuthStore = defineStore('auth', {
    state: (): AuthState => ({
        user: null,
        isAuthenticated: false,
    }),

    getters: {
        // Helper to calculate role rank from a user object
        getRoleRank: (state) => (user: User | null = null): number => {
            const targetUser = user || state.user;
            if (!targetUser || !targetUser.roles) return 0;

            let maxRank = 0;
            targetUser.roles.forEach((role: Role) => {
                const rank = ROLE_RANKS[role.name] || 0;
                if (rank > maxRank) maxRank = rank;
            });

            return maxRank;
        },

        // Check if current user has at least the specified role level
        isAtLeastRole: (state) => (roleName: string): boolean => {
            if (!state.user || !state.user.roles) return false;

            const minRank = ROLE_RANKS[roleName] || 0;

            let myRank = 0;
            state.user.roles.forEach((role: Role) => {
                const rank = ROLE_RANKS[role.name] || 0;
                if (rank > myRank) myRank = rank;
            });

            return myRank >= minRank;
        },

        // Check if current user has higher rank than another user
        isHigherThan: (state) => (otherUser: User): boolean => {
            if (!otherUser) return true;
            if (!state.user || !state.user.roles) return false;

            // Calculate my rank
            let myRank = 0;
            state.user.roles.forEach((role: Role) => {
                const rank = ROLE_RANKS[role.name] || 0;
                if (rank > myRank) myRank = rank;
            });

            // Calculate other user's rank
            let otherRank = 0;
            if (otherUser.roles) {
                otherUser.roles.forEach((role: Role) => {
                    const rank = ROLE_RANKS[role.name] || 0;
                    if (rank > otherRank) otherRank = rank;
                });
            }

            return myRank > otherRank;
        },

        isAdmin: (state): boolean => {
            if (!state.user || !state.user.roles) return false;
            return state.user.roles.some((role: Role) => role.name === 'admin' || role.name === 'super-admin');
        },

        hasPermission: (state) => (permission: string): boolean => {
            if (!state.user) return false;
            // Super-admin always has all permissions
            if (state.user.roles?.some((role: Role) => role.name === 'super-admin')) return true;
            return state.user.permissions?.some(perm => perm.name === permission) || false;
        },
    },

    actions: {
        async login(credentials: LoginCredentials): Promise<AuthResponse> {
            try {
                // Ensure CSRF cookie is fresh before login
                await getCsrfCookie();

                const response: AxiosResponse = await api.post('/login', credentials);
                // Handle different response structures
                const responseData = response.data?.data || response.data;

                // Handle 2FA requirement
                if (responseData && responseData.requires_two_factor) {
                    return {
                        success: true,
                        requiresTwoFactor: true,
                        userId: responseData.user_id,
                        message: response.data.message
                    };
                }

                if (responseData && responseData.user) {
                    this.setAuth(responseData);
                    return { success: true, data: responseData };
                } else {
                    throw new Error('Invalid response format from server');
                }
            } catch (error: any) {
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

        async register(userData: any): Promise<AuthResponse> {
            try {
                // Ensure CSRF cookie is fresh before register
                await getCsrfCookie();

                const response = await api.post('/register', userData);
                this.setAuth(response.data);
                return { success: true, data: response.data };
            } catch (error: any) {
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
                await api.post('/logout', {}, { _skipManualRedirect: true } as any);
            } catch (error: any) {
                // Silence session errors (401/419) and cancellations during logout
                // These are expected if the session is already terminated.
                const status = error.response?.status;
                // @ts-ignore
                const isSilentError = status === 401 || status === 419 || AxiosError.isCancel(error);

                if (!isSilentError) {
                    console.error('Logout error:', error);
                }
            } finally {
                this.clearAuth();
                // Reset all circuit breaker flags
                if (typeof window !== 'undefined') {
                    window.__isSessionTerminated = false;
                    window.__is403Blocked = false;
                }
            }
        },

        async fetchUser(): Promise<AuthResponse> {
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
            } catch (error: any) {
                this.clearAuth();
                return { success: false, message: error.response?.data?.message };
            }
        },

        async forgotPassword(email: string): Promise<AuthResponse> {
            try {
                const response = await api.post('/forgot-password', { email });
                return { success: true, message: response.data.message };
            } catch (error: any) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Failed to send reset link',
                };
            }
        },

        async resetPassword(data: any): Promise<AuthResponse> {
            try {
                const response = await api.post('/reset-password', data);
                return { success: true, message: response.data.message };
            } catch (error: any) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Password reset failed',
                    errors: error.response?.data?.errors,
                };
            }
        },

        setAuth(data: { user: User }) {
            this.user = data.user;
            this.isAuthenticated = true;
            localStorage.setItem('user', JSON.stringify(data.user));
            // Reset circuit breaker flags on successful auth
            if (typeof window !== 'undefined') {
                window.__isSessionTerminated = false;
                window.__is403Blocked = false;
            }
        },

        clearAuth() {
            this.user = null;
            this.isAuthenticated = false;
            localStorage.removeItem('user');
        },

        initAuth() {
            try {
                const user = localStorage.getItem('user');
                if (user) {
                    // Try to parse user JSON, clear if invalid
                    try {
                        const parsedUser = JSON.parse(user);
                        this.user = parsedUser;
                        this.isAuthenticated = true;
                    } catch (parseError) {
                        // Invalid JSON in localStorage, clear it
                        if (import.meta.env.DEV) {
                            console.warn('Invalid user data in localStorage, clearing:', parseError);
                        }
                        this.clearAuth();
                    }
                }
            } catch (error) {
                if (import.meta.env.DEV) {
                    console.warn('Error initializing auth:', error);
                }
                this.clearAuth();
            }
        },
    },
});
