<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Log Viewer</h1>
            <div class="flex items-center space-x-2">
                <button
                    @click="clearLogs"
                    :disabled="clearing"
                    class="px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 disabled:opacity-50"
                >
                    {{ clearing ? 'Clearing...' : 'Clear Logs' }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Log Files List -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Log Files</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <button
                            v-for="logFile in logFiles"
                            :key="logFile.name"
                            @click="selectLogFile(logFile)"
                            :class="[
                                'w-full px-6 py-4 text-left hover:bg-gray-50 transition-colors',
                                selectedLogFile?.name === logFile.name ? 'bg-indigo-50 border-l-4 border-indigo-600' : ''
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ logFile.name }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ formatFileSize(logFile.size) }}</p>
                                </div>
                                <button
                                    @click.stop="downloadLog(logFile)"
                                    class="text-indigo-600 hover:text-indigo-800"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </button>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Log Viewer -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ selectedLogFile ? selectedLogFile.name : 'Select a log file' }}
                        </h2>
                        <div v-if="selectedLogFile" class="flex items-center space-x-2">
                            <input
                                v-model="logSearch"
                                type="text"
                                placeholder="Search in log..."
                                class="px-3 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <button
                                @click="refreshLog"
                                class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Refresh
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div v-if="!selectedLogFile" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-4 text-gray-500">Select a log file to view</p>
                        </div>
                        <div v-else-if="loadingLog" class="text-center py-12">
                            <p class="text-gray-500">Loading log file...</p>
                        </div>
                        <div v-else class="bg-gray-900 rounded-lg p-4 overflow-x-auto max-h-[600px] overflow-y-auto">
                            <pre class="text-xs font-mono text-green-400" v-html="highlightedLogContent"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../../../services/api';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';

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
    content = content.replace(/\[ERROR\]/g, '<span class="text-red-400 font-bold">[ERROR]</span>');
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
        logContent.value = 'Failed to load log content';
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
        link.setAttribute('download', logFile.name);
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        console.error('Failed to download log:', error);
        alert('Failed to download log file');
    }
};

const clearLogs = async () => {
    if (!confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
        return;
    }

    clearing.value = true;
    try {
        await api.post('/admin/cms/logs/clear');
        alert('Logs cleared successfully');
        logContent.value = '';
        await fetchLogFiles();
    } catch (error) {
        console.error('Failed to clear logs:', error);
        alert('Failed to clear logs');
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

