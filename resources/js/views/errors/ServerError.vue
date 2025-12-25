<template>
    <ErrorLayout>
        <template #icon>
            <div class="h-20 w-20 rounded-2xl bg-red-500/10 border border-red-500/20 flex items-center justify-center">
                <svg class="h-10 w-10 text-red-600 dark:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </template>

        <template #title>
            500
        </template>

        <template #message>
            {{ t('features.errors.500.title') }}
        </template>

        <template #description>
            {{ t('features.errors.500.message') }}
        </template>

        <template #details>
            <!-- Status Check (Compact) -->
            <div class="flex items-center justify-center gap-4 text-xs font-mono bg-muted border border-border rounded-xl p-3 mb-4">
                <div class="flex items-center gap-1.5">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="serverStatus.api ? 'bg-green-500' : 'bg-red-500'"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2" :class="serverStatus.api ? 'bg-green-500' : 'bg-red-500'"></span>
                    </span>
                    <span class="text-muted-foreground">{{ t('features.errors.500.api') }}</span>
                </div>
                <div class="w-px h-3 bg-border"></div>
                <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full" :class="serverStatus.database ? 'bg-green-500' : 'bg-red-500'"></span>
                    <span class="text-muted-foreground">{{ t('features.errors.500.database') }}</span>
                </div>
            </div>

            <!-- Error Details Toggle -->
            <div v-if="errorDetails" class="w-full">
                <button
                    @click="showDetails = !showDetails"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-muted-foreground hover:text-foreground hover:bg-muted/50 rounded-lg transition-all"
                >
                    <span>{{ showDetails ? t('features.errors.500.hideDetails') : t('features.errors.500.showDetails') }}</span>
                    <svg
                        class="w-3 h-3 transform transition-transform duration-200"
                        :class="{ 'rotate-180': showDetails }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                
                <transition name="slide-fade">
                    <div v-if="showDetails" class="mt-2 p-3 bg-muted border border-border rounded-xl text-left max-h-48 overflow-y-auto custom-scrollbar">
                        <pre class="text-[10px] sm:text-xs font-mono text-foreground whitespace-pre-wrap break-all leading-relaxed">{{ errorDetails }}</pre>
                    </div>
                </transition>
            </div>
        </template>

        <template #actions>
            <button
                @click="retry"
                :disabled="retrying"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-xl text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500 shadow-xl shadow-red-500/20 transition-all disabled:opacity-70 disabled:cursor-not-allowed"
            >
                <svg
                    class="w-4 h-4 mr-2"
                    :class="{ 'animate-spin': retrying }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ retrying ? t('features.errors.500.retrying') : t('features.errors.500.retry') }}
            </button>
            
            <router-link
                to="/"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-border text-sm font-medium rounded-xl text-foreground bg-background hover:bg-muted/50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500 transition-all"
            >
                <svg class="w-4 h-4 mr-2 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                {{ t('features.errors.404.home') }}
            </router-link>
        </template>

        <template #footer>
            <div class="flex items-center justify-center gap-3">
                <button
                    @click="reportIssue"
                    class="text-muted-foreground hover:text-red-500 transition-colors flex items-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ t('features.errors.500.report') }}
                </button>
                <span class="w-0.5 h-3 bg-border"></span>
                <span class="font-mono text-[10px] opacity-50">{{ errorId }}</span>
            </div>
        </template>
    </ErrorLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import ErrorLayout from '@/layouts/ErrorLayout.vue';

const router = useRouter();
const { t } = useI18n();
const showDetails = ref(false);
const retrying = ref(false);
const errorId = ref(generateErrorId());
const errorDetails = ref(getErrorDetails());

const serverStatus = ref({
  api: false,
  database: false,
  cache: false,
});

function generateErrorId() {
  return `ERR-${Date.now().toString().slice(-6)}-${Math.random().toString(36).substring(7).toUpperCase()}`;
}

function getErrorDetails() {
  const state = window.history.state;
  return state?.errorDetails || null;
}

const retry = async () => {
  retrying.value = true;
  await new Promise(resolve => setTimeout(resolve, 1000));
  window.location.reload();
};

const reportIssue = () => {
  const subject = encodeURIComponent(`Server Error Report - ${errorId.value}`);
  const body = encodeURIComponent(`Error ID: ${errorId.value}\nTimestamp: ${new Date().toISOString()}\nURL: ${window.location.href}\n\nDeskripsi masalah:\n`);
  window.location.href = `mailto:support@jejakawan.com?subject=${subject}&body=${body}`;
};

const checkServerStatus = async () => {
  try {
    // Simulated check
    serverStatus.value.api = true;
    serverStatus.value.database = true;
  } catch (e) {
    serverStatus.value.api = false;
  }
};

onMounted(() => {
  checkServerStatus();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 4px;
}
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-5px);
  opacity: 0;
}
</style>

