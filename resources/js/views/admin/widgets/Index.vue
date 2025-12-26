<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.widgets.title') }}</h1>
            <Button @click="showCreateModal = true">
                <Plus class="w-4 h-4 mr-2" />
                {{ $t('features.widgets.new') }}
            </Button>
        </div>
        <Card>
            <div v-if="loading" class="flex flex-col items-center justify-center py-12">
                <Loader2 class="w-8 h-8 animate-spin text-muted-foreground mb-2" />
                <p class="text-muted-foreground">{{ $t('features.widgets.loading') }}</p>
            </div>
            <div v-else-if="widgets.length === 0" class="flex flex-col items-center justify-center py-12">
                <Layout class="w-12 h-12 text-muted-foreground/20 mb-4" />
                <p class="text-muted-foreground">{{ $t('features.widgets.empty') }}</p>
            </div>
            <Table v-else>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ $t('features.widgets.table.name') }}</TableHead>
                        <TableHead>{{ $t('features.widgets.table.type') }}</TableHead>
                        <TableHead>{{ $t('features.widgets.table.position') }}</TableHead>
                        <TableHead class="text-right">{{ $t('features.widgets.table.actions') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="widget in widgets" :key="widget.id">
                        <TableCell class="font-medium">
                            <div class="flex items-center gap-2">
                                {{ widget.name }}
                                <Badge v-if="!widget.is_active" variant="destructive" class="text-[10px] px-1.5 py-0 h-4">
                                    Inactive
                                </Badge>
                            </div>
                        </TableCell>
                        <TableCell>
                            <Badge variant="outline" class="capitalize">{{ widget.type }}</Badge>
                        </TableCell>
                        <TableCell>
                            <code class="text-xs bg-muted px-1.5 py-0.5 rounded border border-border">
                                {{ widget.position || '-' }}
                            </code>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-2">
                                <Button variant="ghost" size="sm" @click="editWidget(widget)">
                                    <Pencil class="w-4 h-4 mr-2" />
                                    {{ $t('features.widgets.actions.edit') }}
                                </Button>
                                <Button 
                                    variant="ghost" 
                                    size="sm" 
                                    @click="deleteWidget(widget)"
                                    class="text-destructive hover:text-destructive hover:bg-destructive/10"
                                >
                                    <Trash2 class="w-4 h-4 mr-2" />
                                    {{ $t('features.widgets.actions.delete') }}
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </Card>
        <WidgetModal v-if="showCreateModal || showEditModal" @close="closeModal" @saved="handleWidgetSaved" :widget="editingWidget" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import Button from '../../../components/ui/button.vue';
import Card from '../../../components/ui/card.vue';
import Badge from '../../../components/ui/badge.vue';
import Table from '../../../components/ui/table.vue';
import TableHeader from '../../../components/ui/table-header.vue';
import TableRow from '../../../components/ui/table-row.vue';
import TableHead from '../../../components/ui/table-head.vue';
import TableBody from '../../../components/ui/table-body.vue';
import TableCell from '../../../components/ui/table-cell.vue';
import { 
    Plus, Pencil, Trash2, 
    Loader2, Layout 
} from 'lucide-vue-next';
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

