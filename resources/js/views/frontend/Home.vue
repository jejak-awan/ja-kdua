<template>
  <div class="homepage">
    <!-- Hero Section (Dynamic or Default) -->
    <component 
      :is="DynamicHero" 
      v-if="DynamicHero"
      v-bind="{ settings, stats, isAuthenticated }"
    />
    <section v-else class="hero">
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
    
    <!-- Featured Content -->
    <section v-if="featuredPosts.length > 0" class="section">
      <div class="container">
        <h2 class="section-title">{{ t('features.frontend.home.sections.featured') }}</h2>
        <div class="posts-grid">
          <component 
            :is="DynamicPostCard || PostCard"
            v-for="post in featuredPosts" 
            :key="post.id" 
            :post="post"
          />
        </div>
      </div>
    </section>
    
    <!-- Recent Posts -->
    <section v-if="recentPosts.length > 0" class="section section-gray">
      <div class="container">
        <h2 class="section-title">{{ t('features.frontend.home.sections.recent') }}</h2>
        <div class="posts-grid">
          <component 
            :is="DynamicPostCard || PostCard"
            v-for="post in recentPosts" 
            :key="post.id" 
            :post="post"
          />
        </div>
        
        <div class="section-actions">
          <router-link to="/blog" class="btn btn-primary">
            {{ t('features.frontend.home.actions.viewAll') }}
          </router-link>
        </div>
      </div>
    </section>
    
    <!-- Newsletter Section -->
    <section class="newsletter-section section-gray">
      <div class="container">
        <NewsletterWidget
          variant="default"
          :title="t('features.frontend.newsletter.title')"
          :description="t('features.frontend.newsletter.description')"
          :button-text="t('features.frontend.newsletter.button')"
        />
      </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2 class="cta-title">{{ t('features.frontend.home.sections.cta.title') }}</h2>
          <p class="cta-text">{{ t('features.frontend.home.sections.cta.text') }}</p>
          <div class="cta-actions">
            <router-link to="/register" class="btn btn-primary btn-lg">{{ t('features.frontend.home.actions.startReading') }}</router-link>
            <router-link to="/contact" class="btn btn-outline-white btn-lg">{{ t('features.frontend.home.actions.contactUs') }}</router-link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTheme } from '@/composables/useTheme'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import PostCard from '@/components/theme/PostCard.vue'
import NewsletterWidget from '@/components/NewsletterWidget.vue'

const authStore = useAuthStore()
const { t } = useI18n()
const { activeTheme, themeSettings } = useTheme()
const { loadComponent } = useThemeComponents()

const DynamicHero = ref(null)
const DynamicPostCard = ref(null)

const featuredPosts = ref([])
const recentPosts = ref([])
const stats = ref({
  contents: 0,
  visitors: 0,
  categories: 0
})

const settings = computed(() => themeSettings.value || {})
const isAuthenticated = computed(() => authStore.isAuthenticated)

// Fetch homepage data
onMounted(async () => {
  await Promise.all([
    fetchFeaturedPosts(),
    fetchRecentPosts(),
    fetchStats()
  ])

  if (activeTheme.value) {
    const hero = await loadComponent('heroes', 'default')
    const card = await loadComponent('cards', 'post')
    
    if (hero) DynamicHero.value = markRaw(hero)
    if (card) DynamicPostCard.value = markRaw(card)
  }
})

const fetchFeaturedPosts = async () => {
  try {
    const response = await api.get('/cms/contents', {
      params: {
        is_featured: true,
        status: 'published',
        type: 'post',
        limit: 3
      }
    })
    featuredPosts.value = response.data.data || []
  } catch (error) {
    console.error('Failed to fetch featured posts:', error)
  }
}

const fetchRecentPosts = async () => {
  try {
    const response = await api.get('/cms/contents', {
      params: {
        status: 'published',
        type: 'post',
        sort: '-published_at',
        limit: 6
      }
    })
    recentPosts.value = response.data.data || []
  } catch (error) {
    console.error('Failed to fetch recent posts:', error)
  }
}

const fetchStats = async () => {
  try {
    // Fetch stats from respective endpoints
    const [contentsRes, categoriesRes] = await Promise.all([
      api.get('/cms/contents', { params: { status: 'published', type: 'post', per_page: 1 } }),
      api.get('/cms/categories')
    ])
    
    // Get visitors count - for now use a fallback
    let visitorsCount = 3789; // Default from sample data
    try {
      const visitorsRes = await api.get('/admin/cms/analytics/visitors/count')
      visitorsCount = visitorsRes.data.count || visitorsCount
    } catch (e) {
      // Fallback to default if endpoint doesn't exist
    }
    
    stats.value = {
      contents: contentsRes.data.meta?.total || contentsRes.data.data?.length || 0,
      visitors: visitorsCount,
      categories: categoriesRes.data.data?.length || 0
    }
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  }
}
</script>

<style scoped>
/* Hero Section */
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

/* Section Styles */
.section {
  padding: 4rem 0;
}

.section-gray {
  background-color: #f9fafb;
}

.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1rem;
}

.section-title {
  text-align: center;
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 3rem;
  color: var(--theme-text-color, #1f2937);
}

.posts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
}

.section-actions {
  text-align: center;
  margin-top: 3rem;
}

/* CTA Section */
.cta-section {
  background: linear-gradient(135deg, var(--theme-primary-color, #2563eb) 0%, var(--theme-secondary-color, #1e40af) 100%);
  color: white;
  padding: 5rem 0;
  text-align: center;
}

.cta-content {
  max-width: 700px;
  margin: 0 auto;
}

.cta-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.cta-text {
  font-size: 1.25rem;
  margin-bottom: 2rem;
  opacity: 0.95;
}

.cta-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
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
  background-color: var(--theme-primary-color, #2563eb);
  color: white;
}

.btn-primary:hover {
  background-color: var(--theme-secondary-color, #1e40af);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-outline {
  background: transparent;
  border-color: var(--theme-primary-color, #2563eb);
  color: var(--theme-primary-color, #2563eb);
}

.btn-outline:hover {
  background-color: var(--theme-primary-color, #2563eb);
  color: white;
}

.btn-outline-white {
  background: transparent;
  border-color: white;
  color: white;
}

.btn-outline-white:hover {
  background-color: white;
  color: var(--theme-primary-color, #2563eb);
}

@media (max-width: 768px) {
  .hero {
    padding: 4rem 0;
  }
  
  .hero-stats {
    grid-template-columns: 1fr;
  }
  
  .posts-grid {
    grid-template-columns: 1fr;
  }
}
</style>
