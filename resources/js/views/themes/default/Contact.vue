<template>
    <div class="min-h-screen bg-background py-16 px-4">
        <div class="max-w-4xl mx-auto bg-card rounded-xl shadow-lg overflow-hidden border border-border">
            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="p-8 bg-indigo-900 text-white">
                    <h1 class="text-3xl font-bold mb-4">Get in Touch</h1>
                    <p class="text-indigo-200 mb-8">
                        Have questions? detailed feedback? or just want to say hi? We'd love to hear from you.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>contact@ja-cms.com</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Jakarta, Indonesia</span>
                        </div>
                    </div>
                </div>
                <div class="p-8">
                    <form @submit.prevent="submitContact" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input 
                                v-model="form.name"
                                type="text" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
                                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.name }"
                            >
                            <p v-if="errors.name" class="text-sm text-red-500 mt-1">{{ errors.name[0] }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input 
                                v-model="form.email"
                                type="email" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
                                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.email }"
                            >
                            <p v-if="errors.email" class="text-sm text-red-500 mt-1">{{ errors.email[0] }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                            <textarea 
                                v-model="form.message"
                                rows="4" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all"
                                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.message }"
                            ></textarea>
                            <p v-if="errors.message" class="text-sm text-red-500 mt-1">{{ errors.message[0] }}</p>
                        </div>
                        <button 
                            type="submit" 
                            :disabled="loading"
                            class="w-full bg-indigo-600 text-white font-bold py-2 rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50"
                        >
                            {{ loading ? 'Sending...' : 'Send Message' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useToast } from '../../../composables/useToast'
import { useFormValidation } from '../../../composables/useFormValidation'
import { contactSchema } from '../../../schemas'
import api from '../../../services/api'

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
        
        toast.success('Message sent! We will get back to you soon.')
        form.value = { name: '', email: '', message: '' }
    } catch (error) {
        if (error.response?.status === 422) {
            setErrors(error.response.data.errors)
        } else {
            toast.error('Failed to send message. Please try again.')
        }
    } finally {
        loading.value = false
    }
}
</script>
