<template>
    <div class="flex-1 bg-muted/50 h-full overflow-hidden flex flex-col relative transition-colors">
    <!-- Device Toolbar -->
    <div class="h-16 bg-background/80 backdrop-blur-sm border-b flex items-center justify-between px-6 shadow-sm z-20 shrink-0">
        <div class="flex items-center gap-2">
            <button 
                v-for="mode in deviceModes" 
                :key="mode.id" 
                @click="activeDevice = mode.id"
                :class="activeDevice === mode.id ? 'bg-primary/10 text-primary ring-1 ring-primary/20' : 'text-muted-foreground hover:bg-muted'"
                class="p-2 rounded-md transition-colors flex items-center gap-2"
                :title="mode.label"
            >
                <component :is="mode.icon" class="w-5 h-5" />
                <span class="text-xs font-medium hidden sm:block">{{ mode.label }}</span>
            </button>
        </div>

        <button 
            @click="refreshPreview"
            :disabled="isRefreshing"
            class="p-2 text-muted-foreground hover:text-primary hover:bg-muted rounded-md transition-colors flex items-center gap-2 bg-background border hover:border-primary/50"
            title="Refresh Preview"
        >
            <svg class="w-4 h-4" :class="{'animate-spin': isRefreshing}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
            <span class="text-xs font-medium">Refresh</span>
        </button>
    </div>

        <div class="flex-1 overflow-auto p-4 md:p-8 flex justify-center items-start custom-scrollbar">
            <!-- Device Container -->
            <div 
                class="relative bg-background shadow-2xl transition-[width] duration-500 ease-in-out border ring-1 ring-border/10 overflow-hidden flex flex-col" 
                :class="[
                    previewClasses, 
                    activeDevice === 'desktop' ? 'rounded-xl' : 'rounded-[2.5rem] border-[14px] border-slate-900 dark:border-slate-800'
                ]"
                :style="previewStyles"
            >
                 <!-- Mobile/Tablet Status Bar (Safe Area) -->
                 <div 
                    v-if="activeDevice !== 'desktop'" 
                    class="h-7 w-full shrink-0 z-20 transition-colors duration-500 flex items-center justify-between px-6"
                    :class="'bg-slate-900 dark:bg-slate-800'"
                 >
                    <!-- Fake standard status bar elements -->
                    <div class="text-[10px] font-medium text-slate-400">9:41</div>
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 text-slate-400"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2z" /></svg></div>
                        <div class="w-3 h-3 text-slate-400"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.67 4H14V2h-4v2H8.33C7.6 4 7 4.6 7 5.33v15.33C7 21.4 7.6 22 8.33 22h7.33c.74 0 1.34-.6 1.34-1.33V5.33C17 4.6 16.4 4 15.67 4z" /></svg></div>
                    </div>
                 </div>

                 <ThemePreview
                    ref="themePreviewRef"
                    :theme="previewTheme"
                    :preview-url="previewUrl"
                    class="w-full h-full bg-background flex-1"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import MonitorIcon from 'lucide-vue-next/dist/esm/icons/monitor.js';
import TabletIcon from 'lucide-vue-next/dist/esm/icons/tablet.js';
import SmartphoneIcon from 'lucide-vue-next/dist/esm/icons/smartphone.js';
import ThemePreview from '../../ThemePreview.vue';
import type { Theme } from '@/types/theme';

defineProps<{
    previewTheme: Theme;
    previewUrl?: string;
}>();

const themePreviewRef = ref<any>(null);
const isRefreshing = ref(false);

const refreshPreview = async () => {
    isRefreshing.value = true;
    if (themePreviewRef.value && themePreviewRef.value.refreshPreview) {
        await themePreviewRef.value.refreshPreview();
    }
    // Artificial delay if refresh triggers too fast (just for UX feedback)
    setTimeout(() => {
        isRefreshing.value = false;
    }, 800);
};

const activeDevice = ref<'desktop' | 'tablet' | 'mobile'>('desktop');
const deviceModes = [
    { id: 'desktop' as const, label: 'Desktop', icon: MonitorIcon },
    { id: 'tablet' as const, label: 'Tablet', icon: TabletIcon },
    { id: 'mobile' as const, label: 'Mobile', icon: SmartphoneIcon },
];

const previewStyles = computed(() => {
    switch (activeDevice.value) {
        case 'mobile':
            return { width: '375px', height: '100%' };
        case 'tablet':
            return { width: '768px', height: '100%' };
        default:
            return { width: '100%', height: '100%' };
    }
});

const previewClasses = computed(() => {
    return activeDevice.value === 'desktop' ? 'w-full h-full' : 'shadow-2xl';
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: hsl(var(--muted-foreground) / 0.3);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: hsl(var(--muted-foreground) / 0.5);
}
</style>
