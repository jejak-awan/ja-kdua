<template>
  <div class="system-health-widget bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
        <svg class="w-5 h-5 mr-2" :class="overallStatusClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        System Health
      </h3>
      <button
        @click="refresh"
        :disabled="loading"
        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
        title="Refresh"
      >
        <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
      </button>
    </div>

    <!-- Overall Status -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Overall Status</span>
        <span :class="overallStatusBadgeClass" class="px-3 py-1 rounded-full text-xs font-semibold">
          {{ overallStatusText }}
        </span>
      </div>
    </div>

    <!-- Health Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <!-- CPU -->
      <div class="health-metric">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
            </svg>
            CPU
          </span>
          <span :class="getStatusClass(health.cpu?.status)" class="text-xs font-semibold">
            {{ health.cpu?.percent || 0 }}%
          </span>
        </div>
        <div class="progress-bar">
          <div
            :class="getProgressBarClass(health.cpu?.status)"
            :style="{ width: `${health.cpu?.percent || 0}%` }"
            class="progress-fill"
          ></div>
        </div>
        <div v-if="health.cpu?.load" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          Load: {{ health.cpu.load }}
        </div>
      </div>

      <!-- Memory -->
      <div class="health-metric">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
            </svg>
            Memory
          </span>
          <span :class="getStatusClass(health.memory?.status)" class="text-xs font-semibold">
            {{ health.memory?.percent || 0 }}%
          </span>
        </div>
        <div class="progress-bar">
          <div
            :class="getProgressBarClass(health.memory?.status)"
            :style="{ width: `${health.memory?.percent || 0}%` }"
            class="progress-fill"
          ></div>
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          {{ health.memory?.used || '0 B' }} / {{ health.memory?.total || '0 B' }}
        </div>
      </div>

      <!-- Disk -->
      <div class="health-metric">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
            </svg>
            Disk
          </span>
          <span :class="getStatusClass(health.disk?.status)" class="text-xs font-semibold">
            {{ health.disk?.percent || 0 }}%
          </span>
        </div>
        <div class="progress-bar">
          <div
            :class="getProgressBarClass(health.disk?.status)"
            :style="{ width: `${health.disk?.percent || 0}%` }"
            class="progress-fill"
          ></div>
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          {{ health.disk?.used || '0 B' }} / {{ health.disk?.total || '0 B' }}
        </div>
      </div>

      <!-- Database -->
      <div class="health-metric">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
            </svg>
            Database
          </span>
          <span :class="getStatusClass(health.database?.status)" class="text-xs font-semibold">
            {{ health.database?.status === 'ok' ? 'OK' : 'Error' }}
          </span>
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">
          {{ health.database?.message || 'Unknown' }}
        </div>
      </div>

      <!-- Redis -->
      <div class="health-metric">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Redis
          </span>
          <span :class="getStatusClass(health.redis?.status)" class="text-xs font-semibold">
            {{ health.redis?.status === 'ok' ? 'OK' : health.redis?.status === 'disabled' ? 'Disabled' : 'Error' }}
          </span>
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">
          {{ health.redis?.message || 'Unknown' }}
        </div>
      </div>
    </div>

    <!-- Last Updated -->
    <div v-if="lastUpdated" class="mt-4 text-xs text-gray-500 dark:text-gray-400 text-center">
      Last updated: {{ formatTime(lastUpdated) }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import api from '@/services/api';
import { parseSingleResponse } from '@/utils/responseParser';

const health = ref({
  cpu: { percent: 0, status: 'unknown' },
  memory: { percent: 0, used: '0 B', total: '0 B', status: 'unknown' },
  disk: { percent: 0, used: '0 B', total: '0 B', status: 'unknown' },
  database: { status: 'unknown', message: 'Unknown' },
  redis: { status: 'unknown', message: 'Unknown' },
  overall: 'unknown',
});

const loading = ref(false);
const lastUpdated = ref(null);
let refreshInterval = null;

const overallStatus = computed(() => health.value.overall || 'unknown');

const overallStatusText = computed(() => {
  const status = overallStatus.value;
  return status.charAt(0).toUpperCase() + status.slice(1);
});

const overallStatusClass = computed(() => {
  const status = overallStatus.value;
  if (status === 'healthy') return 'text-green-600 dark:text-green-400';
  if (status === 'warning') return 'text-yellow-600 dark:text-yellow-400';
  if (status === 'critical') return 'text-red-600 dark:text-red-400';
  return 'text-gray-600 dark:text-gray-400';
});

const overallStatusBadgeClass = computed(() => {
  const status = overallStatus.value;
  if (status === 'healthy') return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
  if (status === 'warning') return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
  if (status === 'critical') return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
  return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400';
});

const getStatusClass = (status) => {
  if (status === 'ok') return 'text-green-600 dark:text-green-400';
  if (status === 'warning') return 'text-yellow-600 dark:text-yellow-400';
  if (status === 'critical' || status === 'error') return 'text-red-600 dark:text-red-400';
  return 'text-gray-600 dark:text-gray-400';
};

const getProgressBarClass = (status) => {
  if (status === 'ok') return 'bg-green-500';
  if (status === 'warning') return 'bg-yellow-500';
  if (status === 'critical' || status === 'error') return 'bg-red-500';
  return 'bg-gray-400';
};

const fetchHealth = async () => {
  loading.value = true;
  try {
    const response = await api.get('/admin/cms/system/health/detailed');
    const data = parseSingleResponse(response);
    if (data) {
      health.value = data;
      lastUpdated.value = new Date();
    }
  } catch (error) {
    console.error('Failed to fetch system health:', error);
  } finally {
    loading.value = false;
  }
};

const refresh = () => {
  fetchHealth();
};

const formatTime = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
};

onMounted(() => {
  fetchHealth();
  // Auto-refresh every 30 seconds
  refreshInterval = setInterval(fetchHealth, 30000);
});

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});
</script>

<style scoped>
.health-metric {
  padding: 0.75rem;
  background-color: #f9fafb;
  border-radius: 0.5rem;
}

.dark .health-metric {
  background-color: #1f2937;
}

.progress-bar {
  width: 100%;
  height: 0.5rem;
  background-color: #e5e7eb;
  border-radius: 0.25rem;
  overflow: hidden;
}

.dark .progress-bar {
  background-color: #374151;
}

.progress-fill {
  height: 100%;
  transition: width 0.3s ease;
  border-radius: 0.25rem;
}
</style>

