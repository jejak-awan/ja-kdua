<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="team-member-block"
    :class="layoutClass"
    :style="wrapperStyles"
  >
    <!-- Image -->
    <div class="member-image-wrapper">
      <div v-if="getVal(settings, 'image')" class="member-image" :style="imageStyles">
        <img :src="getVal(settings, 'image')" :alt="getVal(settings, 'name')" />
      </div>
      <div v-else class="member-image member-image--placeholder" :style="imageStyles">
        <User class="placeholder-icon" />
      </div>
    </div>
    
    <!-- Info -->
    <div class="member-info">
      <h3 
        class="member-name" 
        :style="nameStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateField('name', $event.target.innerText)"
        v-text="getVal(settings, 'name') || 'Team Member'"
      ></h3>
      <p 
        class="member-position" 
        :style="positionStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateField('position', $event.target.innerText)"
        v-text="getVal(settings, 'position') || 'Position'"
      ></p>
      <p 
        v-if="bioValue || mode === 'edit'" 
        class="member-bio" 
        :style="bioStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateField('bio', $event.target.innerText)"
        v-text="bioValue"
      ></p>
      
      <!-- Social Links (Child Blocks / Inline Logic) -->
      <div class="member-social" v-if="socialLinks.length || mode === 'edit'">
        <template v-if="socialLinks.length">
            <a 
              v-for="(link, index) in socialLinks" 
              :key="index"
              :href="link.url || '#'"
              class="social-link"
              @click.prevent
            >
              <LucideIcon :name="link.network || 'globe'" class="social-icon" />
            </a>
        </template>
        <template v-else-if="mode === 'edit'">
            <div class="social-placeholder text-xs opacity-50">Social links (see settings)</div>
        </template>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import { User } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean
})

const builder = inject('builder', null)

const bioValue = computed(() => getVal(props.settings, 'bio'))
const layoutValue = computed(() => getVal(props.settings, 'layout') || 'stacked')
const layoutClass = computed(() => `team-member--${layoutValue.value}`)

const imageSize = computed(() => parseInt(getVal(props.settings, 'imageSize')) || 150)
const imageBorderRadius = computed(() => getVal(props.settings, 'imageBorderRadius') || 50)

const socialLinks = computed(() => {
    // In builder, might be children. In renderer, might be in settings.
    // We'll prioritize settings to match the new unified approach
    return props.settings.socialLinks || []
})

const wrapperStyles = computed(() => ({
  textAlign: getVal(props.settings, 'alignment') || 'center',
  width: '100%'
}))

const imageStyles = computed(() => ({
  width: `${imageSize.value}px`,
  height: `${imageSize.value}px`,
  borderRadius: `${imageBorderRadius.value}%`,
  margin: layoutValue.value === 'stacked' ? '0 auto' : '0'
}))

const nameStyles = computed(() => getTypographyStyles(props.settings, 'name_'))
const positionStyles = computed(() => getTypographyStyles(props.settings, 'position_'))
const bioStyles = computed(() => getTypographyStyles(props.settings, 'bio_'))

const updateField = (key, value) => {
  if (props.mode !== 'edit' || !builder) return
  
  const current = props.settings[key]
  let newValue
  
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [builder.device.value]: value }
  } else {
    newValue = { [builder.device.value]: value }
  }
  
  builder.updateModuleSettings(props.id, { [key]: newValue })
}
</script>

<style scoped>
.team-member-block {
  width: 100%;
}

.team-member--stacked {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.team-member--horizontal {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 24px;
  text-align: left !important;
}

.member-image-wrapper {
  flex-shrink: 0;
}

.member-image {
  overflow: hidden;
  position: relative;
}

.member-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.member-image--placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
}

.placeholder-icon {
  width: 40%;
  height: 40%;
  color: #94a3b8;
}

.member-info {
  flex: 1;
}

.member-name {
  margin: 0 0 4px;
  font-weight: 700;
  outline: none;
}

.member-position {
  margin: 0 0 12px;
  font-size: 0.9em;
  opacity: 0.8;
  outline: none;
}

.member-bio {
  margin: 0 0 16px;
  line-height: 1.6;
  outline: none;
}

.member-bio[contenteditable="true"]:empty:before {
  content: 'Add biography details here...';
  opacity: 0.3;
}

.member-social {
  display: flex;
  gap: 12px;
  justify-content: inherit;
}

.social-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #f1f5f9;
  color: #475569;
  transition: all 0.2s;
}

.social-link:hover {
  background: var(--theme-primary-color, #2059ea);
  color: white;
  transform: translateY(-2px);
}

.social-icon {
  width: 16px;
  height: 16px;
}

@media (max-width: 640px) {
    .team-member--horizontal {
        flex-direction: column;
        text-align: center !important;
    }
}
</style>
