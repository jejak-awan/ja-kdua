<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Dependency Vulnerabilities</h1>
        <p class="text-sm text-muted-foreground mt-1">Security audit results for Composer and NPM packages</p>
      </div>
      <div class="flex gap-2">
        <Button @click="runAudit" variant="default" size="sm" :disabled="auditing">
          <Loader2 v-if="auditing" class="w-4 h-4 mr-2 animate-spin" />
          <Shield v-else class="w-4 h-4 mr-2" />
          Run Audit
        </Button>
        <Button @click="fetchVulnerabilities" variant="outline" size="sm" :disabled="loading">
          <RefreshCw class="w-4 h-4 mr-2" />
          Refresh
        </Button>
      </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
      <Card>
        <CardContent class="p-4">
          <div class="text-sm text-muted-foreground">Total</div>
          <div class="text-2xl font-bold text-foreground">{{ stats.total || 0 }}</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-4">
          <div class="text-sm text-muted-foreground">Critical</div>
          <div class="text-2xl font-bold text-red-600">{{ stats.critical || 0 }}</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-4">
          <div class="text-sm text-muted-foreground">High</div>
          <div class="text-2xl font-bold text-orange-600">{{ stats.high || 0 }}</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-4">
          <div class="text-sm text-muted-foreground">Medium</div>
          <div class="text-2xl font-bold text-yellow-600">{{ stats.medium || 0 }}</div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-4">
          <div class="text-sm text-muted-foreground">Low</div>
          <div class="text-2xl font-bold text-blue-600">{{ stats.low || 0 }}</div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters -->
    <Card class="mb-6">
      <CardContent class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <Label class="mb-2 block">Source</Label>
            <select v-model="filters.source" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
              <option value="">All</option>
              <option value="composer">Composer</option>
              <option value="npm">NPM</option>
            </select>
          </div>
          <div>
            <Label class="mb-2 block">Severity</Label>
            <select v-model="filters.severity" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
              <option value="">All</option>
              <option value="critical">Critical</option>
              <option value="high">High</option>
              <option value="medium">Medium</option>
              <option value="low">Low</option>
            </select>
          </div>
          <div>
            <Label class="mb-2 block">Status</Label>
            <select v-model="filters.status" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
              <option value="">All</option>
              <option value="new">New</option>
              <option value="acknowledged">Acknowledged</option>
              <option value="patched">Patched</option>
              <option value="ignored">Ignored</option>
            </select>
          </div>
          <div>
            <Label class="mb-2 block">Package</Label>
            <Input v-model="filters.package" placeholder="Search package..." />
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
          <Button @click="applyFilters" size="sm">Apply</Button>
          <Button @click="resetFilters" variant="outline" size="sm">Reset</Button>
        </div>
      </CardContent>
    </Card>

    <!-- Vulnerabilities Table -->
    <Card>
      <CardContent class="p-0">
        <table class="w-full">
          <thead class="bg-muted/50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Package</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Version</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Severity</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">CVE</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Source</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr v-if="loading">
              <td colspan="7" class="px-4 py-8 text-center">
                <Loader2 class="w-6 h-6 animate-spin mx-auto text-muted-foreground" />
              </td>
            </tr>
            <tr v-else-if="vulnerabilities.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                <ShieldCheck class="w-12 h-12 mx-auto mb-2 text-green-500" />
                No vulnerabilities found. Your dependencies are secure! 🛡️
              </td>
            </tr>
            <tr v-for="vuln in vulnerabilities" :key="vuln.id" class="hover:bg-muted/50">
              <td class="px-4 py-3 font-medium">{{ vuln.package_name }}</td>
              <td class="px-4 py-3 font-mono text-sm">{{ vuln.version }}</td>
              <td class="px-4 py-3">
                <Badge :variant="getSeverityVariant(vuln.severity)">{{ vuln.severity }}</Badge>
              </td>
              <td class="px-4 py-3">
                <a v-if="vuln.cve" :href="`https://nvd.nist.gov/vuln/detail/${vuln.cve}`" target="_blank" class="text-blue-600 hover:underline text-sm">
                  {{ vuln.cve }}
                </a>
                <span v-else class="text-muted-foreground">-</span>
              </td>
              <td class="px-4 py-3">
                <Badge variant="outline">{{ vuln.source }}</Badge>
              </td>
              <td class="px-4 py-3">
                <Badge :variant="getStatusVariant(vuln.status)">{{ vuln.status }}</Badge>
              </td>
              <td class="px-4 py-3">
                <select @change="updateStatus(vuln, ($event.target as any).value)" :value="vuln.status" class="text-xs rounded border border-input bg-background px-2 py-1">
                  <option value="new">New</option>
                  <option value="acknowledged">Acknowledged</option>
                  <option value="patched">Patched</option>
                  <option value="ignored">Ignored</option>
                </select>
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

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import {
    Button,
    Card,
    CardContent,
    Input,
    Label,
    Badge
} from '@/components/ui';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Shield from 'lucide-vue-next/dist/esm/icons/shield.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';

const toast = useToast();
const vulnerabilities = ref<any[]>([]);
const loading = ref(false);
const auditing = ref(false);

const filters = ref({
  source: '',
  severity: '',
  status: '',
  package: '',
  page: 1,
  per_page: 50,
});

const pagination = ref({ total: 0, from: 0, to: 0, current_page: 1, last_page: 1 });

const stats = computed(() => {
  const all = vulnerabilities.value;
  return {
    total: pagination.value.total,
    critical: all.filter(v => v.severity === 'critical').length,
    high: all.filter(v => v.severity === 'high').length,
    medium: all.filter(v => v.severity === 'medium').length,
    low: all.filter(v => v.severity === 'low').length,
  };
});

async function fetchVulnerabilities() {
  loading.value = true;
  try {
    const response = await api.get('/admin/ja/security/dependency-vulnerabilities', { params: filters.value });
    vulnerabilities.value = response.data.data.data || [];
    pagination.value = {
      total: response.data.data.total || 0,
      from: response.data.data.from || 0,
      to: response.data.data.to || 0,
      current_page: response.data.data.current_page || 1,
      last_page: response.data.data.last_page || 1,
    };
  } catch (error: any) {
    console.error('Failed to fetch vulnerabilities:', error);
  } finally {
    loading.value = false;
  }
}

async function runAudit() {
  auditing.value = true;
  try {
    await api.post('/admin/ja/security/run-dependency-audit');
    toast.success.action('Dependency audit completed');
    fetchVulnerabilities();
  } catch (error: any) {
    toast.error.fromResponse(error);
    console.error(error);
  } finally {
    auditing.value = false;
  }
}

async function updateStatus(vuln: any, status: string) {
  try {
    await api.put(`/admin/ja/security/dependency-vulnerabilities/${vuln.id}`, { status });
    vuln.status = status;
    toast.success.action('Status updated');
  } catch (error: any) {
    toast.error.fromResponse(error);
  }
}

function applyFilters() {
  filters.value.page = 1;
  fetchVulnerabilities();
}

function resetFilters() {
  filters.value = { source: '', severity: '', status: '', package: '', page: 1, per_page: 50 };
  fetchVulnerabilities();
}

function changePage(page: number) {
  filters.value.page = page;
  fetchVulnerabilities();
}

function getSeverityVariant(severity: string) {
  const variants: Record<string, string> = { critical: 'destructive', high: 'secondary', medium: 'secondary', low: 'outline' };
  return (variants[severity] || 'secondary') as any;
}

function getStatusVariant(status: string) {
  const variants: Record<string, string> = { new: 'destructive', acknowledged: 'secondary', patched: 'default', ignored: 'secondary' };
  return (variants[status] || 'secondary') as any;
}

onMounted(fetchVulnerabilities);
</script>
