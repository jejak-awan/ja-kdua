<template>
  <div class="autosave-indicator" :class="statusClass">
    <div class="flex items-center gap-2">
      <!-- Status Icon -->
      <svg
        v-if="status === 'saving'"
        class="w-4 h-4 animate-spin"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
        />
      </svg>
      <svg
        v-else-if="status === 'saved'"
        class="w-4 h-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M5 13l4 4L19 7"
        />
      </svg>
      <svg
        v-else-if="status === 'error'"
        class="w-4 h-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
        />
      </svg>

      <!-- Status Text -->
      <span class="text-sm font-medium">
        <span v-if="status === 'saving'">{{ $t('features.autosave.saving') }}</span>
        <span v-else-if="status === 'saved'">{{ $t('features.autosave.savedAt', { time: lastSavedTime }) }}</span>
        <span v-else-if="status === 'error'">{{ $t('features.autosave.failed') }}</span>
        <span v-else>{{ $t('features.autosave.notSaved') }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  status: {
    type: String,
    default: 'idle',
    validator: (value) => ['idle', 'saving', 'saved', 'error'].includes(value),
  },
  lastSaved: {
    type: Date,
    default: null,
  },
});

const statusClass = computed(() => {
  return {
    'text-gray-500': props.status === 'idle',
    'text-blue-600': props.status === 'saving',
    'text-green-600': props.status === 'saved',
    'text-red-600': props.status === 'error',
  };
});

const lastSavedTime = computed(() => {
  if (!props.lastSaved) return '';

  const now = new Date();
  const diff = now - props.lastSaved;
  const seconds = Math.floor(diff / 1000);
  const minutes = Math.floor(seconds / 60);

  if (seconds < 10) return t('features.autosave.justNow');
  if (seconds < 60) return t('features.autosave.agoSeconds', { seconds });
  if (minutes < 60) return t('features.autosave.agoMinutes', { minutes });

  return props.lastSaved.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
  });
});
</script>

<style scoped>
.autosave-indicator {
  transition: color 0.2s;
}
</style>

