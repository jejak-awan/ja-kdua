<template>
  <div class="scheduled-tasks">
    <div class="page-header">
      <h1 class="page-title">{{ $t('features.scheduled_tasks.title') }}</h1>
      <p class="page-description">{{ $t('features.scheduled_tasks.description') }}</p>
      <Button @click="openCreateDialog" class="ml-auto">
        <Plus class="w-4 h-4 mr-2" />
        {{ $t('features.scheduled_tasks.create') }}
      </Button>
    </div>

    <!-- Tasks Table -->
    <div class="card">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>{{ $t('features.scheduled_tasks.table.name') }}</TableHead>
            <TableHead>{{ $t('features.scheduled_tasks.table.command') }}</TableHead>
            <TableHead>{{ $t('features.scheduled_tasks.table.schedule') }}</TableHead>
            <TableHead>{{ $t('features.scheduled_tasks.table.last_run') }}</TableHead>
            <TableHead>{{ $t('features.scheduled_tasks.table.status') }}</TableHead>
            <TableHead>{{ $t('features.scheduled_tasks.table.active') }}</TableHead>
            <TableHead class="text-right">{{ $t('features.scheduled_tasks.table.actions') }}</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="loading">
            <TableCell colspan="7" class="text-center py-8">
              <Loader2 class="w-6 h-6 animate-spin mx-auto" />
            </TableCell>
          </TableRow>
          <TableRow v-else-if="tasks.length === 0">
            <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
              {{ $t('features.scheduled_tasks.no_tasks') }}
            </TableCell>
          </TableRow>
          <template v-else>
            <TableRow v-for="task in tasks" :key="task.id">
              <TableCell class="font-medium">{{ task.name }}</TableCell>
              <TableCell>
                <code class="text-xs bg-muted px-2 py-1 rounded">{{ task.command }}</code>
              </TableCell>
              <TableCell>
                <code class="text-xs">{{ task.schedule }}</code>
              </TableCell>
              <TableCell>
                <span v-if="task.last_run_at" class="text-sm">
                  {{ formatDate(task.last_run_at) }}
                </span>
                <span v-else class="text-muted-foreground text-sm">Never</span>
              </TableCell>
              <TableCell>
                <Badge 
                  :variant="getStatusVariant(task.status)"
                  class="capitalize"
                >
                  {{ task.status || 'pending' }}
                </Badge>
              </TableCell>
              <TableCell>
                <Switch 
                  :checked="task.is_active" 
                  @update:checked="toggleActive(task)"
                />
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button 
                    size="sm" 
                    variant="outline"
                    @click="runTask(task)"
                    :disabled="running === task.id"
                  >
                    <Play class="w-4 h-4" />
                  </Button>
                  <Button 
                    size="sm" 
                    variant="outline"
                    @click="viewOutput(task)"
                    v-if="task.output"
                  >
                    <FileText class="w-4 h-4" />
                  </Button>
                  <Button 
                    size="sm" 
                    variant="outline"
                    @click="editTask(task)"
                  >
                    <Pencil class="w-4 h-4" />
                  </Button>
                  <Button 
                    size="sm" 
                    variant="destructive"
                    @click="deleteTask(task)"
                  >
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </template>
        </TableBody>
      </Table>
    </div>

    <!-- Create/Edit Dialog -->
    <Dialog v-model:open="dialogOpen">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>
            {{ editingTask ? $t('features.scheduled_tasks.edit') : $t('features.scheduled_tasks.create') }}
          </DialogTitle>
        </DialogHeader>
        
        <form @submit.prevent="saveTask" class="space-y-4">
          <div>
            <Label>{{ $t('features.scheduled_tasks.form.name') }}</Label>
            <Input v-model="form.name" required />
          </div>

          <div>
            <Label>{{ $t('features.scheduled_tasks.form.command') }}</Label>
            <Select v-model="form.command" required>
              <SelectTrigger>
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
          </div>

          <div>
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
              />
            </div>
            <p class="text-xs text-muted-foreground mt-1">
              {{ $t('features.scheduled_tasks.form.cron_help') }}
            </p>
          </div>

          <div>
            <Label>{{ $t('features.scheduled_tasks.form.description') }}</Label>
            <Textarea v-model="form.description" rows="3" />
          </div>

          <div class="flex items-center gap-2">
            <Switch v-model="form.is_active" />
            <Label>{{ $t('features.scheduled_tasks.form.active') }}</Label>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="dialogOpen = false">
              {{ $t('common.cancel') }}
            </Button>
            <Button type="submit" :disabled="saving">
              {{ saving ? $t('common.saving') : $t('common.save') }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Output Dialog -->
    <Dialog v-model:open="outputDialogOpen">
      <DialogContent class="max-w-3xl">
        <DialogHeader>
          <DialogTitle>{{ $t('features.scheduled_tasks.output.title') }}</DialogTitle>
        </DialogHeader>
        <div class="bg-black text-green-400 p-4 rounded font-mono text-sm overflow-auto max-h-96">
          <pre>{{ selectedTaskOutput }}</pre>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import apiService from '@/services/apiService';
import {
  Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
  Button, Input, Label, Textarea, Badge, Switch,
  Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';
import { Plus, Play, Pencil, Trash2, FileText, Loader2 } from 'lucide-vue-next';

const { t } = useI18n();

const tasks = ref([]);
const loading = ref(true);
const dialogOpen = ref(false);
const outputDialogOpen = ref(false);
const selectedTaskOutput = ref('');
const editingTask = ref(null);
const saving = ref(false);
const running = ref(null);
const allowedCommands = ref([]);
const cronPreset = ref('');

const form = ref({
  name: '',
  command: '',
  schedule: '',
  description: '',
  is_active: true}
);

const cronPresets = {
  hourly: '0 * * * *',
  daily: '0 0 * * *',
  weekly: '0 0 * * 0',
  monthly: '0 0 1 * *'
};

onMounted(async () => {
  await Promise.all([
    fetchTasks(),
    fetchAllowedCommands()
  ]);
);

async function fetchTasks() {
  try {
    loading.value = true;
    const response = await apiService.get('/admin/cms/scheduled-tasks');
    tasks.value = response.data.data;
  } catch (error) {
    console.log('Success:', 
      variant: 'destructive',
      title: t('common.error'),
      description: error.message
    );
  } finally {
    loading.value = false;
  }
}

async function fetchAllowedCommands() {
  try {
    const response = await apiService.get('/admin/cms/scheduled-tasks/meta/allowed-commands');
    allowedCommands.value = response.data.data.commands;
  } catch (error) {
    console.error('Failed to fetch allowed commands:', error);
  }
}

function openCreateDialog() {
  editingTask.value = null;
  form.value = {
    name: '',
    command: '',
    schedule: '',
    description: '',
    is_active: true}
  };
  cronPreset.value = '';
  dialogOpen.value = true;
}

function editTask(task) {
  editingTask.value = task;
  form.value = {
    name: task.name,
    command: task.command,
    schedule: task.schedule,
    description: task.description || '',
    is_active: task.is_active
  };
  dialogOpen.value = true;
}

function applyCronPreset(preset) {
  form.value.schedule = cronPresets[preset];
}

async function saveTask() {
  try {
    saving.value = true;
    
    if (editingTask.value) {
      await apiService.put(`/admin/cms/scheduled-tasks/${editingTask.value.id}`, form.value);
      console.log('Success:', 
        title: t('common.success'),
        description: t('features.scheduled_tasks.messages.updated')
      );
    } else {
      await apiService.post('/admin/cms/scheduled-tasks', form.value);
      console.log('Success:', 
        title: t('common.success'),
        description: t('features.scheduled_tasks.messages.created')
      );
    }
    
    dialogOpen.value = false;
    await fetchTasks();
  } catch (error) {
    console.log('Success:', 
      variant: 'destructive',
      title: t('common.error'),
      description: error.response?.data?.message || error.message
    );
  } finally {
    saving.value = false;
  }
}

async function runTask(task) {
  if (!confirm(t('features.scheduled_tasks.confirm_run', { name: task.name }))) {
    return;
  }

  try {
    running.value = task.id;
    const response = await apiService.post(`/admin/cms/scheduled-tasks/${task.id}/run`);
    
    console.log('Success:', 
      title: t('common.success'),
      description: t('features.scheduled_tasks.messages.executed')
    );

    // Show output
    selectedTaskOutput.value = response.data.data.output;
    outputDialogOpen.value = true;
    
    await fetchTasks();
  } catch (error) {
    console.log('Success:', 
      variant: 'destructive',
      title: t('common.error'),
      description: error.response?.data?.message || error.message
    );
  } finally {
    running.value = null;
  }
}

async function toggleActive(task) {
  try {
    await apiService.put(`/admin/cms/scheduled-tasks/${task.id}`, {
      is_active: !task.is_active
    );
    
    await fetchTasks();
    
    console.log('Success:', 
      title: t('common.success'),
      description: t('features.scheduled_tasks.messages.toggled')
    );
  } catch (error) {
    console.log('Success:', 
      variant: 'destructive',
      title: t('common.error'),
      description: error.message
    );
  }
}

function viewOutput(task) {
  selectedTaskOutput.value = task.output || 'No output available';
  outputDialogOpen.value = true;
}

async function deleteTask(task) {
  if (!confirm(t('features.scheduled_tasks.confirm_delete', { name: task.name }))) {
    return;
  }

  try {
    await apiService.delete(`/admin/cms/scheduled-tasks/${task.id}`);
    
    console.log('Success:', 
      title: t('common.success'),
      description: t('features.scheduled_tasks.messages.deleted')
    );
    
    await fetchTasks();
  } catch (error) {
    console.log('Success:', 
      variant: 'destructive',
      title: t('common.error'),
      description: error.message
    );
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
</script>

<style scoped>
.scheduled-tasks {
  padding: 2rem;
}

.page-header {
  display: flex;
  align-items: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  margin: 0;
}

.page-description {
  color: var(--muted-foreground);
  margin: 0.5rem 0 0 0;
}

.card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  overflow: hidden;
}
</style>
