<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.forms.title') }}</h1>
            <router-link :to="{ name: 'forms.create' }">
                <Button>
                    <Plus class="w-5 h-5 mr-2" />
                    {{ $t('features.forms.actions.create') }}
                </Button>
            </router-link>
        </div>

        <!-- Filters -->
        <Card class="p-4 mb-4">
            <div class="flex items-center gap-4">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        type="text"
                        :placeholder="$t('features.forms.filters.search')"
                        class="pl-9"
                    />
                </div>
                <Select v-model="statusFilter">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue :placeholder="$t('features.forms.filters.status')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ $t('features.forms.filters.status') }}</SelectItem>
                        <SelectItem value="active">{{ $t('features.forms.filters.active') }}</SelectItem>
                        <SelectItem value="inactive">{{ $t('features.forms.filters.inactive') }}</SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="trashedFilter">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue :placeholder="$t('common.labels.status')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="without">{{ $t('common.labels.activeOnly') }}</SelectItem>
                        <SelectItem value="with">{{ $t('common.labels.includesTrashed') }}</SelectItem>
                        <SelectItem value="only">{{ $t('common.labels.trashedOnly') }}</SelectItem>
                    </SelectContent>
                </Select>
                <!-- View Toggle -->
                <div class="flex items-center gap-1 p-1 border border-border/40 rounded-xl bg-muted/30">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8"
                        :class="{ 'bg-background shadow-sm': viewMode === 'card' }"
                        @click="viewMode = 'card'"
                        :title="$t('common.actions.view') + ' (Card)'"
                    >
                        <LayoutGrid class="w-4 h-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8"
                        :class="{ 'bg-background shadow-sm': viewMode === 'list' }"
                        @click="viewMode = 'list'"
                        :title="$t('common.actions.view') + ' (List)'"
                    >
                        <List class="w-4 h-4" />
                    </Button>
                </div>

                <!-- Bulk Actions -->
                <div v-if="selectedIds.length > 0" class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10 transition-opacity animate-in fade-in slide-in-from-top-1 ml-auto">
                    <span class="text-sm font-medium text-primary">
                        {{ selectedIds.length }} selected
                    </span>
                    <div class="h-4 w-px bg-primary/20"></div>
                    <Select
                        v-model="bulkActionSelection"
                        @update:model-value="handleBulkAction"
                    >
                        <SelectTrigger class="w-[160px] h-8 border-primary/20">
                            <SelectValue :placeholder="$t('features.content.list.bulkActions')" />
                        </SelectTrigger>
                        <SelectContent>
                             <SelectItem v-if="trashedFilter !== 'only'" value="delete" class="text-destructive focus:text-destructive">{{ $t('common.actions.delete') }}</SelectItem>
                             <SelectItem v-if="trashedFilter === 'only'" value="restore" class="text-emerald-600 focus:text-emerald-600">{{ $t('common.actions.restore') }}</SelectItem>
                             <SelectItem v-if="trashedFilter === 'only'" value="force_delete" class="text-destructive focus:text-destructive">{{ $t('common.actions.forceDelete') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </Card>

        <!-- Loading State -->
        <div v-if="loading" class="bg-card border border-border rounded-lg p-12 text-center">
            <Loader2 class="w-8 h-8 mx-auto animate-spin text-muted-foreground" />
            <p class="text-muted-foreground mt-2">{{ $t('features.forms.messages.loading') }}</p>
        </div>

        <!-- Empty State -->
        <Card v-else-if="filteredForms.length === 0" class="p-12 text-center">
            <FileText class="mx-auto h-12 w-12 text-muted-foreground opacity-50" />
            <p class="mt-4 text-muted-foreground">{{ $t('features.forms.messages.empty') }}</p>
            <router-link :to="{ name: 'forms.create' }">
                <Button class="mt-4">
                    {{ $t('features.forms.actions.createFirst') }}
                </Button>
            </router-link>
        </Card>

        <!-- Card View -->
        <div v-else-if="viewMode === 'card'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <Card
                v-for="form in filteredForms"
                :key="form.id"
                class="overflow-hidden hover:shadow-md"
            >
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start gap-3 flex-1">
                             <Checkbox 
                                :checked="selectedIds.includes(form.id)"
                                @update:checked="toggleSelection(form.id)"
                                class="mt-1"
                            />
                            <div>
                                <h3 class="text-lg font-semibold text-foreground">
                                    {{ form.name }}
                                    <span v-if="form.deleted_at" class="ml-2 px-1.5 py-0.5 rounded text-[10px] font-bold bg-destructive/10 text-destructive uppercase tracking-wide">
                                        {{ $t('common.labels.deleted') }}
                                    </span>
                                </h3>
                                <p class="text-sm text-muted-foreground mt-1">{{ form.slug }}</p>
                            </div>
                        </div>
                        <Badge
                            :variant="form.is_active ? 'success' : 'secondary'"
                        >
                            {{ form.is_active ? $t('features.forms.filters.active') : $t('features.forms.filters.inactive') }}
                        </Badge>
                    </div>

                    <p v-if="form.description" class="text-sm text-muted-foreground mb-4 line-clamp-2">
                        {{ form.description }}
                    </p>

                    <div class="flex items-center justify-between text-sm text-muted-foreground mb-4">
                        <div class="flex items-center">
                            <Tag class="w-4 h-4 mr-1" />
                            {{ $t('features.forms.stats.fields', { count: form.fields?.length || 0 }) }}
                        </div>
                        <div class="flex items-center">
                            <FileText class="w-4 h-4 mr-1" />
                            {{ $t('features.forms.stats.submissions', { count: form.submission_count || 0 }) }}
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 pt-4 border-t border-border">
                        <Button @click="editForm(form)" size="sm" class="flex-1 transition-colors">
                            <Pencil class="w-4 h-4 mr-1" />
                            {{ $t('features.forms.actions.edit') }}
                        </Button>
                        <Button @click="viewSubmissions(form)" variant="secondary" size="sm" class="flex-1">
                            <Inbox class="w-4 h-4 mr-1" />
                            {{ $t('features.forms.actions.submissions') }}
                        </Button>
                        <Button
                            @click="toggleFormStatus(form)"
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8"
                        >
                            <Ban v-if="form.is_active" class="w-4 h-4" />
                            <Check v-else class="w-4 h-4" />
                        </Button>
                        <Button
                            v-if="!form.deleted_at"
                            @click="deleteForm(form)"
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 text-destructive hover:text-destructive"
                            :title="$t('common.actions.delete')"
                        >
                            <Trash2 class="w-4 h-4" />
                        </Button>
                        <Button
                            v-if="form.deleted_at"
                            @click="restoreForm(form)"
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
                            :title="$t('common.actions.restore')"
                        >
                            <RotateCcw class="w-4 h-4" />
                        </Button>
                        <Button
                            v-if="form.deleted_at"
                            @click="forceDeleteForm(form)"
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                            :title="$t('common.actions.forceDelete')"
                        >
                            <Trash2 class="w-4 h-4" />
                        </Button>
                    </div>
                </div>
            </Card>
        </div>

        <!-- List View -->
        <Card v-else class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 w-[50px]">
                                <Checkbox 
                                    :checked="isAllSelected"
                                    @update:checked="toggleSelectAll"
                                />
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">
                                {{ $t('features.forms.modal.formName') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground">
                                {{ $t('features.forms.modal.slug') }}
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-muted-foreground">
                                {{ $t('features.forms.stats.fields', { count: '' }).replace('{count}', '').trim() }}
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-muted-foreground">
                                {{ $t('features.forms.actions.submissions') }}
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-muted-foreground">
                                Status
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-muted-foreground">
                                {{ $t('common.actions.title') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="form in filteredForms" :key="form.id" class="hover:bg-muted/30 transition-colors">
                            <td class="px-4 py-4">
                                <Checkbox 
                                    :checked="selectedIds.includes(form.id)"
                                    @update:checked="toggleSelection(form.id)"
                                />
                            </td>
                            <td class="px-4 py-4">
                                <div>
                                    <p class="font-medium text-foreground">
                                        {{ form.name }}
                                        <span v-if="form.deleted_at" class="ml-2 px-1.5 py-0.5 rounded text-[10px] font-bold bg-destructive/10 text-destructive uppercase tracking-wide">
                                            {{ $t('common.labels.deleted') }}
                                        </span>
                                    </p>
                                    <p v-if="form.description" class="text-sm text-muted-foreground line-clamp-1">{{ form.description }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <code class="text-sm text-muted-foreground bg-muted px-2 py-1 rounded">{{ form.slug }}</code>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="text-sm text-muted-foreground">{{ form.fields?.length || 0 }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <Button variant="link" size="sm" class="h-auto p-0" @click="viewSubmissions(form)">
                                    {{ form.submission_count || 0 }}
                                </Button>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <Badge
                                    :variant="form.is_active ? 'success' : 'secondary'"
                                >
                                    {{ form.is_active ? $t('features.forms.filters.active') : $t('features.forms.filters.inactive') }}
                                </Badge>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center space-x-1">
                                    <Button @click="editForm(form)" variant="ghost" size="icon" class="h-8 w-8 text-indigo-500 hover:text-indigo-600 hover:bg-indigo-500/10" :title="$t('common.actions.edit')">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button @click="viewSubmissions(form)" variant="ghost" size="icon" class="h-8 w-8 text-blue-500 hover:text-blue-600 hover:bg-blue-500/10" :title="$t('features.forms.actions.submissions')">
                                        <Inbox class="w-4 h-4" />
                                    </Button>
                                    <Button
                                        @click="toggleFormStatus(form)"
                                        variant="ghost"
                                        size="icon"
                                        :class="form.is_active ? 'h-8 w-8 text-amber-500 hover:text-amber-600 hover:bg-amber-500/10' : 'h-8 w-8 text-emerald-500 hover:text-emerald-600 hover:bg-emerald-500/10'"
                                        :title="form.is_active ? $t('common.actions.deactivate') : $t('common.actions.activate')"
                                    >
                                        <Ban v-if="form.is_active" class="w-4 h-4" />
                                        <Check v-else class="w-4 h-4" />
                                    </Button>
                                    <Button
                                        v-if="!form.deleted_at"
                                        @click="deleteForm(form)"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                        :title="$t('common.actions.delete')"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                    <Button
                                        v-if="form.deleted_at"
                                        @click="restoreForm(form)"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
                                        :title="$t('common.actions.restore')"
                                    >
                                        <RotateCcw class="w-4 h-4" />
                                    </Button>
                                    <Button
                                        v-if="form.deleted_at"
                                        @click="forceDeleteForm(form)"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                        :title="$t('common.actions.forceDelete')"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- Submissions View -->
        <Submissions
            v-if="selectedForm"
            :form="selectedForm"
            @close="selectedForm = null"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { useConfirm } from '../../../composables/useConfirm';
import api from '../../../services/api';
import { useToast } from '../../../composables/useToast';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import Submissions from './Submissions.vue';
import Card from '../../../components/ui/card.vue';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Badge from '../../../components/ui/badge.vue';
import Select from '../../../components/ui/select.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';
import Checkbox from '../../../components/ui/checkbox.vue';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import LayoutGrid from 'lucide-vue-next/dist/esm/icons/layout-grid.js';
import List from 'lucide-vue-next/dist/esm/icons/list.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Tag from 'lucide-vue-next/dist/esm/icons/tag.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Inbox from 'lucide-vue-next/dist/esm/icons/inbox.js';
import Ban from 'lucide-vue-next/dist/esm/icons/ban.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';

import type { Form } from '@/types/forms';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

// State
const router = useRouter();
const forms = ref<Form[]>([]);
const loading = ref(true);
const search = ref('');
const statusFilter = ref('all');
const trashedFilter = ref('without');
const selectedForm = ref<Form | null>(null);
const viewMode = ref('card');

const filteredForms = computed(() => {
    let result = forms.value;

    if (search.value) {
        const query = search.value.toLowerCase();
        result = result.filter(form =>
            form.name.toLowerCase().includes(query) ||
            form.slug.toLowerCase().includes(query) ||
            (form.description && form.description.toLowerCase().includes(query))
        );
    }

    if (statusFilter.value && statusFilter.value !== 'all') {
        const isActive = statusFilter.value === 'active';
        result = result.filter((form: Form) => form.is_active === isActive);
    }

    return result;
});

const fetchForms = async () => {
    try {
        loading.value = true;
        
        const params: Record<string, any> = {};
        if (trashedFilter.value !== 'without') {
            params.trashed = trashedFilter.value;
        }

        const response = await api.get('/admin/ja/forms', { params });
        const { data } = parseResponse<Form>(response);
        forms.value = ensureArray<Form>(data);
    } catch (error: any) {
        console.error('Error fetching forms:', error);
        forms.value = [];
    } finally {
        loading.value = false;
    }
};

const editForm = (form: Form) => {
    router.push({ name: 'forms.edit', params: { id: form.id } });
};

const viewSubmissions = (form: Form) => {
    router.push({ name: 'forms.submissions', params: { id: form.id } });
};

const toggleFormStatus = async (form: Form) => {
    try {
        const response = await api.put(`/admin/ja/forms/${form.id}`, {
            is_active: !form.is_active
        });
        const updatedForm = (response.data?.data || response.data) as Form;
        const index = forms.value.findIndex((f: Form) => f.id === form.id);
        if (index !== -1) {
            forms.value[index] = updatedForm;
        }
        toast.success.action(t('common.messages.success.updated', { item: 'Form status' }));
    } catch (error: any) {
        console.error('Error toggling form status:', error);
        toast.error.fromResponse(error);
    }
};

const deleteForm = async (form: Form) => {
    const confirmed = await confirm({
        title: t('features.forms.actions.delete'),
        message: t('features.forms.messages.deleteConfirm', { name: form.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/admin/ja/forms/${form.id}`);
        toast.success.delete('Form');
        fetchForms();
    } catch (error: any) {
        console.error('Failed to delete form:', error);
        toast.error.fromResponse(error);
    }
};

const bulkDelete = async () => {
    if (selectedIds.value.length === 0) return;

    const confirmed = await confirm({
        title: t('features.forms.bulk.delete'),
        message: t('features.forms.bulk.confirmDelete', { count: selectedIds.value.length }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/ja/forms/bulk-action', { 
            ids: selectedIds.value,
            action: 'delete'
        });
        toast.success.delete('Forms');
        selectedIds.value = [];
        fetchForms();
    } catch (error: any) {
        console.error('Failed to bulk delete forms:', error);
        toast.error.fromResponse(error);
    }
};

const duplicateForm = async (form: Form) => {
    try {
        await api.post(`/admin/ja/forms/${form.id}/duplicate`);
        toast.success.duplicate('Form');
        fetchForms();
    } catch (error: any) {
        console.error('Failed to duplicate form:', error);
        toast.error.fromResponse(error);
    }
};

const restoreForm = async (form: Form) => {
    const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: `Are you sure you want to restore ${form.name}?`,
        variant: 'info',
        confirmText: t('common.actions.restore'),
    });

    if (!confirmed) return;

    try {
        await api.post(`/admin/ja/forms/${form.id}/restore`);
        toast.success.restore('Form');
        fetchForms();
    } catch (error: any) {
        console.error('Failed to restore form:', error);
        toast.error.fromResponse(error);
    }
};

const forceDeleteForm = async (form: Form) => {
    const confirmed = await confirm({
        title: t('common.actions.forceDelete'),
        message: `Are you sure you want to PERMANENTLY delete ${form.name}? This cannot be undone.`,
        variant: 'danger',
        confirmText: t('common.actions.forceDelete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/admin/ja/forms/${form.id}/force-delete`);
        toast.success.action(t('common.messages.success.deleted', { item: 'Form' }));
        fetchForms();
    } catch (error: any) {
        console.error('Failed to force delete form:', error);
        toast.error.fromResponse(error);
    }
};

const selectedIds = ref<(number | string)[]>([]);

const isAllSelected = computed(() => {
    return filteredForms.value.length > 0 && selectedIds.value.length === filteredForms.value.length;
});

const toggleSelection = (formId: number | string) => {
    const index = selectedIds.value.indexOf(formId);
    if (index === -1) {
        selectedIds.value.push(formId);
    } else {
        selectedIds.value.splice(index, 1);
    }
};

const toggleSelectAll = (checked: any) => {
    if (checked) {
        selectedIds.value = filteredForms.value.map(f => f.id);
    } else {
        selectedIds.value = [];
    }
};

const bulkActionSelection = ref('');

const handleBulkAction = async (value: any) => {
    if (!value) return;
    
    if (value === 'delete') {
        const confirmed = await confirm({
            title: t('features.forms.bulk.delete'),
            message: t('features.forms.bulk.confirmDelete', { count: selectedIds.value.length }),
            variant: 'danger',
            confirmText: t('common.actions.delete'),
        });
        if (confirmed) await performBulkAction('delete');
    } else if (value === 'restore') {
        const confirmed = await confirm({
            title: t('common.actions.restore'),
            message: `Restore ${selectedIds.value.length} forms?`,
            variant: 'info',
            confirmText: t('common.actions.restore'),
        });
        if (confirmed) await performBulkAction('restore');
    } else if (value === 'force_delete') {
        const confirmed = await confirm({
            title: t('common.actions.forceDelete'),
            message: `Permanently delete ${selectedIds.value.length} forms?`,
            variant: 'danger',
            confirmText: t('common.actions.forceDelete'),
        });
        if (confirmed) await performBulkAction('force_delete');
    }
    
    bulkActionSelection.value = '';
};

const performBulkAction = async (action: any) => {
    try {
        await api.post('/admin/ja/forms/bulk-action', { 
            ids: selectedIds.value,
            action: action
        });
        toast.success.action(t('common.messages.success.updated', { item: 'Forms' }));
        selectedIds.value = [];
        fetchForms();
    } catch (error: any) {
        console.error('Failed to bulk action forms:', error);
        toast.error.fromResponse(error);
    }
};

onMounted(() => {
    fetchForms();
});

watch(trashedFilter, () => {
    fetchForms();
});
</script>
