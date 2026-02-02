
import api from '@/services/api';

interface ErrorLog {
    message: string;
    stack?: string;
    url: string;
    user_agent: string;
    user_id?: number;
    data?: any;
    level: 'debug' | 'info' | 'warning' | 'error' | 'critical';
}

class Logger {
    private apiUrl = '/logs/frontend';
    private queue: ErrorLog[] = []; // Kept for checking but technically unused now

    // Rate Limiting Logic
    private logCount = 0;
    private lastResetTime = Date.now();
    private lastLogSignature = '';

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
        // Also log to console in development
        if (import.meta.env.DEV) {
            const consoleMethod = level === 'critical' ? 'error' : (level === 'warning' ? 'warn' : (level === 'debug' ? 'debug' : 'log'));

            if (Object.keys(data).length > 0) {
                console[consoleMethod](`[${level.toUpperCase()}] ${message}`, data);
            } else {
                console[consoleMethod](`[${level.toUpperCase()}] ${message}`);
            }
        }

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

    public debug(message: string, data?: any) {
        this.log('debug', message, data);
    }

    public warning(message: string, data?: any) {
        this.log('warning', message, data);
    }

    public error(message: string, data?: any) {
        this.log('error', message, data);
    }

    private async send(log: ErrorLog) {
        // Don't send debug logs to backend
        if (log.level === 'debug') return;

        // Rate Limiting: Reset count every 60 seconds
        if (Date.now() - this.lastResetTime > 60000) {
            this.logCount = 0;
            this.lastResetTime = Date.now();
        }

        // Limit to 20 logs per minute
        if (this.logCount >= 20) {
            if (this.logCount === 20) {
                console.warn('[Logger] Rate limit exceeded. Further logs paused for 1 minute.');
                this.logCount++;
            }
            return;
        }

        // Deduplication: Prevent sending the exact same error twice in a row
        // (Use message + partial stack as signature)
        const signature = `${log.message}|${log.stack?.substring(0, 100) || ''}`;
        if (signature === this.lastLogSignature) {
            return;
        }
        this.lastLogSignature = signature;

        this.logCount++;

        try {
            await api.post(this.apiUrl, log);
        } catch (e) {
            // Check if we are being rate limited by backend (429)
            // If so, stop sending for a while? 
            // The frontend rate limit should handle it, but fail safe:
            console.error('Failed to send log to backend', e);
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
