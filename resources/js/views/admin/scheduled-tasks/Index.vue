<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Scheduled Tasks</h1>
            <button
                @click="showCreateModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Task
            </button>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center space-x-4">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search tasks..."
                        class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <select
                        v-model="statusFilter"
                        class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-gray-500">Loading...</p>
            </div>

            <div v-else-if="filteredTasks.length === 0" class="p-6 text-center">
                <p class="text-gray-500">No scheduled tasks found</p>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Command
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Schedule
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Last Run
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="task in filteredTasks" :key="task.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ task.name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 font-mono">{{ task.command }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ task.schedule || '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatDate(task.last_run_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="task.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                            >
                                {{ task.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button
                                    @click="runTask(task)"
                                    class="text-green-600 hover:text-green-900"
                                >
                                    Run Now
                                </button>
                                <button
                                    @click="editTask(task)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="deleteTask(task)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Delete
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
import api from '../../../services/api';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import TaskModal from '../../../components/scheduled-tasks/TaskModal.vue';

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
    if (!confirm(`Are you sure you want to run task "${task.name}" now?`)) {
        return;
    }

    try {
        await api.post(`/admin/cms/scheduled-tasks/${task.id}/run`);
        alert('Task executed successfully');
        await fetchTasks();
    } catch (error) {
        console.error('Failed to run task:', error);
        alert(error.response?.data?.message || 'Failed to run task');
    }
};

const deleteTask = async (task) => {
    if (!confirm(`Are you sure you want to delete task "${task.name}"?`)) {
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
    if (!date) return 'Never';
    return new Date(date).toLocaleString();
};

onMounted(() => {
    fetchTasks();
});
</script>

