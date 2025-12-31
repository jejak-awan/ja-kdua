<template>
    <div class="min-h-screen bg-background py-16 px-4">
        <div class="max-w-4xl mx-auto bg-card rounded-xl shadow-lg overflow-hidden border border-border">
            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="p-8 bg-primary text-primary-foreground">
                    <h1 class="text-3xl font-bold mb-4">{{ $t('features.frontend.contact.title') }}</h1>
                    <p class="text-primary-foreground/80 mb-8">
                        {{ $t('features.frontend.contact.subtitle') }}
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-foreground/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>contact@ja-cms.com</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-foreground/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Jakarta, Indonesia</span>
                        </div>
                    </div>
                </div>
                <div class="p-8 bg-card">
                    <form @submit.prevent="submitContact" class="space-y-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-foreground">
                                {{ $t('common.labels.name') }}
                            </label>
                            <input 
                                v-model="form.name"
                                type="text" 
                                class="w-full px-4 py-2 border border-input rounded-lg bg-background text-foreground focus:ring-2 focus:ring-ring focus:border-ring outline-none transition-all"
                                :class="{ 'border-destructive focus:ring-destructive focus:border-destructive': errors.name }"
                                :placeholder="$t('features.frontend.contact.namePlaceholder')"
                            >
                            <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name[0] }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-foreground">
                                {{ $t('common.labels.email') }}
                            </label>
                            <input 
                                v-model="form.email"
                                type="email" 
                                class="w-full px-4 py-2 border border-input rounded-lg bg-background text-foreground focus:ring-2 focus:ring-ring focus:border-ring outline-none transition-all"
                                :class="{ 'border-destructive focus:ring-destructive focus:border-destructive': errors.email }"
                                :placeholder="$t('features.frontend.contact.emailPlaceholder')"
                            >
                            <p v-if="errors.email" class="text-sm text-destructive">{{ errors.email[0] }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-foreground">
                                {{ $t('features.frontend.contact.message') }}
                            </label>
                            <textarea 
                                v-model="form.message"
                                rows="4" 
                                class="w-full px-4 py-2 border border-input rounded-lg bg-background text-foreground focus:ring-2 focus:ring-ring focus:border-ring outline-none transition-all resize-none"
                                :class="{ 'border-destructive focus:ring-destructive focus:border-destructive': errors.message }"
                                :placeholder="$t('features.frontend.contact.messagePlaceholder')"
                            ></textarea>
                            <p v-if="errors.message" class="text-sm text-destructive">{{ errors.message[0] }}</p>
                        </div>
                        <button 
                            type="submit" 
                            :disabled="loading"
                            class="w-full bg-primary text-primary-foreground font-medium py-2 rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50"
                        >
                            {{ loading ? $t('common.messages.loading.sending') : $t('features.frontend.contact.submit') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from '../../../composables/useToast'
import { useFormValidation } from '../../../composables/useFormValidation'
import { contactSchema } from '../../../schemas'
import api from '../../../services/api'

const { t } = useI18n()
const toast = useToast()
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(contactSchema)
const loading = ref(false)

const form = ref({
    name: '',
    email: '',
    message: ''
})

const submitContact = async () => {
    if (!validateWithZod(form.value)) return

    loading.value = true
    clearErrors()
    
    try {
        // Mock API call or use real one if available
        // await api.post('/contact', form.value)
        
        // Simulating success for now since no backend route exists
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        toast.success(t('features.frontend.contact.success'))
        form.value = { name: '', email: '', message: '' }
    } catch (error) {
        if (error.response?.status === 422) {
            setErrors(error.response.data.errors)
        } else {
            toast.error(t('features.frontend.contact.error'))
        }
    } finally {
        loading.value = false
    }
}
</script>
