<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <Button
                    variant="ghost"
                    size="icon"
                    as-child
                    class="h-9 w-9"
                >
                    <router-link to="/admin/logs-dashboard">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </router-link>
                </Button>
                <h1 class="text-2xl font-bold text-foreground">{{ t('features.system.logs.title') }}</h1>
            </div>
            <div class="flex items-center space-x-2">
                <Button
                    @click="clearLogs"
                    :disabled="clearing"
                    variant="destructive"
                    variant-type="outline"
                >
                    {{ clearing ? t('features.system.logs.clearing') : t('features.system.logs.clear') }}
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Log Files List -->
            <div class="lg:col-span-1">
                <div class="bg-card border border-border rounded-lg">
                    <div class="px-6 py-4 border-b border-border">
                        <h2 class="text-lg font-semibold text-foreground">{{ t('features.system.logs.files') }}</h2>
                    </div>
                    <div class="divide-y divide-border">
                        <Button
                            v-for="logFile in logFiles"
                            :key="logFile.name"
                            variant="ghost"
                            class="w-full justify-start h-auto px-6 py-4 rounded-none border-b border-border last:border-0 hover:bg-muted transition-colors"
                            :class="[
                                selectedLogFile?.name === logFile.name ? 'bg-muted border-l-4 border-l-primary' : ''
                            ]"
                            @click="selectLogFile(logFile)"
                        >
                            <div class="flex items-center justify-between w-full">
                                <div class="text-left">
                                    <p class="text-sm font-medium text-foreground">{{ logFile.name }}</p>
                                    <p class="text-xs text-muted-foreground mt-1">{{ formatFileSize(logFile.size) }}</p>
                                </div>
                                <Button
                                    @click.stop="downloadLog(logFile)"
                                    variant="ghost"
                                    size="icon"
                                    class="h-8 w-8 text-primary hover:text-primary/80"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </Button>
                            </div>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Log Viewer -->
            <div class="lg:col-span-2">
                <div class="bg-card border border-border rounded-lg">
                    <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-foreground">
                            {{ selectedLogFile ? selectedLogFile.name : t('features.system.logs.select') }}
                        </h2>
                        <div v-if="selectedLogFile" class="flex items-center space-x-2">
                            <Input
                                v-model="logSearch"
                                type="text"
                                :placeholder="t('features.system.logs.search')"
                                class="h-8 w-48"
                            />
                            <Button
                                @click="refreshLog"
                                variant="outline"
                                size="sm"
                            >
                                {{ t('features.system.logs.refresh') }}
                            </Button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div v-if="!selectedLogFile" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-4 text-muted-foreground">{{ t('features.system.logs.empty') }}</p>
                        </div>
                        <div v-else-if="loadingLog" class="text-center py-12">
                            <p class="text-muted-foreground">{{ t('features.system.logs.loading') }}</p>
                        </div>
                        <div v-else class="bg-background rounded-lg p-4 overflow-x-auto max-h-[600px] overflow-y-auto">
                            <pre class="text-xs font-mono text-muted-foreground" v-html="highlightedLogContent" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import toast from '../../../services/toast';
import { useConfirm } from '../../../composables/useConfirm';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';

const { t } = useI18n();
const { confirm } = useConfirm();

const logFiles = ref([]);
const selectedLogFile = ref(null);
const logContent = ref('');
const loadingLog = ref(false);
const clearing = ref(false);
const logSearch = ref('');

const highlightedLogContent = computed(() => {
    if (!logContent.value) return '';
    
    let content = logContent.value;
    
    // Highlight error lines
    content = content.replace(/\[ERROR\]/g, '<span class="text-destructive font-bold">[ERROR]</span>');
    content = content.replace(/\[WARNING\]/g, '<span class="text-yellow-400 font-bold">[WARNING]</span>');
    content = content.replace(/\[INFO\]/g, '<span class="text-blue-400 font-bold">[INFO]</span>');
    
    // Highlight search term
    if (logSearch.value) {
        const regex = new RegExp(`(${logSearch.value})`, 'gi');
        content = content.replace(regex, '<span class="bg-yellow-500 text-black">$1</span>');
    }
    
    return content;
});

const fetchLogFiles = async () => {
    try {
        const response = await api.get('/admin/cms/logs');
        const { data } = parseResponse(response);
        logFiles.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch log files:', error);
        logFiles.value = [];
    }
};

const selectLogFile = async (logFile) => {
    selectedLogFile.value = logFile;
    loadingLog.value = true;
    try {
        const response = await api.get(`/admin/cms/logs/${logFile.name}`);
        const data = parseSingleResponse(response) || {};
        logContent.value = data.content || '';
    } catch (error) {
        console.error('Failed to fetch log content:', error);
        logContent.value = t('features.system.logs.failed_load');
    } finally {
        loadingLog.value = false;
    }
};

const refreshLog = () => {
    if (selectedLogFile.value) {
        selectLogFile(selectedLogFile.value);
    }
};

const downloadLog = async (logFile) => {
    try {
        const response = await api.get(`/admin/cms/logs/${logFile.name}/download`, {
            responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'system.log');
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        console.error('Failed to download logs:', error);
        toast.error('Error', t('features.system.logs.messages.failed_download'));
    }
};

const clearLogs = async () => {
    const confirmed = await confirm({
        title: t('features.system.logs.actions.clear'),
        message: t('features.system.logs.confirm.clear'),
        variant: 'danger',
        confirmText: t('common.actions.clear'),
    });

    if (!confirmed) return;

    clearing.value = true;
    try {
        await api.delete('/admin/cms/logs');
        toast.success(t('features.system.logs.messages.cleared'));
        logContent.value = '';
        fetchLogFiles();
    } catch (error) {
        console.error('Failed to clear logs:', error);
        toast.error('Error', t('features.system.logs.messages.failed_clear'));
    } finally {
        clearing.value = false;
    }
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

onMounted(() => {
    fetchLogFiles();
});
</script>

