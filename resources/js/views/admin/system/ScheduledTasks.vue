<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-foreground">{{ $t('features.scheduled_tasks.title') }}</h1>
        <p class="text-muted-foreground mt-1 text-sm">{{ $t('features.scheduled_tasks.description') }}</p>
      </div>
      <div class="flex gap-2">
        <Button v-if="isSuperAdmin" variant="outline" @click="openRunCommandDialog">
          <Terminal class="w-4 h-4 mr-2" />
          {{ $t('features.command_runner.run') }}
        </Button>
        <Button @click="openCreateDialog">
          <Plus class="w-4 h-4 mr-2" />
          {{ $t('features.scheduled_tasks.create') }}
        </Button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-card border border-border rounded-lg p-4 sticky top-0 z-10 shadow-sm backdrop-blur-xl bg-card/80">
      <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
        <!-- Search -->
        <div class="flex items-center gap-3 flex-1 w-full md:w-auto">
          <div class="relative flex-1 md:max-w-xs">
            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="search"
              :placeholder="$t('common.actions.search')"
              class="pl-9 h-9 w-full md:w-[280px]"
              @input="debouncedSearch"
            />
          </div>
        </div>

        <!-- Bulk Actions & View Toggle -->
        <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
          <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div v-if="selectedTasks.length > 0" class="flex items-center gap-2">
              <span class="text-sm text-muted-foreground hidden sm:inline-block">
                {{ selectedTasks.length }} selected
              </span>
              <Select v-model="bulkActionSelection" @update:modelValue="handleBulkAction">
                <SelectTrigger class="w-[140px] h-9 bg-primary/5 border-primary/20 text-primary hover:bg-primary/10 transition-colors">
                  <SelectValue :placeholder="$t('common.actions.bulkAction')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="delete" class="text-destructive focus:text-destructive">
                    <div class="flex items-center">
                      <Trash2 class="w-4 h-4 mr-2" />
                      {{ $t('common.actions.delete') }}
                    </div>
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </Transition>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="space-y-2">
        <div v-for="i in 3" :key="i" class="h-16 rounded-lg bg-card border border-border animate-pulse"></div>
    </div>

    <!-- No Tasks State -->
    <div v-else-if="tasks.length === 0" class="text-center py-12 bg-card border border-border rounded-lg">
      <div class="p-4 rounded-full bg-muted w-16 h-16 mx-auto mb-4 flex items-center justify-center">
        <Calendar class="w-8 h-8 text-muted-foreground" />
      </div>
      <h3 class="text-lg font-medium text-foreground mb-1">{{ $t('features.scheduled_tasks.no_tasks') }}</h3>
      <p class="text-muted-foreground text-sm max-w-sm mx-auto mb-6">
        {{ search ? $t('common.messages.empty.search', { query: search }) : $t('features.scheduled_tasks.description') }}
      </p>
      <Button v-if="search" variant="outline" @click="search = ''; fetchTasks()">
        {{ $t('common.actions.clear') }}
      </Button>
      <Button v-else @click="openCreateDialog">
        <Plus class="w-4 h-4 mr-2" />
        {{ $t('features.scheduled_tasks.create') }}
      </Button>
    </div>

    <!-- Tasks List -->
    <div v-else class="bg-card border border-border rounded-lg overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-muted/50 border-b">
            <TableRow>
              <TableHead class="w-10 px-6 py-3">
                <Checkbox 
                  :checked="isAllSelected"
                  @update:checked="toggleSelectAll"
                />
              </TableHead>
              <TableHead class="px-6 py-3 font-medium text-muted-foreground font-medium">{{ $t('features.scheduled_tasks.table.name') }}</TableHead>
              <TableHead class="px-6 py-3 font-medium text-muted-foreground font-medium">{{ $t('features.scheduled_tasks.table.command') }}</TableHead>
              <TableHead class="px-6 py-3 font-medium text-muted-foreground font-medium">{{ $t('features.scheduled_tasks.table.schedule') }}</TableHead>
              <TableHead class="px-6 py-3 font-medium text-muted-foreground font-medium">{{ $t('features.scheduled_tasks.table.last_run') }}</TableHead>
              <TableHead class="px-6 py-3 font-medium text-muted-foreground font-medium">{{ $t('features.scheduled_tasks.table.status') }}</TableHead>
              <TableHead class="px-6 py-3 font-medium text-muted-foreground font-medium">{{ $t('features.scheduled_tasks.table.active') }}</TableHead>
              <TableHead class="px-6 py-3 font-medium text-right text-muted-foreground font-medium">{{ $t('features.scheduled_tasks.table.actions') }}</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody class="divide-y divide-border">
            <TableRow 
              v-for="task in tasks" 
              :key="task.id" 
              class="group hover:bg-muted/50 transition-colors"
              :class="{ 'bg-muted/30': selectedTasks.includes(task.id) }"
            >
              <TableCell class="px-6 py-4">
                <Checkbox 
                  :checked="selectedTasks.includes(task.id)"
                  @update:checked="(checked) => toggleSelection(task.id)"
                />
              </TableCell>
              <TableCell class="px-6 py-4 font-semibold">{{ task.name }}</TableCell>
              <TableCell class="px-6 py-4">
                <code class="text-[11px] bg-muted/50 px-2 py-0.5 rounded border border-border dark:bg-muted/20">{{ task.command }}</code>
              </TableCell>
              <TableCell class="px-6 py-4">
                <code class="text-[11px] font-mono font-bold text-primary">{{ task.schedule }}</code>
              </TableCell>
              <TableCell class="px-6 py-4">
                <div v-if="task.last_run_at" class="flex flex-col">
                  <span class="text-sm font-medium">{{ formatDate(task.last_run_at) }}</span>
                  <span class="text-[10px] text-muted-foreground tabular-nums">{{ task.last_run_duration ? (task.last_run_duration / 1000).toFixed(2) + 's' : '' }}</span>
                </div>
                <span v-else class="text-muted-foreground text-sm italic">{{ $t('common.labels.never') }}</span>
              </TableCell>
              <TableCell class="px-6 py-4">
                <Badge 
                  :variant="getStatusVariant(task.status)"
                  class="capitalize text-[10px] px-2 py-0"
                >
                  {{ $t('features.scheduled_tasks.status.' + (task.status || 'pending')) }}
                </Badge>
              </TableCell>
              <TableCell class="px-6 py-4">
                 <Button 
                    size="sm" 
                    :variant="task.is_active ? 'default' : 'secondary'"
                    @click="toggleActive(task)"
                    class="h-6 text-[10px] font-bold px-2"
                  >
                    {{ task.is_active ? $t('common.labels.active') : $t('common.labels.inactive') }}
                  </Button>
              </TableCell>
              <TableCell class="px-6 py-4 text-right">
                <div class="flex justify-end gap-1">
                  <Button 
                    size="icon" 
                    variant="ghost"
                    @click="runTask(task)"
                    :disabled="running === task.id"
                    :title="$t('common.actions.run')"
                    class="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20"
                  >
                    <Loader2 v-if="running === task.id" class="w-4 h-4 animate-spin" />
                    <Play v-else class="w-4 h-4" />
                  </Button>
                  <Button 
                    size="icon" 
                    variant="ghost"
                    @click="viewOutput(task)"
                    v-if="task.output"
                    :title="$t('features.scheduled_tasks.output.title')"
                    class="h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                  >
                    <FileText class="w-4 h-4" />
                  </Button>
                  <Button 
                    size="icon" 
                    variant="ghost"
                    @click="editTask(task)"
                    :title="$t('common.actions.edit')"
                    class="h-8 w-8 text-orange-600 hover:text-orange-700 hover:bg-orange-50 dark:hover:bg-orange-900/20"
                  >
                    <Pencil class="w-4 h-4" />
                  </Button>
                  <Button 
                    size="icon" 
                    variant="ghost"
                    @click="confirmDelete(task)"
                    :title="$t('common.actions.delete')"
                    class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                  >
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>
    
    <!-- Pagination -->
    <div class="mt-6">
        <Pagination
            v-if="pagination.total > 0"
            :total="pagination.total"
            :per-page="pagination.per_page"
            :current-page="pagination.current_page"
            @page-change="changePage"
        />
    </div>

    <!-- Create/Edit Dialog -->
    <Dialog v-model:open="dialogOpen">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>
            {{ editingTask ? $t('features.scheduled_tasks.edit') : $t('features.scheduled_tasks.create') }}
          </DialogTitle>
        </DialogHeader>
        
        <form @submit.prevent="saveTask" class="space-y-4 py-4">
          <div class="grid gap-2">
            <Label>{{ $t('features.scheduled_tasks.form.name') }}</Label>
            <Input v-model="form.name" required :class="{ 'border-destructive focus-visible:ring-destructive': errors.name }" />
            <p v-if="errors.name" class="text-sm text-destructive">{{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}</p>
          </div>

          <div class="grid gap-2">
            <Label>{{ $t('features.scheduled_tasks.form.command') }}</Label>
            <Select v-model="form.command" required>
              <SelectTrigger :class="{ 'border-destructive focus:ring-destructive': errors.command }">
                <SelectValue :placeholder="$t('features.scheduled_tasks.form.select_command')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem 
                  v-for="cmd in allowedCommands" 
                  :key="cmd.value" 
                  :value="cmd.value"
                >
                  {{ cmd.label }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p v-if="errors.command" class="text-sm text-destructive mt-1">{{ Array.isArray(errors.command) ? errors.command[0] : errors.command }}</p>
          </div>

          <div class="grid gap-2">
            <Label>{{ $t('features.scheduled_tasks.form.schedule') }}</Label>
            <div class="flex gap-2">
              <Select v-model="cronPreset" @update:model-value="applyCronPreset">
                <SelectTrigger class="w-48">
                  <SelectValue :placeholder="$t('features.scheduled_tasks.form.preset')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="hourly">Hourly</SelectItem>
                  <SelectItem value="daily">Daily at midnight</SelectItem>
                  <SelectItem value="weekly">Weekly on Sunday</SelectItem>
                  <SelectItem value="monthly">Monthly on 1st</SelectItem>
                </SelectContent>
              </Select>
              <Input 
                v-model="form.schedule" 
                placeholder="* * * * *" 
                class="flex-1 font-mono"
                required
                :class="{ 'border-destructive focus-visible:ring-destructive': errors.schedule }"
              />
            </div>
             <p v-if="errors.schedule" class="text-sm text-destructive mt-1">{{ Array.isArray(errors.schedule) ? errors.schedule[0] : errors.schedule }}</p>
            <p class="text-xs text-muted-foreground">
              {{ $t('features.scheduled_tasks.form.cron_help') }}
            </p>
          </div>

          <div class="grid gap-2">
            <Label>{{ $t('features.scheduled_tasks.form.description') }}</Label>
            <Textarea v-model="form.description" rows="3" />
          </div>

          <div class="flex items-center gap-2">
            <input type="checkbox" v-model="form.is_active" id="is_active" class="h-4 w-4 rounded border-input" />
            <Label for="is_active" class="cursor-pointer">{{ $t('features.scheduled_tasks.form.active') }}</Label>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="dialogOpen = false">
              {{ $t('common.actions.cancel') }}
            </Button>
            <Button type="submit" :disabled="saving || !isValid || (editingTask && !isDirty)">
              {{ saving ? $t('common.actions.save') + '...' : $t('common.actions.save') }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Run Command Dialog (New) -->
    <Dialog v-model:open="runCommandDialogOpen">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>{{ $t('features.command_runner.title') }}</DialogTitle>
        </DialogHeader>
        
        <div class="grid gap-4 py-4">
          <div class="grid gap-2">
            <Label>{{ $t('features.command_runner.select_command') }}</Label>
            <Select v-model="adhocCommand.command">
              <SelectTrigger>
                <SelectValue :placeholder="$t('features.command_runner.select_placeholder')" />
              </SelectTrigger>
              <SelectContent>
                 <SelectItem 
                  v-for="cmd in allowedCommands" 
                  :key="cmd.value" 
                  :value="cmd.value"
                >
                  {{ cmd.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="grid gap-2">
            <Label>{{ $t('features.command_runner.parameters') }}</Label>
            <Input 
              v-model="adhocCommand.parameters" 
              :placeholder="$t('features.command_runner.parameters_placeholder')" 
            />
            <p v-if="commandHint" class="text-xs text-muted-foreground italic">
              💡 {{ commandHint }}
            </p>
          </div>

          <div v-if="adhocOutput" class="mt-4">
             <Label class="mb-2 block">Output</Label>
             <div class="bg-black text-green-400 p-4 rounded-md font-mono text-sm overflow-auto max-h-[300px] border border-border">
                <div v-if="adhocExecuting" class="flex items-center gap-2">
                  <Loader2 class="w-4 h-4 animate-spin" />
                  <span>{{ $t('features.command_runner.executing') }}</span>
                </div>
                <pre v-else class="whitespace-pre-wrap break-words">{{ adhocOutput }}</pre>
             </div>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="runCommandDialogOpen = false">
            {{ $t('common.actions.close') }}
          </Button>
          <Button 
            @click="runAdhocCommand" 
            :disabled="!adhocCommand.command || adhocExecuting"
          >
            <Loader2 v-if="adhocExecuting" class="w-4 h-4 mr-2 animate-spin" />
            <Play v-else class="w-4 h-4 mr-2" />
            {{ adhocExecuting ? $t('features.command_runner.running') : $t('features.command_runner.run') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Output Dialog -->
    <Dialog v-model:open="outputDialogOpen">
      <DialogContent class="max-w-3xl">
        <DialogHeader>
          <DialogTitle>{{ $t('features.scheduled_tasks.output.title') }}</DialogTitle>
        </DialogHeader>
        <div class="bg-black text-green-400 p-4 rounded-md font-mono text-sm overflow-auto max-h-[500px] border border-border">
          <pre>{{ selectedTaskOutput }}</pre>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { debounce } from 'lodash';

import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { useAuthStore } from '@/stores/auth'; // Import useAuthStore

// UI Components
import Card from '@/components/ui/card.vue';
import CardContent from '@/components/ui/card-content.vue';
import Table from '@/components/ui/table.vue';
import TableBody from '@/components/ui/table-body.vue';
import TableCell from '@/components/ui/table-cell.vue';
import TableHead from '@/components/ui/table-head.vue';
import TableHeader from '@/components/ui/table-header.vue';
import TableRow from '@/components/ui/table-row.vue';
import Dialog from '@/components/ui/dialog.vue';
import DialogContent from '@/components/ui/dialog-content.vue';
import DialogHeader from '@/components/ui/dialog-header.vue';
import DialogTitle from '@/components/ui/dialog-title.vue';
import DialogFooter from '@/components/ui/dialog-footer.vue';
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Label from '@/components/ui/label.vue';
import Textarea from '@/components/ui/textarea.vue';
import Badge from '@/components/ui/badge.vue';
import Select from '@/components/ui/select.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import Pagination from '@/components/ui/pagination.vue';
import Checkbox from '@/components/ui/checkbox.vue';

import { Plus, Play, Pencil, Trash2, FileText, Loader2, Calendar, Search, Terminal } from 'lucide-vue-next'; // Add Terminal

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const authStore = useAuthStore(); // Use authStore

const tasks = ref([]);
const loading = ref(true);
const search = ref('');
const dialogOpen = ref(false);
const outputDialogOpen = ref(false);
const runCommandDialogOpen = ref(false); // New state
const selectedTaskOutput = ref('');
const editingTask = ref(null);
const saving = ref(false);
const running = ref(null);
const allowedCommands = ref([]);
const cronPreset = ref('');
const errors = ref({});

// Ad-hoc Command State
const adhocCommand = ref({ command: '', parameters: '' });
const adhocOutput = ref('');
const adhocExecuting = ref(false);

// Command Hints for better UX - covers all allowed commands
const commandHints = {
  // Cache Management
  'cache:clear': 'Hapus semua cache aplikasi. Tidak perlu parameter.',
  'cache:warm': 'Panaskan cache untuk performa. Tidak perlu parameter.',
  'cms:clear-cache': 'Hapus cache CMS spesifik. Tidak perlu parameter.',
  
  // Maintenance
  'logs:cleanup': 'Bersihkan log files. Parameter: --days=7 (opsional)',
  'analytics:cleanup': 'Hapus data analytics lama. Tidak perlu parameter.',
  'media:thumbnails': 'Regenerasi thumbnail media. Tidak perlu parameter.',
  
  // Health & Diagnostics
  'cms:health-check': 'Cek kesehatan sistem CMS. Tidak perlu parameter.',
  'config:clear': 'Hapus cache konfigurasi. Tidak perlu parameter.',
  'route:clear': 'Hapus cache routing. Tidak perlu parameter.',
  'view:clear': 'Hapus cache view/blade. Tidak perlu parameter.',
  
  // Backup
  'cms:backup': 'Buat backup database & files. Tidak perlu parameter.',
  
  // Security
  'security:clear-blocked-ips': 'Hapus semua IP yang diblokir. Tidak perlu parameter.',
  'security:clear-rate-limit': 'Reset rate limiter. Tidak perlu parameter.',
  'security:audit-dependencies': 'Audit keamanan package. Tidak perlu parameter.',
  
  // Maintenance & Cleanup
  'logs:cleanup-slow-queries': 'Hapus log slow queries. Tidak perlu parameter.',
  'logs:cleanup-csp-reports': 'Hapus laporan CSP. Tidak perlu parameter.',
  
  // Queue Management
  'queue:restart': 'Restart semua queue worker. Tidak perlu parameter.',
  'queue:flush': 'Kosongkan antrean job. Parameter: default (nama queue)',
  'queue:prune-failed': 'Hapus job gagal lama. Parameter: --hours=24 (opsional)',
  'queue:retry': 'Coba ulang job gagal. Parameter: all atau ID job tertentu',
  'queue:monitor': 'Monitor antrean. Parameter: default (nama queue)',
  
  // System Optimization
  'optimize': 'Optimasi config, routes & views. Tidak perlu parameter.',
  'optimize:clear': 'Hapus semua cache optimasi. Tidak perlu parameter.',
  'activitylog:clean': 'Hapus activity logs lama. Tidak perlu parameter.',
  'sanctum:prune-tokens': 'Hapus token API expired. Tidak perlu parameter.'
};

const commandHint = computed(() => {
  return commandHints[adhocCommand.value.command] || '';
});

// Selection & Bulk Actions
const selectedTasks = ref([]);
const bulkActionSelection = ref('');

const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
    total: 0
});

const form = ref({
  name: '',
  command: '',
  schedule: '',
  description: '',
  is_active: true
});

const initialForm = ref(null);

const cronPresets = {
  hourly: '0 * * * *',
  daily: '0 0 * * *',
  weekly: '0 0 * * 0',
  monthly: '0 0 1 * *'
};

const isValid = computed(() => {
    return !!form.value.name?.trim() && !!form.value.command && !!form.value.schedule?.trim();
});

const isDirty = computed(() => {
    if (!initialForm.value) return true;
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
});

const isAllSelected = computed(() => {
    return tasks.value.length > 0 && selectedTasks.value.length === tasks.value.length;
});

const isSuperAdmin = computed(() => {
  return authStore.user?.roles?.some(role => role.name === 'super-admin');
});

onMounted(async () => {
  await Promise.all([
    fetchTasks(),
    fetchAllowedCommands()
  ]);
});

async function fetchTasks(page = 1) {
  try {
    loading.value = true;
    const params = {
        page,
        search: search.value,
        limit: 10
    }
    const response = await api.get('/admin/cms/scheduled-tasks', { params });
    
    // Handle both paginated and non-paginated responses for compatibility
    if (response.data?.meta) {
        tasks.value = response.data.data;
        pagination.value = {
            current_page: response.data.meta.current_page,
            last_page: response.data.meta.last_page,
            per_page: response.data.meta.per_page,
            total: response.data.meta.total
        };
    } else if (response.data?.data) {
        // If API returns plain list, handle client-side if needed or just display all
        tasks.value = response.data.data;
        pagination.value.total = tasks.value.length;
    } else {
        tasks.value = [];
    }
    
    selectedTasks.value = [];
  } catch (error) {
    console.error('Failed to fetch tasks:', error.message);
    toast.error.load(error);
  } finally {
    loading.value = false;
  }
}

async function fetchAllowedCommands() {
  try {
    const response = await api.get('/admin/cms/scheduled-tasks/allowed-commands');
    allowedCommands.value = response.data.data;
  } catch (error) {
    console.error('Failed to fetch allowed commands:', error);
  }
}

const debouncedSearch = debounce(() => {
    fetchTasks(1);
}, 300);

const changePage = (page) => {
    fetchTasks(page);
};

// Selection Logic
const toggleSelection = (id) => {
    if (selectedTasks.value.includes(id)) {
        selectedTasks.value = selectedTasks.value.filter(taskId => taskId !== id);
    } else {
        selectedTasks.value.push(id);
    }
};

const toggleSelectAll = (checked) => {
    if (checked) {
        selectedTasks.value = tasks.value.map(t => t.id);
    } else {
        selectedTasks.value = [];
    }
};

// Bulk Actions
const handleBulkAction = async (action) => {
    if (!action) return;

    if (action === 'delete') {
        if (!selectedTasks.value.length) return;

        const confirmed = await confirm({
            title: t('common.messages.confirm.title'),
            message: t('common.messages.confirm.bulkDelete'),
            variant: 'destructive',
            confirmText: t('common.actions.delete'),
        });

        if (!confirmed) {
            bulkActionSelection.value = '';
            return;
        }

        try {
            // Check if backend supports bulk action, otherwise loop
            // Assuming we might not have a bulk endpoint, we can loop for now or try common pattern
            // Ideally: await api.post('/admin/cms/scheduled-tasks/bulk-action', { action: 'delete', ids: selectedTasks.value });
            
            // Implementing Client-Side Loop fallback if needed, but best to assume we need a bulk endpoint or add it.
            // For now, let's try parallel deletion to be safe without backend changes if possible
            const deletePromises = selectedTasks.value.map(id => api.delete(`/admin/cms/scheduled-tasks/${id}`));
            await Promise.all(deletePromises);
            
            toast.success.delete(`${selectedTasks.value.length} Tasks`);
            selectedTasks.value = [];
            bulkActionSelection.value = '';
            fetchTasks(pagination.value.current_page);
        } catch (error) {
            console.error('Bulk action failed:', error);
            toast.error.action(error);
        }
    }
};


function openCreateDialog() {
  editingTask.value = null;
  form.value = {
    name: '',
    command: '',
    schedule: '',
    description: '',
    is_active: true
  };
  cronPreset.value = '';
  errors.value = {};
  initialForm.value = JSON.parse(JSON.stringify(form.value));
  dialogOpen.value = true;
}

function editTask(task) {
  editingTask.value = task;
  errors.value = {};
  form.value = {
    name: task.name,
    command: task.command,
    schedule: task.schedule,
    description: task.description || '',
    is_active: task.is_active
  };
  initialForm.value = JSON.parse(JSON.stringify(form.value));
  dialogOpen.value = true;
}

function applyCronPreset(preset) {
  form.value.schedule = cronPresets[preset];
}

async function saveTask() {
  try {
    saving.value = true;
    errors.value = {};
    
    if (editingTask.value) {
      await api.put(`/admin/cms/scheduled-tasks/${editingTask.value.id}`, form.value);
      toast.success.update('Scheduled Task');
    } else {
      await api.post('/admin/cms/scheduled-tasks', form.value);
      toast.success.create('Scheduled Task');
    }
    
    dialogOpen.value = false;
    await fetchTasks(pagination.value.current_page);
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    } else {
        console.error('Failed to save task:', error.response?.data?.message || error.message);
        toast.error.fromResponse(error);
    }
  } finally {
    saving.value = false;
  }
}

async function runTask(task) {
  const confirmed = await confirm({
    title: t('features.scheduled_tasks.actions.run'),
    message: t('features.scheduled_tasks.confirm_run', { name: task.name }),
    variant: 'info',
    confirmText: t('common.actions.run'),
  });

  if (!confirmed) return;

  try {
    running.value = task.id;
    const response = await api.post(`/admin/cms/scheduled-tasks/${task.id}/run`);
    
    toast.success.action(t('features.scheduled_tasks.messages.executed') || 'Task executed successfully');

    selectedTaskOutput.value = response.data.data.output;
    outputDialogOpen.value = true;
    
    await fetchTasks(pagination.value.current_page);
  } catch (error) {
    console.error('Failed to run task:', error.response?.data?.message || error.message);
    toast.error.fromResponse(error);
  } finally {
    running.value = null;
  }
}

async function toggleActive(task) {
  try {
    await api.put(`/admin/cms/scheduled-tasks/${task.id}`, {
      is_active: !task.is_active
    });
    
    await fetchTasks(pagination.value.current_page);
  } catch (error) {
    console.error('Failed to toggle task:', error.message);
    toast.error.fromResponse(error);
  }
}

function viewOutput(task) {
  selectedTaskOutput.value = task.output || 'No output available';
  outputDialogOpen.value = true;
}

async function confirmDelete(task) {
  const confirmed = await confirm({
    title: t('features.scheduled_tasks.actions.delete'),
    message: t('features.scheduled_tasks.confirm_delete', { name: task.name }),
    variant: 'destructive',
    confirmText: t('common.actions.delete'),
  });

  if (!confirmed) return;

  try {
    await api.delete(`/admin/cms/scheduled-tasks/${task.id}`);
    toast.success.delete();
    await fetchTasks(pagination.value.current_page);
  } catch (error) {
    console.error('Failed to delete task:', error.message);
    toast.error.fromResponse(error);
  }
}

function getStatusVariant(status) {
  const variants = {
    completed: 'success',
    running: 'default',
    failed: 'destructive',
    pending: 'secondary'
  };
  return variants[status] || 'secondary';
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleString();
}

function openRunCommandDialog() {
  adhocCommand.value = { command: '', parameters: '' };
  adhocOutput.value = '';
  runCommandDialogOpen.value = true;
}

async function runAdhocCommand() {
  if (!adhocCommand.value.command) return;

  const fullCommand = adhocCommand.value.parameters 
    ? `${adhocCommand.value.command} ${adhocCommand.value.parameters}`
    : adhocCommand.value.command;

  try {
    adhocExecuting.value = true;
    adhocOutput.value = '';

    // Create a temporary task
    const createResponse = await api.post('/admin/cms/scheduled-tasks', {
      name: `Temp Command - ${Date.now()}`,
      command: fullCommand,
      schedule: '0 0 1 1 *', // Default invalid schedule
      description: 'Temporary task created by command runner',
      is_active: false
    });

    const taskId = createResponse.data.data.id;

    // Run the task
    const runResponse = await api.post(`/admin/cms/scheduled-tasks/${taskId}/run`);

    adhocOutput.value = runResponse.data.data.output || 'No output';
    
    // Delete temporary task
    await api.delete(`/admin/cms/scheduled-tasks/${taskId}`);
    
    // Check exit code if available, but for now just show output
    if(runResponse.data.data.exit_code !== 0) {
         // handle error visual if needed, but text is likely enough
    }

  } catch (error) {
    adhocOutput.value = error.response?.data?.message || error.message;
    console.error('Failed to execute command:', error.message);
  } finally {
    adhocExecuting.value = false;
  }
}
</script>
