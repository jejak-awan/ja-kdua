<template>
  <div class="csp-reports-container p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">CSP Violation Reports</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Monitor Content Security Policy violations and potential XSS attacks</p>
      </div>
      <div class="flex gap-2">
        <Button @click="refreshReports" variant="outline" size="sm">
          <i class="fas fa-sync-alt mr-2"></i>
          Refresh
        </Button>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
        <div class="text-sm text-gray-600 dark:text-gray-400">Total Reports</div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total || 0 }}</div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
        <div class="text-sm text-gray-600 dark:text-gray-400">New Reports</div>
        <div class="text-2xl font-bold text-orange-600">{{ stats.new || 0 }}</div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
        <div class="text-sm text-gray-600 dark:text-gray-400">Top Violation</div>
        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
          {{ topViolation || 'None' }}
        </div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
        <div class="text-sm text-gray-600 dark:text-gray-400">Last 24h</div>
        <div class="text-2xl font-bold text-blue-600">{{ recentCount || 0 }}</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
          <select v-model="filters.status" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">All</option>
            <option value="new">New</option>
            <option value="reviewed">Reviewed</option>
            <option value="false_positive">False Positive</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Directive</label>
          <input v-model="filters.directive" type="text" placeholder="e.g. script-src" 
                 class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date From</label>
          <input v-model="filters.date_from" type="date" 
                 class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date To</label>
          <input v-model="filters.date_to" type="date" 
                 class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        </div>
      </div>
      <div class="flex justify-end gap-2 mt-4">
        <Button @click="applyFilters" variant="default" size="sm">Apply Filters</Button>
        <Button @click="resetFilters" variant="outline" size="sm">Reset</Button>
      </div>
    </div>

    <div v-if="selectedReports.length > 0" class="bg-muted/50 border border-border rounded-lg p-4 mb-4 flex items-center justify-between transition-all duration-200">
      <span class="text-sm font-medium text-foreground">
        {{ selectedReports.length }} report(s) selected
      </span>
        <div class="flex gap-2">
          <Button @click="bulkAction('mark_reviewed')" variant="outline" size="sm">Mark Reviewed</Button>
          <Button @click="bulkAction('mark_false_positive')" variant="outline" size="sm">Mark False Positive</Button>
          <Button @click="bulkAction('delete')" variant="destructive" size="sm">Delete</Button>
        </div>
      </div>

    <!-- Reports Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
          <tr>
            <th class="px-4 py-3">
              <input type="checkbox" @change="toggleSelectAll" :checked="allSelected" 
                     class="rounded border-gray-300 dark:border-gray-600">
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
              Violated Directive
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
              Blocked URI
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
              Document URI
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
              IP Address
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
              Status
            </th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
              Date
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="report in reports" :key="report.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-4 py-3">
              <input type="checkbox" v-model="selectedReports" :value="report.id" 
                     class="rounded border-gray-300 dark:border-gray-600">
            </td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                {{ report.violated_directive }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
              <div class="truncate max-w-xs" :title="report.blocked_uri">
                {{ report.blocked_uri || 'N/A' }}
              </div>
            </td>
            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
              <div class="truncate max-w-xs" :title="report.document_uri">
                {{ report.document_uri }}
              </div>
            </td>
            <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-gray-100">
              {{ report.ip_address }}
            </td>
            <td class="px-4 py-3">
              <Badge :variant="getStatusVariant(report.status)">
                {{ formatStatus(report.status) }}
              </Badge>
            </td>
            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
              {{ formatDate(report.created_at) }}
            </td>
          </tr>
          <tr v-if="reports.length === 0">
            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
              No CSP reports found
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="bg-gray-50 dark:bg-gray-900 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
        </div>
        <div class="flex gap-2">
          <Button @click="changePage(pagination.current_page - 1)" 
                  :disabled="pagination.current_page === 1" 
                  variant="outline" size="sm">Previous</Button>
          <Button @click="changePage(pagination.current_page + 1)" 
                  :disabled="pagination.current_page === pagination.last_page" 
                  variant="outline" size="sm">Next</Button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import Button from '@/components/ui/button.vue';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const reports = ref([]);
const stats = ref({});
const selectedReports = ref([]);
const loading = ref(false);

const filters = ref({
  status: '',
  directive: '',
  date_from: '',
  date_to: '',
  page: 1,
  per_page: 50,
});

const pagination = ref({
  total: 0,
  per_page: 50,
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
});

const topViolation = computed(() => {
  if (!stats.value.by_directive || stats.value.by_directive.length === 0) return 'None';
  return stats.value.by_directive[0].violated_directive;
});

const recentCount = computed(() => {
  if (!stats.value.recent_trend) return 0;
  const lastDay = stats.value.recent_trend[stats.value.recent_trend.length - 1];
  return lastDay ? lastDay.count : 0;
});

const allSelected = computed(() => {
  return reports.value.length > 0 && selectedReports.value.length === reports.value.length;
});

async function fetchReports() {
  loading.value = true;
  try {
    const response = await api.get('/admin/cms/security/csp-reports', { params: filters.value });
    reports.value = response.data.data.data;
    pagination.value = {
      total: response.data.data.total,
      per_page: response.data.data.per_page,
      current_page: response.data.data.current_page,
      last_page: response.data.data.last_page,
      from: response.data.data.from,
      to: response.data.data.to,
    };
  } catch (error) {
    toast.error.fromResponse(error);
    console.error(error);
  } finally {
    loading.value = false;
  }
}

async function fetchStatistics() {
  try {
    const response = await api.get('/admin/cms/security/csp-reports/statistics');
    stats.value = response.data.data;
  } catch (error) {
    console.error('Failed to load statistics', error);
  }
}

function applyFilters() {
  filters.value.page = 1;
  fetchReports();
}

function resetFilters() {
  filters.value = {
    status: '',
    directive: '',
    date_from: '',
    date_to: '',
    page: 1,
    per_page: 50,
  };
  fetchReports();
}

function changePage(page) {
  filters.value.page = page;
  fetchReports();
}

function toggleSelectAll(event) {
  if (event.target.checked) {
    selectedReports.value = reports.value.map(r => r.id);
  } else {
    selectedReports.value = [];
  }
}

async function bulkAction(action) {
  if (selectedReports.value.length === 0) return;

  const confirmed = confirm(`Are you sure you want to ${action.replace('_', ' ')} ${selectedReports.value.length} report(s)?`);
  if (!confirmed) return;

  try {
    await api.post('/admin/cms/security/csp-reports/bulk-action', {
      ids: selectedReports.value,
      action,
    });
    toast.success.action('Bulk action completed');
    selectedReports.value = [];
    fetchReports();
    fetchStatistics();
  } catch (error) {
    toast.error.fromResponse(error);
    console.error(error);
  }
}

function refreshReports() {
  fetchReports();
  fetchStatistics();
}

function getStatusVariant(status) {
  const variants = {
    new: 'warning',
    reviewed: 'info',
    false_positive: 'secondary',
  };
  return variants[status] || 'secondary';
}

function formatStatus(status) {
  return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

onMounted(() => {
  fetchReports();
  fetchStatistics();
});
</script>

