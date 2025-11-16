<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Themes Management</h1>
                <p class="text-sm text-gray-500 mt-1">Manage and customize your themes</p>
            </div>
            <div class="flex items-center gap-3">
                <select
                    v-model="selectedType"
                    @change="fetchThemes"
                    class="px-3 py-2 border border-gray-300 rounded-md text-sm"
                >
                    <option value="">All Types</option>
                    <option value="frontend">Frontend</option>
                    <option value="admin">Admin</option>
                    <option value="email">Email</option>
                </select>
                <button
                    @click="scanThemes"
                    :disabled="scanning"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-md disabled:opacity-50"
                >
                    {{ scanning ? 'Scanning...' : 'Scan Themes' }}
                </button>
            </div>
        </div>

        <div v-if="themes.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No themes found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new theme.</p>
            <div class="mt-6">
                <button
                    @click="scanThemes"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    Scan for Themes
                </button>
            </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="theme in themes"
                :key="theme.id"
                class="bg-white shadow rounded-lg overflow-hidden transition-shadow hover:shadow-lg"
                :class="{ 'ring-2 ring-indigo-500': theme.is_active }"
            >
                <!-- Preview Image -->
                <div class="h-48 bg-gray-200 relative group">
                    <img
                        v-if="theme.preview_image"
                        :src="theme.preview_image"
                        :alt="theme.name"
                        class="w-full h-full object-cover"
                    >
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
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
                            Active
                        </span>
                        <span
                            v-else-if="theme.status && theme.status !== 'active'"
                            class="px-2 py-1 text-xs font-semibold rounded-full shadow-sm"
                            :class="{
                                'bg-red-100 text-red-800': theme.status === 'broken',
                                'bg-gray-100 text-gray-800': theme.status === 'inactive',
                                'bg-yellow-100 text-yellow-800': theme.status === 'pending',
                            }"
                        >
                            {{ theme.status }}
                        </span>
                    </div>

                    <!-- Hover Actions -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                        <button
                            @click="openPreview(theme)"
                            class="px-3 py-2 bg-white text-gray-900 rounded-md text-sm font-medium hover:bg-gray-100"
                        >
                            Preview
                        </button>
                        <button
                            v-if="theme.is_active"
                            @click="openCustomizer(theme)"
                            class="px-3 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700"
                        >
                            Customize
                        </button>
                    </div>
                </div>

                <!-- Theme Info -->
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ theme.name }}</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-sm text-gray-500">v{{ theme.version || '1.0.0' }}</span>
                                <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded">
                                    {{ theme.type || 'frontend' }}
                                </span>
                                <span v-if="theme.parent_theme" class="text-xs px-2 py-0.5 bg-blue-100 text-blue-600 rounded">
                                    Child
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <p v-if="theme.description" class="text-sm text-gray-600 mt-2 line-clamp-2">
                        {{ theme.description }}
                    </p>

                    <div v-if="theme.author" class="mt-2 text-xs text-gray-500">
                        by {{ theme.author }}
                    </div>

                    <!-- Actions -->
                    <div class="mt-4 flex items-center gap-2 flex-wrap">
                        <!-- Primary Action Button -->
                        <button
                            v-if="theme.is_active"
                            @click="openCustomizer(theme)"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                            Customize
                        </button>
                        <button
                            v-else
                            @click="activateTheme(theme)"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Activate
                        </button>
                        
                        <!-- Secondary Action Buttons -->
                        <button
                            @click="openPreview(theme)"
                            class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50"
                            title="Preview theme"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                        <button
                            @click="openSettings(theme)"
                            class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50"
                            title="Settings"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                        <button
                            @click="validateTheme(theme)"
                            class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50"
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
            <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl h-[90vh] flex flex-col">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold">Theme Preview: {{ selectedTheme?.name }}</h3>
                    <button
                        @click="showPreviewModal = false"
                        class="text-gray-400 hover:text-gray-600"
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
        alert(`Scan completed! Found ${count} theme(s).`);
    } catch (error) {
        console.error('Failed to scan themes:', error);
        alert('Failed to scan themes');
    } finally {
        scanning.value = false;
    }
};

const activateTheme = async (theme) => {
    if (!confirm(`Are you sure you want to activate theme "${theme.name}"?`)) {
        return;
    }

    try {
        await api.post(`/admin/cms/themes/${theme.id}/activate`);
        await fetchThemes();
        alert('Theme activated successfully');
    } catch (error) {
        console.error('Failed to activate theme:', error);
        alert(error.response?.data?.message || 'Failed to activate theme');
    }
};

const validateTheme = async (theme) => {
    try {
        const response = await api.post(`/admin/cms/themes/${theme.id}/validate`);
        const data = response.data?.data || response.data;
        
        if (data.valid) {
            alert('Theme is valid!');
        } else {
            alert('Theme validation failed:\n' + data.errors.join('\n'));
        }
        
        await fetchThemes();
    } catch (error) {
        console.error('Failed to validate theme:', error);
        alert('Failed to validate theme');
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
