<template>
  <div class="newsletter-widget" :class="variant">
    <div class="newsletter-content">
      <div v-if="showIcon" class="newsletter-icon">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
      </div>
      
      <h3 v-if="title || t('features.frontend.newsletter.title')" class="newsletter-title">{{ title || t('features.frontend.newsletter.title') }}</h3>
      <p v-if="description || t('features.frontend.newsletter.description')" class="newsletter-description">{{ description || t('features.frontend.newsletter.description') }}</p>
      
      <form @submit.prevent="handleSubscribe" class="newsletter-form">
        <div class="form-group">
          <input
            v-model="email"
            type="email"
            :placeholder="t('features.frontend.newsletter.placeholder')"
            required
            :disabled="loading || success"
            class="newsletter-input"
            :class="{ 'error': error }"
          />
          <button
            type="submit"
            :disabled="loading || success"
            class="newsletter-button"
          >
            <span v-if="loading">
              <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </span>
            <span v-else-if="success">✓</span>
            <span v-else>{{ buttonText || t('features.frontend.newsletter.button') }}</span>
          </button>
        </div>
        
        <div v-if="error" class="error-message">
          {{ error }}
        </div>
        
        <div v-if="success" class="success-message">
          {{ successMessage || t('features.frontend.newsletter.success') }}
        </div>
      </form>
      
      <p v-if="showPrivacy" class="newsletter-privacy">
        {{ t('features.frontend.newsletter.privacy') }}
        <a href="/privacy" class="privacy-link">{{ t('features.frontend.newsletter.privacyLink') }}</a>.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';

type NewsletterVariant = 'default' | 'compact' | 'inline';

const { t } = useI18n();

const props = withDefaults(defineProps<{
  variant?: NewsletterVariant;
  title?: string;
  description?: string;
  buttonText?: string;
  showIcon?: boolean;
  showPrivacy?: boolean;
  successMessage?: string;
}>(), {
  variant: 'default' as const,
  showIcon: true,
  showPrivacy: true,
});

const email = ref('');
const loading = ref(false);
const success = ref(false);
const error = ref('');

const handleSubscribe = async () => {
  if (!email.value) return;
  
  loading.value = true;
  error.value = '';
  success.value = false;
  
  try {
    const response = await api.post('/cms/newsletter/subscribe', {
      email: email.value,
    });
    
    if (response.data.success) {
      success.value = true;
      email.value = '';
      
      // Reset success message after 5 seconds
      setTimeout(() => {
        success.value = false;
      }, 5000);
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || t('common.messages.error.generic');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.newsletter-widget {
  width: 100%;
}

.newsletter-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.newsletter-icon {
  display: flex;
  justify-content: center;
  color: var(--theme-primary-color, #2563eb);
}

.newsletter-title {
  font-size: 1.5rem;
  font-weight: 700;
  text-align: center;
  color: var(--theme-text-color, #1f2937);
  margin: 0;
}

.dark .newsletter-title {
  color: #f9fafb;
}

.newsletter-description {
  text-align: center;
  color: #6b7280;
  margin: 0;
}

.dark .newsletter-description {
  color: #9ca3af;
}

.newsletter-form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.form-group {
  display: flex;
  gap: 0.5rem;
}

.newsletter-input {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.2s;
  background-color: white;
  color: #1f2937;
}

.dark .newsletter-input {
  background-color: #1f2937;
  border-color: #374151;
  color: #f9fafb;
}

.newsletter-input:focus {
  outline: none;
  border-color: var(--theme-primary-color, #2563eb);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.newsletter-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.newsletter-input.error {
  border-color: #ef4444;
}

.newsletter-button {
  padding: 0.75rem 1.5rem;
  background-color: var(--theme-primary-color, #2563eb);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 120px;
}

.newsletter-button:hover:not(:disabled) {
  background-color: var(--theme-secondary-color, #1e40af);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.newsletter-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  color: #ef4444;
  font-size: 0.875rem;
  text-align: center;
}

.success-message {
  color: #10b981;
  font-size: 0.875rem;
  text-align: center;
  font-weight: 600;
}

.newsletter-privacy {
  font-size: 0.75rem;
  color: #9ca3af;
  text-align: center;
  margin: 0;
}

.dark .newsletter-privacy {
  color: #6b7280;
}

.privacy-link {
  color: var(--theme-primary-color, #2563eb);
  text-decoration: underline;
}

.privacy-link:hover {
  text-decoration: none;
}

/* Variants */
.newsletter-widget.compact .newsletter-title {
  font-size: 1.25rem;
}

.newsletter-widget.compact .newsletter-description {
  font-size: 0.875rem;
}

.newsletter-widget.inline .form-group {
  flex-direction: row;
}

.newsletter-widget.inline .newsletter-content {
  flex-direction: row;
  align-items: center;
  gap: 1rem;
}

.newsletter-widget.inline .newsletter-title,
.newsletter-widget.inline .newsletter-description {
  text-align: left;
  margin: 0;
}

@media (max-width: 640px) {
  .form-group {
    flex-direction: column;
  }
  
  .newsletter-button {
    width: 100%;
  }
  
  .newsletter-widget.inline .newsletter-content {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>

