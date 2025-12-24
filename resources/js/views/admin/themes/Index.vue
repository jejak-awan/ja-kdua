<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.themes.title') }}</h1>
                <p class="text-sm text-muted-foreground mt-1">{{ $t('features.themes.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <select
                    v-model="selectedType"
                    @change="fetchThemes"
                    class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm"
                >
                    <option value="">{{ $t('features.themes.types.all') }}</option>
                    <option value="frontend">{{ $t('features.themes.types.frontend') }}</option>
                    <option value="admin">{{ $t('features.themes.types.admin') }}</option>
                    <option value="email">{{ $t('features.themes.types.email') }}</option>
                </select>
                <button
                    @click="scanThemes"
                    :disabled="scanning"
                    class="px-4 py-2 bg-secondary hover:bg-accent text-foreground text-sm font-medium rounded-md disabled:opacity-50"
                >
                    {{ scanning ? $t('features.themes.scanning') : $t('features.themes.scan') }}
                </button>
            </div>
        </div>

        <div v-if="themes.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-foreground">{{ $t('features.themes.list.empty') }}</h3>
            <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.themes.list.emptySubtitle') }}</p>
            <div class="mt-6">
                <button
                    @click="scanThemes"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/80"
                >
                    {{ $t('features.themes.scan') }}
                </button>
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
                        <span
                            v-if="theme.is_active"
                            class="px-2 py-1 text-xs font-semibold rounded-full bg-green-500 text-white shadow-sm"
                        >
                            {{ $t('features.themes.status.active') }}
                        </span>
                        <span
                            v-else-if="theme.status && theme.status !== 'active'"
                            class="px-2 py-1 text-xs font-semibold rounded-full shadow-sm"
                            :class="{
                                'bg-red-500/20 text-red-400': theme.status === 'broken',
                                'bg-secondary text-gray-800': theme.status === 'inactive',
                                'bg-yellow-500/20 text-yellow-400': theme.status === 'pending',
                            }"
                        >
                            {{ theme.status }}
                        </span>
                    </div>

                    <!-- Hover Actions -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                        <button
                            @click="openPreview(theme)"
                            class="px-3 py-2 bg-card text-foreground rounded-md text-sm font-medium hover:bg-accent"
                        >
                            {{ $t('features.themes.actions.preview') }}
                        </button>
                        <button
                            v-if="theme.is_active"
                            @click="openCustomizer(theme)"
                            class="px-3 py-2 bg-primary text-primary-foreground rounded-md text-sm font-medium hover:bg-primary/80"
                        >
                            {{ $t('features.themes.actions.customize') }}
                        </button>
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
                        <button
                            v-if="theme.is_active"
                            @click="openCustomizer(theme)"
                            class="flex-1 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-md hover:bg-primary/80 flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                            {{ $t('features.themes.actions.customize') }}
                        </button>
                        <button
                            v-else
                            @click="activateTheme(theme)"
                            class="flex-1 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-md hover:bg-primary/80 flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $t('features.themes.actions.activate') }}
                        </button>
                        
                        <!-- Secondary Action Buttons -->
                        <button
                            @click="openPreview(theme)"
                            class="px-4 py-2 border border-input text-foreground text-sm font-medium rounded-md hover:bg-muted"
                            title="Preview theme"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                        <button
                            @click="openSettings(theme)"
                            class="px-4 py-2 border border-input text-foreground text-sm font-medium rounded-md hover:bg-muted"
                            title="Settings"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                        <button
                            @click="validateTheme(theme)"
                            class="px-4 py-2 border border-input text-foreground text-sm font-medium rounded-md hover:bg-muted"
                            title="Validate theme"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
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

        <!-- Settings Modal -->
        <ThemeSettingsModal
            v-if="showSettingsModal"
            @close="showSettingsModal = false"
            @saved="handleSettingsSaved"
            :theme="selectedTheme"
        />

        <!-- Custom CSS Modal -->
        <CustomCSSModal
            v-if="showCustomCSSModal"
            @close="showCustomCSSModal = false"
            @saved="handleCSSSaved"
            :theme="selectedTheme"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../../services/api';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import ThemeSettingsModal from '../../../components/themes/ThemeSettingsModal.vue';
import CustomCSSModal from '../../../components/themes/CustomCSSModal.vue';
import ThemePreview from '../../../components/themes/ThemePreview.vue';
import ThemeCustomizer from '../../../components/themes/ThemeCustomizer.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const themes = ref([]);
const selectedType = ref('');
const scanning = ref(false);
const showSettingsModal = ref(false);
const showCustomCSSModal = ref(false);
const showPreviewModal = ref(false);
const showCustomizerModal = ref(false);
const selectedTheme = ref(null);

const fetchThemes = async () => {
    try {
        const params = selectedType.value ? { type: selectedType.value } : {};
        const response = await api.get('/admin/cms/themes', { params });
        const { data } = parseResponse(response);
        themes.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch themes:', error);
        themes.value = [];
    }
};

const scanThemes = async () => {
    scanning.value = true;
    try {
        const response = await api.post('/admin/cms/themes/scan');
        await fetchThemes();
        const count = response.data?.data?.count || 0;
        alert(t('features.themes.messages.scanSuccess', { count }));
    } catch (error) {
        console.error('Failed to scan themes:', error);
        alert(t('features.themes.messages.scanFailed'));
    } finally {
        scanning.value = false;
    }
};

const activateTheme = async (theme) => {
    if (!confirm(t('features.themes.messages.activateConfirm', { name: theme.name }))) {
        return;
    }

    try {
        await api.post(`/admin/cms/themes/${theme.id}/activate`);
        await fetchThemes();
        alert(t('features.themes.messages.activateSuccess'));
    } catch (error) {
        console.error('Failed to activate theme:', error);
        alert(error.response?.data?.message || t('features.themes.messages.activateFailed'));
    }
};

const validateTheme = async (theme) => {
    try {
        const response = await api.post(`/admin/cms/themes/${theme.id}/validate`);
        const data = response.data?.data || response.data;
        
        if (data.valid) {
            alert(t('features.themes.messages.validateSuccess'));
        } else {
            alert(t('features.themes.messages.validateFailed') + '\n' + data.errors.join('\n'));
        }
        
        await fetchThemes();
    } catch (error) {
        console.error('Failed to validate theme:', error);
        alert(t('features.themes.messages.validateError'));
    }
};

const openPreview = (theme) => {
    selectedTheme.value = theme;
    showPreviewModal.value = true;
};

const openCustomizer = (theme) => {
    selectedTheme.value = theme;
    showCustomizerModal.value = true;
};

const openSettings = (theme) => {
    selectedTheme.value = theme;
    showSettingsModal.value = true;
};

const openCustomCSS = (theme) => {
    selectedTheme.value = theme;
    showCustomCSSModal.value = true;
};

const handleSettingsSaved = () => {
    fetchThemes();
    showSettingsModal.value = false;
};

const handleCSSSaved = () => {
    fetchThemes();
    showCustomCSSModal.value = false;
};

const handleCustomizerSaved = () => {
    fetchThemes();
    showCustomizerModal.value = false;
};

onMounted(() => {
    fetchThemes();
});
</script>
