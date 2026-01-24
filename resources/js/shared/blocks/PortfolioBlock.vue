<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings }">
      <div class="portfolio-block w-full">
        <h2 
          v-if="settings.title || mode === 'edit'" 
          class="portfolio-title text-center mb-12" 
          :style="titleDisplayStyles"
          :contenteditable="mode === 'edit'"
          @blur="e => updateResponsiveField('title', e.target.innerText)"
          v-text="settings.title"
        >
        </h2>

        <!-- Filter -->
        <div v-if="settings.showFilter !== false" class="portfolio-filter flex justify-center gap-2 mb-12 flex-wrap">
          <Button 
            v-for="cat in categories" 
            :key="cat" 
            :variant="activeFilter === cat ? 'default' : 'outline'"
            class="rounded-full px-6 transition-all duration-300"
            :style="activeFilter === cat ? {} : filterTypographyStyles"
            @click="activeFilter = cat"
          >
            {{ cat }}
          </Button>
        </div>
        
        <!-- Grid -->
        <div class="portfolio-grid" :style="gridStyles">
          <Card 
            v-for="item in mockItems" 
            :key="item.id" 
            class="portfolio-item group relative overflow-hidden bg-slate-100 rounded-[30px] border-none aspect-square cursor-pointer"
          >
            <!-- Image Area -->
            <div class="absolute inset-0 z-0 bg-slate-200 flex items-center justify-center transition-transform duration-700 group-hover:scale-110">
               <LucideIcon name="Layers" class="w-16 h-16 text-slate-300 opacity-50" />
            </div>

            <!-- Overlay -->
            <div 
                class="item-overlay absolute inset-0 z-10 flex flex-col items-center justify-center p-8 opacity-0 group-hover:opacity-100 transition-all duration-500 backdrop-blur-sm bg-primary/80"
                :style="overlayStyles"
            >
              <Badge 
                v-if="settings.showCategory !== false" 
                variant="secondary"
                class="mb-3 rounded-full uppercase tracking-widest text-[10px] font-bold py-1 px-4 bg-white/20 text-white border-white/30 backdrop-blur-md"
              >
                {{ item.category }}
              </Badge>
              <h4 
                v-if="settings.showTitle !== false" 
                class="item-title text-white text-2xl font-black text-center tracking-tighter"
                :style="itemTitleStyles"
              >
                {{ item.title }}
              </h4>
              
              <!-- Action Button (Optional Visual) -->
              <div class="mt-6 w-12 h-12 rounded-full bg-white text-primary flex items-center justify-center transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500 delay-100">
                 <LucideIcon name="ArrowUpRight" :size="20" />
              </div>
            </div>
          </Card>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, Button, Badge } from '../ui'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module?.settings || {})

const categories = ['All', 'Web', 'Mobile', 'Branding']
const activeFilter = ref('All')

const mockItems = computed(() => {
    const count = getVal(settings.value, 'itemsPerPage', props.device) || 9
    return Array.from({ length: count }, (_, i) => ({
        id: i + 1, title: `Creative Project ${i + 1}`, category: categories[1 + (i % 3)]
    }))
})

const gridStyles = computed(() => {
    const cols = getVal(settings.value, 'columns', props.device) || 3
    const gap = getVal(settings.value, 'gap', props.device) || 24
    return { 
        display: 'grid', 
        gridTemplateColumns: `repeat(${cols}, 1fr)`, 
        gap: `${gap}px`,
        width: '100%'
    }
})

const overlayStyles = computed(() => ({ 
    backgroundColor: settings.value.overlayColor || '', 
}))

const filterTypographyStyles = computed(() => getTypographyStyles(settings.value, 'filter_', props.device))
const titleDisplayStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const itemTitleStyles = computed(() => getTypographyStyles(settings.value, 'item_title_', props.device))

const updateResponsiveField = (fieldName, value) => {
  if (props.mode !== 'edit' || !builder) return
  const current = settings.value[fieldName]
  let newValue
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [props.device]: value }
  } else {
    newValue = { [props.device]: value }
  }
  builder.updateModuleSettings(props.module.id, { [fieldName]: newValue })
}
</script>

<style scoped>
.portfolio-block { width: 100%; }
</style>
