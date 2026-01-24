<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :device="device"
    class="team-member-block"
  >
    <div 
      class="flex transition-all duration-300 w-full" 
      :class="[
        layout === 'stacked' ? 'flex-col items-center gap-4 text-center' : 'flex-row items-center gap-6 text-left',
        getVal(settings, 'alignment') === 'right' ? 'flex-row-reverse text-right' : ''
      ]"
    >
      <!-- Avatar -->
      <Avatar :style="avatarStyles" class="flex-shrink-0">
        <AvatarImage 
          v-if="getVal(settings, 'image')" 
          :src="getVal(settings, 'image')" 
          :alt="getVal(settings, 'name')" 
          class="object-cover"
        />
        <AvatarFallback class="bg-slate-100 text-slate-400">
          <User class="w-1/2 h-1/2" />
        </AvatarFallback>
      </Avatar>
      
      <!-- Info -->
      <div class="member-info flex-1">
        <h3 
          class="font-bold mb-1 outline-none" 
          :style="nameStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateField('name', $event.target.innerText)"
          v-text="getVal(settings, 'name') || 'Team Member'"
        ></h3>
        <p 
          class="text-sm opacity-80 mb-3 outline-none" 
          :style="positionStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateField('position', $event.target.innerText)"
          v-text="getVal(settings, 'position') || 'Position'"
        ></p>
        <div 
          v-if="bioValue || mode === 'edit'" 
          class="mb-4 leading-relaxed outline-none" 
          :style="bioStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateField('bio', $event.target.innerText)"
          v-text="bioValue"
        ></div>
        
        <!-- Social Links -->
        <div class="flex flex-wrap gap-3" :class="socialAlignmentClass">
          <template v-if="socialLinks.length">
              <a 
                v-for="(link, index) in socialLinks" 
                :key="index"
                :href="link.url || '#'"
                class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center hover:bg-primary hover:text-white hover:-translate-y-0.5 transition-all"
                @click.prevent
              >
                <LucideIcon :name="link.network || 'globe'" class="w-4 h-4" />
              </a>
          </template>
          <template v-else-if="mode === 'edit'">
              <span class="text-xs text-slate-400 italic">No social links</span>
          </template>
        </div>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import { User } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { Avatar, AvatarImage, AvatarFallback } from '../ui'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getTypographyStyles, getResponsiveValue } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})

const bioValue = computed(() => getVal(settings.value, 'bio'))
const layout = computed(() => getResponsiveValue(settings.value, 'layout', props.device) || 'stacked')
const alignment = computed(() => getResponsiveValue(settings.value, 'alignment', props.device) || 'center')

const avatarStyles = computed(() => {
    const size = parseInt(getResponsiveValue(settings.value, 'imageSize', props.device)) || 150
    return {
        width: `${size}px`,
        height: `${size}px`
    }
})

const socialAlignmentClass = computed(() => {
    if (layout.value === 'stacked') return 'justify-center'
    if (alignment.value === 'right') return 'justify-end'
    return 'justify-start' 
})

const socialLinks = computed(() => settings.value.socialLinks || [])

const nameStyles = computed(() => getTypographyStyles(settings.value, 'name_', props.device))
const positionStyles = computed(() => getTypographyStyles(settings.value, 'position_', props.device))
const bioStyles = computed(() => getTypographyStyles(settings.value, 'bio_', props.device))

const updateField = (key, value) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { [key]: value })
}
</script>

<style scoped>
.team-member-block { width: 100%; }

/* Edit mode overrides */
div[contenteditable="true"]:empty:before {
  content: 'Add details...';
  opacity: 0.3;
  font-style: italic;
}
</style>
