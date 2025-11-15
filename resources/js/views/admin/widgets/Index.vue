<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Widgets</h1>
            <button @click="showCreateModal = true" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Widget
            </button>
        </div>
        <div class="bg-white shadow rounded-lg">
            <div v-if="loading" class="p-6 text-center"><p class="text-gray-500">Loading...</p></div>
            <div v-else-if="widgets.length === 0" class="p-6 text-center"><p class="text-gray-500">No widgets found</p></div>
            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="widget in widgets" :key="widget.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ widget.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ widget.type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ widget.position || '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button @click="editWidget(widget)" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                <button @click="deleteWidget(widget)" class="text-red-600 hover:text-red-900">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <WidgetModal v-if="showCreateModal || showEditModal" @close="closeModal" @saved="handleWidgetSaved" :widget="editingWidget" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../../services/api';
import WidgetModal from '../../../components/widgets/WidgetModal.vue';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const widgets = ref([]);
const loading = ref(false);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingWidget = ref(null);

const fetchWidgets = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/widgets');
        const { data } = parseResponse(response);
        widgets.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch widgets:', error);
    } finally {
        loading.value = false;
    }
};

const editWidget = (widget) => {
    editingWidget.value = widget;
    showEditModal.value = true;
};

const deleteWidget = async (widget) => {
    if (!confirm(`Delete widget "${widget.name}"?`)) return;
    try {
        await api.delete(`/admin/cms/widgets/${widget.id}`);
        await fetchWidgets();
    } catch (error) {
        console.error('Failed to delete widget:', error);
        alert('Failed to delete widget');
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingWidget.value = null;
};

const handleWidgetSaved = () => {
    fetchWidgets();
    closeModal();
};

onMounted(() => {
    fetchWidgets();
});
</script>

