<template>
  <div class="relative">
    <button
      @click="showMenu = !showMenu"
      class="p-2 rounded-lg hover:bg-accent transition-colors"
      aria-label="Toggle dark mode"
      :aria-expanded="showMenu"
    >
      <Sun v-if="!isDark" class="h-5 w-5 text-foreground" />
      <Moon v-else class="h-5 w-5 text-foreground" />
    </button>

    <!-- Dropdown Menu -->
    <div
      v-if="showMenu"
      class="absolute right-0 mt-2 w-48 bg-popover text-popover-foreground border border-border rounded-lg shadow-lg z-50"
      @click.stop
    >
      <div class="p-1">
        <button
          v-for="option in modeOptions"
          :key="option.value"
          @click="selectMode(option.value)"
          class="flex items-center w-full px-3 py-2 text-sm rounded hover:bg-accent transition-colors text-foreground"
          :class="{ 'bg-accent': currentMode === option.value }"
        >
          <component :is="option.icon" class="h-4 w-4 mr-3" />
          <span>{{ option.label }}</span>
          <Check v-if="currentMode === option.value" class="h-4 w-4 ml-auto" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Sun, Moon, Monitor, Check } from 'lucide-vue-next';
import { useDarkMode } from '@/composables/useDarkMode';

const { t } = useI18n();

const { currentMode, isDark, setMode, modes } = useDarkMode();
const showMenu = ref(false);

const modeOptions = computed(() => [
  { value: modes.LIGHT, label: t('common.labels.light'), icon: Sun },
  { value: modes.DARK, label: t('common.labels.dark'), icon: Moon },
  { value: modes.SYSTEM, label: t('common.labels.system'), icon: Monitor },
]);

const selectMode = (mode) => {
  setMode(mode);
  showMenu.value = false;
};

// Close menu when clicking outside
const handleClickOutside = (event) => {
  if (showMenu.value && !event.target.closest('.relative')) {
    showMenu.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
