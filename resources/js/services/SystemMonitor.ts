import { logger } from '@/utils/logger';
import { reactive } from 'vue';

export interface SystemState {
    isSystemDown: boolean;
    downReason: 'maintenance' | 'connection' | 'error' | string;
    retryCount: number;
    nextRetry: number | null;
}

// Singleton state for system health
export const systemState = reactive<SystemState>({
    isSystemDown: false,
    downReason: 'maintenance',
    retryCount: 0,
    nextRetry: null,
});

let isPolling = false;

export const SystemMonitor = {
    // Trigger the global lockdown
    triggerLockdown(reason: string = 'maintenance'): void {
        if (systemState.isSystemDown) return;

        logger.warning(`System Lockdown triggered: ${reason}`);
        systemState.isSystemDown = true;
        systemState.downReason = reason;

        // Stop all pending execution if possible
        if (typeof window.stop === 'function') {
            // window.stop(); // Optional: can be too aggressive
        }

        this.startPolling();
    },

    get isRequestBlocked(): boolean {
        return systemState.isSystemDown;
    },

    // Reset state to normal (usually followed by a reload)
    reset(): void {
        systemState.isSystemDown = false;
        systemState.retryCount = 0;
        systemState.nextRetry = null;
        isPolling = false;
    },

    // Poll the server until it's back
    async startPolling(): Promise<void> {
        if (isPolling) return;
        isPolling = true;

        const checkHealth = async () => {
            try {
                // Use a dedicated simple endpoint or just the root
                // Using fetch directly to bypass axios interceptors if needed
                const response = await fetch('/api/core/system/health', {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });

                if (response.ok) {
                    window.location.reload();
                    return;
                }
            } catch {
                // Still down
            }

            systemState.retryCount++;
            // Exponential backoff or max delay of 5s
            const delay = Math.min(systemState.retryCount * 1000, 5000);
            systemState.nextRetry = Date.now() + delay;

            setTimeout(checkHealth, delay);
        };

        // Initial delay
        setTimeout(checkHealth, 2000);
    }
};
