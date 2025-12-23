<template>
  <div class="contact-page">
    <div class="container">
      <h1 class="page-title">{{ t('features.frontend.contact.title') }}</h1>
      <div class="content">
        <p>{{ t('features.frontend.contact.subtitle') }}</p>
        <p>{{ t('features.frontend.contact.placeholder') }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<style scoped>
.contact-page {
  padding: 3rem 0;
}

.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 1rem;
}

.page-title {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 2rem;
  text-align: center;
}

.content {
  font-size: 1.125rem;
  line-height: 1.8;
}
</style>
