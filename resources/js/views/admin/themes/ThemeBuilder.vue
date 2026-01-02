<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Theme Builder</h1>
                <p class="text-muted-foreground">Manage global headers, footers, and page templates.</p>
            </div>
            <Button @click="showCreateModal = true">
                <Plus class="w-4 h-4 mr-2" />
                Add New Template
            </Button>
        </div>

        <div v-if="loading" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <div v-for="i in 3" :key="i" class="h-64 border rounded-lg bg-muted/20 animate-pulse"></div>
        </div>

        <div v-else-if="templates.length > 0" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <!-- Template Card -->
            <div v-for="template in templates" :key="template.id" class="border rounded-lg shadow-sm bg-card hover:shadow-md transition-shadow">
                <div class="p-4 border-b bg-muted/30 flex justify-between items-center">
                     <div class="flex items-center gap-2">
                         <h3 class="font-semibold">{{ template.name }}</h3>
                    </div>
                    <div class="flex gap-1">
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="editTemplate(template)" title="Settings">
                            <Settings class="h-4 w-4" />
                        </Button>
                         <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="deleteTemplate(template)" title="Delete">
                            <Trash class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
                
                <div class="p-4 space-y-3">
                    <!-- Header -->
                    <div class="flex items-center justify-between p-2 text-sm border rounded hover:bg-muted/50 transition-colors">
                        <div class="flex items-center gap-2">
                            <LayoutTemplate class="w-4 h-4 text-muted-foreground" />
                            <span>Global Header</span>
                        </div>
                        <div class="flex gap-1">
                             <Button size="icon" variant="ghost" class="h-6 w-6" @click="openBuilder(template.id, 'header')">
                                <Edit class="w-3 h-3" />
                            </Button>
                             <Button size="icon" variant="ghost" class="h-6 w-6 text-destructive" @click="clearPart(template, 'header')">
                                <Trash class="w-3 h-3" />
                            </Button>
                        </div>
                    </div>

                    <!-- Body (Custom Body) -->
                    <div class="flex items-center justify-between p-2 text-sm border rounded hover:bg-muted/50 transition-colors">
                        <div class="flex items-center gap-2">
                            <FileText class="w-4 h-4 text-muted-foreground" />
                            <span>Custom Body</span>
                        </div>
                        <div class="flex gap-1">
                             <Button size="icon" variant="ghost" class="h-6 w-6" @click="openBuilder(template.id, 'body')">
                                <Edit class="w-3 h-3" />
                            </Button>
                             <Button size="icon" variant="ghost" class="h-6 w-6 text-destructive" @click="clearPart(template, 'body')">
                                <Trash class="w-3 h-3" />
                            </Button>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between p-2 text-sm border rounded hover:bg-muted/50 transition-colors">
                        <div class="flex items-center gap-2">
                            <PanelBottom class="w-4 h-4 text-muted-foreground" />
                            <span>Global Footer</span>
                        </div>
                        <div class="flex gap-1">
                             <Button size="icon" variant="ghost" class="h-6 w-6" @click="openBuilder(template.id, 'footer')">
                                <Edit class="w-3 h-3" />
                            </Button>
                             <Button size="icon" variant="ghost" class="h-6 w-6 text-destructive" @click="clearPart(template, 'footer')">
                                <Trash class="w-3 h-3" />
                            </Button>
                        </div>
                    </div>
                </div>
                
                 <div class="p-3 border-t bg-muted/10 text-xs text-muted-foreground">
                    <div class="flex flex-wrap gap-1">
                        <Badge variant="outline" v-for="(cond, i) in template.conditions" :key="i">
                            {{ formatCondition(cond) }}
                        </Badge>
                        <span v-if="!template.conditions?.length">No conditions (Unused)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center justify-center py-20 border-2 border-dashed rounded-xl bg-muted/5">
            <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-4">
                <LayoutTemplate class="w-8 h-8 text-muted-foreground" />
            </div>
            <h2 class="text-xl font-semibold">No Templates Found</h2>
            <p class="text-muted-foreground mt-1 mb-6">Create a template to start building global layouts.</p>
            <Button @click="showCreateModal = true" variant="outline">
                <Plus class="w-4 h-4 mr-2" />
                Add Your First Template
            </Button>
        </div>

        <!-- Create/Edit Modal -->
        <Dialog :open="showCreateModal" @update:open="showCreateModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingTemplate ? 'Edit Template' : 'Create Template' }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label>Template Name</Label>
                        <Input v-model="form.name" placeholder="e.g. Products Page" />
                    </div>
                    <div class="space-y-2">
                        <Label>Apply To (Condition)</Label>
                        <Select v-model="form.conditionType">
                            <SelectTrigger>
                                <SelectValue placeholder="Select condition" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Entire Site</SelectItem>
                                <SelectItem value="homepage">Homepage</SelectItem>
                                <SelectItem value="post_type">Specific Post Type</SelectItem>
                                <SelectItem value="404">404 Page</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                     <div class="space-y-2" v-if="form.conditionType === 'post_type'">
                        <Label>Post Type</Label>
                        <Select v-model="form.conditionValue">
                            <SelectTrigger>
                                <SelectValue placeholder="Select post type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="post">Posts</SelectItem>
                                <SelectItem value="page">Pages</SelectItem>
                                <SelectItem value="product">Products</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showCreateModal = false">Cancel</Button>
                    <Button @click="saveTemplate">Save Template</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';
import { Plus, LayoutTemplate, FileText, PanelBottom, Edit, Trash, Settings } from 'lucide-vue-next';

// Components
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Badge from '@/components/ui/badge.vue';
import Label from '@/components/ui/label.vue';

// Select
import Select from '@/components/ui/select.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';

// Dialog
import Dialog from '@/components/ui/dialog.vue';
import DialogContent from '@/components/ui/dialog-content.vue';
import DialogHeader from '@/components/ui/dialog-header.vue';
import DialogTitle from '@/components/ui/dialog-title.vue';
import DialogFooter from '@/components/ui/dialog-footer.vue';


const router = useRouter();
const templates = ref([]);
const loading = ref(true);
const showCreateModal = ref(false);
const editingTemplate = ref(null);
const form = ref({
    name: '',
    conditionType: 'all',
    conditionValue: ''
});

const loadTemplates = async () => {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/cms/theme-templates');
        if (Array.isArray(data)) {
            templates.value = data;
        } else if (data && Array.isArray(data.data)) {
             // Handle pagination/resource wrapper if it changes in future
             templates.value = data.data;
        } else {
             console.error('Invalid templates format:', data);
             templates.value = [];
        }
    } catch (e) {
        console.error('Failed to load templates:', e);
        templates.value = [];
    } finally {
        loading.value = false;
    }
};

const saveTemplate = async () => {
    const payload = {
        name: form.value.name,
        conditions: [{ type: form.value.conditionType, value: form.value.conditionValue }]
    };

    try {
        if (editingTemplate.value) {
            await api.put(`/admin/cms/theme-templates/${editingTemplate.value.id}`, payload);
        } else {
            await api.post('/admin/cms/theme-templates', payload);
        }
        showCreateModal.value = false;
        loadTemplates();
        form.value = { name: '', conditionType: 'all', conditionValue: '' };
        editingTemplate.value = null;
    } catch (e) {
        console.error(e);
    }
};

const editTemplate = (template) => {
    editingTemplate.value = template;
    form.value.name = template.name;
    const cond = template.conditions?.[0] || {};
    form.value.conditionType = cond.type || 'all';
    form.value.conditionValue = cond.value || '';
    showCreateModal.value = true;
};

const deleteTemplate = async (template) => {
    if(!confirm('Are you sure?')) return;
    await api.delete(`/admin/cms/theme-templates/${template.id}`);
    loadTemplates();
};

const openBuilder = (id, part) => {
    router.push({ 
        name: 'theme-template-editor', 
        params: { 
            id: id, 
            part: part 
        } 
    });
};

const clearPart = async (template, part) => {
     if(!confirm(`Clear ${part} layout?`)) return;
     // Optimization: Just update the specific field to null
     await api.put(`/admin/cms/theme-templates/${template.id}`, { [`${part}_data`]: null });
     loadTemplates();
};

const formatCondition = (cond) => {
    if (cond.type === 'all') return 'Entire Site';
    if (cond.type === 'homepage') return 'Homepage';
    if (cond.type === 'post_type') return `All ${cond.value}s`;
    return cond.type;
};

onMounted(loadTemplates);
</script>
