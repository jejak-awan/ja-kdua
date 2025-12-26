<template>
  <header class="header" :class="{ 'scrolled': isScrolled }">
    <div class="container">
      <div class="header-container">
        <!-- Logo -->
        <router-link to="/" class="header-logo">
          <img v-if="settings.site_logo_url" :src="settings.site_logo_url" :alt="settings.site_title" class="logo-image">
          <span v-else class="logo-text">{{ settings.site_title || 'JA-CMS' }}</span>
        </router-link>
        
        <!-- Navigation -->
        <nav class="header-nav" :class="{ 'active': isMobileMenuOpen }">
          <router-link to="/" class="nav-link" exact-active-class="active">{{ t('features.frontend.nav.home') }}</router-link>
          <router-link to="/blog" class="nav-link" active-class="active">{{ t('features.frontend.nav.blog') }}</router-link>
          <router-link to="/about" class="nav-link" active-class="active">{{ t('features.frontend.nav.about') }}</router-link>
          <router-link to="/contact" class="nav-link" active-class="active">{{ t('features.frontend.nav.contact') }}</router-link>
        </nav>
        
        <!-- Actions -->
        <div class="header-actions">
          <!-- Language Switcher -->
          <LanguageSwitcher />
          
          <button @click="toggleSearch" class="btn-icon" aria-label="Search">
            <i class="bi bi-search"></i>
          </button>
          
          <template v-if="isAuthenticated">
            <router-link to="/admin" class="btn btn-secondary btn-sm">{{ t('features.frontend.nav.dashboard') }}</router-link>
            <button @click="handleLogout" class="btn btn-outline btn-sm">{{ t('features.frontend.nav.logout') }}</button>
          </template>
          <template v-else>
            <router-link to="/login" class="btn btn-outline btn-sm">{{ t('features.frontend.nav.login') }}</router-link>
            <router-link to="/register" class="btn btn-primary btn-sm">{{ t('features.frontend.nav.register') }}</router-link>
          </template>
        </div>
        
        <!-- Mobile Menu Toggle -->
        <button @click="toggleMobileMenu" class="header-menu-toggle" aria-label="Toggle menu">
          <i class="bi" :class="isMobileMenuOpen ? 'bi-x' : 'bi-list'"></i>
        </button>
      </div>
    </div>
    
    <!-- Search Modal -->
    <Teleport to="body">
      <div v-if="isSearchOpen" class="search-modal" @click="toggleSearch">
        <div class="search-modal-content" @click.stop>
          <input
            ref="searchInput"
            v-model="searchQuery"
            type="text"
            :placeholder="t('common.actions.search') + '...'"
            class="search-input"
            @keyup.enter="handleSearch"
          >
          <button @click="handleSearch" class="btn btn-primary">{{ t('common.actions.search') }}</button>
        </div>
      </div>
    </Teleport>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTheme } from '@/composables/useTheme'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'

const router = useRouter()
const authStore = useAuthStore()
const { t } = useI18n()
const { activeTheme, themeSettings } = useTheme()

const isScrolled = ref(false)
const isMobileMenuOpen = ref(false)
const isSearchOpen = ref(false)
const searchQuery = ref('')
const searchInput = ref(null)

const settings = computed(() => themeSettings.value || {})
const isAuthenticated = computed(() => authStore.isAuthenticated)

// Handle scroll
const handleScroll = () => {
  isScrolled.value = window.scrollY > 50
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const toggleSearch = async () => {
  isSearchOpen.value = !isSearchOpen.value
  if (isSearchOpen.value) {
    await nextTick()
    searchInput.value?.focus()
  }
}

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ name: 'search', query: { q: searchQuery.value } })
    isSearchOpen.value = false
    searchQuery.value = ''
  }
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/')
}
</script>

<style scoped>
/* Header Styles */
.header {
  background-color: var(--theme-background-color, #ffffff);
  border-bottom: 1px solid var(--theme-border-color, #e5e7eb);
  position: sticky;
  top: 0;
  z-index: 50;
  transition: all 0.3s ease;
}

.header.scrolled {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1rem;
}

.header-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 4rem;
  gap: 2rem;
}

.header-logo {
  display: flex;
  align-items: center;
  text-decoration: none;
  font-weight: 700;
  font-size: 1.5rem;
  color: var(--theme-primary-color, #2563eb);
}

.logo-image {
  height: 2.5rem;
  width: auto;
}

.header-nav {
  display: flex;
  gap: 2rem;
  align-items: center;
}

.nav-link {
  text-decoration: none;
  color: var(--theme-text-color, #1f2937);
  font-weight: 500;
  transition: color 0.2s;
}

.nav-link:hover,
.nav-link.active {
  color: var(--theme-primary-color, #2563eb);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.btn-icon {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: var(--theme-text-color, #1f2937);
  padding: 0.5rem;
  border-radius: 0.375rem;
  transition: background-color 0.2s;
}

.btn-icon:hover {
  background-color: var(--theme-background-secondary, #f3f4f6);
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 600;
  text-decoration: none;
  display: inline-block;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
}

.btn-sm {
  padding: 0.375rem 0.875rem;
  font-size: 0.875rem;
}

.btn-primary {
  background-color: var(--theme-primary-color, #2563eb);
  color: white;
}

.btn-primary:hover {
  background-color: var(--theme-secondary-color, #1e40af);
}

.btn-secondary {
  background-color: var(--theme-secondary-color, #1e40af);
  color: white;
}

.btn-outline {
  background: transparent;
  border: 2px solid var(--theme-primary-color, #2563eb);
  color: var(--theme-primary-color, #2563eb);
}

.btn-outline:hover {
  background-color: var(--theme-primary-color, #2563eb);
  color: white;
}

.header-menu-toggle {
  display: none;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
}

/* Search Modal */
.search-modal {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.search-modal-content {
  background: white;
  padding: 2rem;
  border-radius: 0.5rem;
  width: 90%;
  max-width: 600px;
  display: flex;
  gap: 1rem;
}

.search-input {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 2px solid var(--theme-border-color, #e5e7eb);
  border-radius: 0.375rem;
  font-size: 1rem;
}

.search-input:focus {
  outline: none;
  border-color: var(--theme-primary-color, #2563eb);
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .header-nav {
    position: fixed;
    top: 4rem;
    left: 0;
    right: 0;
    background: white;
    flex-direction: column;
    padding: 1rem;
    border-bottom: 1px solid var(--theme-border-color, #e5e7eb);
    display: none;
  }
  
  .header-nav.active {
    display: flex;
  }
  
  .header-actions {
    display: none;
  }
  
  .header-menu-toggle {
    display: block;
  }
}
</style>
