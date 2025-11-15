import { defineStore } from 'pinia';
import api from '../services/api';

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
                const response = await api.post('/login', credentials);
                this.setAuth(response.data);
                return { success: true, data: response.data };
            } catch (error) {
                const errorData = error.response?.data || {};
                const errors = errorData.errors || {};
                const status = error.response?.status;
                
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
                await api.post('/logout');
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                this.clearAuth();
            }
        },

        async fetchUser() {
            try {
                const response = await api.get('/user');
                this.user = response.data;
                return { success: true, data: response.data };
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
        },

        clearAuth() {
            this.user = null;
            this.token = null;
            this.isAuthenticated = false;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
        },

        initAuth() {
            const token = localStorage.getItem('auth_token');
            const user = localStorage.getItem('user');
            if (token && user) {
                this.token = token;
                this.user = JSON.parse(user);
                this.isAuthenticated = true;
            }
        },
    },
});
