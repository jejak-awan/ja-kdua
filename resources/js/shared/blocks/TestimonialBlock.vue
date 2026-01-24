<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <Card 
          class="testimonial-block relative p-8 overflow-hidden transition-all duration-300 border-none" 
          :class="[
            getVal(settings, 'cardShadow') || 'shadow-sm',
            getVal(settings, 'alignment') || 'text-center'
          ]"
          :style="testimonialBlockStyles(settings)"
      >
        <!-- Quote Icon Overlay -->
        <QuoteIcon 
          v-if="getVal(settings, 'showQuoteIcon') !== false" 
          class="absolute -top-5 -right-5 w-32 h-32 opacity-5 transform rotate-180 z-0 pointer-events-none"
          :style="{ color: getVal(settings, 'quoteIconColor') }"
        />
        
        <!-- Content Wrapper -->
        <CardContent class="p-0 relative z-10 flex flex-col h-full" :class="{ 'items-center': (getVal(settings, 'alignment') || 'text-center') === 'text-center' }">
          
          <!-- Quote Icon Small -->
          <QuoteIcon 
            v-if="getVal(settings, 'showQuoteIcon') !== false" 
            class="mb-6 transform rotate-180 opacity-80"
            :style="quoteIconStyles(settings)"
          />

          <!-- Rating -->
          <div v-if="getVal(settings, 'rating') > 0" class="flex gap-1 mb-6 rating-stars">
             <StarIcon 
                v-for="i in 5" 
                :key="i"
                class="w-5 h-5"
                :class="i <= getVal(settings, 'rating') ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'"
             />
          </div>
          
          <!-- Content -->
          <div 
            class="testimonial-content mb-8 max-w-3xl font-serif italic text-pretty leading-relaxed" 
            :style="contentStyles(settings, blockDevice)"
            :contenteditable="mode === 'edit'"
            @blur="e => updateResponsiveField('quote', e.target.innerText)"
          >
            "{{ getVal(settings, 'quote', blockDevice) }}"
          </div>
          
          <!-- Author Section -->
          <div class="testimonial-author mt-auto flex items-center gap-4" :class="{ 'flex-col text-center': (getVal(settings, 'alignment')) === 'text-center' }">
            <Avatar class="h-16 w-16">
              <AvatarImage 
                v-if="getVal(settings, 'avatar')"
                :src="getVal(settings, 'avatar')"
                alt="Author"
                class="object-cover"
              />
              <AvatarFallback class="bg-slate-100 text-slate-400">
                  <UserIcon class="w-2/3 h-2/3" />
              </AvatarFallback>
            </Avatar>
            
            <div class="author-info flex flex-col" :class="{ 'items-center': (getVal(settings, 'alignment')) === 'text-center' }">
              <CardTitle 
                class="author-name font-bold text-lg text-slate-900 border-none"
                :contenteditable="mode === 'edit'"
                @blur="e => updateResponsiveField('author', e.target.innerText)"
              >
                {{ getVal(settings, 'author', blockDevice) || 'Author Name' }}
              </CardTitle>
              <CardDescription class="text-sm text-slate-500 font-medium">
                <span 
                    :contenteditable="mode === 'edit'"
                     @blur="e => updateResponsiveField('job_title', e.target.innerText)"
                >{{ getVal(settings, 'job_title', blockDevice) }}</span>
                <span v-if="getVal(settings, 'company_name')" class="mx-1">•</span>
                <span 
                    v-if="getVal(settings, 'company_name') || mode === 'edit'"
                    :contenteditable="mode === 'edit'"
                     @blur="e => updateResponsiveField('company_name', e.target.innerText)"
                >{{ getVal(settings, 'company_name', blockDevice) }}</span>
              </CardDescription>
              
               <img 
                v-if="getVal(settings, 'companyLogo')"
                :src="getVal(settings, 'companyLogo')"
                alt="Company Logo"
                class="h-6 w-auto mt-2 opacity-80 grayscale hover:grayscale-0 transition-all"
              />
            </div>
          </div>
        </CardContent>
      </Card>
    </template>
  </BaseBlock>
</template>

<script setup>
import { inject } from 'vue'
import { Quote as QuoteIcon, User as UserIcon, Star as StarIcon } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardContent, CardTitle, CardDescription, Avatar, AvatarImage, AvatarFallback } from '../ui'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)

const testimonialBlockStyles = (settings) => {
  return { 
    backgroundColor: getVal(settings, 'cardBgColor') || '#ffffff',
    borderColor: getVal(settings, 'cardBorderColor') || 'rgba(0,0,0,0.05)',
    borderWidth: '1px',
    borderStyle: 'solid',
    borderRadius: getVal(settings, 'radius') || '1rem'
  }
}

const quoteIconStyles = (settings) => {
  const size = 48
  const color = getVal(settings, 'quoteIconColor') || '#e0e0e0'
  return {
    width: `${size}px`,
    height: `${size}px`,
    color: color
  }
}

const contentStyles = (settings, device) => {
    return {
        color: getVal(settings, 'quoteColor') || '#1e293b',
        fontSize: (getVal(settings, 'quoteSize') || 18) + 'px',
        lineHeight: 1.6
    }
}

const updateResponsiveField = (fieldName, value) => {
  if (props.mode !== 'edit' || !builder) return
  const current = props.module.settings[fieldName]
  let newValue
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [props.device]: value }
  } else {
    newValue = { [props.device]: value }
  }
  builder?.updateModuleSettings(props.module.id, { [fieldName]: newValue })
}
</script>

<style scoped>
.testimonial-block { 
  width: 100%; 
}
</style>
