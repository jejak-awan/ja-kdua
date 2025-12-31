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
    <Card>
      <CardContent class="p-0">
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
              <TableCell colspan="7" class="text-center py-12">
                <Loader2 class="w-8 h-8 animate-spin mx-auto text-muted-foreground" />
                <p class="text-sm text-muted-foreground mt-2">Loading tasks...</p>
              </TableCell>
            </TableRow>
            <TableRow v-else-if="tasks.length === 0">
              <TableCell colspan="7" class="text-center py-12">
                 <Calendar class="w-12 h-12 mx-auto text-muted-foreground/20 mb-4" />
                 <p class="text-muted-foreground font-medium">{{ $t('features.scheduled_tasks.no_tasks') }}</p>
              </TableCell>
            </TableRow>
            <template v-else>
              <TableRow v-for="task in tasks" :key="task.id" class="hover:bg-muted/50 group transition-colors">
                <TableCell class="font-semibold">{{ task.name }}</TableCell>
                <TableCell>
                  <code class="text-[11px] bg-muted px-2 py-0.5 rounded border border-border group-hover:bg-background transition-colors">{{ task.command }}</code>
                </TableCell>
                <TableCell>
                  <code class="text-[11px] font-mono font-bold text-primary">{{ task.schedule }}</code>
                </TableCell>
                <TableCell>
                  <div v-if="task.last_run_at" class="flex flex-col">
                    <span class="text-sm font-medium">{{ formatDate(task.last_run_at) }}</span>
                    <span class="text-[10px] text-muted-foreground tabular-nums">{{ task.last_run_duration ? (task.last_run_duration / 1000).toFixed(2) + 's' : '' }}</span>
                  </div>
                  <span v-else class="text-muted-foreground text-sm italic">Never</span>
                </TableCell>
                <TableCell>
                  <Badge 
                    :variant="getStatusVariant(task.status)"
                    class="capitalize text-[10px] px-2 py-0"
                  >
                    {{ task.status || 'pending' }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <Button 
                    size="sm" 
                    :variant="task.is_active ? 'default' : 'secondary'"
                    @click="toggleActive(task)"
                    class="h-7 text-[10px] font-bold uppercase tracking-wider px-3"
                  >
                    {{ task.is_active ? 'Active' : 'Inactive' }}
                  </Button>
                </TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
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
                      class="h-8 w-8 hover:bg-muted"
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
            </template>
          </TableBody>
        </Table>
      </CardContent>
    </Card>

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
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

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

import { Plus, Play, Pencil, Trash2, FileText, Loader2, Calendar } from 'lucide-vue-next';

const { t } = useI18n();

const { confirm } = useConfirm();
const toast = useToast();

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
const errors = ref({});

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
    if (!initialForm.value) return true; // Default to true if init not set
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
});

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
    await fetchTasks();
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

    // Show output
    selectedTaskOutput.value = response.data.data.output;
    outputDialogOpen.value = true;
    
    await fetchTasks();
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
    
    await fetchTasks();
    console.log('Task toggled successfully');
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
    await fetchTasks();
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
</script>


