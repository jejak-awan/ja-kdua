
import api from '@/services/api';

interface ErrorLog {
    message: string;
    stack?: string;
    url: string;
    user_agent: string;
    user_id?: number;
    data?: any;
    level: 'info' | 'warning' | 'error' | 'critical';
}

class Logger {
    private apiUrl = '/logs/frontend';
    private isSending = false;
    private queue: ErrorLog[] = [];

    constructor() {
        this.setupGlobalHandlers();
    }

    private setupGlobalHandlers() {
        // Window errors
        window.onerror = (message, source, lineno, colno, error) => {
            this.error(message as string, {
                stack: error?.stack,
                source,
                lineno,
                colno
            });
        };

        // Unhandled promise rejections
        window.onunhandledrejection = (event) => {
            this.error(event.reason?.message || 'Unhandled Promise Rejection', {
                stack: event.reason?.stack
            });
        };
    }

    public log(level: ErrorLog['level'], message: string, data: any = {}) {
        const errorLog: ErrorLog = {
            message,
            level,
            url: window.location.href,
            user_agent: navigator.userAgent,
            data,
            stack: data.stack
        };

        // Try to add user ID if available in localStorage or store
        try {
            // Simplified check - adjust based on your auth implementation
            const userStr = localStorage.getItem('user'); // Adjust key as needed
            if (userStr) {
                const user = JSON.parse(userStr);
                errorLog.user_id = user.id;
            }
        } catch (e) {
            // Ignore
        }

        this.send(errorLog);
    }

    public info(message: string, data?: any) {
        this.log('info', message, data);
    }

    public warning(message: string, data?: any) {
        this.log('warning', message, data);
    }

    public error(message: string, data?: any) {
        this.log('error', message, data);
    }

    private async send(log: ErrorLog) {
        // Prevent flood
        if (this.queue.length > 10) return;

        try {
            await api.post(this.apiUrl, log);
        } catch (e) {
            console.error('Failed to send log to backend', e);
            // Fallback: Use Navigator.sendBeacon if axios fails (e.g. unload)
            // const blob = new Blob([JSON.stringify(log)], { type: 'application/json' });
            // navigator.sendBeacon(this.apiUrl, blob);
        }
    }
}

export const logger = new Logger();

// Vue Plugin
export default {
    install(app: any) {
        app.config.errorHandler = (err: any, instance: any, info: string) => {
            logger.error(err.message || 'Vue Error', {
                stack: err.stack,
                component: instance?.$options?.__file || instance?.$options?.name,
                info
            });
            console.error(err);
        };

        // Make $logger available globally
        app.config.globalProperties.$logger = logger;
    }
};
