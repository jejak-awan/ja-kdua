<template>
  <div class="space-y-6">
    <!-- Access Check -->
    <div v-if="!isSuperAdmin" class="flex flex-col items-center justify-center min-h-[60vh] text-center">
      <AlertCircle class="w-12 h-12 text-destructive mb-4" />
      <h2 class="text-xl font-bold mb-2">{{ $t('features.command_runner.access_denied') }}</h2>
      <p class="text-muted-foreground">{{ $t('features.command_runner.super_admin_only') }}</p>
      <Button @click="$router.push('/admin/dashboard')" class="mt-4">
        {{ $t('common.navigation.menu.dashboard') }}
      </Button>
    </div>

    <template v-else>
      <div class="flex justify-between items-start">
        <div>
          <h1 class="text-2xl font-bold text-foreground">{{ $t('features.command_runner.title') }}</h1>
          <p class="text-muted-foreground mt-1">{{ $t('features.command_runner.description') }}</p>
        </div>
        <Badge variant="destructive" class="ml-auto">
          {{ $t('features.command_runner.super_admin') }}
        </Badge>
      </div>

      <!-- Command Execution Card -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg font-semibold">{{ $t('features.command_runner.execute') }}</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2 space-y-2">
              <Label>{{ $t('features.command_runner.select_command') }}</Label>
              <Select v-model="selectedCommand">
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
            <div class="space-y-2">
              <Label>{{ $t('features.command_runner.parameters') }}</Label>
              <Input 
                v-model="parameters" 
                :placeholder="$t('features.command_runner.parameters_placeholder')"
              />
            </div>
          </div>

          <div class="flex gap-2">
            <Button 
              @click="runCommand" 
              :disabled="!selectedCommand || executing"
              class="w-32"
            >
              <Play v-if="!executing" class="w-4 h-4 mr-2" />
              <Loader2 v-else class="w-4 h-4 mr-2 animate-spin" />
              {{ executing ? $t('features.command_runner.running') : $t('features.command_runner.run') }}
            </Button>
            <Button 
              variant="outline" 
              @click="clearOutput"
              :disabled="!output"
            >
              <X class="w-4 h-4 mr-2" />
              {{ $t('features.command_runner.clear') }}
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Output Card -->
      <Card v-if="output || executing">
        <CardHeader class="flex flex-row items-center justify-between space-y-0">
          <CardTitle class="text-lg font-semibold">{{ $t('features.command_runner.output') }}</CardTitle>
          <div class="flex items-center gap-2">
            <Badge v-if="exitCode !== null" :variant="exitCode === 0 ? 'success' : 'destructive'">
              Exit Code: {{ exitCode }}
            </Badge>
            <span v-if="executionTime" class="text-sm text-muted-foreground">
              {{ executionTime }}ms
            </span>
          </div>
        </CardHeader>
        <CardContent class="p-0">
          <div class="bg-black text-green-400 p-6 font-mono text-sm overflow-auto max-h-[500px]">
            <div v-if="executing" class="flex items-center gap-2">
              <Loader2 class="w-4 h-4 animate-spin" />
              <span>{{ $t('features.command_runner.executing') }}</span>
            </div>
            <pre v-else class="whitespace-pre-wrap break-words">{{ output }}</pre>
          </div>
        </CardContent>
      </Card>

      <!-- Command History Card -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg font-semibold">{{ $t('features.command_runner.history') }}</CardTitle>
        </CardHeader>
        <CardContent class="p-0">
          <div v-if="historyLoading" class="p-8 text-center">
            <Loader2 class="w-6 h-6 animate-spin mx-auto text-muted-foreground" />
          </div>
          <div v-else-if="history.length === 0" class="p-8 text-center text-muted-foreground">
            {{ $t('features.command_runner.no_history') }}
          </div>
          <Table v-else>
            <TableHeader>
              <TableRow>
                <TableHead>{{ $t('features.command_runner.table.command') }}</TableHead>
                <TableHead>{{ $t('features.command_runner.table.parameters') }}</TableHead>
                <TableHead>{{ $t('features.command_runner.table.runner') }}</TableHead>
                <TableHead>{{ $t('features.command_runner.table.status') }}</TableHead>
                <TableHead>{{ $t('features.command_runner.table.date') }}</TableHead>
                <TableHead class="text-right">{{ $t('common.actions.title') }}</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in history" :key="item.id">
                <TableCell class="font-medium">{{ item.command }}</TableCell>
                <TableCell>
                  <code class="text-xs bg-muted px-1 py-0.5 rounded" v-if="item.parameters">{{ item.parameters }}</code>
                  <span v-else class="text-muted-foreground italic">-</span>
                </TableCell>
                <TableCell>{{ item.user?.name || 'Unknown' }}</TableCell>
                <TableCell>
                  <Badge :variant="item.exit_code === 0 ? 'success' : 'destructive'">
                    {{ item.exit_code === 0 ? $t('common.status.success') : $t('common.status.failed') }}
                  </Badge>
                </TableCell>
                <TableCell class="text-sm text-muted-foreground">
                  {{ formatDate(item.created_at) }}
                </TableCell>
                <TableCell class="text-right">
                  <Button variant="ghost" size="sm" @click="viewHistoryOutput(item)">
                    <Eye class="w-4 h-4 mr-2" />
                    {{ $t('common.actions.view') }}
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

// UI Components - individual imports
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Label from '@/components/ui/label.vue';
import Badge from '@/components/ui/badge.vue';
import Select from '@/components/ui/select.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import Table from '@/components/ui/table.vue';
import TableHeader from '@/components/ui/table-header.vue';
import TableBody from '@/components/ui/table-body.vue';
import TableRow from '@/components/ui/table-row.vue';
import TableCell from '@/components/ui/table-cell.vue';
import TableHead from '@/components/ui/table-head.vue';

import { Play, X, Loader2, AlertCircle, Eye } from 'lucide-vue-next';

const router = useRouter();
const { t } = useI18n();
const authStore = useAuthStore();

const selectedCommand = ref('');
const parameters = ref('');
const output = ref('');
const exitCode = ref(null);
const executionTime = ref(null);
const executing = ref(false);
const allowedCommands = ref([]);
const history = ref([]);

const isSuperAdmin = computed(() => {
  return authStore.user?.roles?.some(role => role.name === 'super-admin');
});

onMounted(async () => {
  if (!isSuperAdmin.value) {
    return;
  }
  
  await fetchAllowedCommands();
  loadHistory();
});

async function fetchAllowedCommands() {
  try {
    const response = await api.get('/admin/cms/scheduled-tasks/meta/allowed-commands');
    allowedCommands.value = response.data.data.commands;
  } catch (error) {
    console.error('Failed to fetch allowed commands:', error.message);
  }
}

async function runCommand() {
  if (!selectedCommand.value) return;

  const fullCommand = parameters.value 
    ? `${selectedCommand.value} ${parameters.value}`
    : selectedCommand.value;

  try {
    executing.value = true;
    output.value = '';
    exitCode.value = null;
    executionTime.value = null;

    const startTime = Date.now();

    // Create a temporary task and run it
    const createResponse = await api.post('/admin/cms/scheduled-tasks', {
      name: `Temp Command - ${Date.now()}`,
      command: fullCommand,
      schedule: '0 0 1 1 *', // Never runs automatically
      description: 'Temporary task created by command runner',
      is_active: false
    });

    const taskId = createResponse.data.data.id;

    // Run the task
    const runResponse = await api.post(`/admin/cms/scheduled-tasks/${taskId}/run`);

    executionTime.value = Date.now() - startTime;
    output.value = runResponse.data.data.output || 'No output';
    exitCode.value = runResponse.data.data.exit_code || 0;

    // Delete temporary task
    await api.delete(`/admin/cms/scheduled-tasks/${taskId}`);

    // Add to history
    addToHistory({
      id: Date.now(),
      command: selectedCommand.value,
      parameters: parameters.value,
      user: { name: authStore.user?.name || 'System' },
      exit_code: exitCode.value,
      created_at: new Date().toISOString()
    });

    if (exitCode.value === 0) {
      console.log('Command executed successfully');
    } else {
      console.warn('Command executed with exit code:', exitCode.value);
    }

  } catch (error) {
    output.value = error.response?.data?.message || error.message;
    exitCode.value = 1;
    console.error('Failed to execute command:', error.message);
  } finally {
    executing.value = false;
  }
}

function clearOutput() {
  output.value = '';
  exitCode.value = null;
  executionTime.value = null;
}

function addToHistory(item) {
  history.value.unshift(item);
  if (history.value.length > 10) {
    history.value.pop();
  }
  saveHistory();
}

function loadFromHistory(item) {
  const [cmd, ...params] = item.command.split(' ');
  selectedCommand.value = cmd;
  parameters.value = params.join(' ');
}

function saveHistory() {
  localStorage.setItem('command_runner_history', JSON.stringify(history.value));
}

function loadHistory() {
  const saved = localStorage.getItem('command_runner_history');
  if (saved) {
    try {
      const parsed = JSON.parse(saved);
      history.value = parsed.map(item => ({
        id: item.id || Date.now() + Math.random(),
        command: item.command,
        parameters: item.parameters || (item.command.includes(' ') ? item.command.split(' ').slice(1).join(' ') : ''),
        user: item.user || { name: 'System' },
        exit_code: item.exit_code !== undefined ? item.exit_code : (item.exitCode !== undefined ? item.exitCode : 0),
        created_at: item.created_at || item.timestamp || new Date().toISOString()
      }));
    } catch (e) {
      console.error('Failed to parse history:', e);
      history.value = [];
    }
  }
}

function formatDate(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return isNaN(date.getTime()) ? '-' : date.toLocaleString();
}
</script>


