<template>
  <footer class="footer">
    <div class="container">
      <div class="footer-content">
        <!-- About Section -->
        <div class="footer-column">
          <h3 class="footer-heading">About</h3>
          <p class="footer-text">{{ settings.footer_about || 'Modern, clean, and powerful content management system built for the web.' }}</p>
          
          <!-- Social Links -->
          <div v-if="settings.social_links" class="social-links">
            <a v-for="(url, platform) in settings.social_links" :key="platform" :href="url" target="_blank" rel="noopener noreferrer" class="social-link">
              <i class="bi" :class="`bi-${platform}`"></i>
            </a>
          </div>
        </div>
        
        <!-- Resources -->
        <div class="footer-column">
          <h3 class="footer-heading">Resources</h3>
          <ul class="footer-links">
            <li><router-link to="/blog">Blog</router-link></li>
            <li><a href="#">Documentation</a></li>
            <li><a href="#">Support</a></li>
            <li><a href="#">FAQ</a></li>
          </ul>
        </div>
        
        <!-- Categories -->
        <div class="footer-column">
          <h3 class="footer-heading">Categories</h3>
          <ul class="footer-links">
            <li v-for="category in categories" :key="category.id">
              <router-link :to="`/blog?category=${category.slug}`">{{ category.name }}</router-link>
            </li>
          </ul>
        </div>
        
        <!-- Follow Us -->
        <div class="footer-column">
          <h3 class="footer-heading">Follow Us</h3>
          <ul class="footer-links">
            <li><a href="#" target="_blank">Facebook</a></li>
            <li><a href="#" target="_blank">Twitter</a></li>
            <li><a href="#" target="_blank">Instagram</a></li>
            <li><a href="#" target="_blank">LinkedIn</a></li>
          </ul>
        </div>
      </div>
      
      <!-- Footer Bottom -->
      <div class="footer-bottom">
        <p class="footer-copyright">
          {{ settings.footer_text || `© ${new Date().getFullYear()} JA-CMS. All rights reserved.` }}
        </p>
        <p class="footer-powered">
          Powered by <strong>JA-CMS</strong> - Modern Content Management System
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useTheme } from '@/composables/useTheme'
import api from '@/services/api'

const { themeSettings } = useTheme()
const categories = ref([])

const settings = computed(() => themeSettings.value || {})

// Fetch categories for footer
onMounted(async () => {
  try {
    const response = await api.get('/cms/categories', {
      params: { limit: 5 }
    })
    categories.value = response.data.data || []
  } catch (error) {
    console.error('Failed to load categories:', error)
  }
})
</script>

<style scoped>
.footer {
  background-color: #1f2937;
  color: #d1d5db;
  padding: 3rem 0 1rem;
  margin-top: 4rem;
}

.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1rem;
}

.footer-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.footer-column {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.footer-heading {
  font-size: 1.125rem;
  font-weight: 600;
  color: white;
  margin: 0 0 0.5rem 0;
}

.footer-text {
  font-size: 0.875rem;
  line-height: 1.6;
  margin: 0;
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.footer-links a {
  color: #d1d5db;
  text-decoration: none;
  font-size: 0.875rem;
  transition: color 0.2s;
}

.footer-links a:hover {
  color: var(--theme-primary-color, #3b82f6);
}

.social-links {
  display: flex;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.social-link {
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 0.375rem;
  color: white;
  text-decoration: none;
  transition: all 0.2s;
}

.social-link:hover {
  background-color: var(--theme-primary-color, #3b82f6);
  transform: translateY(-2px);
}

.footer-bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.footer-copyright,
.footer-powered {
  font-size: 0.875rem;
  margin: 0;
}

.footer-powered strong {
  color: var(--theme-primary-color, #3b82f6);
}

@media (max-width: 768px) {
  .footer-content {
    grid-template-columns: 1fr;
  }
  
  .footer-bottom {
    flex-direction: column;
    text-align: center;
  }
}
</style>
