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

      <div v-else class="flex justify-end pt-4">
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
import { ref, reactive, onMounted, provide, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import type { Form } from '@/types/forms'
import BlockRenderer from '@/components/content-renderer/BlockRenderer.vue'
import { Button } from '@/shared/ui'
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js'
import Check from 'lucide-vue-next/dist/esm/icons/check.js'

const props = defineProps<{
  slug: string
}>()

const router = useRouter()
const form = ref<Form | null>(null)
const loading = ref(true)
const submitting = ref(false)
const success = ref(false)
const error = ref<string | null>(null)

const formData = reactive<Record<string, any>>({})

// Provide form state to child blocks
provide('formState', formData)
provide('updateFormValue', (fieldId: string, value: any) => {
  formData[fieldId] = value
})

const renderingContext = computed(() => ({
  form: form.value,
  data: formData
}))

const fetchForm = async () => {
  loading.value = true
  try {
    const response = await api.get(`/cms/forms/${props.slug}`)
    form.value = response.data
    // Initialize form data with default values if any
  } catch (err: any) {
    error.value = 'Failed to load form.'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const handleSubmit = async () => {
  submitting.value = true
  error.value = null
  
  try {
    await api.post(`/cms/forms/${props.slug}/submit`, formData)
    success.value = true
    
    if (form.value?.redirect_url) {
      setTimeout(() => {
        handleRedirect()
      }, 3000)
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'An error occurred while submitting the form.'
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

onMounted(fetchForm)
</script>
