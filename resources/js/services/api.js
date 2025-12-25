import router from '../router';

let isRedirecting = false;
// Global flag to prevent multiple toasts and parallel redirect attempts
window.__isSessionTerminated = false;

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
        // Whitelist auth-related endpoints so they are NOT blocked even if session is marked as dead
        const authEndpoints = ['/login', '/register', '/forgot-password', '/reset-password', '/sanctum/csrf-cookie'];
        const isAuthRequest = authEndpoints.some(endpoint => config.url?.includes(endpoint));

        // Instantly block any non-auth request if session is already known to be dead
        if (window.__isSessionTerminated && !isAuthRequest) {
            return Promise.reject(new Error('Session terminated'));
        }

        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
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

        // Handle 419 CSRF token mismatch / Session Expired
        // NOTE: We do NOT retry here as it can cause infinite loops
        // If session is truly expired, retrying won't help
        if (error.response?.status === 419) {
            if (window.__isSessionTerminated) return Promise.reject(error);

            const currentPath = window.location.pathname;
            if (!currentPath.includes('/419') && !currentPath.includes('/login')) {
                // NUCLEAR SHUTDOWN: Stop all pending requests/animations/scripts
                if (typeof window.stop === 'function') window.stop();

                window.__isSessionTerminated = true;
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');

                // Instant hard redirect to clear all zombie states and the termination flag.
                // We use replace to prevent back-button loops.
                const redirectUrl = `/419?reason=concurrent&redirect=${encodeURIComponent(currentPath !== '/' ? currentPath : '/admin')}`;
                window.location.replace(redirectUrl);
            }
        }

        // Handle 401 Unauthorized (session expired from Sanctum)
        if (error.response?.status === 401) {
            if (window.__isSessionTerminated) return Promise.reject(error);

            const url = error.config?.url || '';
            const responseData = error.response?.data || {};

            // Public endpoints are frontend routes that don't require session termination on 401
            // We use anchored paths to avoid matching '/admin/cms/...'
            const publicEndpoints = [
                '/api/v1/cms/',
                '/api/v1/cms/contents',
                '/api/v1/cms/categories',
                '/api/v1/cms/tags',
                '/api/v1/cms/themes/active',
                '/api/v1/cms/search',
                '/api/v1/cms/languages',
            ];

            const isPublicEndpoint = publicEndpoints.some(endpoint => url.startsWith(endpoint));
            const hasAuthHeader = !!error.config?.headers?.Authorization;

            // If it has an auth header, it was intended to be authenticated, 
            // so a 401 is always a session termination event.
            if (hasAuthHeader || !isPublicEndpoint) {
                const currentPath = window.location.pathname;

                if (!currentPath.includes('/login') && !currentPath.includes('/419')) {
                    // NUCLEAR SHUTDOWN: Stop everything
                    if (typeof window.stop === 'function') window.stop();

                    window.__isSessionTerminated = true;
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');

                    // Instant hard redirect to ensure clean state
                    window.location.replace(`/419?reason=concurrent&redirect=${encodeURIComponent(currentPath)}`);
                }
            }
        }

        // Handle 403 Forbidden - use router instead of hard redirect
        if (error.response?.status === 403) {
            const currentPath = window.location.pathname;
            // Only redirect if not already on error page
            if (!currentPath.includes('/403')) {
                const responseData = error.response?.data || {};
                router.push({
                    name: 'forbidden',
                    state: {
                        reason: responseData.message,
                        requiredPermissions: responseData.required_permissions || []
                    }
                }).catch(() => {
                    // Fallback to hard redirect if router fails
                    window.location.href = '/403';
                });
            }
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

        // Handle 500 Server Error - use router instead of hard redirect
        if (error.response?.status === 500) {
            const currentPath = window.location.pathname;
            // Only redirect if not already on error page
            if (!currentPath.includes('/500')) {
                router.push({
                    name: 'server-error',
                    state: { errorDetails: error.response?.data?.message || error.message }
                }).catch(() => {
                    // Fallback to hard redirect if router fails
                    window.location.href = '/500';
                });
            }
        }

        return Promise.reject(error);
    }
);

export default api;
