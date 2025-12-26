<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ t('features.content_templates.title') }}</h1>
                <p class="text-sm text-muted-foreground mt-1">Create and manage reuseable templates for your content</p>
            </div>
            <Button as-child>
                <router-link :to="{ name: 'content-templates.create' }">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ t('features.content_templates.create') }}
                </router-link>
            </Button>
        </div>

        <Card>
            <CardHeader class="pb-0 border-b-0 space-y-4">
                <div class="flex items-center gap-4">
                    <div class="relative flex-1 max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            :placeholder="t('features.content_templates.search')"
                            class="pl-9"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div v-if="loading" class="p-12 text-center">
                    <Loader2 class="w-8 h-8 animate-spin mx-auto text-muted-foreground mb-4" />
                    <p class="text-muted-foreground font-medium">{{ t('features.content_templates.loading') }}</p>
                </div>

                <div v-else-if="filteredTemplates.length === 0" class="p-12 text-center">
                    <FileText class="w-12 h-12 mx-auto text-muted-foreground/20 mb-4" />
                    <p class="text-muted-foreground font-medium">{{ t('features.content_templates.empty') }}</p>
                </div>

                <Table v-else>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('features.content_templates.table.name') }}</TableHead>
                            <TableHead>{{ t('features.content_templates.table.type') }}</TableHead>
                            <TableHead>{{ t('features.content_templates.table.description') }}</TableHead>
                            <TableHead>{{ t('features.content_templates.table.updated') }}</TableHead>
                            <TableHead class="text-right">{{ t('features.content_templates.table.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="template in filteredTemplates" :key="template.id" class="hover:bg-muted/50 transition-colors group">
                            <TableCell>
                                <div class="text-sm font-semibold text-foreground">{{ template.name }}</div>
                            </TableCell>
                            <TableCell>
                                <Badge variant="secondary" class="capitalize">
                                    {{ template.type || 'post' }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="text-sm text-muted-foreground truncate max-w-xs" :title="template.description">
                                    {{ template.description || '-' }}
                                </div>
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">
                                {{ formatDate(template.updated_at) }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Button 
                                        variant="ghost" 
                                        size="icon" 
                                        @click="createFromTemplate(template)"
                                        :title="t('features.content_templates.actions.createContent')"
                                        class="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20"
                                    >
                                        <CopyPlus class="w-4 h-4" />
                                    </Button>
                                    <Button 
                                        variant="ghost" 
                                        size="icon" 
                                        as-child
                                        class="h-8 w-8"
                                    >
                                        <router-link :to="{ name: 'content-templates.edit', params: { id: template.id } }">
                                            <Pencil class="w-4 h-4" />
                                        </router-link>
                                    </Button>
                                    <Button 
                                        variant="ghost" 
                                        size="icon" 
                                        @click="handleDelete(template)"
                                        :title="t('features.content_templates.actions.delete')"
                                        class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';
import Card from '../../../components/ui/card.vue';
import CardHeader from '../../../components/ui/card-header.vue';
import CardContent from '../../../components/ui/card-content.vue';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Table from '../../../components/ui/table.vue';
import TableHeader from '../../../components/ui/table-header.vue';
import TableBody from '../../../components/ui/table-body.vue';
import TableRow from '../../../components/ui/table-row.vue';
import TableCell from '../../../components/ui/table-cell.vue';
import TableHead from '../../../components/ui/table-head.vue';
import Badge from '../../../components/ui/badge.vue';
import { 
    Plus, 
    Search, 
    FileText, 
    Pencil, 
    Trash2, 
    CopyPlus,
    Loader2
} from 'lucide-vue-next';

const { t } = useI18n();
const router = useRouter();
const templates = ref([]);
const loading = ref(false);
const search = ref('');

const filteredTemplates = computed(() => {
    if (!search.value) return templates.value;
    
    const searchLower = search.value.toLowerCase();
    return templates.value.filter(template => 
        template.name.toLowerCase().includes(searchLower) ||
        (template.description && template.description.toLowerCase().includes(searchLower))
    );
});

const fetchTemplates = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/content-templates');
        const { data } = parseResponse(response);
        templates.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch templates:', error);
        templates.value = [];
    } finally {
        loading.value = false;
    }
};

const createFromTemplate = async (template) => {
    try {
        const response = await api.post(`/admin/cms/content-templates/${template.id}/create-content`);
        const content = parseSingleResponse(response);
        if (content && content.id) {
            router.push({ name: 'contents.edit', params: { id: content.id } });
        }
    } catch (error) {
        console.error('Failed to create content from template:', error);
        alert(error.response?.data?.message || t('features.content_templates.messages.createError'));
    }
};

const handleDelete = async (template) => {
    if (!confirm(t('features.content_templates.messages.deleteConfirm', { name: template.name }))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/content-templates/${template.id}`);
        await fetchTemplates();
    } catch (error) {
        console.error('Failed to delete template:', error);
        alert(t('features.content_templates.messages.deleteError'));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

onMounted(() => {
    fetchTemplates();
});
</script>

