<template>
    <footer class="bg-card text-card-foreground mt-auto border-t border-border">
        <div class="container mx-auto px-4 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Brand -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-primary text-primary-foreground flex items-center justify-center font-bold">JA</div>
                        <span class="text-xl font-bold">JA-CMS</span>
                    </div>
                    <h5 class="font-semibold mb-4 text-foreground">Stay Updated</h5>
                    <p class="text-muted-foreground text-sm mb-4">
                        Subscribe to our newsletter for the latest updates and articles.
                    </p>
                    <form class="flex flex-col gap-2" @submit.prevent="submitNewsletter">
                        <div class="flex gap-2">
                            <input 
                                v-model="email"
                                type="email" 
                                placeholder="Email address" 
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
                    &copy; 2024 JA-CMS. All rights reserved.
                </p>
                <div class="flex gap-6">
                    <a href="#" class="text-muted-foreground hover:text-foreground text-sm transition-colors">Privacy Policy</a>
                    <a href="#" class="text-muted-foreground hover:text-foreground text-sm transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { ref } from 'vue'
import { useToast } from '../../../../composables/useToast'
// import api from '../../../services/api' // Uncomment when ready

const toast = useToast()
const loading = ref(false)
const errors = ref({})
const email = ref('')

const submitNewsletter = async () => {
    loading.value = true
    errors.value = {}
    
    try {
        // Mock API call
        // await api.post('/newsletter/subscribe', { email: email.value })
        
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        toast.success('Subscribed successfully!')
        email.value = ''
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        } else {
            toast.error('Subscription failed.')
        }
    } finally {
        loading.value = false
    }
}
</script>
