<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.themes.title') }}</h1>
                <p class="text-sm text-muted-foreground mt-1">{{ $t('features.themes.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <Select
                    v-model="selectedType"
                    @update:model-value="fetchThemes"
                >
                    <SelectTrigger class="w-[180px]">
                        <SelectValue :placeholder="$t('features.themes.types.all')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ $t('features.themes.types.all') }}</SelectItem>
                        <SelectItem value="frontend">{{ $t('features.themes.types.frontend') }}</SelectItem>
                        <SelectItem value="admin">{{ $t('features.themes.types.admin') }}</SelectItem>
                        <SelectItem value="email">{{ $t('features.themes.types.email') }}</SelectItem>
                    </SelectContent>
                </Select>
                <Button
                    @click="scanThemes"
                    :disabled="scanning"
                    variant="secondary"
                >
                    {{ scanning ? $t('features.themes.scanning') : $t('features.themes.scan') }}
                </Button>
            </div>
        </div>

        <div v-if="themes.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-foreground">{{ $t('features.themes.list.empty') }}</h3>
            <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.themes.list.emptySubtitle') }}</p>
            <div class="mt-6">
                <Button
                    @click="scanThemes"
                >
                    {{ $t('features.themes.scan') }}
                </Button>
            </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="theme in themes"
                :key="theme.id"
                class="bg-card border border-border rounded-lg overflow-hidden transition-shadow "
                :class="{ 'ring-2 ring-indigo-500': theme.is_active }"
            >
                <!-- Preview Image -->
                <div class="h-48 bg-muted relative group">
                    <img
                        v-if="theme.preview_image"
                        :src="theme.preview_image"
                        :alt="theme.name"
                        class="w-full h-full object-cover"
                    >
                    <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    
                    <!-- Status Badge -->
                    <div class="absolute top-2 right-2">
                        <Badge
                            v-if="theme.is_active"
                            variant="success"
                            class="shadow-sm"
                        >
                            {{ $t('features.themes.status.active') }}
                        </Badge>
                        <Badge
                            v-else-if="theme.status && theme.status !== 'active'"
                            class="shadow-sm"
                            :variant="theme.status === 'broken' ? 'destructive' : (theme.status === 'pending' ? 'warning' : 'secondary')"
                        >
                            {{ theme.status }}
                        </Badge>
                    </div>

                    <!-- Hover Actions -->
                    <div class="absolute inset-0 bg-background/50 backdrop-blur-[1px] opacity-0 group-hover:opacity-100 transition-colors flex items-center justify-center gap-2">
                        <Button
                            @click="openPreview(theme)"
                            variant="secondary"
                            size="sm"
                        >
                            {{ $t('features.themes.actions.preview') }}
                        </Button>
                        <Button
                            v-if="theme.is_active"
                            @click="openCustomizer(theme)"
                            size="sm"
                        >
                            {{ $t('features.themes.actions.customize') }}
                        </Button>
                    </div>
                </div>

                <!-- Theme Info -->
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-foreground">{{ theme.name }}</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-sm text-muted-foreground">{{ $t('features.themes.list.version', { version: theme.version || '1.0.0' }) }}</span>
                                <span class="text-xs px-2 py-0.5 bg-secondary text-muted-foreground rounded">
                                    {{ theme.type || 'frontend' }}
                                </span>
                                <span v-if="theme.parent_theme" class="text-xs px-2 py-0.5 bg-blue-100 text-blue-600 rounded">
                                    {{ $t('features.themes.list.child') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <p v-if="theme.description" class="text-sm text-muted-foreground mt-2 line-clamp-2">
                        {{ theme.description }}
                    </p>

                    <div v-if="theme.author" class="mt-2 text-xs text-muted-foreground">
                        {{ $t('features.themes.list.by', { author: theme.author }) }}
                    </div>

                    <!-- Actions -->
                    <div class="mt-4 flex items-center gap-2 flex-wrap">
                        <!-- Primary Action Button -->
                        <Button
                            v-if="theme.is_active"
                            @click="openCustomizer(theme)"
                            class="flex-1"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                            {{ $t('features.themes.actions.customize') }}
                        </Button>
                        <Button
                            v-else
                            @click="activateTheme(theme)"
                            class="flex-1"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $t('features.themes.actions.activate') }}
                        </Button>
                        
                        <!-- Secondary Action Buttons -->
                        <Button
                            @click="openPreview(theme)"
                            variant="outline"
                            size="icon"
                            :title="$t('features.themes.actions.preview')"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </Button>
                        <Button
                            @click="validateTheme(theme)"
                            variant="outline"
                            size="icon"
                            :title="$t('features.themes.actions.validate')"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div
            v-if="showPreviewModal"
            class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4"
            @click.self="showPreviewModal = false"
        >
            <div class="bg-card rounded-lg w-full max-w-6xl h-[90vh] flex flex-col">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold">{{ $t('features.themes.modals.previewTitle', { name: selectedTheme?.name }) }}</h3>
                    <button
                        @click="showPreviewModal = false"
                        class="text-muted-foreground hover:text-muted-foreground"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-hidden">
                    <ThemePreview
                        v-if="selectedTheme"
                        :theme="selectedTheme"
                        preview-url="/"
                        @close="showPreviewModal = false"
                    />
                </div>
            </div>
        </div>

        <!-- Customizer Modal -->
        <div
            v-if="showCustomizerModal"
            class="fixed inset-0 z-50"
        >
            <ThemeCustomizer
                v-if="selectedTheme"
                :theme="selectedTheme"
                preview-url="/"
                @close="showCustomizerModal = false"
                @saved="handleCustomizerSaved"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../../../services/api';
import toast from '../../../services/toast';
import { useConfirm } from '../../../composables/useConfirm';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import ThemePreview from '../../../components/themes/ThemePreview.vue';
import ThemeCustomizer from '../../../components/themes/ThemeCustomizer.vue';

import { useI18n } from 'vue-i18n';
import Button from '../../../components/ui/button.vue';
import Badge from '../../../components/ui/badge.vue';
import Select from '../../../components/ui/select.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';

const { t } = useI18n();
const { confirm } = useConfirm();

interface Theme {
    id: number;
    name: string;
    slug: string;
    description?: string;
    author?: string;
    version?: string;
    type?: string;
    parent_theme?: string;
    is_active?: boolean;
    status?: string;
    preview_image?: string;
}

const themes = ref<Theme[]>([]);
const selectedType = ref('');
const scanning = ref(false);
const showPreviewModal = ref(false);
const showCustomizerModal = ref(false);
const selectedTheme = ref<Theme | null>(null);

const fetchThemes = async () => {
    try {
        const params = selectedType.value ? { type: selectedType.value } : {};
        const response = await api.get('/admin/ja/themes', { params });
        const { data } = parseResponse(response);
        themes.value = ensureArray(data);
    } catch (error: any) {
        console.error('Failed to fetch themes:', error);
        themes.value = [];
    }
};

const scanThemes = async () => {
    scanning.value = true;
    try {
        const response = await api.post('/admin/ja/themes/scan');
        await fetchThemes();
        const count = response.data?.data?.count || 0;
        toast.success(t('features.themes.messages.scanSuccess', { count }));
    } catch (error: any) {
        console.error('Failed to scan themes:', error);
        toast.error('Error', t('features.themes.messages.scanFailed'));
    } finally {
        scanning.value = false;
    }
};

const activateTheme = async (theme: Theme) => {
    const confirmed = await confirm({
        title: t('features.themes.actions.activate'),
        message: t('features.themes.messages.activateConfirm', { name: theme.name }),
        variant: 'info',
        confirmText: t('features.themes.actions.activate'),
    });

    if (!confirmed) return;

    try {
        await api.post(`/admin/ja/themes/${theme.slug}/activate`);
        await fetchThemes();
        toast.success(t('features.themes.messages.activateSuccess'));
    } catch (error: any) {
        console.error('Failed to activate theme:', error);
        toast.error(error instanceof Error ? error.message : 'Failed to activate theme');
    }
};

const validateTheme = async (theme: Theme) => {
    try {
        const response = await api.post(`/admin/ja/themes/${theme.slug}/validate`);
        const data = response.data?.data || response.data;
        
        if (data.valid) {
            toast.success(t('features.themes.messages.validateSuccess'));
        } else {
            // Can be replaced with a modal or detailed toast if needed, 
            // but multiline toast might be tricky. Using error toast with detail.
            console.error('Validation errors:', data.errors);
            toast.error(t('features.themes.messages.validateFailed'), data.errors.join(', '));
        }
        
        await fetchThemes();
    } catch (error: any) {
        console.error('Failed to validate theme:', error);
        toast.error('Error', t('features.themes.messages.validateError'));
    }
};

const openPreview = (theme: Theme) => {
    selectedTheme.value = theme;
    showPreviewModal.value = true;
};

const openCustomizer = (theme: Theme) => {
    selectedTheme.value = theme;
    showCustomizerModal.value = true;
};

const handleCustomizerSaved = () => {
    fetchThemes();
};

onMounted(() => {
    fetchThemes();
});
</script>
