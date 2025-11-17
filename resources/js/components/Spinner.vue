<template>
  <div class="spinner-container" :class="[size, color, { 'inline': inline }]">
    <svg
      v-if="type === 'circular'"
      class="spinner-circular"
      :class="[size]"
      viewBox="0 0 50 50"
    >
      <circle
        class="spinner-path"
        cx="25"
        cy="25"
        r="20"
        fill="none"
        stroke-width="4"
      />
    </svg>
    
    <div v-else-if="type === 'dots'" class="spinner-dots">
      <span></span>
      <span></span>
      <span></span>
    </div>
    
    <div v-else-if="type === 'bars'" class="spinner-bars">
      <div></div>
      <div></div>
      <div></div>
      <div></div>
    </div>
    
    <div v-if="text" class="spinner-text mt-2 text-sm text-gray-600 dark:text-gray-400">
      {{ text }}
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  type: {
    type: String,
    default: 'circular',
    validator: (value) => ['circular', 'dots', 'bars'].includes(value),
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value),
  },
  color: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'white', 'gray'].includes(value),
  },
  text: {
    type: String,
    default: '',
  },
  inline: {
    type: Boolean,
    default: false,
  },
});
</script>

<style scoped>
.spinner-container {
  @apply flex flex-col items-center justify-center;
}

.spinner-container.inline {
  @apply inline-flex;
}

/* Circular Spinner */
.spinner-circular {
  @apply animate-spin;
}

.spinner-circular.sm {
  @apply w-4 h-4;
}

.spinner-circular.md {
  @apply w-8 h-8;
}

.spinner-circular.lg {
  @apply w-12 h-12;
}

.spinner-circular.xl {
  @apply w-16 h-16;
}

.spinner-path {
  stroke: currentColor;
  stroke-linecap: round;
  stroke-dasharray: 90, 150;
  stroke-dashoffset: 0;
  animation: spinner-dash 1.5s ease-in-out infinite;
}

.spinner-path.primary {
  @apply text-blue-600 dark:text-blue-400;
}

.spinner-path.white {
  @apply text-white;
}

.spinner-path.gray {
  @apply text-gray-600 dark:text-gray-400;
}

@keyframes spinner-dash {
  0% {
    stroke-dasharray: 1, 150;
    stroke-dashoffset: 0;
  }
  50% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -35;
  }
  100% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -124;
  }
}

/* Dots Spinner */
.spinner-dots {
  @apply flex gap-1;
}

.spinner-dots.sm span {
  @apply w-1 h-1;
}

.spinner-dots.md span {
  @apply w-2 h-2;
}

.spinner-dots.lg span {
  @apply w-3 h-3;
}

.spinner-dots span {
  @apply bg-blue-600 dark:bg-blue-400 rounded-full animate-pulse;
  animation-delay: calc(var(--i) * 0.2s);
}

.spinner-dots span:nth-child(1) {
  --i: 0;
}

.spinner-dots span:nth-child(2) {
  --i: 1;
}

.spinner-dots span:nth-child(3) {
  --i: 2;
}

/* Bars Spinner */
.spinner-bars {
  @apply flex gap-1 items-end;
}

.spinner-bars.sm div {
  @apply w-1;
}

.spinner-bars.md div {
  @apply w-1.5;
}

.spinner-bars.lg div {
  @apply w-2;
}

.spinner-bars div {
  @apply bg-blue-600 dark:bg-blue-400 rounded-t;
  animation: spinner-bars 1.2s ease-in-out infinite;
}

.spinner-bars div:nth-child(1) {
  height: 20%;
  animation-delay: 0s;
}

.spinner-bars div:nth-child(2) {
  height: 40%;
  animation-delay: 0.1s;
}

.spinner-bars div:nth-child(3) {
  height: 60%;
  animation-delay: 0.2s;
}

.spinner-bars div:nth-child(4) {
  height: 80%;
  animation-delay: 0.3s;
}

@keyframes spinner-bars {
  0%, 40%, 100% {
    transform: scaleY(0.4);
  }
  20% {
    transform: scaleY(1);
  }
}
</style>

