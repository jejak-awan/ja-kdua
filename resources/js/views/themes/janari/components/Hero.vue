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
          class="px-8 py-3 rounded-lg bg-white text-indigo-600 font-bold hover:bg-gray-50 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1"
        >
          Explore Content
        </router-link>
        <router-link 
            v-if="isAuthenticated"
            to="/admin" 
            class="px-8 py-3 rounded-lg border-2 border-white text-white font-bold hover:bg-white/10 transition-all"
        >
            Dashboard
        </router-link>
        <router-link 
            v-else
            to="/login"
            class="px-8 py-3 rounded-lg border-2 border-white text-white font-bold hover:bg-white/10 transition-all"
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

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
  settings: {
    type: Object,
    default: () => ({})
  },
  stats: {
    type: Object,
    default: () => ({ contents: 0, visitors: 0, categories: 0 })
  },
  isAuthenticated: {
    type: Boolean,
    default: false
  }
})
</script>

<style scoped>
.hero {
  background: linear-gradient(135deg, var(--theme-primary-color, #2563eb) 0%, var(--theme-secondary-color, #1e40af) 100%);
  color: white;
  padding: 6rem 0;
  text-align: center;
}

.hero-content {
  max-width: 800px;
  margin: 0 auto;
}

.hero-title {
  font-size: clamp(2.5rem, 5vw, 4rem);
  font-weight: 800;
  margin-bottom: 1.5rem;
  line-height: 1.2;
}

.hero-subtitle {
  font-size: clamp(1.125rem, 2vw, 1.5rem);
  margin-bottom: 2rem;
  opacity: 0.95;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 3rem;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
  padding-top: 3rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-item {
  text-align: center;
}

.stat-value {
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
}

.stat-label {
  opacity: 0.9;
  font-size: 0.875rem;
}

/* Button Styles */
.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
  text-decoration: none;
  display: inline-block;
  transition: all 0.2s;
  border: 2px solid transparent;
  cursor: pointer;
}

.btn-lg {
  padding: 1rem 2rem;
  font-size: 1.125rem;
}

.btn-primary {
  background-color: white;
  color: #2563eb;
}

.btn-primary:hover {
  background-color: #f3f4f6;
  transform: translateY(-2px);
}

.btn-outline {
  background: transparent;
  border-color: white;
  color: white;
}

.btn-outline:hover {
  background-color: white;
  color: #2563eb;
}

@media (max-width: 768px) {
  .hero-stats {
    grid-template-columns: 1fr;
  }
}
</style>
