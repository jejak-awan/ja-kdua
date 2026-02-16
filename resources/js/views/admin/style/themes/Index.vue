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
            <Palette class="mx-auto h-12 w-12 text-muted-foreground" />
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
                        <Image class="w-16 h-16" />
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
                            <Palette class="w-4 h-4 mr-2" />
                            {{ $t('features.themes.actions.customize') }}
                        </Button>
                        <Button
                            v-else
                            @click="activateTheme(theme)"
                            class="flex-1"
                        >
                            <Check class="w-4 h-4 mr-2" />
                            {{ $t('features.themes.actions.activate') }}
                        </Button>
                        
                        <!-- Secondary Action Buttons -->
                        <Button
                            @click="openPreview(theme)"
                            variant="outline"
                            size="icon"
                            :title="$t('features.themes.actions.preview')"
                        >
                            <Eye class="w-4 h-4" />
                        </Button>
                        <Button
                            @click="validateTheme(theme)"
                            variant="outline"
                            size="icon"
                            :title="$t('features.themes.actions.validate')"
                        >
                            <CheckCircle class="w-4 h-4" />
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
                        <X class="w-6 h-6" />
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
import { logger } from '@/utils/logger';
import { ref, onMounted } from 'vue';
import api from '@/services/api';
import toast from '@/services/toast';
import { useConfirm } from '@/composables/useConfirm';
import { parseResponse, ensureArray } from '@/utils/responseParser';
import Palette from 'lucide-vue-next/dist/esm/icons/palette.js';
import Image from 'lucide-vue-next/dist/esm/icons/image.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import CheckCircle from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import ThemePreview from '@/components/themes/ThemePreview.vue';
import ThemeCustomizer from '@/components/themes/ThemeCustomizer.vue';

import { useI18n } from 'vue-i18n';
import { Badge, Button, Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui';

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
        const response = await api.get('/admin/janet/themes', { params });
        const { data } = parseResponse(response);
        themes.value = ensureArray(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch themes:', error);
        themes.value = [];
    }
};

const scanThemes = async () => {
    scanning.value = true;
    try {
        const response = await api.post('/admin/janet/themes/scan');
        await fetchThemes();
        const count = response.data?.data?.count || 0;
        toast.success(t('features.themes.messages.scanSuccess', { count }));
    } catch (error: unknown) {
        logger.error('Failed to scan themes:', error);
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
        await api.post(`/admin/janet/themes/${theme.slug}/activate`);
        await fetchThemes();
        toast.success(t('features.themes.messages.activateSuccess'));
    } catch (error: unknown) {
        logger.error('Failed to activate theme:', error);
        toast.error(error instanceof Error ? error.message : 'Failed to activate theme');
    }
};

const validateTheme = async (theme: Theme) => {
    try {
        const response = await api.post(`/admin/janet/themes/${theme.slug}/validate`);
        const data = response.data?.data || response.data;
        
        if (data.valid) {
            toast.success(t('features.themes.messages.validateSuccess'));
        } else {
            // Can be replaced with a modal or detailed toast if needed, 
            // but multiline toast might be tricky. Using error toast with detail.
            logger.error('Validation errors:', data.errors);
            toast.error(t('features.themes.messages.validateFailed'), data.errors.join(', '));
        }
        
        await fetchThemes();
    } catch (error: unknown) {
        logger.error('Failed to validate theme:', error);
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
