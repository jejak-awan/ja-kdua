<template>
  <Teleport to="body">
    <TransitionGroup
      name="toast"
      tag="div"
      class="fixed bottom-4 right-4 z-[100000] flex flex-col gap-2 max-w-sm w-full pointer-events-none"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'pointer-events-auto p-4 rounded-lg shadow-lg border backdrop-blur-sm transition-all duration-300',
          variantClasses[toast.variant || 'default']
        ]"
      >
        <div class="flex items-start gap-3">
          <!-- Icon -->
          <div class="flex-shrink-0">
            <component :is="getIcon(toast.variant)" class="w-5 h-5" />
          </div>
          
          <!-- Content -->
          <div class="flex-1 min-w-0">
            <h4 v-if="toast.title" class="font-semibold text-sm mb-1">
              {{ toast.title }}
            </h4>
            <p v-if="toast.description" class="text-sm opacity-90">
              {{ toast.description }}
            </p>
          </div>
          
          <!-- Close button -->
          <button
            @click="removeToast(toast.id)"
            class="flex-shrink-0 opacity-70 hover:opacity-100 transition-opacity"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </TransitionGroup>
  </Teleport>
</template>

<script setup>
import { ref, computed, h } from 'vue';

const toasts = ref([]);
let toastId = 0;

const variantClasses = {
  default: 'bg-card/95 border-border text-foreground',
  success: 'bg-green-500/95 border-green-600 text-white',
  error: 'bg-red-500/95 border-red-600 text-white',
  warning: 'bg-yellow-500/95 border-yellow-600 text-white',
  info: 'bg-blue-500/95 border-blue-600 text-white',
};

const getIcon = (variant) => {
  const icons = {
    success: {
      render() {
        return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M5 13l4 4L19 7' })
        ]);
      }
    },
    error: {
      render() {
        return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M6 18L18 6M6 6l12 12' })
        ]);
      }
    },
    warning: {
      render() {
        return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' })
        ]);
      }
    },
    info: {
      render() {
        return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' })
        ]);
      }
    },
    default: {
      render() {
        return h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' })
        ]);
      }
    },
  };
  return icons[variant] || icons.default;
};

const addToast = (options) => {
  const id = ++toastId;
  const toast = {
    id,
    title: options.title || '',
    description: options.description || '',
    variant: options.variant || 'default',
    duration: options.duration ?? 5000,
  };
  
  toasts.value.push(toast);
  
  if (toast.duration > 0) {
    setTimeout(() => {
      removeToast(id);
    }, toast.duration);
  }
  
  return id;
};

const removeToast = (id) => {
  const index = toasts.value.findIndex(t => t.id === id);
  if (index > -1) {
    toasts.value.splice(index, 1);
  }
};

// Expose methods globally
defineExpose({ addToast, removeToast });

// Make toast available globally via window
if (typeof window !== 'undefined') {
  window.__toastInstance = { addToast, removeToast };
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
.toast-move {
  transition: transform 0.3s ease;
}
</style>
