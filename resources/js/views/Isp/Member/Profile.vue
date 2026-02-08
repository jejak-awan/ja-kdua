<script setup lang="ts">
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">{{ t('isp.member.profile.title') }}</h1>
    <div class="bg-card rounded-lg border p-6">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium">{{ t('isp.member.profile.full_name') }}</label>
          <p class="text-lg">{{ t('isp.member.profile.member_placeholder') }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium">{{ t('isp.member.profile.subscription_plan') }}</label>
          <p class="text-lg">{{ t('isp.member.profile.not_active') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
