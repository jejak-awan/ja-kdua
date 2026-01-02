<template>
    <section 
        :class="['transition-all duration-500', padding, radius, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <div :class="['transition-all duration-500', style]">
                <div v-if="!submitted">
                    <div class="mb-10" :class="alignment === 'text-center' ? 'text-center' : alignment === 'text-right' ? 'text-right' : 'text-left'">
                        <h2 v-if="title" class="text-3xl font-bold tracking-tight mb-2">{{ title }}</h2>
                        <p v-if="description" class="text-muted-foreground">{{ description }}</p>
                    </div>

                    <form @submit.prevent="handleSubmit" class="flex flex-wrap gap-4">
                        <template v-for="(field, index) in fields" :key="index">
                            <div :class="field.width || 'w-full'" class="space-y-2">
                                <label class="text-sm font-semibold text-foreground/80 ml-1">
                                    {{ field.label }}
                                    <span v-if="field.required" class="text-primary">*</span>
                                </label>
                                
                                <template v-if="field.type === 'textarea'">
                                    <textarea 
                                        class="w-full min-h-[120px] rounded-xl border bg-background px-4 py-3 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition-all"
                                        :placeholder="field.label"
                                        :required="field.required"
                                    ></textarea>
                                </template>

                                <template v-else-if="field.type === 'select'">
                                    <select 
                                        class="w-full h-12 rounded-xl border bg-background px-4 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition-all appearance-none"
                                        :required="field.required"
                                    >
                                        <option value="" disabled selected>Select an option</option>
                                        <option v-for="opt in (field.options || 'Option 1, Option 2').split(',')" :key="opt" :value="opt.trim()">
                                            {{ opt.trim() }}
                                        </option>
                                    </select>
                                </template>

                                <template v-else-if="field.type === 'checkbox'">
                                    <div class="flex items-center gap-2 px-1">
                                        <input type="checkbox" :id="`field-${index}`" class="w-4 h-4 rounded border-input text-primary focus:ring-primary" :required="field.required">
                                        <label :for="`field-${index}`" class="text-sm text-muted-foreground line-clamp-1">{{ field.label }}</label>
                                    </div>
                                </template>

                                <template v-else>
                                    <input 
                                        :type="field.type || 'text'"
                                        class="w-full h-12 rounded-xl border bg-background px-4 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition-all"
                                        :placeholder="field.label"
                                        :required="field.required"
                                    />
                                </template>
                            </div>
                        </template>

                        <div class="w-full mt-4">
                            <button 
                                type="submit" 
                                class="w-full h-12 bg-primary text-primary-foreground font-bold rounded-xl hover:opacity-90 transition-all active:scale-[0.98] shadow-lg shadow-primary/20 flex items-center justify-center gap-2"
                                :disabled="submitting"
                            >
                                <Loader2 v-if="submitting" class="w-4 h-4 animate-spin" />
                                {{ submitting ? 'Sending...' : button_text }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Success State -->
                <div v-else class="text-center py-10 space-y-4 animate-in fade-in zoom-in duration-500">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto text-primary border-2 border-primary/20">
                        <Check class="w-10 h-10" />
                    </div>
                    <h3 class="text-2xl font-bold">{{ success_message }}</h3>
                    <p class="text-muted-foreground">We typically respond within 24 hours.</p>
                    <button @click="submitted = false" class="text-primary text-sm font-semibold hover:underline">
                        Send another message
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref } from 'vue';
import { Loader2, Check } from 'lucide-vue-next';

defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: 'Get in Touch' },
    description: { type: String, default: '' },
    button_text: { type: String, default: 'Send Message' },
    success_message: { type: String, default: 'Thank you! Your message has been sent.' },
    fields: { type: Array, default: () => [] },
    style: { type: String, default: 'bg-card border shadow-sm p-8 rounded-2xl' },
    padding: { type: String, default: 'py-20' },
    width: { type: String, default: 'max-w-3xl' },
    bgColor: String,
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' },
    alignment: { type: String, default: 'text-left' }
});

const submitted = ref(false);
const submitting = ref(false);

const handleSubmit = () => {
    submitting.value = true;
    // Simulate API call
    setTimeout(() => {
        submitting.value = false;
        submitted.value = true;
    }, 1500);
};
</script>
