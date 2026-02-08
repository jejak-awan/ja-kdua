<template>
  <div class="contact-field-block">
    <div class="flex flex-col gap-2">
      <label v-if="settings.label" class="text-[10px] font-black uppercase tracking-[0.2em] opacity-40 mb-1 ml-1">{{ settings.label }}</label>
      <div 
        class="contact-input-wrapper relative group/input p-4 rounded-2xl border-2 border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-950 focus-within:border-primary/40 focus-within:bg-primary/5 transition-all duration-300"
      >
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-900 flex items-center justify-center group-hover/input:bg-primary transition-colors duration-300">
                <component :is="fieldIcon" class="w-5 h-5 opacity-40 group-hover/input:opacity-100 group-hover/input:text-white" />
            </div>
            <div class="flex-1">
                <textarea 
                    v-if="settings.fieldType === 'message'"
                    :placeholder="(settings.placeholder as string) || 'Your focus here...'"
                    class="w-full bg-transparent border-none outline-none font-black text-sm tracking-tight placeholder:opacity-20 min-h-[100px] resize-none"
                    v-model="fieldValue"
                ></textarea>
                <input 
                    v-else
                    :type="inputType"
                    :placeholder="(settings.placeholder as string) || 'Your focus here...'"
                    class="w-full bg-transparent border-none outline-none font-black text-sm tracking-tight placeholder:opacity-20"
                    v-model="fieldValue"
                />
            </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, inject, watch } from 'vue'
import User from 'lucide-vue-next/dist/esm/icons/user.js';
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import Phone from 'lucide-vue-next/dist/esm/icons/phone.js';
import MessageSquare from 'lucide-vue-next/dist/esm/icons/message-square.js';
import type { BlockInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

// Form Integration
const formContext = inject<{ updateField: (id: string, value: string | number | boolean) => void } | null>('contactForm', null)
const fieldValue = ref('')

watch(fieldValue, (newVal) => {
    if (formContext && settings.value.fieldId) {
        formContext.updateField(settings.value.fieldId as string, newVal)
    }
})

const inputType = computed(() => {
    const type = settings.value.fieldType || 'text'
    if (type === 'email') return 'email'
    if (type === 'phone') return 'tel'
    return 'text'
})

const fieldIcon = computed(() => {
    const type = settings.value.fieldType || 'text'
    if (type === 'email') return Mail
    if (type === 'phone') return Phone
    if (type === 'message') return MessageSquare
    return User
})
</script>

<style scoped>
.contact-field-block { width: 100%; }
</style>
