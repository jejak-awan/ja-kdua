import axios from 'axios';

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

        // Handle 419 CSRF token mismatch - refresh CSRF cookie and retry
        if (error.response?.status === 419 && !originalRequest._retry) {
            originalRequest._retry = true;
            try {
                await getCsrfCookie();
                return api(originalRequest);
            } catch (csrfError) {
                console.error('Failed to refresh CSRF token:', csrfError);
            }
        }

        // Handle 401 Unauthorized
        if (error.response?.status === 401) {
            const url = error.config?.url || '';
            const responseData = error.response?.data || {};

            // Don't redirect for public endpoints
            const publicEndpoints = [
                '/cms/',
                '/cms/contents',
                '/cms/categories',
                '/cms/tags',
                '/cms/themes/active',
                '/cms/search',
                '/cms/languages',
            ];

            const isPublicEndpoint = publicEndpoints.some(endpoint => url.includes(endpoint));

            if (!isPublicEndpoint) {
                // Only redirect to login for protected endpoints
                // Check if we're already on login page to avoid redirect loop
                if (window.location.pathname !== '/login') {
                    // Check if this is a session invalidation (concurrent login)
                    const isSessionInvalidated = responseData.session_expired ||
                        responseData.message?.includes('session') ||
                        responseData.message?.includes('expired');

                    // Show toast notification before redirect
                    if (window.__toastInstance?.addToast) {
                        const message = isSessionInvalidated
                            ? (responseData.message || 'Sesi Anda telah berakhir. Silakan login kembali.')
                            : 'Sesi berakhir. Silakan login kembali.';

                        window.__toastInstance.addToast({
                            title: 'Sesi Berakhir',
                            description: message,
                            variant: 'warning',
                            duration: 4000,
                        });
                    }

                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');

                    // Delay redirect to allow toast to be seen
                    setTimeout(() => {
                        window.location.href = '/login';
                    }, 1500);
                }
            }
        }

        // Handle 403 Forbidden - use direct navigation to avoid circular import
        if (error.response?.status === 403) {
            // Only redirect if not already on error page
            if (!window.location.pathname.includes('/403')) {
                window.location.href = '/403';
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

        // Handle 500 Server Error - use direct navigation to avoid circular import
        if (error.response?.status === 500) {
            // Only redirect if not already on error page
            if (!window.location.pathname.includes('/500')) {
                window.location.href = '/500';
            }
        }

        return Promise.reject(error);
    }
);

export default api;
