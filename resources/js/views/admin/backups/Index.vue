<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ t('features.system.backups.title') }}</h1>
                <p class="text-sm text-muted-foreground">{{ t('features.system.backups.description') }}</p>
            </div>
            <Button
                @click="createBackup"
                :disabled="creating"
                class="min-w-[140px]"
            >
                <Loader2 v-if="creating" class="w-4 h-4 mr-2 animate-spin" />
                <Plus v-else class="w-4 h-4 mr-2" />
                {{ creating ? t('features.system.backups.creating') : t('features.system.backups.create') }}
            </Button>
        </div>

        <!-- Statistics -->
        <div v-if="statistics" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-2.5 bg-indigo-500/10 rounded-xl">
                            <Database class="h-6 w-6 text-indigo-500" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ t('features.system.backups.stats.total') }}</p>
                            <p class="text-2xl font-bold tracking-tight text-foreground">{{ statistics.total || 0 }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-2.5 bg-emerald-500/10 rounded-xl">
                            <HardDrive class="h-6 w-6 text-emerald-500" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ t('features.system.backups.stats.size') }}</p>
                            <p class="text-2xl font-bold tracking-tight text-foreground">{{ formatFileSize(statistics.total_size || 0) }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-2.5 bg-blue-500/10 rounded-xl">
                            <Clock class="h-6 w-6 text-blue-500" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ t('features.system.backups.stats.last') }}</p>
                            <p class="text-xl font-bold tracking-tight text-foreground truncate max-w-[150px]" :title="formatDate(statistics.last_backup)">
                                {{ formatDate(statistics.last_backup) || t('features.system.backups.stats.never') }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-2.5 bg-amber-500/10 rounded-xl">
                            <Calendar class="h-6 w-6 text-amber-500" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ t('features.system.backups.stats.auto') }}</p>
                            <Badge :variant="statistics.schedule?.enabled ? 'success' : 'secondary'">
                                {{ statistics.schedule?.enabled ? t('features.system.backups.stats.enabled') : t('features.system.backups.stats.disabled') }}
                            </Badge>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Schedule Settings -->
        <Card class="mb-6">
            <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                <CardTitle class="text-lg font-semibold">{{ t('features.system.backups.schedule.title') }}</CardTitle>
                <Button
                    variant="ghost"
                    size="sm"
                    @click="showScheduleModal = true"
                    class="h-8 text-primary hover:text-primary hover:bg-primary/10"
                >
                    <Settings class="w-4 h-4 mr-2" />
                    {{ t('features.system.backups.schedule.configure') }}
                </Button>
            </CardHeader>
            <CardContent>
                <div v-if="statistics?.schedule" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">{{ t('features.system.backups.schedule.status') }}</p>
                        <div class="flex items-center gap-2">
                             <span class="w-2 h-2 rounded-full" :class="statistics.schedule.enabled ? 'bg-emerald-500' : 'bg-muted-foreground/30'"></span>
                             <p class="text-sm font-medium" :class="statistics.schedule.enabled ? 'text-emerald-600' : 'text-muted-foreground'">
                                 {{ statistics.schedule.enabled ? t('features.system.backups.stats.enabled') : t('features.system.backups.stats.disabled') }}
                             </p>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">{{ t('features.system.backups.schedule.frequency') }}</p>
                        <p class="text-sm font-medium text-foreground capitalize">{{ t(`features.system.backups.schedule.frequencies.${statistics.schedule.frequency}`) || 'Daily' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">{{ t('features.system.backups.schedule.time') }}</p>
                        <p class="text-sm font-medium text-foreground">{{ statistics.schedule.time || '02:00' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">{{ t('features.system.backups.schedule.retention') }}</p>
                        <p class="text-sm font-medium text-foreground">{{ statistics.schedule.retention_days || 30 }} {{ t('features.system.backups.schedule.days') }}</p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader class="pb-0 border-b-0 space-y-4">
                <div class="flex items-center gap-4">
                    <div class="relative flex-1 max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            :placeholder="t('features.system.backups.search')"
                            class="pl-9"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div v-if="loading" class="p-12 text-center">
                    <Loader2 class="w-8 h-8 animate-spin mx-auto text-muted-foreground mb-4" />
                    <p class="text-muted-foreground font-medium">{{ t('features.system.backups.loading') }}</p>
                </div>

                <div v-else-if="filteredBackups.length === 0" class="p-12 text-center">
                    <Database class="w-12 h-12 mx-auto text-muted-foreground/20 mb-4" />
                    <p class="text-muted-foreground font-medium">{{ t('features.system.backups.empty') }}</p>
                </div>

                <Table v-else>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('features.system.backups.table.name') }}</TableHead>
                            <TableHead>{{ t('features.system.backups.table.size') }}</TableHead>
                            <TableHead>{{ t('features.system.backups.table.created') }}</TableHead>
                            <TableHead class="text-right">{{ t('features.system.backups.table.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="backup in filteredBackups" :key="backup.id" class="hover:bg-muted/50 transition-colors group">
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-muted rounded-lg group-hover:bg-background transition-colors">
                                        <FileArchive class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-foreground">{{ backup.name }}</div>
                                        <div class="text-xs text-muted-foreground tracking-tight">{{ backup.type || 'Full System Backup' }}</div>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="text-sm tabular-nums">
                                {{ formatFileSize(backup.size) }}
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">
                                {{ formatDate(backup.created_at) }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Button 
                                        variant="ghost" 
                                        size="icon" 
                                        @click="downloadBackup(backup)"
                                        :title="t('features.system.backups.table.download')"
                                        class="h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    >
                                        <Download class="w-4 h-4" />
                                    </Button>
                                    <Button 
                                        variant="ghost" 
                                        size="icon" 
                                        @click="restoreBackup(backup)"
                                        :title="t('features.system.backups.table.restore')"
                                        class="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20"
                                    >
                                        <RotateCcw class="w-4 h-4" />
                                    </Button>
                                    <Button 
                                        variant="ghost" 
                                        size="icon" 
                                        @click="deleteBackup(backup)"
                                        :title="t('features.system.backups.table.delete')"
                                        class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <!-- Schedule Modal -->
        <Dialog v-model:open="showScheduleModal">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ t('features.system.backups.schedule.modal.title') }}</DialogTitle>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <div class="flex items-center space-x-2">
                        <Checkbox 
                            id="scheduleEnabled" 
                            :checked="scheduleForm.backup_schedule_enabled"
                            @update:checked="scheduleForm.backup_schedule_enabled = $event"
                        />
                        <Label for="scheduleEnabled" class="text-sm font-medium leading-none cursor-pointer">
                            {{ t('features.system.backups.schedule.modal.enable') }}
                        </Label>
                    </div>
                    <div class="grid gap-2">
                        <Label>{{ t('features.system.backups.schedule.frequency') }}</Label>
                        <Select v-model="scheduleForm.backup_schedule_frequency">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="daily">{{ t('features.system.backups.schedule.frequencies.daily') }}</SelectItem>
                                <SelectItem value="weekly">{{ t('features.system.backups.schedule.frequencies.weekly') }}</SelectItem>
                                <SelectItem value="monthly">{{ t('features.system.backups.schedule.frequencies.monthly') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label>{{ t('features.system.backups.schedule.time') }}</Label>
                        <Input type="time" v-model="scheduleForm.backup_schedule_time" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label>{{ t('features.system.backups.schedule.retention') }} ({{ t('features.system.backups.schedule.days') }})</Label>
                            <Input type="number" v-model.number="scheduleForm.backup_retention_days" min="1" max="365" />
                        </div>
                        <div class="grid gap-2">
                            <Label>{{ t('features.system.backups.schedule.modal.max') }}</Label>
                            <Input type="number" v-model.number="scheduleForm.backup_max_count" min="1" max="100" />
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showScheduleModal = false">
                        {{ t('features.system.backups.schedule.modal.cancel') }}
                    </Button>
                    <Button @click="saveSchedule" :disabled="savingSchedule">
                        <Loader2 v-if="savingSchedule" class="w-4 h-4 mr-2 animate-spin" />
                        {{ savingSchedule ? t('features.system.backups.schedule.modal.saving') : t('features.system.backups.schedule.modal.save') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import toast from '../../../services/toast';
import { useConfirm } from '../../../composables/useConfirm';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';
import Card from '../../../components/ui/card.vue';
import CardHeader from '../../../components/ui/card-header.vue';
import CardTitle from '../../../components/ui/card-title.vue';
import CardContent from '../../../components/ui/card-content.vue';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Table from '../../../components/ui/table.vue';
import TableHeader from '../../../components/ui/table-header.vue';
import TableBody from '../../../components/ui/table-body.vue';
import TableRow from '../../../components/ui/table-row.vue';
import TableCell from '../../../components/ui/table-cell.vue';
import TableHead from '../../../components/ui/table-head.vue';
import Badge from '../../../components/ui/badge.vue';
import Dialog from '../../../components/ui/dialog.vue';
import DialogContent from '../../../components/ui/dialog-content.vue';
import DialogHeader from '../../../components/ui/dialog-header.vue';
import DialogTitle from '../../../components/ui/dialog-title.vue';
import DialogFooter from '../../../components/ui/dialog-footer.vue';
import Label from '../../../components/ui/label.vue';
import Select from '../../../components/ui/select.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import Checkbox from '../../../components/ui/checkbox.vue';
import { 
    Plus, 
    Loader2, 
    Database, 
    HardDrive, 
    Clock, 
    Calendar, 
    Settings, 
    Search,
    FileArchive,
    Download,
    RotateCcw,
    Trash2
} from 'lucide-vue-next';

const { t } = useI18n();
const { confirm } = useConfirm();
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
        toast.success(t('features.system.backups.messages.created'));
        await fetchBackups();
    } catch (error) {
        console.error('Failed to create backup:', error);
        toast.error('Error', error.response?.data?.message || t('features.system.backups.messages.failed_create'));
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
        toast.error('Error', t('features.system.backups.messages.failed_download'));
    }
};

const restoreBackup = async (backup) => {
    const confirmed = await confirm({
        title: t('features.system.backups.actions.restore'),
        message: t('features.system.backups.confirm.restore', { name: backup.name }),
        variant: 'warning',
        confirmText: t('features.system.backups.actions.restore'),
    });

    if (!confirmed) return;

    const doubleConfirmed = await confirm({
        title: t('features.system.backups.actions.restore'),
        message: t('features.system.backups.confirm.restore_warning'),
        variant: 'danger',
        confirmText: t('common.actions.confirm'),
    });

    if (!doubleConfirmed) return;

    try {
        await api.post(`/admin/cms/backups/${backup.id}/restore`);
        toast.success(t('features.system.backups.messages.restored'));
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    } catch (error) {
        console.error('Failed to restore backup:', error);
        toast.error('Error', error.response?.data?.message || t('features.system.backups.messages.failed_restore'));
    }
};

const deleteBackup = async (backup) => {
    const confirmed = await confirm({
        title: t('features.system.backups.actions.delete'),
        message: t('features.system.backups.confirm.delete', { name: backup.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/admin/cms/backups/${backup.id}`);
        toast.success(t('features.system.backups.messages.deleted', 'Backup deleted'));
        await fetchBackups();
    } catch (error) {
        console.error('Failed to delete backup:', error);
        toast.error('Error', 'Failed to delete backup');
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
        toast.success(t('features.system.backups.messages.saved'));
    } catch (error) {
        console.error('Failed to save schedule:', error);
        toast.error('Error', t('features.system.backups.messages.failed_save'));
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

