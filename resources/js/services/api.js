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
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');
                    window.location.href = '/login';
                }
            }
        }

        // Handle 403 Forbidden
        if (error.response?.status === 403) {
            const reason = error.response?.data?.message || 'Anda tidak memiliki izin untuk mengakses resource ini';
            const requiredPermissions = error.response?.data?.required_permissions || [];
            
            // Only redirect if not already on error page
            if (!window.location.pathname.includes('/403')) {
                import('@/router').then((routerModule) => {
                    routerModule.default.push({
                        name: 'forbidden',
                        state: { reason, requiredPermissions }
                    });
                });
            }
        }

        // Handle 500 Server Error
        if (error.response?.status === 500) {
            const errorDetails = error.response?.data?.message || error.message;
            
            // Only redirect if not already on error page
            if (!window.location.pathname.includes('/500')) {
                import('@/router').then((routerModule) => {
                    routerModule.default.push({
                        name: 'server-error',
                        state: { errorDetails }
                    });
                });
            }
        }
        
        return Promise.reject(error);
    }
);

export default api;

