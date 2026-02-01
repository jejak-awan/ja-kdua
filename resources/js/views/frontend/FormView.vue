<template>
  <div class="min-h-screen bg-background py-16">
    <div class="container mx-auto max-w-2xl px-4">
      <FormRenderer :slug="slug" />
    </div>
  </div>
</template>

<script setup lang="ts">
import FormRenderer from '@/components/forms/FormRenderer.vue'

defineProps<{
  slug: string
}>()
</script>
