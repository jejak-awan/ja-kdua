<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Slow Query Monitor</h1>
        <p class="text-sm text-muted-foreground mt-1">Track database queries exceeding {{ threshold }}ms threshold</p>
      </div>
      <div class="flex gap-2">
        <Button @click="fetchQueries" variant="outline" size="sm" :disabled="loading">
          <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
          <RefreshCw v-else class="w-4 h-4 mr-2" />
          Refresh
        </Button>
      </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <Card>
        <CardContent class="p-4">
          <div class="text-sm text-muted-foreground">Total Logged</div>
          <div class="text-2xl font-bold text-foreground">{{ stats.total || 0 }}</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-4">
          <div class="text-sm text-muted-foreground">Avg Duration</div>
          <div class="text-2xl font-bold text-foreground">{{ stats.avg_duration || 0 }}ms</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-4">
          <div class="text-sm text-muted-foreground">Max Duration</div>
          <div class="text-2xl font-bold text-foreground">{{ stats.max_duration || 0 }}ms</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-4">
          <div class="text-sm text-muted-foreground">Today</div>
          <div class="text-2xl font-bold text-foreground">{{ stats.today || 0 }}</div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters -->
    <Card class="mb-6">
      <CardContent class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <Label class="mb-2 block">Route</Label>
            <Input v-model="filters.route" placeholder="e.g. api/v1/contents" />
          </div>
          <div>
            <Label class="mb-2 block">Min Duration (ms)</Label>
            <Input v-model.number="filters.min_duration" type="number" placeholder="1000" />
          </div>
          <div>
            <Label class="mb-2 block">Date From</Label>
            <Input v-model="filters.date_from" type="date" />
          </div>
          <div>
            <Label class="mb-2 block">Date To</Label>
            <Input v-model="filters.date_to" type="date" />
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
          <Button @click="applyFilters" size="sm">Apply</Button>
          <Button @click="resetFilters" variant="outline" size="sm">Reset</Button>
        </div>
      </CardContent>
    </Card>

    <!-- Queries Table -->
    <Card>
      <CardContent class="p-0">
        <table class="w-full">
          <thead class="bg-muted/50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Route</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Duration</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">User</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Query</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-if="loading">
              <td colspan="5" class="px-4 py-8 text-center">
                <Loader2 class="w-6 h-6 animate-spin mx-auto text-muted-foreground" />
              </td>
            </tr>
            <tr v-else-if="queries.length === 0">
              <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                No slow queries found. Great job! 🎉
              </td>
            </tr>
            <tr v-for="query in queries" :key="query.id" class="hover:bg-muted/50">
              <td class="px-4 py-3 font-mono text-sm">{{ query.route || 'N/A' }}</td>
              <td class="px-4 py-3">
                <Badge :variant="getDurationVariant(query.duration)">{{ query.duration }}ms</Badge>
              </td>
              <td class="px-4 py-3 text-sm">{{ query.user?.name || 'Guest' }}</td>
              <td class="px-4 py-3">
                <code class="text-xs bg-muted px-2 py-1 rounded block truncate max-w-md" :title="query.query">
                  {{ query.query }}
                </code>
              </td>
              <td class="px-4 py-3 text-sm text-muted-foreground whitespace-nowrap">
                {{ formatDate(query.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </CardContent>
      
      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="border-t border-border px-4 py-3 flex items-center justify-between">
        <span class="text-sm text-muted-foreground">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }}
        </span>
        <div class="flex gap-2">
          <Button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" variant="outline" size="sm">Previous</Button>
          <Button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" variant="outline" size="sm">Next</Button>
        </div>
      </div>
    </Card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import Button from '@/components/ui/button.vue';
import Card from '@/components/ui/card.vue';
import CardContent from '@/components/ui/card-content.vue';
import Input from '@/components/ui/input.vue';
import Label from '@/components/ui/label.vue';
import Badge from '@/components/ui/badge.vue';
import { RefreshCw, Loader2 } from 'lucide-vue-next';

const toast = useToast();
const queries = ref([]);
const stats = ref({});
const loading = ref(false);
const threshold = ref(1000);

const filters = ref({
  route: '',
  min_duration: '',
  date_from: '',
  date_to: '',
  page: 1,
  per_page: 50,
});

const pagination = ref({ total: 0, from: 0, to: 0, current_page: 1, last_page: 1 });

async function fetchQueries() {
  loading.value = true;
  try {
    const response = await api.get('/admin/ja/security/slow-queries', { params: filters.value });
    queries.value = response.data.data.data || [];
    pagination.value = {
      total: response.data.data.total || 0,
      from: response.data.data.from || 0,
      to: response.data.data.to || 0,
      current_page: response.data.data.current_page || 1,
      last_page: response.data.data.last_page || 1,
    };
  } catch (error) {
    console.error('Failed to fetch slow queries:', error);
  } finally {
    loading.value = false;
  }
}

async function fetchStats() {
  try {
    const response = await api.get('/admin/ja/security/slow-queries/statistics');
    stats.value = response.data.data || {};
  } catch (error) {
    console.error('Failed to fetch stats:', error);
  }
}

function applyFilters() {
  filters.value.page = 1;
  fetchQueries();
}

function resetFilters() {
  filters.value = { route: '', min_duration: '', date_from: '', date_to: '', page: 1, per_page: 50 };
  fetchQueries();
}

function changePage(page) {
  filters.value.page = page;
  fetchQueries();
}

function getDurationVariant(duration) {
  if (duration >= 5000) return 'destructive';
  if (duration >= 2000) return 'warning';
  return 'secondary';
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleString();
}

onMounted(() => {
  fetchQueries();
  fetchStats();
});
</script>
