<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.system.backups.title') }}</h1>
            <button
                @click="createBackup"
                :disabled="creating"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/80 disabled:opacity-50"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ creating ? t('features.system.backups.creating') : t('features.system.backups.create') }}
            </button>
        </div>

        <!-- Statistics -->
        <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">{{ t('features.system.backups.stats.total') }}</p>
                        <p class="text-2xl font-semibold text-foreground">{{ statistics.total || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">{{ t('features.system.backups.stats.size') }}</p>
                        <p class="text-2xl font-semibold text-foreground">{{ formatFileSize(statistics.total_size || 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">{{ t('features.system.backups.stats.last') }}</p>
                        <p class="text-2xl font-semibold text-foreground">{{ formatDate(statistics.last_backup) || t('features.system.backups.stats.never') }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">{{ t('features.system.backups.stats.auto') }}</p>
                        <p class="text-2xl font-semibold text-foreground">{{ statistics.schedule?.enabled ? t('features.system.backups.stats.enabled') : t('features.system.backups.stats.disabled') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Settings -->
        <div class="bg-card border border-border rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-foreground">{{ t('features.system.backups.schedule.title') }}</h3>
                <button
                    @click="showScheduleModal = true"
                    class="text-sm text-indigo-600 hover:text-indigo-800"
                >
                    {{ t('features.system.backups.schedule.configure') }}
                </button>
            </div>
            <div v-if="statistics?.schedule" class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-muted-foreground">{{ t('features.system.backups.schedule.status') }}</p>
                    <p class="font-medium" :class="statistics.schedule.enabled ? 'text-green-600' : 'text-muted-foreground'">
                        {{ statistics.schedule.enabled ? t('features.system.backups.stats.enabled') : t('features.system.backups.stats.disabled') }}
                    </p>
                </div>
                <div>
                    <p class="text-muted-foreground">{{ t('features.system.backups.schedule.frequency') }}</p>
                    <p class="font-medium text-foreground capitalize">{{ t(`features.system.backups.schedule.frequencies.${statistics.schedule.frequency}`) || 'Daily' }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">{{ t('features.system.backups.schedule.time') }}</p>
                    <p class="font-medium text-foreground">{{ statistics.schedule.time || '02:00' }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">{{ t('features.system.backups.schedule.retention') }}</p>
                    <p class="font-medium text-foreground">{{ statistics.schedule.retention_days || 30 }} {{ t('features.system.backups.schedule.days') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-card border border-border rounded-lg">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center space-x-4">
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="t('features.system.backups.search')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.system.backups.loading') }}</p>
            </div>

            <div v-else-if="filteredBackups.length === 0" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.system.backups.empty') }}</p>
            </div>

            <table v-else class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ t('features.system.backups.table.name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ t('features.system.backups.table.size') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ t('features.system.backups.table.created') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground tracking-wider">
                            {{ t('features.system.backups.table.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="backup in filteredBackups" :key="backup.id" class="hover:bg-muted">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ backup.name }}</div>
                            <div class="text-sm text-muted-foreground">{{ backup.type || 'Full' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                            {{ formatFileSize(backup.size) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                            {{ formatDate(backup.created_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button
                                    @click="downloadBackup(backup)"
                                    class="text-blue-600 hover:text-blue-900"
                                >
                                    {{ t('features.system.backups.table.download') }}
                                </button>
                                <button
                                    @click="restoreBackup(backup)"
                                    class="text-green-600 hover:text-green-900"
                                >
                                    {{ t('features.system.backups.table.restore') }}
                                </button>
                                <button
                                    @click="deleteBackup(backup)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    {{ t('features.system.backups.table.delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Schedule Modal -->
        <div v-if="showScheduleModal" class="fixed inset-0 bg-background/80 backdrop-blur-sm overflow-y-auto h-full w-full z-50" @click.self="showScheduleModal = false">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-md rounded-md bg-card">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-foreground">{{ t('features.system.backups.schedule.modal.title') }}</h3>
                    <button @click="showScheduleModal = false" class="text-muted-foreground hover:text-muted-foreground">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="scheduleEnabled" v-model="scheduleForm.backup_schedule_enabled" class="h-4 w-4 text-indigo-600 border-input rounded">
                        <label for="scheduleEnabled" class="ml-2 text-sm text-foreground">{{ t('features.system.backups.schedule.modal.enable') }}</label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ t('features.system.backups.schedule.frequency') }}</label>
                        <select v-model="scheduleForm.backup_schedule_frequency" class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md">
                            <option value="daily">{{ t('features.system.backups.schedule.frequencies.daily') }}</option>
                            <option value="weekly">{{ t('features.system.backups.schedule.frequencies.weekly') }}</option>
                            <option value="monthly">{{ t('features.system.backups.schedule.frequencies.monthly') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ t('features.system.backups.schedule.time') }}</label>
                        <input type="time" v-model="scheduleForm.backup_schedule_time" class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ t('features.system.backups.schedule.retention') }} ({{ t('features.system.backups.schedule.days') }})</label>
                        <input type="number" v-model.number="scheduleForm.backup_retention_days" min="1" max="365" class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ t('features.system.backups.schedule.modal.max') }}</label>
                        <input type="number" v-model.number="scheduleForm.backup_max_count" min="1" max="100" class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md">
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="showScheduleModal = false" class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-sm font-medium text-foreground bg-card hover:bg-muted">
                        {{ t('features.system.backups.schedule.modal.cancel') }}
                    </button>
                    <button @click="saveSchedule" :disabled="savingSchedule" class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-primary-foreground bg-primary hover:bg-primary/80 disabled:opacity-50">
                        {{ savingSchedule ? t('features.system.backups.schedule.modal.saving') : t('features.system.backups.schedule.modal.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';

const { t } = useI18n();
const backups = ref([]);
const statistics = ref(null);
const loading = ref(false);
const creating = ref(false);
const search = ref('');
const showScheduleModal = ref(false);
const savingSchedule = ref(false);
const scheduleForm = ref({
    backup_schedule_enabled: false,
    backup_schedule_frequency: 'daily',
    backup_schedule_time: '02:00',
    backup_retention_days: 30,
    backup_max_count: 10
});

const filteredBackups = computed(() => {
    if (!search.value) return backups.value;
    
    const searchLower = search.value.toLowerCase();
    return backups.value.filter(backup => 
        backup.name.toLowerCase().includes(searchLower)
    );
});

const fetchBackups = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/backups');
        const { data } = parseResponse(response);
        backups.value = ensureArray(data);
        
        // Fetch statistics
        try {
            const statsResponse = await api.get('/admin/cms/backups/statistics');
            statistics.value = parseSingleResponse(statsResponse);
        } catch (error) {
            // Calculate from backups if endpoint doesn't exist
            statistics.value = {
                total: backups.value.length,
                total_size: backups.value.reduce((sum, b) => sum + (b.size || 0), 0),
                last_backup: backups.value.length > 0 ? backups.value[0].created_at : null,
                auto_backup: false,
            };
        }
    } catch (error) {
        console.error('Failed to fetch backups:', error);
    } finally {
        loading.value = false;
    }
};

const createBackup = async () => {
    creating.value = true;
    try {
        await api.post('/admin/cms/backups');
        alert(t('features.system.backups.messages.created'));
        await fetchBackups();
    } catch (error) {
        console.error('Failed to create backup:', error);
        alert(error.response?.data?.message || t('features.system.backups.messages.failed_create'));
    } finally {
        creating.value = false;
    }
};

const downloadBackup = async (backup) => {
    try {
        const response = await api.get(`/admin/cms/backups/${backup.id}/download`, {
            responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', backup.name);
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        console.error('Failed to download backup:', error);
        alert(t('features.system.backups.messages.failed_download'));
    }
};

const restoreBackup = async (backup) => {
    if (!confirm(t('features.system.backups.confirm.restore', { name: backup.name }))) {
        return;
    }

    if (!confirm(t('features.system.backups.confirm.restore_warning'))) {
        return;
    }

    try {
        await api.post(`/admin/cms/backups/${backup.id}/restore`);
        alert(t('features.system.backups.messages.restored'));
        window.location.reload();
    } catch (error) {
        console.error('Failed to restore backup:', error);
        alert(error.response?.data?.message || t('features.system.backups.messages.failed_restore'));
    }
};

const deleteBackup = async (backup) => {
    if (!confirm(t('features.system.backups.confirm.delete', { name: backup.name }))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/backups/${backup.id}`);
        await fetchBackups();
    } catch (error) {
        console.error('Failed to delete backup:', error);
        alert('Failed to delete backup');
    }
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const saveSchedule = async () => {
    savingSchedule.value = true;
    try {
        await api.post('/admin/cms/backups/schedule', scheduleForm.value);
        showScheduleModal.value = false;
        await fetchBackups(); // Refresh statistics
        alert(t('features.system.backups.messages.saved'));
    } catch (error) {
        console.error('Failed to save schedule:', error);
        alert(t('features.system.backups.messages.failed_save'));
    } finally {
        savingSchedule.value = false;
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    // Use i18n date format or component logic. 
    // Ideally use d() from useI18n if setup, but standard toLocaleString is used here.
    // I will keep standard toLocaleString but maybe use locale from i18n if possible, 
    // but browser locale is often fine. 
    // Actually, I should probably respect the app locale.
    return new Date(date).toLocaleString(); 
};

onMounted(() => {
    fetchBackups();
});
</script>

