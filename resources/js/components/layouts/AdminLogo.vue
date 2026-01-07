<template>
  <div 
    class="flex items-center gap-2 select-none group"
    :class="[minimized ? 'flex-col justify-center' : 'flex-row']"
  >
    <!-- Logo Container -->
    <div 
      class="relative flex items-center justify-center overflow-hidden"
      :class="[
        minimized ? 'w-9 h-9' : 'w-9 h-9'
      ]"
    >
      <!-- Option A: User uploaded Logo Image -->
      <img 
        v-if="siteLogo" 
        :src="siteLogo" 
        :alt="siteName"
        class="w-full h-full object-contain rounded-lg"
      />

      <!-- Option B: Stylized Box Fallback -->
      <div 
        v-else
        class="w-full h-full flex flex-col items-center justify-center border-2 border-primary rounded-lg shadow-sm"
      >
        <span class="text-[13px] leading-none font-[900] text-primary tracking-tighter">JA</span>
        <span class="text-[7.5px] leading-tight font-bold text-primary/80 tracking-widest -mt-0.5">CMS</span>
      </div>
    </div>

    <!-- Site Branding (Hidden when minimized) -->
    <div v-if="!minimized" class="flex flex-col ml-1">
      <span class="text-sm font-black tracking-tight text-foreground leading-none">{{ siteName }}</span>
      <span class="text-[10px] font-medium text-muted-foreground leading-none mt-0.5">{{ siteVersion }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useCmsStore } from '../../stores/cms';

const props = defineProps({
  minimized: {
    type: Boolean,
    default: false
  }
});

const cmsStore = useCmsStore();
const siteName = computed(() => cmsStore.siteSettings?.site_name || 'JA CMS');
const siteVersion = computed(() => cmsStore.siteSettings?.site_version || 'v1.0 Janari');
const siteLogo = computed(() => cmsStore.siteSettings?.site_logo || '');
</script>

<style scoped>
.group:hover .bg-primary {
  filter: brightness(1.1);
  transition: all 300ms ease-in-out;
}
</style>
