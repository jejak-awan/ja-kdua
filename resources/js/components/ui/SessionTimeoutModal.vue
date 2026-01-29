<template>
  <teleport to="body">
    <transition name="modal">
      <div
        v-if="isVisible"
        class="fixed inset-0 z-[9999] overflow-y-auto"
        @click.self="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>

        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
          <div
            class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-card shadow-2xl transition-[opacity,transform]"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-title"
          >
            <!-- Icon -->
            <div class="flex items-center justify-center pt-8 pb-4">
              <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                <svg
                  class="h-8 w-8 text-amber-600 dark:text-amber-400 animate-pulse"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
            </div>

            <!-- Content -->
            <div class="px-6 pb-6">
              <h3
                id="modal-title"
                class="text-center text-xl font-semibold text-foreground mb-2"
              >
                Sesi Anda Akan Berakhir
              </h3>
              
              <p class="text-center text-muted-foreground mb-6">
                Sesi Anda akan berakhir dalam
              </p>

              <!-- Countdown Timer -->
              <div class="flex items-center justify-center mb-6">
                <div class="text-center">
                  <div class="text-5xl font-bold text-amber-600 dark:text-amber-400 tabular-nums">
                    {{ formatTime(timeRemaining) }}
                  </div>
                  <div class="text-sm text-muted-foreground mt-1">
                    {{ timeRemaining > 60 ? 'menit:detik' : 'detik' }}
                  </div>
                </div>
              </div>

              <!-- Warning Message -->
              <div class="mb-6 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4">
                <div class="flex">
                  <svg
                    class="h-5 w-5 text-amber-600 dark:text-amber-400 mt-0.5 mr-3 flex-shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  <div class="text-sm text-amber-800 dark:text-amber-400">
                    <p class="font-medium mb-1">Pekerjaan yang belum disimpan akan hilang</p>
                    <p class="text-amber-700 dark:text-amber-500">
                      Klik "Perpanjang Sesi" untuk melanjutkan bekerja atau simpan pekerjaan Anda sekarang.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex flex-col sm:flex-row gap-3">
                <button
                  @click="extendSession"
                  :disabled="extending"
                  class="flex-1 inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg
                    v-if="extending"
                    class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle
                      class="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      stroke-width="4"
                    ></circle>
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                  </svg>
                  <svg
                    v-else
                    class="h-5 w-5 mr-2"
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
                  {{ extending ? 'Memperpanjang...' : 'Perpanjang Sesi' }}
                </button>
                
                <button
                  @click="logout"
                  class="flex-1 inline-flex items-center justify-center px-6 py-3 border border-input text-base font-medium rounded-lg text-foreground bg-card hover:bg-muted focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors"
                >
                  <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                    />
                  </svg>
                  Logout Sekarang
                </button>
              </div>

              <!-- Additional Info -->
              <div class="mt-4 text-center text-xs text-muted-foreground">
                <p>Jika tidak ada aktivitas, Anda akan otomatis logout</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = withDefaults(defineProps<{
  isVisible?: boolean;
  timeRemaining?: number;
}>(), {
  isVisible: false,
  timeRemaining: 300,
});

const emit = defineEmits<{
  'extend': [];
  'logout': [];
  'close': [];
}>();

const extending = ref(false);

const formatTime = (seconds: number) => {
  if (seconds <= 0) return '0:00';
  
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  
  if (mins > 0) {
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  }
  
  return `${secs}`;
};

const extendSession = async () => {
  extending.value = true;
  try {
    emit('extend');
    // Small delay for UX
    await new Promise(resolve => setTimeout(resolve, 500));
  } finally {
    extending.value = false;
  }
};

const logout = () => {
  emit('logout');
};

const handleBackdropClick = () => {
  // Don't allow closing by clicking backdrop for critical modal
  // User must choose an action
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: all 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95) translateY(-20px);
  opacity: 0;
}
</style>

