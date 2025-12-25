<template>
  <section class="hero">
    <div class="container">
      <div class="hero-content">
        <h1 class="hero-title">{{ settings.site_title || t('features.frontend.home.title') }}</h1>
        <p class="hero-subtitle">{{ settings.site_tagline || t('features.frontend.home.subtitle') }}</p>
        
        <div class="hero-actions">
          <router-link to="/blog" class="btn btn-primary btn-lg">
            {{ t('features.frontend.home.actions.explore') }}
          </router-link>
          <router-link v-if="!isAuthenticated" to="/register" class="btn btn-outline btn-lg">
            {{ t('features.frontend.home.actions.getStarted') }}
          </router-link>
          <router-link v-else to="/admin" class="btn btn-outline btn-lg">
            {{ t('features.frontend.home.actions.dashboard') }}
          </router-link>
        </div>
        
        <!-- Stats -->
        <div class="hero-stats">
          <div class="stat-item">
            <div class="stat-value">{{ stats.contents }}</div>
            <div class="stat-label">{{ t('features.frontend.home.stats.content') }}</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">{{ stats.visitors }}</div>
            <div class="stat-label">{{ t('features.frontend.home.stats.visitors') }}</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">{{ stats.categories }}</div>
            <div class="stat-label">{{ t('features.frontend.home.stats.categories') }}</div>
          </div>
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
