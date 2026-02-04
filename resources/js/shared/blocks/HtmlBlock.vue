<template>
  <BaseBlock :module="module" :mode="mode" :settings="settings">
    <div class="html-block-container" v-html="(content(settings) as string)"></div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal } from '../utils/styleUtils'
import type { BlockProps, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view'
})


const settings = computed(() => (props.settings || props.module?.settings || {}) as ModuleSettings)
const content = (settings: ModuleSettings) => getVal<string>(settings, 'content') || '<!-- Raw HTML -->'
</script>
