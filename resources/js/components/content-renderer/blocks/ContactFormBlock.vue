<template>
    <div class="contact-form-block w-full">
        <div v-if="title || description" class="text-center mb-8">
            <h3 v-if="title" class="text-2xl font-bold mb-2">{{ title }}</h3>
            <p v-if="description" class="text-muted-foreground">{{ description }}</p>
        </div>

        <form @submit.prevent="submitForm" class="grid gap-6">
            <div class="flex flex-wrap -mx-3">
                <div 
                    v-for="(field, index) in fields" 
                    :key="index" 
                    class="px-3 mb-6"
                    :style="{ width: field.width || '100%' }"
                >
                    <label 
                        v-if="field.label" 
                        class="block text-sm font-medium mb-2"
                        :for="field.fieldId"
                    >
                        {{ field.label }} <span v-if="field.required" class="text-red-500">*</span>
                    </label>

                    <!-- Text / Email / Password -->
                    <input 
                        v-if="['text', 'email', 'password', 'tel'].includes(field.type)"
                        :type="field.type"
                        :id="field.fieldId"
                        :placeholder="field.placeholder"
                        :required="field.required"
                        class="w-full px-4 py-3 rounded-lg border bg-background focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                    />

                    <!-- Textarea -->
                    <textarea 
                        v-else-if="field.type === 'textarea'"
                        :id="field.fieldId"
                        :placeholder="field.placeholder"
                        :required="field.required"
                        rows="4"
                        class="w-full px-4 py-3 rounded-lg border bg-background focus:ring-2 focus:ring-primary/20 outline-none transition-all resize-y"
                    ></textarea>

                    <!-- Select -->
                    <select 
                        v-else-if="field.type === 'select'"
                        :id="field.fieldId"
                        :required="field.required"
                        class="w-full px-4 py-3 rounded-lg border bg-background focus:ring-2 focus:ring-primary/20 outline-none transition-all appearance-none"
                    >
                        <option value="" disabled selected>{{ field.placeholder || 'Select an option' }}</option>
                        <option 
                            v-for="opt in parseOptions(field.options)" 
                            :key="opt" 
                            :value="opt"
                        >
                            {{ opt }}
                        </option>
                    </select>

                    <!-- Checkbox -->
                    <div v-else-if="field.type === 'checkbox'" class="flex items-center gap-2">
                        <input 
                            type="checkbox" 
                            :id="field.fieldId"
                            :required="field.required"
                            class="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary"
                        />
                        <span class="text-sm cursor-pointer" @click="focusField(field.fieldId)">
                            {{ field.placeholder || 'Yes, I agree' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button 
                    type="submit" 
                    class="inline-flex items-center justify-center px-8 py-4 rounded-full font-bold text-white transition-all transform hover:-translate-y-1 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    :style="{ backgroundColor: buttonBackgroundColor || 'var(--primary)', color: buttonTextColor || '#ffffff' }"
                    :disabled="isSubmitting"
                >
                    <span v-if="isSubmitting">Sending...</span>
                    <span v-else>{{ buttonText || 'Send Message' }}</span>
                </button>
            </div>

            <div v-if="success" class="p-4 rounded-lg bg-green-50 text-green-700 text-center border border-green-200">
                {{ successMessage || 'Thank you! Your message has been sent.' }}
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: String,
    description: String,
    buttonText: String,
    successMessage: String,
    emailTo: String,
    fields: { type: Array, default: () => [] },
    buttonBackgroundColor: String,
    buttonTextColor: String
});

const isSubmitting = ref(false);
const success = ref(false);

const parseOptions = (optionsString) => {
    if (!optionsString) return [];
    return optionsString.split('\n').filter(Boolean);
};

const submitForm = () => {
    isSubmitting.value = true;
    // Simulate submission
    setTimeout(() => {
        isSubmitting.value = false;
        success.value = true;
    }, 1500);
};

const focusField = (id) => {
    const el = document.getElementById(id);
    if (el) el.click();
};
</script>
