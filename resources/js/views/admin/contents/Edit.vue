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
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ $t('features.content.form.editTitle') }}</h1>
                    
                    <!-- Lock Status -->
                    <div v-if="lockStatus" class="flex flex-wrap items-center gap-2">
                        <Badge
                            variant="outline"
                            :class="lockStatus.is_locked ? 'bg-amber-500/10 text-amber-600 border-amber-500/20' : 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20'"
                            class="gap-1.5 h-5 text-[10px]"
                        >
                            <Lock v-if="lockStatus.is_locked" class="w-3 h-3" />
                            <Unlock v-else class="w-3 h-3" />
                            {{ lockStatus.is_locked ? $t('features.content.form.locked') : $t('features.content.form.unlocked') }}
                        </Badge>
                        <span v-if="lockStatus.is_locked && lockStatus.locked_by" class="text-xs text-muted-foreground flex items-center gap-1.5">
                            <div class="w-1 h-1 rounded-full bg-muted-foreground/30"></div>
                            {{ $t('features.content.form.lockedBy', { name: lockStatus.locked_by.name }) }}
                        </span>
                        <Button
                            v-if="lockStatus.is_locked && lockStatus.can_unlock"
                            @click="handleUnlock"
                            variant="link"
                            size="sm"
                            class="h-auto p-0 text-xs text-primary hover:text-primary/80"
                        >
                            {{ $t('features.content.form.unlock') }}
                        </Button>
                        <AutoSaveIndicator
                            :status="autoSaveStatus"
                            :last-saved="lastSaved"
                        />
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
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
                <!-- Preview Button -->
                <Button variant="outline" class="hidden lg:flex" @click="handlePreview">
                    {{ $t('features.content.form.preview') }}
                </Button>
                
                 <Button variant="ghost" asChild>
                    <router-link :to="{ name: 'contents' }">
                        {{ $t('features.content.form.cancel') }}
                    </router-link>
                </Button>
                <Button
                    @click="handleSubmit"
                    :disabled="loading || (lockStatus?.locked_by && lockStatus.locked_by.id !== authStore.user?.id)"
                    class="min-w-[120px] shadow-sm"
                >
                    <template v-if="loading">
                        <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                        {{ $t('features.content.form.updating') }}
                    </template>
                    <template v-else>
                        <Save class="w-4 h-4 mr-2" />
                        {{ $t('features.content.form.update') }}
                    </template>
                </Button>
            </div>
        </div>
 
        <!-- Pending Review Notice -->
        <Alert v-if="form.status === 'pending'" class="mb-6 bg-amber-500/10 border-amber-500/20 text-amber-600">
            <Clock3 class="w-4 h-4" />
            <AlertTitle>{{ $t('features.content.status.pending') }}</AlertTitle>
            <AlertDescription>
                {{ $t('features.content.messages.pendingNotice') || 'This content is currently pending review and will be published once approved by an editor.' }}
            </AlertDescription>
        </Alert>

        <div v-if="loading && !form.title" class="flex flex-col items-center justify-center py-20 text-muted-foreground space-y-4">
            <Loader2 class="w-10 h-10 animate-spin opacity-20" />
            <p class="text-sm font-medium animate-pulse">{{ $t('features.content.form.loading') }}</p>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
             <!-- Main Content Area (Center) -->
            <div :class="[isSidebarOpen ? 'lg:col-span-8' : 'lg:col-span-12', 'space-y-8 transition-all duration-300 ease-in-out']">
                <ContentMain
                    v-model="form"
                />
            </div>

            <!-- Sidebar (Right) -->
            <div :class="[isSidebarOpen ? 'lg:col-span-4' : 'hidden', 'space-y-6 transition-all duration-300 ease-in-out']">
                <ContentSidebar
                    v-model="form"
                    v-model:selected-tags="selectedTags"
                    :categories="categories"
                    :tags="tags"
                >
                    <!-- Mobile Actions Slot -->
                     <template #actions>
                        <Button variant="outline" size="sm" class="w-full" @click="handlePreview">
                            {{ $t('features.content.form.preview') }}
                        </Button>
                     </template>
                </ContentSidebar>
            </div>
        </div>

        <!-- Preview Modal -->
        <ContentPreviewModal
            :show="showPreviewModal"
            :content="previewContent"
            :can-publish="form.status !== 'published'"
            @close="showPreviewModal = false"
            @publish="handlePublishFromPreview"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { useAuthStore } from '../../../stores/auth'; // Import Auth Store
import { parseSingleResponse } from '../../../utils/responseParser';
import Button from '@/components/ui/button.vue';
import Badge from '@/components/ui/badge.vue';
import { 
    ArrowLeft, 
    Save, 
    Loader2, 
    Eye,
    Lock,
    Calendar,
    PanelRightClose,
    PanelRightOpen
} from 'lucide-vue-next';
import AutoSaveIndicator from '../../../components/AutoSaveIndicator.vue';
import ContentPreviewModal from '../../../components/admin/ContentPreviewModal.vue';
import ContentMain from '../../../components/content/ContentMain.vue';
import ContentSidebar from '../../../components/content/ContentSidebar.vue';
import Alert from '@/components/ui/alert.vue';
import AlertTitle from '@/components/ui/alert-title.vue';
import AlertDescription from '@/components/ui/alert-description.vue';
import { useAutoSave } from '../../../composables/useAutoSave';
import { useToast } from '../../../composables/useToast';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore(); // Initialize Auth Store
const isSidebarOpen = ref(true);
const contentId = route.params.id;
const { t } = useI18n();
const toast = useToast();

const loading = ref(false);
const categories = ref([]);
const tags = ref([]);
const selectedTags = ref([]);
const lockStatus = ref(null);
const lockInterval = ref(null);

const form = ref({
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
});

// Auto-generation logic (Similar to Create but cautious about overwriting existing data)
watch(() => form.value.title, (newTitle) => {
    // Only auto-update if slug is empty (unlikely in edit) or user specifically wants sync? 
    // Usually in Edit, we are more careful. But user asked "jika tidak diisi".
    if (!form.value.slug) {
         form.value.slug = slugify(newTitle);
    }
    if (!form.value.meta_title) {
        form.value.meta_title = newTitle;
    }
});

watch(() => form.value.excerpt, (newExcerpt) => {
    if (!form.value.meta_description) {
        form.value.meta_description = newExcerpt;
    }
});

// Helper for slugify
const slugify = (text) => {
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

// Auto-save setup
const autoSaveEnabled = ref(false);
const {
    isSaving: autoSaving,
    lastSaved,
    saveStatus: autoSaveStatus,
    hasChanges,
    startAutoSave,
} = useAutoSave(formWithTags, contentId, {
    interval: 30000, // 30 seconds
    get enabled() {
        return autoSaveEnabled.value;
    },
});

const fetchContent = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/contents/${contentId}`);
        const content = parseSingleResponse(response) || {};
        
        form.value = {
            title: content.title || '',
            slug: content.slug || '',
            excerpt: content.excerpt || '',
            body: content.body || '',
            featured_image: content.featured_image || null,
            status: content.status || 'draft',
            type: content.type || 'post',
            category_id: content.category_id || null,
            published_at: content.published_at ? formatDateTimeLocal(content.published_at) : null,
            meta_title: content.meta_title || '',
            meta_description: content.meta_description || '',
            meta_keywords: content.meta_keywords || '',
            og_image: content.og_image || null,
        };

        // Set selected tags
        if (content.tags && Array.isArray(content.tags)) {
            selectedTags.value = content.tags;
        }
        
        // Enable auto-save after content is loaded
        autoSaveEnabled.value = true;
        startAutoSave();
        
        // Lock content on edit
        await lockContent();
    } catch (error) {
        console.error('Failed to fetch content:', error);
        toast.error.load(error);
        router.push({ name: 'contents' });
    } finally {
        loading.value = false;
    }
};

const lockContent = async () => {
    try {
        const response = await api.post(`/admin/cms/contents/${contentId}/lock`);
        lockStatus.value = parseSingleResponse(response) || {};
        
        // Refresh lock status every 30 seconds
        if (lockInterval.value) {
            clearInterval(lockInterval.value);
        }
        lockInterval.value = setInterval(checkLockStatus, 30000);
    } catch (error) {
        console.error('Failed to lock content:', error);
    }
};

const checkLockStatus = async () => {
    try {
        const response = await api.get(`/admin/cms/contents/${contentId}`);
        const content = parseSingleResponse(response) || {};
        if (content.lock_status) {
            lockStatus.value = content.lock_status;
        }
    } catch (error) {
        console.error('Failed to check lock status:', error);
    }
};

const handleUnlock = async () => {
    try {
        await api.post(`/admin/cms/contents/${contentId}/unlock`);
        lockStatus.value = { is_locked: false };
        if (lockInterval.value) {
            clearInterval(lockInterval.value);
        }
    } catch (error) {
        console.error('Failed to unlock content:', error);
        toast.error.unlock(error);
    }
};

const showPreviewModal = ref(false);

const handlePreview = () => {
    showPreviewModal.value = true;
};

const previewContent = computed(() => {
    const category = categories.value.find(c => c.id === form.value.category_id);
    return {
        title: form.value.title,
        body: form.value.body,
        excerpt: form.value.excerpt,
        featured_image: form.value.featured_image,
        author: { name: 'Current User' }, // You can get from auth store
        category: category ? { name: category.name } : null,
        published_at: form.value.published_at || new Date().toISOString(),
    };
});

const handlePublishFromPreview = async () => {
    form.value.status = 'published';
    await handleSubmit();
};

const formatDateTimeLocal = (dateString) => {
    if (!dateString) return null;
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const fetchCategories = async () => {
    try {
        const response = await api.get('/admin/cms/categories');
        const data = response.data?.data || response.data || [];
        categories.value = Array.isArray(data) ? data : [];
    } catch (error) {
        console.error('Failed to fetch categories:', error);
        categories.value = [];
    }
};

const fetchTags = async () => {
    try {
        const response = await api.get('/admin/cms/tags');
        const data = response.data?.data || response.data || [];
        tags.value = Array.isArray(data) ? data : [];
    } catch (error) {
        console.error('Failed to fetch tags:', error);
    }
};

const handleSubmit = async () => {
    loading.value = true;
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

        await api.put(`/admin/cms/contents/${contentId}`, payload);
        
        // Release lock
        if (lockInterval.value) {
            clearInterval(lockInterval.value);
        }
        await handleUnlock();
        
        toast.success.update();
        router.push({ name: 'contents' });
    } catch (error) {
        console.error('Failed to update content:', error);
        toast.error.update(error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchContent();
    fetchCategories();
    fetchTags();
});

onUnmounted(() => {
    // Clean up lock interval
    if (lockInterval.value) {
        clearInterval(lockInterval.value);
    }
    // Unlock content when leaving page
    if (lockStatus.value?.is_locked) {
        handleUnlock().catch(() => {});
    }
});
</script>
