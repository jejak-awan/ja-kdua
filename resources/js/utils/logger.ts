import api from '@/services/api';
import type { App, ComponentPublicInstance } from 'vue';

interface ErrorLog {
    message: string;
    stack?: string;
    url: string;
    user_agent: string;
    user_id?: number;
    data?: unknown;
    level: 'debug' | 'info' | 'warning' | 'error' | 'critical';
}

class Logger {
    private apiUrl = '/journal/frontend';
    private queue: ErrorLog[] = []; // Kept for checking but technically unused now

    // Rate Limiting & Deduplication Logic
    private logCount = 0;
    private lastResetTime = Date.now();
    private signatureMap = new Map<string, number>();

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

    public log(level: ErrorLog['level'], message: string, data: unknown = {}) {
        // Also log to console in development
        if (import.meta.env.DEV) {
            const consoleMethod = level === 'critical' ? 'error' : (level === 'warning' ? 'warn' : (level === 'debug' ? 'debug' : 'log'));

            if (data && typeof data === 'object' && Object.keys(data).length > 0) {
                // eslint-disable-next-line no-console
                console[consoleMethod](`[${level.toUpperCase()}] ${message}`, data);
            } else {
                // eslint-disable-next-line no-console
                console[consoleMethod](`[${level.toUpperCase()}] ${message}`);
            }
        }

        const errorLog: ErrorLog = {
            message,
            level,
            url: window.location.href,
            user_agent: navigator.userAgent,
            data,
            stack: data && typeof data === 'object' ? (data as Record<string, unknown>).stack as string : undefined
        };

        // Try to add user ID if available in localStorage or store
        try {
            // Simplified check - adjust based on your auth implementation
            const userStr = localStorage.getItem('user'); // Adjust key as needed
            if (userStr) {
                const user = JSON.parse(userStr);
                errorLog.user_id = user.id;
            }
        } catch {
            // Ignore
        }

        this.send(errorLog);
    }
    public info(message: string, data: unknown = {}) {
        this.log('info', message, data);
    }

    public debug(message: string, data: unknown = {}) {
        this.log('debug', message, data);
    }

    public warning(message: string, data: unknown = {}) {
        this.log('warning', message, data);
    }

    public error(message: string, data: unknown = {}) {
        this.log('error', message, data);
    }



    private deepTruncate(obj: unknown, limit = 200, depth = 0): unknown {
        // Prevent infinite recursion
        if (depth > 3) return '[Max Depth Exceeded]';

        if (typeof obj === 'string') {
            return obj.length > limit ? obj.substring(0, limit) + '... (truncated)' : obj;
        }
        if (Array.isArray(obj)) {
            return obj.map(item => this.deepTruncate(item, limit, depth + 1));
        }
        if (obj !== null && typeof obj === 'object') {
            const result: Record<string, unknown> = {};
            for (const key in obj as Record<string, unknown>) {
                // Defensively skip nested stacks or huge payloads
                if (key === 'stack' || key === 'long_stack') continue;
                result[key] = this.deepTruncate((obj as Record<string, unknown>)[key], limit, depth + 1);
            }
            return result;
        }
        return obj;
    }

    private async send(log: ErrorLog) {
        // Don't send debug, info, or warning logs to backend to prevent flood
        if (['debug', 'info', 'warning'].includes(log.level)) return;

        // Rate Limiting: Reset count every 60 seconds
        const now = Date.now();
        if (now - this.lastResetTime > 60000) {
            this.logCount = 0;
            this.lastResetTime = now;
            // Also clean up signature map older than 60s
            for (const [sig, time] of this.signatureMap.entries()) {
                if (now - time > 60000) this.signatureMap.delete(sig);
            }
        }

        // Limit to 20 logs per minute
        if (this.logCount >= 20) {
            if (this.logCount === 20) {
                console.warn('[Logger] Rate limit exceeded. Further logs paused for 1 minute.');
                this.logCount++;
            }
            return;
        }

        // Aggressive Truncation: Limit stack trace to 1KB
        if (log.stack && log.stack.length > 1000) {
            log.stack = log.stack.substring(0, 1000) + '\n... (truncated for safety)';
        }

        // Deep Truncation of data object
        if (log.data) {
            log.data = this.deepTruncate(log.data);
        }

        // Deduplication: Use signature cache with 30s TTL
        const signature = `${log.message}|${log.stack?.substring(0, 150) || ''}`;
        const lastSeen = this.signatureMap.get(signature);
        if (lastSeen && now - lastSeen < 30000) {
            return;
        }
        this.signatureMap.set(signature, now);

        this.logCount++;

        try {
            await api.post(this.apiUrl, log);
        } catch (e) {
            console.error('Failed to send log to backend', e);
        }
    }
}

export const logger = new Logger();

// Vue Plugin
export default {
    install(app: App) {
        app.config.errorHandler = (err: unknown, instance: ComponentPublicInstance | null, info: string) => {
            const error = err as Error;
            logger.error(error.message || 'Vue Error', {
                stack: error.stack,
                component: instance?.$options?.__file || instance?.$options?.name,
                info
            });
            console.error(err);
        };

        // Make $logger available globally
        app.config.globalProperties.$logger = logger;
    }
};
