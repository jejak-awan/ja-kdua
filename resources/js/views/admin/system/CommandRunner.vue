<template>
  <div class="command-runner">
    <!-- Access Check -->
    <div v-if="!isSuperAdmin" class="access-denied">
      <AlertCircle class="w-12 h-12 text-destructive mb-4" />
      <h2>{{ $t('features.command_runner.access_denied') }}</h2>
      <p>{{ $t('features.command_runner.super_admin_only') }}</p>
      <Button @click="$router.push('/admin/dashboard')" class="mt-4">
        {{ $t('common.back_to_dashboard') }}
      </Button>
    </div>

    <template v-else>
      <div class="page-header">
        <div>
          <h1 class="page-title">{{ $t('features.command_runner.title') }}</h1>
          <p class="page-description">{{ $t('features.command_runner.description') }}</p>
        </div>
        <Badge variant="destructive" class="ml-auto">
          {{ $t('features.command_runner.super_admin') }}
        </Badge>
      </div>

      <!-- Command Execution Card -->
      <div class="card mb-6">
        <div class="card-header">
          <h3>{{ $t('features.command_runner.execute') }}</h3>
        </div>
        <div class="card-body space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
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
            <div>
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
        </div>
      </div>

      <!-- Output Card -->
      <div class="card mb-6" v-if="output || executing">
        <div class="card-header flex items-center justify-between">
          <h3>{{ $t('features.command_runner.output') }}</h3>
          <div class="flex items-center gap-2">
            <Badge v-if="exitCode !== null" :variant="exitCode === 0 ? 'success' : 'destructive'">
              Exit Code: {{ exitCode }}
            </Badge>
            <span v-if="executionTime" class="text-sm text-muted-foreground">
              {{ executionTime }}ms
            </span>
          </div>
        </div>
        <div class="terminal">
          <div v-if="executing" class="flex items-center gap-2 text-green-400">
            <Loader2 class="w-4 h-4 animate-spin" />
            <span>{{ $t('features.command_runner.executing') }}</span>
          </div>
          <pre v-else>{{ output }}</pre>
        </div>
      </div>

      <!-- Command History Card -->
      <div class="card">
        <div class="card-header">
          <h3>{{ $t('features.command_runner.history') }}</h3>
        </div>
        <div class="card-body">
          <div v-if="history.length === 0" class="text-center py-8 text-muted-foreground">
            {{ $t('features.command_runner.no_history') }}
          </div>
          <div v-else class="space-y-2">
            <div 
              v-for="(item, index) in history" 
              :key="index"
              class="history-item"
              @click="loadFromHistory(item)"
            >
              <div class="flex items-center justify-between">
                <code class="text-sm">{{ item.command }}</code>
                <div class="flex items-center gap-2">
                  <Badge size="sm" :variant="item.exitCode === 0 ? 'success' : 'destructive'">
                    {{ item.exitCode }}
                  </Badge>
                  <span class="text-xs text-muted-foreground">
                    {{ formatDate(item.timestamp) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import apiService from '@/services/apiService';
import {
  Button, Input, Label, Badge,
  Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';
import { Play, X, Loader2, AlertCircle } from 'lucide-vue-next';

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
);

onMounted(async () => {
  if (!isSuperAdmin.value) {
    return;
  }
  
  await fetchAllowedCommands();
  loadHistory();
);

async function fetchAllowedCommands() {
  try {
    const response = await apiService.get('/admin/cms/scheduled-tasks/meta/allowed-commands');
    allowedCommands.value = response.data.data.commands;
  } catch (error) {
    console.log('Success:', 
      variant: 'destructive',
      title: t('common.error'),
      description: error.message
    );
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
    const createResponse = await apiService.post('/admin/cms/scheduled-tasks', {
      name: `Temp Command - ${Date.now()}`,
      command: fullCommand,
      schedule: '0 0 1 1 *', // Never runs automatically
      description: 'Temporary task created by command runner',
      is_active: false
    );

    const taskId = createResponse.data.data.id;

    // Run the task
    const runResponse = await apiService.post(`/admin/cms/scheduled-tasks/${taskId}/run`);

    executionTime.value = Date.now() - startTime;
    output.value = runResponse.data.data.output || 'No output';
    exitCode.value = runResponse.data.data.exit_code || 0;

    // Delete temporary task
    await apiService.delete(`/admin/cms/scheduled-tasks/${taskId}`);

    // Add to history
    addToHistory({
      command: fullCommand,
      exitCode: exitCode.value,
      timestamp: new Date().toISOString()
    );

    if (exitCode.value === 0) {
      console.log('Success:', 
        title: t('common.success'),
        description: t('features.command_runner.executed_success')
      );
    } else {
      console.log('Success:', 
        variant: 'destructive',
        title: t('features.command_runner.executed_error'),
        description: `Exit code: ${exitCode.value}`
      );
    }

  } catch (error) {
    output.value = error.response?.data?.message || error.message;
    exitCode.value = 1;
    
    console.log('Success:', 
      variant: 'destructive',
      title: t('common.error'),
      description: error.message
    );
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
    history.value = JSON.parse(saved);
  }
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleString();
}
</script>

<style scoped>
.command-runner {
  padding: 2rem;
}

.access-denied {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  text-align: center;
}

.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
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

.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.card-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  margin: 0;
}

.card-body {
  padding: 1.5rem;
}

.terminal {
  background: #0a0a0a;
  color: #00ff00;
  padding: 1.5rem;
  border-radius: 0.375rem;
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
  line-height: 1.5;
  max-height: 500px;
  overflow-y: auto;
}

.terminal pre {
  margin: 0;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.history-item {
  padding: 0.75rem;
  border: 1px solid var(--border);
  border-radius: 0.375rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.history-item:hover {
  background: var(--muted);
}
</style>
