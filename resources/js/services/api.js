import axios from 'axios';
import router from '../router';
import { SystemMonitor } from './SystemMonitor';
import { useSystemError } from '@/composables/useSystemError';

const { showError } = useSystemError();
// Global flag to prevent multiple toasts and parallel redirect attempts
window.__isSessionTerminated = false;

// Vapor Lock: Global controller to cancel all pending requests instantly
let abortController = new AbortController();

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
});

/**
 * RESET VAPOR LOCK: Recovers the ability to make requests (used after login/refresh)
 */
export const resetLockdown = () => {
    window.__isSessionTerminated = false;
    abortController = new AbortController();
};

/**
 * TRIGGER VAPOR LOCK: Instantly kills all outgoing and pending requests
 */
export const triggerVaporLock = () => {
    if (window.__isSessionTerminated) return;
    window.__isSessionTerminated = true;
    abortController.abort('Vapor Lock: Session Terminated');
};

// Ensure CSRF cookie is set (call this before first authenticated request)
export const getCsrfCookie = async () => {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    } catch (error) {
        console.error('Failed to get CSRF cookie:', error);
    }
};

// Request interceptor - Add auth token
api.interceptors.request.use(
    (config) => {
        // BREAK CIRCUIT: If system is down, block all non-critical requests
        if (SystemMonitor.isRequestBlocked && !config.url?.includes('system/health')) {
            console.debug('Request blocked by Circuit Breaker:', config.url);
            return Promise.reject(new axios.Cancel('System is undergoing maintenance'));
        }

        // Whitelist auth-related endpoints so they are NOT blocked even if session is marked as dead
        const authEndpoints = ['login', 'register', 'forgot-password', 'reset-password', 'sanctum/csrf-cookie', 'logout', 'captcha/', 'user'];
        const isAuthRequest = authEndpoints.some(endpoint => config.url?.includes(endpoint));

        // VAPOR LOCK CHECK: Instantly block any non-auth request if session is dead
        if (window.__isSessionTerminated && !isAuthRequest) {
            return Promise.reject(new axios.Cancel('Vapor Lock: Request Blocked'));
        }

        // Attach abort signal to the request (unless it's an auth request we WANT to succeed)
        if (!isAuthRequest) {
            config.signal = abortController.signal;
        }

        // Manual XSRF-TOKEN handling for added robustness
        // This ensures the header is ALWAYS set if the cookie is present,
        // bypassing occasional Axios auto-detection failures.
        const xsrfToken = document.cookie
            .split('; ')
            .find(row => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1];

        if (xsrfToken) {
            config.headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrfToken);
        }


        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor - Handle errors
api.interceptors.response.use(
    (response) => response,
    async (error) => {
        const originalRequest = error.config;

        // Skip global redirect if requested by the caller
        if (originalRequest?._skipManualRedirect) {
            return Promise.reject(error);
        }

        // HANDLE 503 SERVICE UNAVAILABLE (Maintenance Mode / Deployment)
        if (error.response?.status === 503) {
            SystemMonitor.triggerLockdown('maintenance');
            return Promise.reject(error);
        }

        // Handle 419 CSRF token mismatch / Session Expired
        if (error.response?.status === 419) {
            const url = error.config?.url || '';
            const authEndpoints = ['login', 'register', 'forgot-password', 'reset-password', 'sanctum/csrf-cookie', 'logout', 'captcha/', 'user'];
            const isAuthRequest = authEndpoints.some(endpoint => url.includes(endpoint));
            const currentPath = window.location.pathname;

            // If it's a 419 on a whitelisted endpoint, don't trigger global lockdown
            if (isAuthRequest) {
                if (currentPath.includes('/login')) {
                    getCsrfCookie(); // Attempt to self-heal CSRF
                }

                // If LOGOUT fails with 419, it's fine (session is already gone), silence it
                // We RESOLVE here to bypass the component's catch block entirely
                if (url.includes('logout')) {
                    return Promise.resolve({ data: { message: 'Logout Silenced: Session already dead' } });
                }

                return Promise.reject(error);
            }

            triggerVaporLock();

            if (!currentPath.includes('/419') && !currentPath.includes('/login')) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');

                showError({
                    code: 419,
                    reason: 'concurrent',
                    redirect: currentPath !== '/' ? currentPath : '/admin'
                });
            }

            // Return silent rejection to avoid component catch blocks logging "419" after lockdown
            return Promise.reject(new axios.Cancel('Vapor Lock: 419 Silenced'));
        }

        // Handle 401 Unauthorized (session expired from Sanctum)
        if (error.response?.status === 401) {
            const url = error.config?.url || '';
            const authEndpoints = ['login', 'register', 'forgot-password', 'reset-password', 'sanctum/csrf-cookie', 'logout', 'captcha/', 'user'];
            const isAuthRequest = authEndpoints.some(endpoint => url.includes(endpoint));

            // Whitelist check
            if (isAuthRequest) {
                // If LOGOUT fails with 401, it's fine (session is already gone), silence it
                // We RESOLVE here to bypass the component's catch block entirely
                if (url.includes('logout')) {
                    return Promise.resolve({ data: { message: 'Logout Silenced: Already unauthorized' } });
                }
                return Promise.reject(error);
            }

            triggerVaporLock();

            // Public endpoints are frontend routes that don't require session termination on 401
            const publicEndpoints = ['/api/v1/cms/', '/api/v1/cms/contents', '/api/v1/cms/categories', '/api/v1/cms/tags', '/api/v1/cms/themes/active', '/api/v1/cms/search', '/api/v1/cms/languages'];
            const isPublicEndpoint = publicEndpoints.some(endpoint => url.startsWith(endpoint));
            const hasAuthHeader = !!error.config?.headers?.Authorization;

            if (hasAuthHeader || !isPublicEndpoint) {
                const currentPath = window.location.pathname;
                if (!currentPath.includes('/login') && !currentPath.includes('/419')) {
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');

                    showError({
                        code: 401,
                        reason: 'concurrent',
                        redirect: currentPath
                    });
                }
            }

            // Return silent rejection
            return Promise.reject(new axios.Cancel('Vapor Lock: 401 Silenced'));
        }

        // Handle 403 Forbidden - DON'T auto-redirect, let components handle it
        // This allows for graceful error handling with toast notifications
        if (error.response?.status === 403) {
            // SPECIAL CASE: If /user endpoint returns 403, it means the user exists but is likely suspended/banned
            // or the session state is corrupted. in this case we MUST force logout.
            if (error.config?.url?.endsWith('/user') || error.config?.url?.includes('/user?')) {
                triggerVaporLock();
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');

                showError({
                    code: 403, // We use 403 here so the error page knows to say "Access Denied / Suspended"
                    reason: 'concurrent',
                    redirect: '/login'
                });
                return Promise.reject(new axios.Cancel('Vapor Lock: 403 Session Terminated'));
            }

            // Just log and let the error propagate to the caller
            console.debug('403 Forbidden:', error.config?.url);
            // Components should handle 403 with toast.error() and appropriate fallback
        }

        // Handle 429 Rate Limit - ensure retry_after is in response data
        if (error.response?.status === 429) {
            // Add retry_after to error data if not present
            if (error.response.data && !error.response.data.retry_after) {
                const retryAfter = error.response.headers?.['retry-after'] ||
                    error.response.headers?.['Retry-After'] ||
                    60;
                error.response.data.retry_after = parseInt(retryAfter, 10);
            }
        }

        // Handle 500 Server Error - use modal instead of redirect
        if (error.response?.status === 500) {
            triggerVaporLock();
            const currentPath = window.location.pathname;
            showError({
                code: 500,
                message: error.response?.data?.message || error.message,
                redirect: currentPath
            });
        }

        return Promise.reject(error);
    }
);

export default api;
