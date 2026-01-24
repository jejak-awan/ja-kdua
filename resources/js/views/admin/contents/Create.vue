<template>
    <div class="max-w-[1600px] mx-auto pb-20 px-4 sm:px-6 lg:px-8">
        <!-- Header Actions -->
        <div class="mb-8 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" asChild>
                    <router-link :to="{ name: 'contents' }">
                        <ArrowLeft class="w-4 h-4" />
                    </router-link>
                </Button>
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ $t('features.content.list.createNew') }}</h1>
                    <AutoSaveIndicator
                        :status="autoSaveStatus"
                        :last-saved="lastSaved || undefined"
                    />
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                 <Button 
                    variant="ghost" 
                    size="icon" 
                    @click="isSidebarOpen = !isSidebarOpen"
                    :title="isSidebarOpen ? 'Close Settings' : 'Open Settings'"
                >
                    <PanelRightClose v-if="isSidebarOpen" class="w-4 h-4" />
                    <PanelRightOpen v-else class="w-4 h-4" />
                </Button>
                <Button variant="ghost" @click="handleCancel">
                    {{ $t('features.content.form.cancel') }}
                </Button>
                <Button
                    @click="handleSubmit"
                    :disabled="loading || !isValid"
                    class="min-w-[120px] shadow-sm"
                >
                    <template v-if="loading">
                        <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                        {{ $t('features.content.form.creating') }}
                    </template>
                    <template v-else>
                        <Save class="w-4 h-4 mr-2" />
                        {{ $t('features.content.form.create') }}
                    </template>
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <!-- Main Content Area (Center) -->
            <div :class="[isSidebarOpen ? 'lg:col-span-8' : 'lg:col-span-12', 'space-y-8 transition-all duration-300 ease-in-out']">
                <ContentMain
                    v-model="form"
                    @save="handleSubmit"
                    @mode-selected="handleModeSelected"
                    @toggle-auto-save="handleAutoSaveToggle"
                    @cancel="handleCancel"
                />
            </div>

            <!-- Sidebar (Right) -->
            <div :class="[isSidebarOpen ? 'lg:col-span-4' : 'hidden', 'space-y-6 transition-all duration-300 ease-in-out']">
                <ContentSidebar
                    v-model="form"
                    v-model:selected-tags="selectedTags"
                    :categories="categories"
                    :tags="tags"
                    :menus="menus"
                />
            </div>
        </div>

        <!-- Confirm Dialog -->
        <Dialog :open="showConfirmDialog" @update:open="showConfirmDialog = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Confirm Navigation</DialogTitle>
                    <DialogDescription>
                        {{ $t('features.content.messages.unsavedChanges') }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showConfirmDialog = false">
                        {{ $t('features.content.form.cancel') }}
                    </Button>
                    <Button @click="confirmCancel">
                         {{ $t('common.actions.confirm') || 'OK' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
// @ts-ignore
import Button from '@/components/ui/button.vue';
import {
    ArrowLeft, 
    Save, 
    Loader2, 
    PanelRightClose, 
    PanelRightOpen 
} from 'lucide-vue-next';
// @ts-ignore
import Dialog from '@/components/ui/dialog.vue';
// @ts-ignore
import DialogContent from '@/components/ui/dialog-content.vue';
// @ts-ignore
import DialogHeader from '@/components/ui/dialog-header.vue';
// @ts-ignore
import DialogTitle from '@/components/ui/dialog-title.vue';
// @ts-ignore
import DialogDescription from '@/components/ui/dialog-description.vue';
// @ts-ignore
import DialogFooter from '@/components/ui/dialog-footer.vue';
import { parseResponse, ensureArray } from '@/utils/responseParser';
import AutoSaveIndicator from '@/components/AutoSaveIndicator.vue';
import ContentMain from '@/components/content/ContentMain.vue';
import ContentSidebar from '@/components/content/ContentSidebar.vue';
import { useAutoSave } from '@/composables/useAutoSave';
import { useToast } from '@/composables/useToast';
import { useFormValidation } from '@/composables/useFormValidation';
import { contentSchema } from '@/schemas';
import type { Category, Tag } from '@/types/cms';

const { t } = useI18n();
const router = useRouter();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(contentSchema);

const isSidebarOpen = ref(false);

const loading = ref(false);
const categories = ref<Category[]>([]);
const tags = ref<Tag[]>([]);
const menus = ref<any[]>([]);
const selectedTags = ref<Tag[]>([]);
const contentId = ref<number | null>(null);

const form = ref<any>({
    title: '',
    slug: '',
    excerpt: '',
    body: '',
    featured_image: null,
    status: 'draft',
    type: 'post',
    category_id: null,
    published_at: null,
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    og_image: null,
    menu_item: {
        add_to_menu: false,
        menu_id: '',
        parent_id: null,
        title: ''
    },
    blocks: [],
    comment_status: true,
    editor_type: null
});

// Auto-generation logic
watch(() => form.value.title, (newTitle) => {
    // Slug
    if (!form.value.slug || form.value.slug === slugify(localStorage.getItem('last_title') || '')) {
         form.value.slug = slugify(newTitle);
    }
    // Meta Title
    if (!form.value.meta_title || form.value.meta_title === localStorage.getItem('last_title')) {
        form.value.meta_title = newTitle;
    }
    localStorage.setItem('last_title', newTitle);
});

watch(() => form.value.excerpt, (newExcerpt) => {
    // Meta Description
    if (!form.value.meta_description || form.value.meta_description === localStorage.getItem('last_excerpt')) {
        form.value.meta_description = newExcerpt;
    }
    localStorage.setItem('last_excerpt', newExcerpt);
});

// Helper for slugify
const slugify = (text: string) => {
    if (!text) return '';
    return text.toString().toLowerCase().trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
};

// Create a computed form that includes tags for auto-save
const formWithTags = computed(() => ({
    ...form.value,
    tags: selectedTags.value.map(t => t.id),
}));

const isValid = computed(() => {
    return !!form.value.title?.trim();
});

// Auto-save setup
const autoSaveEnabled = ref(true); // Default to true

const {
    isSaving: autoSaving,
    lastSaved,
    saveStatus: autoSaveStatus,
    hasChanges,
} = useAutoSave(formWithTags as any, contentId as any, {
    interval: 30000, 
    enabled: computed(() => autoSaveEnabled.value),
    onSave: (response: any) => {
        // Update contentId if new content was created
        if (response?.data?.id && !contentId.value) {
            contentId.value = response.data.id;
        }
    },
});

const handleAutoSaveToggle = (isEnabled: boolean) => {
    autoSaveEnabled.value = isEnabled;
};

const fetchCategories = async () => {
    try {
        const response = await api.get('/admin/ja/categories');
        const { data } = parseResponse(response);
        categories.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch categories:', error);
        categories.value = [];
    }
};

const fetchTags = async () => {
    try {
        const response = await api.get('/admin/ja/tags');
        const { data } = parseResponse(response);
        tags.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch tags:', error);
    }
};

const fetchMenus = async () => {
    try {
        const response = await api.get('/admin/ja/menus');
        const { data } = parseResponse(response);
        menus.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch menus:', error);
    }
};

const handleModeSelected = (mode: string) => {
    form.value.editor_type = mode;
    // Only auto-open sidebar if classic is selected
    if (mode === 'classic') {
        isSidebarOpen.value = true;
    } else {
        isSidebarOpen.value = false;
    }
};

const handleSubmit = async (status: string | null = null) => {
    // Update status if provided (from Builder save/publish buttons)
    if (status && (status === 'draft' || status === 'published')) {
        form.value.status = status;
    }

    if (!validateWithZod(form.value)) return;

    loading.value = true;
    clearErrors();
    try {
        // Auto-fill SEO fields if empty
        if (!form.value.meta_title && form.value.title) {
            form.value.meta_title = form.value.title;
        }
        if (!form.value.meta_description && form.value.excerpt) {
            form.value.meta_description = form.value.excerpt;
        }
        if (!form.value.meta_keywords && selectedTags.value.length > 0) {
            form.value.meta_keywords = selectedTags.value.map(t => t.name).join(', ');
        }
        
        // Prepare tags: send ids for existing, names for new
        const tagIds = selectedTags.value.filter(t => t.id).map(t => t.id);
        const newTags = selectedTags.value.filter(t => !t.id).map(t => t.name);
        
        const payload = {
            ...form.value,
            tags: tagIds,
            new_tags: newTags,
        };

        // If content was auto-saved, use update endpoint
        const endpoint = contentId.value
            ? `/admin/ja/contents/${contentId.value}`
            : '/admin/ja/contents';
        const method = contentId.value ? 'put' : 'post';

        const response = await (method === 'put'
            ? api.put(endpoint, payload)
            : api.post(endpoint, payload));
        
        if (response.data?.updated_at) {
            lastSaved.value = new Date(response.data.updated_at);
        }
        
        toast.success.create('Content');
        
        // Only redirect if not saving from within the builder
        if (typeof status !== 'string') {
            router.push({ name: 'contents' });
        } else {
             // If staying, update contentId for future saves (though auto-save handles this too)
             if (response.data?.id && !contentId.value) {
                 contentId.value = response.data.id;
             }
        }
    } catch (error: any) {
        if (error.response?.status === 422) {
            setErrors(error.response.data.errors || {});
        } else {
            console.error('Failed to create content:', error);
            toast.error.fromResponse(error);
        }
    } finally {
        loading.value = false;
    }
};

const showConfirmDialog = ref(false);

const handleCancel = () => {
    if (hasChanges.value) {
        showConfirmDialog.value = true;
    } else {
        router.push({ name: 'contents' });
    }
};

const confirmCancel = () => {
    showConfirmDialog.value = false;
    router.push({ name: 'contents' });
};

onMounted(() => {
    fetchCategories();
    fetchTags();
    fetchMenus();
});
</script>
