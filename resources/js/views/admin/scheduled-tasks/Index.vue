<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.system.scheduled_tasks.title') }}</h1>
            <button
                @click="showCreateModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ t('features.system.scheduled_tasks.create') }}
            </button>
        </div>

        <div class="bg-card shadow rounded-lg">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center space-x-4">
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="t('features.system.scheduled_tasks.search')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    <select
                        v-model="statusFilter"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">{{ t('features.system.scheduled_tasks.filters.all') }}</option>
                        <option value="active">{{ t('features.system.scheduled_tasks.filters.active') }}</option>
                        <option value="inactive">{{ t('features.system.scheduled_tasks.filters.inactive') }}</option>
                    </select>
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.system.scheduled_tasks.loading') }}</p>
            </div>

            <div v-else-if="filteredTasks.length === 0" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.system.scheduled_tasks.empty') }}</p>
            </div>

            <table v-else class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.system.scheduled_tasks.table.name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.system.scheduled_tasks.table.command') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.system.scheduled_tasks.table.schedule') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.system.scheduled_tasks.table.last_run') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.system.scheduled_tasks.table.status') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.system.scheduled_tasks.table.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="task in filteredTasks" :key="task.id" class="hover:bg-muted">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ task.name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-foreground font-mono">{{ task.command }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-foreground">{{ task.schedule || '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                            {{ formatDate(task.last_run_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="task.is_active ? 'bg-green-500/20 text-green-400' : 'bg-secondary text-secondary-foreground'"
                            >
                                {{ task.is_active ? t('features.system.scheduled_tasks.filters.active') : t('features.system.scheduled_tasks.filters.inactive') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button
                                    @click="runTask(task)"
                                    class="text-green-600 hover:text-green-900"
                                >
                                    {{ t('features.system.scheduled_tasks.table.run') }}
                                </button>
                                <button
                                    @click="editTask(task)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    {{ t('features.system.scheduled_tasks.table.edit') }}
                                </button>
                                <button
                                    @click="deleteTask(task)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    {{ t('features.system.scheduled_tasks.table.delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Create/Edit Modal -->
        <TaskModal
            v-if="showCreateModal || showEditModal"
            @close="closeModal"
            @saved="handleTaskSaved"
            :task="editingTask"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import TaskModal from '../../../components/scheduled-tasks/TaskModal.vue';

const { t } = useI18n();

const tasks = ref([]);
const loading = ref(false);
const search = ref('');
const statusFilter = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingTask = ref(null);

const filteredTasks = computed(() => {
    let filtered = tasks.value;
    
    if (statusFilter.value) {
        filtered = filtered.filter(task => 
            statusFilter.value === 'active' ? task.is_active : !task.is_active
        );
    }
    
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(task => 
            task.name.toLowerCase().includes(searchLower) ||
            task.command.toLowerCase().includes(searchLower)
        );
    }
    
    return filtered;
});

const fetchTasks = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/scheduled-tasks');
        const { data } = parseResponse(response);
        tasks.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch tasks:', error);
        tasks.value = [];
    } finally {
        loading.value = false;
    }
};

const editTask = (task) => {
    editingTask.value = task;
    showEditModal.value = true;
};

const runTask = async (task) => {
    if (!confirm(t('features.system.scheduled_tasks.confirm.run', { name: task.name }))) {
        return;
    }

    try {
        await api.post(`/admin/cms/scheduled-tasks/${task.id}/run`);
        alert(t('features.system.scheduled_tasks.messages.executed'));
        await fetchTasks();
    } catch (error) {
        console.error('Failed to run task:', error);
        alert(error.response?.data?.message || t('features.system.scheduled_tasks.messages.failed_execute'));
    }
};

const deleteTask = async (task) => {
    if (!confirm(t('features.system.scheduled_tasks.confirm.delete', { name: task.name }))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/scheduled-tasks/${task.id}`);
        await fetchTasks();
    } catch (error) {
        console.error('Failed to delete task:', error);
        alert('Failed to delete task');
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingTask.value = null;
};

const handleTaskSaved = () => {
    fetchTasks();
    closeModal();
};

const formatDate = (date) => {
    if (!date) return t('features.system.scheduled_tasks.table.never');
    return new Date(date).toLocaleString();
};

onMounted(() => {
    fetchTasks();
});
</script>

