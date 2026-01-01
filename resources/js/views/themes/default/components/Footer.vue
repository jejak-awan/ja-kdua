<template>
    <footer class="bg-card text-card-foreground mt-auto border-t border-border">
        <div class="container mx-auto px-4 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Brand -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <img v-if="getSetting('brand_logo')" :src="getSetting('brand_logo')" class="h-8 w-auto object-contain" :alt="getSetting('site_title', 'JA-CMS')">
                        <template v-else>
                            <div class="w-8 h-8 rounded-lg bg-primary text-primary-foreground flex items-center justify-center font-bold">JA</div>
                            <span class="text-xl font-bold">{{ getSetting('site_title', 'JA-CMS') }}</span>
                        </template>
                    </div>
                    <h5 class="font-semibold mb-4 text-foreground">{{ $t('features.frontend.newsletter.title') }}</h5>
                    <p class="text-muted-foreground text-sm mb-4">
                        {{ $t('features.frontend.newsletter.description') }}
                    </p>
                    <form class="flex flex-col gap-2" @submit.prevent="submitNewsletter">
                        <div class="flex gap-2">
                            <input 
                                v-model="email"
                                type="email" 
                                :placeholder="$t('features.frontend.newsletter.placeholder')" 
                                class="flex-1 px-4 py-2 rounded-lg bg-background border border-input text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring transition-colors"
                                :class="{ 'border-destructive focus:ring-destructive': errors.email }"
                            >
                            <button 
                                type="submit" 
                                :disabled="loading"
                                class="p-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50"
                            >
                                <svg v-if="loading" class="w-5 h-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="errors.email" class="text-xs text-destructive">{{ errors.email[0] }}</p>
                    </form>
                </div>
            </div>

            <div class="mt-16 pt-8 border-t border-border flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-muted-foreground text-sm">
                    {{ getSetting('footer_text', `© ${new Date().getFullYear()} JA-CMS. All rights reserved.`) }}
                </p>
                <div class="flex gap-6">
                    <a href="#" class="text-muted-foreground hover:text-foreground text-sm transition-colors">{{ $t('features.frontend.footer.privacy') }}</a>
                    <a href="#" class="text-muted-foreground hover:text-foreground text-sm transition-colors">{{ $t('features.frontend.footer.terms') }}</a>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useTheme } from '../../../../composables/useTheme'
import { useToast } from '../../../../composables/useToast'
import { useFormValidation } from '../../../../composables/useFormValidation'
import { newsletterSchema } from '../../../../schemas'

const { t } = useI18n()
const { getSetting } = useTheme()
const toast = useToast()
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(newsletterSchema)
const loading = ref(false)
const email = ref('')

const submitNewsletter = async () => {
    if (!validateWithZod({ email: email.value })) return

    loading.value = true
    clearErrors()
    
    try {
        // Mock API call - in a real app this would call the newsletter endpoint
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        toast.success(t('features.frontend.newsletter.success'))
        email.value = ''
    } catch (error) {
        if (error.response?.status === 422) {
            setErrors(error.response.data.errors)
        } else {
            toast.error(t('features.frontend.newsletter.error'))
        }
    } finally {
        loading.value = false
    }
}
</script>
