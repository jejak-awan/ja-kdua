<template>
  <BaseBlock :module="module" :settings="settings">
    <div class="html-block-container" v-html="content(settings)"></div>
  </BaseBlock>
</template>

<script setup>
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const settings = computed(() => props.module.settings || {})
const content = (settings) => getVal(settings, 'content') || '<!-- Raw HTML -->'
</script>
