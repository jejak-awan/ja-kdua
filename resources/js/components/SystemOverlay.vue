<template>
    <div v-if="state.isSystemDown" class="fixed inset-0 z-[9999] bg-background/95 backdrop-blur-md flex flex-col items-center justify-center p-4 text-center">
        <div class="max-w-md w-full space-y-8 animate-in fade-in zoom-in duration-300">
            <!-- Icon based on reason -->
            <div class="flex justify-center">
                <div class="relative">
                    <div class="absolute inset-0 bg-primary/20 rounded-full animate-ping"></div>
                    <div class="relative bg-card p-4 rounded-full border border-border shadow-2xl">
                        <svg v-if="state.downReason === 'maintenance'" class="w-12 h-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        <svg v-else class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="space-y-4">
                <h2 class="text-2xl font-bold tracking-tight">
                    {{ title }}
                </h2>
                <p class="text-muted-foreground">
                    {{ message }}
                </p>
                
                <!-- Progress/Status -->
                <div class="flex items-center justify-center gap-2 text-sm text-primary font-medium">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Waiting for connection...</span>
                </div>
            </div>
            
            <button @click="forceReload" class="text-xs text-muted-foreground hover:text-primary transition-colors">
                Force Reload
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { systemState } from '../services/SystemMonitor';

const state = systemState;

const title = computed(() => {
    if (state.downReason === 'maintenance') return 'System Updating';
    if (state.downReason === 'chunk_error') return 'New Version Available';
    return 'Connection Interrupted';
});

const message = computed(() => {
    if (state.downReason === 'maintenance') return 'We are currently deploying improvements. The page will reload automatically when ready.';
    if (state.downReason === 'chunk_error') return 'A new version of the app has been deployed. Updating your local cache...';
    return 'We lost connection to the server. Attempting to reconnect...';
});

const forceReload = () => {
    window.location.reload();
};
</script>
