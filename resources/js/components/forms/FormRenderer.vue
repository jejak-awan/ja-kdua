<template>
  <div class="form-renderer">
    <div v-if="loading" class="flex justify-center py-12">
      <Loader2 class="w-8 h-8 animate-spin text-primary" />
    </div>

    <form v-else-if="form" @submit.prevent="handleSubmit" class="space-y-6">
      <div v-if="form.blocks && form.blocks.length > 0">
        <BlockRenderer 
          :blocks="form.blocks" 
          :context="renderingContext"
          mode="view"
        />
      </div>
      <div v-else class="text-center py-12 text-muted-foreground">
        This form has no content.
      </div>

      <div v-if="error" class="p-4 bg-destructive/10 text-destructive rounded-md text-sm">
        {{ error }}
      </div>

      <div v-if="success" class="p-8 text-center space-y-4">
        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto">
          <Check class="w-8 h-8" />
        </div>
        <h3 class="text-xl font-bold">{{ form.success_message || 'Thank you!' }}</h3>
        <p class="text-muted-foreground" v-if="form.redirect_url">Redirecting you in a few seconds...</p>
        <Button v-if="form.redirect_url" @click="handleRedirect" variant="outline">
          Click here if not redirected
        </Button>
      </div>

      <div v-else-if="!isMultiStep" class="flex justify-end pt-4">
        <Button type="submit" :disabled="submitting" size="lg">
          <Loader2 v-if="submitting" class="w-4 h-4 animate-spin mr-2" />
          {{ submitting ? 'Submitting...' : (form.settings?.submit_button_text || 'Submit') }}
        </Button>
      </div>
    </form>

    <div v-else class="text-center py-12 text-muted-foreground">
      Form not found or inactive.
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, reactive, onMounted, provide, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import type { Form } from '@/types/forms'
import BlockRenderer from '@/components/content-renderer/BlockRenderer.vue'
import { Button } from '@/shared/ui'
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js'
import Check from 'lucide-vue-next/dist/esm/icons/check.js'

const props = withDefaults(defineProps<{
  slug: string
  previewMode?: boolean
}>(), {
  previewMode: false
})

const router = useRouter()
const form = ref<Form | null>(null)
const loading = ref(true)
const submitting = ref(false)
const success = ref(false)
const error = ref<string | null>(null)

const formData = reactive<Record<string, unknown>>({})
const currentStep = ref(0)
const hasStarted = ref(false)

const trackEvent = async (event: 'view' | 'start') => {
  if (props.previewMode) return
  try {
    await api.post(`/ja/forms/${props.slug}/track`, { event })
  } catch (_err) {
    // Silent fail for analytics tracking
  }
}

// Provide form state to child blocks
provide('formState', formData)
provide('updateFormValue', (fieldId: string, value: unknown) => {
  formData[fieldId] = value
})

const steps = computed(() => {
    if (!form.value?.blocks) return []
    return form.value.blocks.filter((b: { type: string }) => b.type === 'form_step')
})

const isMultiStep = computed(() => steps.value.length > 0)

provide('currentStep', currentStep)
provide('totalSteps', computed(() => steps.value.length))
provide('goToNextStep', () => {
    if (currentStep.value < steps.value.length - 1) {
        currentStep.value++
        window.scrollTo({ top: 0, behavior: 'smooth' })
    }
})
provide('goToPrevStep', () => {
    if (currentStep.value > 0) {
        currentStep.value--
        window.scrollTo({ top: 0, behavior: 'smooth' })
    }
})
provide('submitButtonText', computed(() => form.value?.settings?.submit_button_text || 'Submit'))

const renderingContext = computed(() => ({
  form: form.value,
  data: formData,
  currentStep: currentStep.value,
  isMultiStep: isMultiStep.value
}))

const fetchForm = async () => {
  loading.value = true
  try {
    const response = await api.get(`/ja/forms/${props.slug}`)
    form.value = response.data
    // Initialize form data with default values if any
  } catch (err: unknown) {
    error.value = 'Failed to load form.'
    logger.error(err instanceof Error ? err.message : String(err), { error: err })
  } finally {
    loading.value = false
  }
}

watch(formData, () => {
  if (!hasStarted.value && Object.keys(formData).length > 0) {
    hasStarted.value = true
    trackEvent('start')
  }
}, { deep: true })

const handleSubmit = async () => {
  if (props.previewMode) {
    alert('Form submission is disabled in preview mode.')
    return
  }

  submitting.value = true
  error.value = null
  
  try {
    await api.post(`/ja/forms/${props.slug}/submit`, formData)
    success.value = true
    
    if (form.value?.redirect_url) {
      setTimeout(() => {
        handleRedirect()
      }, 3000)
    }
  } catch (err: unknown) {
    const errorData = (err as { response?: { data?: Record<string, unknown> } })?.response?.data;
    error.value = typeof errorData?.message === 'string' ? errorData.message : 'An error occurred while submitting the form.'
  } finally {
    submitting.value = false
  }
}

const handleRedirect = () => {
  if (!form.value?.redirect_url) return;

  if (form.value.redirect_url.startsWith('http')) {
    window.location.href = form.value.redirect_url
  } else {
    router.push(form.value.redirect_url)
  }
}

onMounted(async () => {
  await fetchForm()
  if (form.value) {
    trackEvent('view')
  }
})
</script>
