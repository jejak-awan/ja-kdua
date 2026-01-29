<template>
  <section 
    class="relative py-20 px-4 sm:px-6 lg:px-8 text-white overflow-hidden"
    :class="settings.hero_bg || 'bg-gradient-to-r from-blue-600 to-indigo-700'"
  >
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
      <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
        <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white" />
      </svg>
    </div>

    <div class="relative max-w-7xl mx-auto text-center z-10">
      <div 
        v-if="settings.brand_logo" 
        class="mb-8 flex justify-center"
      >
        <img :src="settings.brand_logo" alt="Logo" class="h-20 w-auto drop-shadow-lg" />
      </div>
      <h1 
        v-else 
        class="text-4xl md:text-6xl font-extrabold tracking-tight mb-4 drop-shadow-sm font-heading"
        :style="{ fontFamily: settings.font_heading }"
      >
        {{ settings.hero_title || 'JA-CMS' }}
      </h1>
      
      <p class="mt-4 max-w-2xl mx-auto text-xl text-blue-100 font-light">
        {{ settings.hero_subtitle || 'Modern Content Management System' }}
      </p>
      
      <div class="mt-10 flex justify-center gap-4">
        <router-link 
          to="/blog" 
          class="px-8 py-3 rounded-lg bg-white text-indigo-600 font-bold hover:bg-muted transition-colors shadow-lg hover:shadow-xl transform hover:-translate-y-1"
        >
          Explore Content
        </router-link>
        <router-link 
            v-if="isAuthenticated"
            to="/admin" 
            class="px-8 py-3 rounded-lg border-2 border-white text-white font-bold hover:bg-muted transition-colors"
        >
            Dashboard
        </router-link>
        <router-link 
            v-else
            to="/login"
            class="px-8 py-3 rounded-lg border-2 border-white text-white font-bold hover:bg-muted transition-colors"
        >
            Get Started
        </router-link>
      </div>

      <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
        <div class="p-6 bg-white/10 rounded-xl backdrop-blur-sm border border-white/10">
          <div class="text-4xl font-bold mb-2">{{ stats.contents || 0 }}</div>
          <div class="text-blue-200 text-sm uppercase tracking-wider">Published Content</div>
        </div>
        <div class="p-6 bg-white/10 rounded-xl backdrop-blur-sm border border-white/10">
          <div class="text-4xl font-bold mb-2">{{ stats.visitors || 0 }}</div>
          <div class="text-blue-200 text-sm uppercase tracking-wider">Total Visitors</div>
        </div>
        <div class="p-6 bg-white/10 rounded-xl backdrop-blur-sm border border-white/10">
          <div class="text-4xl font-bold mb-2">{{ stats.categories || 0 }}</div>
          <div class="text-blue-200 text-sm uppercase tracking-wider">Categories</div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
// import { useI18n } from 'vue-i18n'

interface HeroSettings {
    hero_bg?: string;
    brand_logo?: string;
    hero_title?: string;
    hero_subtitle?: string;
    font_heading?: string;
}

interface HeroStats {
    contents: number;
    visitors: number;
    categories: number;
}

interface Props {
  settings?: HeroSettings;
  stats?: HeroStats;
  isAuthenticated?: boolean;
}

withDefaults(defineProps<Props>(), {
  settings: () => ({}),
  stats: () => ({ contents: 0, visitors: 0, categories: 0 }),
  isAuthenticated: false
})
</script>


