<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.widgets.title') }}</h1>
            <button @click="showCreateModal = true" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ $t('features.widgets.new') }}
            </button>
        </div>
        <div class="bg-card shadow rounded-lg">
            <div v-if="loading" class="p-6 text-center"><p class="text-muted-foreground">{{ $t('features.widgets.loading') }}</p></div>
            <div v-else-if="widgets.length === 0" class="p-6 text-center"><p class="text-muted-foreground">{{ $t('features.widgets.empty') }}</p></div>
            <table v-else class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">{{ $t('features.widgets.table.name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">{{ $t('features.widgets.table.type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">{{ $t('features.widgets.table.position') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase">{{ $t('features.widgets.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="widget in widgets" :key="widget.id" class="hover:bg-muted">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">{{ widget.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ widget.type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ widget.position || '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button @click="editWidget(widget)" class="text-indigo-600 hover:text-indigo-900">{{ $t('features.widgets.actions.edit') }}</button>
                                <button @click="deleteWidget(widget)" class="text-red-600 hover:text-red-900">{{ $t('features.widgets.actions.delete') }}</button>
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
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import WidgetModal from '../../../components/widgets/WidgetModal.vue';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const { t } = useI18n();
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
    if (!confirm(t('features.widgets.messages.deleteConfirm', { name: widget.name }))) return;
    try {
        await api.delete(`/admin/cms/widgets/${widget.id}`);
        await fetchWidgets();
    } catch (error) {
        console.error('Failed to delete widget:', error);
        alert(t('features.widgets.messages.deleteFailed'));
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

