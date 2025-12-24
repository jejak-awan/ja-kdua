// Toast service for global toast notifications
// Use this to show toasts from anywhere in the app

let toastInstance = null;

export const setToastInstance = (instance) => {
    toastInstance = instance;
    if (typeof window !== 'undefined') {
        window.__toastInstance = instance;
    }
};

export const toast = {
    show(options) {
        const instance = toastInstance || window.__toastInstance;
        if (instance?.addToast) {
            return instance.addToast(options);
        }
        // Fallback to console if toast not available
        console.warn('Toast not initialized:', options);
        return null;
    },

    success(title, description = '') {
        return this.show({ title, description, variant: 'success' });
    },

    error(title, description = '') {
        return this.show({ title, description, variant: 'error' });
    },

    warning(title, description = '') {
        return this.show({ title, description, variant: 'warning' });
    },

    info(title, description = '') {
        return this.show({ title, description, variant: 'info' });
    },

    // Special toast for session invalidation
    sessionExpired(message = 'Your session has been terminated due to a new login from another device.') {
        return this.show({
            title: 'Session Expired',
            description: message,
            variant: 'warning',
            duration: 5000,
        });
    },
};

export default toast;
