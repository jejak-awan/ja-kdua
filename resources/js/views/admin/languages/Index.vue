<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.languages.title') }}</h1>
            <div class="flex items-center space-x-3">
                <button @click="showImportModal = true" class="inline-flex items-center px-4 py-2 border border-input text-sm font-medium rounded-md text-foreground bg-card hover:bg-muted">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    {{ $t('features.languages.importPack') }}
                </button>
                <button @click="showCreateModal = true" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/80">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ $t('features.languages.add') }}
                </button>
            </div>
        </div>

        <!-- UI Languages Info Card -->
        <div class="mb-6 bg-card border border-border rounded-lg p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-foreground mb-2">{{ $t('features.languages.uiLanguages.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-4">{{ $t('features.languages.uiLanguages.description') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span 
                            v-for="locale in availableUiLocales" 
                            :key="locale.code"
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                            :class="currentLocale === locale.code ? 'bg-indigo-500/20 text-indigo-500 border border-indigo-500/30' : 'bg-muted text-muted-foreground'"
                        >
                            <span class="mr-1.5">{{ locale.flag }}</span>
                            {{ locale.name }}
                            <span v-if="currentLocale === locale.code" class="ml-1.5 text-xs">({{ $t('features.languages.uiLanguages.active') }})</span>
                        </span>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-muted-foreground mb-1">{{ $t('features.languages.uiLanguages.browserDetected') }}</p>
                    <span class="text-sm font-medium text-foreground">{{ browserLocale || '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Languages Table -->
        <div class="bg-card border border-border rounded-lg">
            <div class="px-6 py-4 border-b border-border">
                <h2 class="text-lg font-semibold text-foreground">{{ $t('features.languages.list.title') }}</h2>
                <p class="text-sm text-muted-foreground">{{ $t('features.languages.list.description') }}</p>
            </div>
            <div v-if="loading" class="p-6 text-center"><p class="text-muted-foreground">{{ $t('common.loading.default') }}</p></div>
            <div v-else-if="languages.length === 0" class="p-6 text-center"><p class="text-muted-foreground">{{ $t('features.languages.list.empty') }}</p></div>
            <table v-else class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.languages.list.headers.name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.languages.list.headers.code') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.languages.list.headers.default') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">{{ $t('features.languages.uiLanguages.translationKeys') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground">{{ $t('features.languages.list.headers.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="lang in languages" :key="lang.id" class="hover:bg-muted">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="text-lg mr-2">{{ getLanguageFlag(lang.code) }}</span>
                                <span class="text-sm font-medium text-foreground">{{ lang.name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ lang.code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span v-if="lang.is_default" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-500/20 text-green-500 border border-green-500/30">
                                {{ $t('features.languages.list.default') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span v-if="lang.has_ui_translations" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-500/20 text-blue-500 border border-blue-500/30">
                                {{ lang.translation_keys }} {{ $t('features.languages.uiLanguages.keys') }}
                            </span>
                            <span v-else class="text-xs text-muted-foreground">-</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-3">
                                <button 
                                    v-if="lang.has_ui_translations" 
                                    @click="exportPack(lang)" 
                                    class="text-indigo-600 hover:text-indigo-900"
                                    :disabled="exporting === lang.id"
                                >
                                    <span v-if="exporting === lang.id">...</span>
                                    <span v-else>{{ $t('features.languages.actions.export') }}</span>
                                </button>
                                <button @click="setDefault(lang)" v-if="!lang.is_default" class="text-green-600 hover:text-green-900">{{ $t('features.languages.actions.setDefault') }}</button>
                                <button @click="deleteLanguage(lang)" v-if="!lang.is_default" class="text-red-600 hover:text-red-900">{{ $t('features.languages.actions.delete') }}</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Import Modal -->
        <div v-if="showImportModal" class="fixed inset-0 bg-background/80 backdrop-blur-sm flex items-center justify-center z-50" @click.self="showImportModal = false">
            <div class="bg-card rounded-lg w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.languages.import.title') }}</h3>
                <p class="text-sm text-muted-foreground mb-4">{{ $t('features.languages.import.description') }}</p>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-foreground mb-2">{{ $t('features.languages.import.file') }}</label>
                    <input 
                        type="file" 
                        @change="handleFileSelect"
                        accept=".zip"
                        class="block w-full text-sm text-foreground file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700"
                    />
                </div>

                <div class="flex justify-end space-x-3">
                    <button @click="showImportModal = false" class="px-4 py-2 text-sm font-medium text-muted-foreground hover:text-foreground">
                        {{ $t('common.actions.cancel') }}
                    </button>
                    <button 
                        @click="importPack" 
                        :disabled="!selectedFile || importing"
                        class="px-4 py-2 text-sm font-medium text-primary-foreground bg-primary rounded-md hover:bg-primary/80 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="importing">{{ $t('common.loading.default') }}</span>
                        <span v-else>{{ $t('features.languages.import.button') }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-background/80 backdrop-blur-sm flex items-center justify-center z-50" @click.self="showCreateModal = false">
            <div class="bg-card rounded-lg w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.languages.create.title') }}</h3>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-foreground mb-2">{{ $t('features.languages.create.code') }}</label>
                    <input 
                        v-model="newLanguage.code"
                        type="text" 
                        placeholder="fr, de, es..."
                        class="w-full px-3 py-2 border border-input bg-background text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-foreground mb-2">{{ $t('features.languages.create.name') }}</label>
                    <input 
                        v-model="newLanguage.name"
                        type="text" 
                        placeholder="French, German..."
                        class="w-full px-3 py-2 border border-input bg-background text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <div class="mb-4 flex items-center">
                    <input 
                        v-model="newLanguage.createFromTemplate"
                        type="checkbox" 
                        id="createFromTemplate"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                    />
                    <label for="createFromTemplate" class="ml-2 block text-sm text-foreground">
                        {{ $t('features.languages.create.fromTemplate') }}
                    </label>
                </div>

                <div class="flex justify-end space-x-3">
                    <button @click="showCreateModal = false" class="px-4 py-2 text-sm font-medium text-muted-foreground hover:text-foreground">
                        {{ $t('common.actions.cancel') }}
                    </button>
                    <button 
                        @click="createLanguage" 
                        :disabled="!newLanguage.code || !newLanguage.name || creating"
                        class="px-4 py-2 text-sm font-medium text-primary-foreground bg-primary rounded-md hover:bg-primary/80 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="creating">{{ $t('common.loading.default') }}</span>
                        <span v-else>{{ $t('common.actions.create') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import { getLocale, getAvailableLocales, getBrowserLocale } from '../../../i18n';

const { t } = useI18n();

const languages = ref([]);
const loading = ref(false);
const showCreateModal = ref(false);
const showImportModal = ref(false);
const creating = ref(false);
const importing = ref(false);
const exporting = ref(null);
const selectedFile = ref(null);

const newLanguage = ref({
    code: '',
    name: '',
    createFromTemplate: true,
});

// UI Locale info
const currentLocale = ref(getLocale());
const availableUiLocales = getAvailableLocales();
const browserLocale = getBrowserLocale();

const fetchLanguages = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/languages');
        const { data } = parseResponse(response);
        languages.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch languages:', error);
        languages.value = [];
    } finally {
        loading.value = false;
    }
};

const setDefault = async (lang) => {
    try {
        await api.post(`/admin/cms/languages/${lang.id}/set-default`);
        await fetchLanguages();
    } catch (error) {
        console.error('Failed to set default language:', error);
        alert(t('features.languages.messages.setDefaultFailed'));
    }
};

const deleteLanguage = async (lang) => {
    if (!confirm(t('features.languages.actions.confirmDelete', { name: lang.name }))) return;
    try {
        await api.delete(`/admin/cms/languages/${lang.id}`);
        await fetchLanguages();
    } catch (error) {
        console.error('Failed to delete language:', error);
        alert(t('features.languages.messages.deleteFailed'));
    }
};

const createLanguage = async () => {
    creating.value = true;
    try {
        await api.post('/admin/cms/languages', {
            code: newLanguage.value.code,
            name: newLanguage.value.name,
            create_from_template: newLanguage.value.createFromTemplate,
            template_locale: 'en',
        });
        showCreateModal.value = false;
        newLanguage.value = { code: '', name: '', createFromTemplate: true };
        await fetchLanguages();
        alert(t('features.languages.messages.createSuccess'));
    } catch (error) {
        console.error('Failed to create language:', error);
        alert(error.response?.data?.message || t('features.languages.messages.createFailed'));
    } finally {
        creating.value = false;
    }
};

const exportPack = async (lang) => {
    exporting.value = lang.id;
    try {
        const response = await api.get(`/admin/cms/languages/${lang.id}/export-pack`, {
            responseType: 'blob',
        });
        
        // Create download link
        const blob = new Blob([response.data], { type: 'application/zip' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `language-pack-${lang.code}-${new Date().toISOString().slice(0, 10)}.zip`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Failed to export language pack:', error);
        alert(t('features.languages.messages.exportFailed'));
    } finally {
        exporting.value = null;
    }
};

const handleFileSelect = (event) => {
    selectedFile.value = event.target.files[0];
};

const importPack = async () => {
    if (!selectedFile.value) return;
    
    importing.value = true;
    try {
        const formData = new FormData();
        formData.append('file', selectedFile.value);

        await api.post('/admin/cms/languages/import-pack', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        showImportModal.value = false;
        selectedFile.value = null;
        await fetchLanguages();
        alert(t('features.languages.messages.importSuccess'));
    } catch (error) {
        console.error('Failed to import language pack:', error);
        alert(error.response?.data?.message || t('features.languages.messages.importFailed'));
    } finally {
        importing.value = false;
    }
};

const getLanguageFlag = (code) => {
    const flagMap = {
        'en': '🇺🇸',
        'id': '🇮🇩',
        'ar': '🇸🇦',
        'he': '🇮🇱',
        'fr': '🇫🇷',
        'de': '🇩🇪',
        'es': '🇪🇸',
        'pt': '🇵🇹',
        'zh': '🇨🇳',
        'ja': '🇯🇵',
        'ko': '🇰🇷',
        'ru': '🇷🇺',
    };
    return flagMap[code.toLowerCase()] || '🌐';
};

onMounted(() => {
    fetchLanguages();
});
</script>
