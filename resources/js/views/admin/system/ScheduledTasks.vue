<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-foreground">{{ $t('features.scheduled_tasks.title') }}</h1>
        <p class="text-muted-foreground mt-1">{{ $t('features.scheduled_tasks.description') }}</p>
      </div>
      <Button @click="openCreateDialog">
        <Plus class="w-4 h-4 mr-2" />
        {{ $t('features.scheduled_tasks.create') }}
      </Button>
    </div>

    <!-- Tasks Table -->
    <div class="bg-card border border-border rounded-lg overflow-hidden">
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
              <Loader2 class="w-6 h-6 animate-spin mx-auto text-muted-foreground" />
            </TableCell>
          </TableRow>
          <TableRow v-else-if="tasks.length === 0">
            <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
              {{ $t('features.scheduled_tasks.no_tasks') }}
            </TableCell>
          </TableRow>
          <template v-else>
            <TableRow v-for="task in tasks" :key="task.id" class="hover:bg-muted/50">
              <TableCell class="font-medium">{{ task.name }}</TableCell>
              <TableCell>
                <code class="text-xs bg-muted px-2 py-1 rounded border border-border">{{ task.command }}</code>
              </TableCell>
              <TableCell>
                <code class="text-xs font-mono">{{ task.schedule }}</code>
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
                <Button 
                  size="sm" 
                  :variant="task.is_active ? 'default' : 'outline'"
                  @click="toggleActive(task)"
                  class="w-20"
                >
                  {{ task.is_active ? 'Active' : 'Inactive' }}
                </Button>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button 
                    size="icon" 
                    variant="ghost"
                    @click="runTask(task)"
                    :disabled="running === task.id"
                    :title="$t('common.actions.run')"
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
                  >
                    <FileText class="w-4 h-4" />
                  </Button>
                  <Button 
                    size="icon" 
                    variant="ghost"
                    @click="editTask(task)"
                    :title="$t('common.actions.edit')"
                  >
                    <Pencil class="w-4 h-4" />
                  </Button>
                  <Button 
                    size="icon" 
                    variant="ghost"
                    class="text-destructive hover:text-destructive"
                    @click="deleteTask(task)"
                    :title="$t('common.actions.delete')"
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
        
        <form @submit.prevent="saveTask" class="space-y-4 py-4">
          <div class="grid gap-2">
            <Label>{{ $t('features.scheduled_tasks.form.name') }}</Label>
            <Input v-model="form.name" required />
          </div>

          <div class="grid gap-2">
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
              />
            </div>
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
            <Button type="submit" :disabled="saving">
              {{ saving ? $t('common.actions.save') + '...' : $t('common.actions.save') }}
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
        <div class="bg-black text-green-400 p-4 rounded-md font-mono text-sm overflow-auto max-h-[500px] border border-border">
          <pre>{{ selectedTaskOutput }}</pre>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';

// UI Components - individual imports
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
  is_active: true
});

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
});

async function fetchTasks() {
  try {
    loading.value = true;
    const response = await api.get('/admin/cms/scheduled-tasks');
    tasks.value = response.data.data;
  } catch (error) {
    console.error('Failed to fetch tasks:', error.message);
  } finally {
    loading.value = false;
  }
}

async function fetchAllowedCommands() {
  try {
    const response = await api.get('/admin/cms/scheduled-tasks/meta/allowed-commands');
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
    is_active: true
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
      await api.put(`/admin/cms/scheduled-tasks/${editingTask.value.id}`, form.value);
      console.log('Task updated successfully');
    } else {
      await api.post('/admin/cms/scheduled-tasks', form.value);
      console.log('Task created successfully');
    }
    
    dialogOpen.value = false;
    await fetchTasks();
  } catch (error) {
    console.error('Failed to save task:', error.response?.data?.message || error.message);
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
    const response = await api.post(`/admin/cms/scheduled-tasks/${task.id}/run`);
    
    console.log('Task executed successfully');

    // Show output
    selectedTaskOutput.value = response.data.data.output;
    outputDialogOpen.value = true;
    
    await fetchTasks();
  } catch (error) {
    console.error('Failed to run task:', error.response?.data?.message || error.message);
  } finally {
    running.value = null;
  }
}

async function toggleActive(task) {
  try {
    await api.put(`/admin/cms/scheduled-tasks/${task.id}`, {
      is_active: !task.is_active
    });
    
    await fetchTasks();
    console.log('Task toggled successfully');
  } catch (error) {
    console.error('Failed to toggle task:', error.message);
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
    await api.delete(`/admin/cms/scheduled-tasks/${task.id}`);
    console.log('Task deleted successfully');
    await fetchTasks();
  } catch (error) {
    console.error('Failed to delete task:', error.message);
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


