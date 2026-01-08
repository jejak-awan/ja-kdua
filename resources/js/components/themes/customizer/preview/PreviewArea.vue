<template>
    <div class="flex-1 bg-muted/50 h-full overflow-hidden flex flex-col relative transition-colors">
         <!-- Device Toolbar -->
         <div class="h-16 bg-background/80 backdrop-blur-sm border-b flex items-center justify-center gap-2 shadow-sm z-20 shrink-0">
             <button 
                 v-for="mode in deviceModes" 
                 :key="mode.id"
                 @click="activeDevice = mode.id"
                 class="p-2 rounded-md transition-all flex items-center gap-2"
                 :class="activeDevice === mode.id ? 'bg-primary/10 text-primary ring-1 ring-primary/20' : 'text-muted-foreground hover:bg-muted'"
                 :title="mode.label"
             >
                 <component :is="mode.icon" class="w-5 h-5" />
                 <span class="text-xs font-medium hidden sm:block">{{ mode.label }}</span>
             </button>
         </div>

        <div class="flex-1 overflow-auto p-4 md:p-8 flex justify-center items-start custom-scrollbar">
            <div 
                class="relative bg-background shadow-2xl transition-all duration-500 ease-in-out border ring-1 ring-border/10 overflow-hidden" 
                :class="[
                    previewClasses, 
                    activeDevice === 'desktop' ? 'rounded-xl' : 'rounded-[2rem] border-[12px] border-slate-900'
                ]"
                :style="previewStyles"
            >
                 <ThemePreview
                    :theme="previewTheme"
                    :preview-url="previewUrl"
                    class="w-full h-full bg-background"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ThemePreview from '../../ThemePreview.vue';

defineProps({
    previewTheme: { type: Object, required: true },
    previewUrl: { type: String, default: '/' },
});

// Device Icon Components
const MonitorIcon = {
  template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="2" y="3" width="20" height="14" rx="2" ry="2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="8" y1="21" x2="16" y2="21" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="17" x2="12" y2="21" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`
};
const TabletIcon = {
  template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="4" y="2" width="16" height="20" rx="2" ry="2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="18" x2="12.01" y2="18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`
};
const SmartphoneIcon = {
  template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="5" y="2" width="14" height="20" rx="2" ry="2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="18" x2="12.01" y2="18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`
};

const activeDevice = ref('desktop');
const deviceModes = [
    { id: 'desktop', label: 'Desktop', icon: MonitorIcon },
    { id: 'tablet', label: 'Tablet', icon: TabletIcon },
    { id: 'mobile', label: 'Mobile', icon: SmartphoneIcon },
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
