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
                <!-- View Toggle -->
                <div class="flex items-center gap-1 p-1 border border-border rounded-md bg-muted/30">
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
            </div>
            <!-- Bulk Actions -->
            <div v-if="selectedIds.length > 0" class="mt-4 pt-4 border-t flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-muted-foreground">{{ selectedIds.length }} selected</span>
                </div>
                <div class="flex items-center gap-2">
                     <Button variant="destructive" size="sm" @click="bulkAction('delete')">
                        <Trash2 class="w-4 h-4 mr-2" />
                        {{ $t('common.actions.delete') }}
                    </Button>
                    <Button variant="ghost" size="sm" @click="selectedIds = []">
                        {{ $t('common.actions.clear') }}
                    </Button>
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
                class="overflow-hidden hover:shadow-md transition-shadow"
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
                                <h3 class="text-lg font-semibold text-foreground">{{ form.name }}</h3>
                                <p class="text-sm text-muted-foreground mt-1">{{ form.slug }}</p>
                            </div>
                        </div>
                        <Badge
                            :class="{
                                'bg-green-500/10 text-green-500 border-green-500/20': form.is_active,
                                'bg-muted text-muted-foreground': !form.is_active
                            }"
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
                        <Button @click="editForm(form)" size="sm" class="flex-1">
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
                            @click="deleteForm(form)"
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 text-destructive hover:text-destructive"
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
                                    <p class="font-medium text-foreground">{{ form.name }}</p>
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
                                    :class="{
                                        'bg-green-500/10 text-green-500 border-green-500/20': form.is_active,
                                        'bg-muted text-muted-foreground': !form.is_active
                                    }"
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
                                        @click="deleteForm(form)"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                        :title="$t('common.actions.delete')"
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

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '../../../services/api';
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
import { 
    Plus, Search, LayoutGrid, List, Loader2, FileText, Tag, 
    Pencil, Inbox, Ban, Check, Trash2 
} from 'lucide-vue-next';

const { t } = useI18n();
const router = useRouter();
const forms = ref([]);
const loading = ref(true);
const search = ref('');
const statusFilter = ref('all');
const selectedForm = ref(null);
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
        result = result.filter(form => form.is_active === isActive);
    }

    return result;
});

const fetchForms = async () => {
    try {
        loading.value = true;
        const response = await api.get('/admin/cms/forms');
        const { data } = parseResponse(response);
        forms.value = ensureArray(data);
    } catch (error) {
        console.error('Error fetching forms:', error);
        forms.value = [];
    } finally {
        loading.value = false;
    }
};

const editForm = (form) => {
    router.push({ name: 'forms.edit', params: { id: form.id } });
};

const viewSubmissions = (form) => {
    router.push({ name: 'forms.submissions', params: { id: form.id } });
};

const toggleFormStatus = async (form) => {
    try {
        const response = await api.put(`/admin/cms/forms/${form.id}`, {
            is_active: !form.is_active
        });
        const updatedForm = response.data?.data || response.data;
        const index = forms.value.findIndex(f => f.id === form.id);
        if (index !== -1) {
            forms.value[index] = updatedForm;
        }
    } catch (error) {
        console.error('Error toggling form status:', error);
        alert(t('features.forms.messages.saveFailed'));
    }
};

const deleteForm = async (form) => {
    if (!confirm(t('features.forms.messages.deleteConfirm', { name: form.name }))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/forms/${form.id}`);
        forms.value = forms.value.filter(f => f.id !== form.id);
    } catch (error) {
        console.error('Error deleting form:', error);
        alert(t('common.messages.error.deleteFailed', { item: 'form' }));
    }
};

const selectedIds = ref([]);

const isAllSelected = computed(() => {
    return filteredForms.value.length > 0 && selectedIds.value.length === filteredForms.value.length;
});

const toggleSelection = (formId) => {
    const index = selectedIds.value.indexOf(formId);
    if (index === -1) {
        selectedIds.value.push(formId);
    } else {
        selectedIds.value.splice(index, 1);
    }
};

const toggleSelectAll = (checked) => {
    if (checked) {
        selectedIds.value = filteredForms.value.map(f => f.id);
    } else {
        selectedIds.value = [];
    }
};

const bulkAction = async (action) => {
    if (selectedIds.value.length === 0) return;
    
    if (action === 'delete') {
         if (!confirm(t('common.messages.confirm.bulkDelete', { count: selectedIds.value.length }))) return;

         try {
             await api.post('/admin/cms/forms/bulk-action', {
                 ids: selectedIds.value,
                 action: 'delete'
             });
             selectedIds.value = [];
             await fetchForms();
             alert(t('common.messages.success.deleted'));
         } catch (error) {
             console.error('Bulk delete failed:', error);
             alert(t('common.messages.error.deleteFailed', { item: 'forms' }));
         }
    }
};

onMounted(() => {
    fetchForms();
});
</script>
