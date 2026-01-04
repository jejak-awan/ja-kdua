<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('features.system.notifications.title') }}</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Send Notification Form -->
            <div v-show="!sidebarCollapsed" class="lg:col-span-4 transition-all duration-300">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle>{{ $t('features.system.notifications.create.title') }}</CardTitle>
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="sidebarCollapsed = true">
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                    </CardHeader>
                    <div class="px-6 pb-2" v-if="queueHealth">
                         <div class="flex items-center gap-2">
                            <div :class="[
                                'h-2 w-2 rounded-full',
                                queueHealth.is_active ? 'bg-green-500' : 'bg-yellow-500'
                            ]"></div>
                            <span class="text-[10px] font-medium uppercase tracking-wider text-muted-foreground">
                                {{ queueHealth.message }} ({{ queueHealth.driver }})
                            </span>
                        </div>
                    </div>
                    <CardContent class="space-y-4 pt-2">
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                {{ $t('features.system.notifications.form.target_type') }}
                            </label>
                            <Select v-model="form.target_type">
                                <SelectTrigger>
                                    <SelectValue :placeholder="$t('features.system.notifications.form.target_type')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{{ $t('features.system.notifications.form.targets.all') }}</SelectItem>
                                    <SelectItem value="role">{{ $t('features.system.notifications.form.targets.role') }}</SelectItem>
                                    <SelectItem value="user">{{ $t('features.system.notifications.form.targets.user') }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div v-if="form.target_type === 'role'" class="space-y-2">
                            <label class="text-sm font-medium leading-none">{{ $t('features.system.notifications.form.targets.role') }}</label>
                            <Select v-model="form.target_id">
                                <SelectTrigger>
                                    <SelectValue :placeholder="$t('features.system.notifications.form.targets.role')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <template v-if="roles && roles.length">
                                        <SelectItem v-for="role in roles" :key="role.id" :value="String(role.id)">
                                            {{ role.name }}
                                        </SelectItem>
                                    </template>
                                </SelectContent>
                            </Select>
                        </div>

                        <div v-if="form.target_type === 'user'" class="space-y-2">
                            <label class="text-sm font-medium leading-none">{{ $t('features.system.notifications.form.targets.user') }}</label>
                            <!-- Simple select for users, might need autocomplete for large user bases -->
                            <Select v-model="form.target_id">
                                <SelectTrigger>
                                    <SelectValue :placeholder="$t('features.system.notifications.form.targets.user')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <template v-if="users && users.length">
                                        <SelectItem v-for="user in users" :key="user.id" :value="String(user.id)">
                                            {{ user.name }} ({{ user.email }})
                                        </SelectItem>
                                    </template>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">{{ $t('features.system.notifications.form.type') }}</label>
                            <Select v-model="form.type">
                                <SelectTrigger>
                                    <SelectValue :placeholder="$t('features.system.notifications.form.type')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="info">Info</SelectItem>
                                    <SelectItem value="success">Success</SelectItem>
                                    <SelectItem value="warning">Warning</SelectItem>
                                    <SelectItem value="error">Error</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Async Toggle -->
                        <div class="flex items-center gap-2 py-2">
                            <Checkbox 
                                id="is_async"
                                :checked="form.is_async"
                                @update:checked="form.is_async = $event"
                            />
                            <label for="is_async" class="text-sm font-medium leading-none cursor-pointer">
                                {{ $t('features.system.notifications.form.process_async') }}
                            </label>
                        </div>

                        <!-- Queue Status Warning -->
                        <div v-if="form.is_async && queueHealth && !queueHealth.is_active" class="flex items-start gap-2 p-3 rounded-md bg-amber-500/10 text-amber-500 text-xs border border-amber-500/20">
                            <AlertTriangle class="h-4 w-4 shrink-0" />
                            <div>
                                <p class="font-semibold">{{ $t('features.system.notifications.queue.inactive_title') }}</p>
                                <p>{{ $t('features.system.notifications.queue.inactive_message') }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">{{ $t('features.system.notifications.form.title') }}</label>
                            <Input v-model="form.title" :placeholder="$t('features.system.notifications.form.title')" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none">{{ $t('features.system.notifications.form.message') }}</label>
                            <textarea
                                v-model="form.message"
                                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                :placeholder="$t('features.system.notifications.form.message')"
                            ></textarea>
                        </div>

                        <Button class="w-full" @click="handleSend" :disabled="sending || !isFormValid">
                            <Send class="mr-2 h-4 w-4" v-if="!sending" />
                            <Loader2 class="mr-2 h-4 w-4 animate-spin" v-else />
                            {{ sending ? $t('features.system.notifications.form.sending') : $t('features.system.notifications.form.send') }}
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- History Table -->
            <div :class="[sidebarCollapsed ? 'lg:col-span-12' : 'lg:col-span-8', 'transition-all duration-300']">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0">
                        <div class="flex items-center gap-4">
                            <Button v-if="sidebarCollapsed" variant="outline" size="sm" @click="sidebarCollapsed = false">
                                <Plus class="mr-2 h-4 w-4" />
                                {{ $t('features.system.notifications.create.title') }}
                            </Button>
                            <CardTitle>{{ $t('features.system.notifications.history.title') }}</CardTitle>
                        </div>
                        <div v-if="selectedItems.length > 0" class="flex items-center gap-2">
                             <span class="text-sm text-muted-foreground mr-2">{{ selectedItems.length }} selected</span>
                             <Button 
                                variant="destructive" 
                                size="sm" 
                                @click="handleBulkRevoke"
                                :disabled="bulkRevoking"
                            >
                                <Trash2 class="h-4 w-4 mr-2" v-if="!bulkRevoking" />
                                <Loader2 class="h-4 w-4 animate-spin mr-2" v-else />
                                {{ $t('features.system.notifications.form.revoke') }}
                             </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-[40px]">
                                            <Checkbox 
                                                :checked="isAllSelected" 
                                                @update:checked="toggleSelectAll"
                                            />
                                        </TableHead>
                                        <TableHead>{{ $t('features.system.notifications.table.title') }}</TableHead>
                                        <TableHead>{{ $t('features.system.notifications.table.type') }}</TableHead>
                                        <TableHead>{{ $t('features.system.notifications.table.recipients') }}</TableHead>
                                        <TableHead>{{ $t('features.system.notifications.table.sent_at') }}</TableHead>
                                        <TableHead class="text-right">{{ $t('common.actions.title') }}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-if="history && history.length">
                                        <TableRow v-for="notification in history" :key="notification.id" :class="{ 'bg-muted/50': isSelected(notification) }">
                                            <TableCell>
                                                <Checkbox 
                                                    :checked="isSelected(notification)"
                                                    @update:checked="toggleSelect(notification)"
                                                />
                                            </TableCell>
                                            <TableCell>
                                                <div class="font-medium">{{ notification.title }}</div>
                                                <div class="text-xs text-muted-foreground truncate max-w-[200px]">{{ notification.message }}</div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge :variant="getBadgeVariant(notification.type)">
                                                    {{ notification.type }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {{ notification.recipient_count }}
                                            </TableCell>
                                            <TableCell>
                                                {{ formatDate(notification.created_at) }}
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-8 w-8 p-0 text-destructive hover:text-destructive hover:bg-destructive/10"
                                                    @click="handleRevoke(notification)"
                                                    :disabled="revoking === notification.id"
                                                    title="Revoke Broadcast"
                                                >
                                                    <Trash2 class="h-4 w-4" v-if="revoking !== notification.id" />
                                                    <Loader2 class="h-4 w-4 animate-spin" v-else />
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                    <TableRow v-if="history.length === 0">
                                        <TableCell colspan="6" class="h-24 text-center text-muted-foreground">
                                            No history found
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Pagination -->
                        <div class="flex items-center justify-between px-2 py-4" v-if="pagination && pagination.last_page > 1">
                            <div class="text-sm text-muted-foreground">
                                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                            </div>
                            <div class="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="pagination.current_page === 1"
                                    @click="fetchHistory(pagination.current_page - 1)"
                                >
                                    Previous
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="pagination.current_page === pagination.last_page"
                                    @click="fetchHistory(pagination.current_page + 1)"
                                >
                                    Next
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import toast from '../../../services/toast';
import { useConfirm } from '../../../composables/useConfirm';
import { Send, Loader2, Trash2, Activity, Info, AlertTriangle, CheckCircle2, ChevronLeft, Plus } from 'lucide-vue-next';
import { parseResponse } from '../../../utils/responseParser';

// UI Components
import Card from '../../../components/ui/card.vue';
import CardHeader from '../../../components/ui/card-header.vue';
import CardTitle from '../../../components/ui/card-title.vue';
import CardContent from '../../../components/ui/card-content.vue';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Badge from '../../../components/ui/badge.vue';
import Select from '../../../components/ui/select.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import Table from '../../../components/ui/table.vue';
import TableBody from '../../../components/ui/table-body.vue';
import TableCell from '../../../components/ui/table-cell.vue';
import TableHead from '../../../components/ui/table-head.vue';
import TableHeader from '../../../components/ui/table-header.vue';
import TableRow from '../../../components/ui/table-row.vue';
import Checkbox from '../../../components/ui/checkbox.vue';

const { t } = useI18n();
const { confirm } = useConfirm();

const roles = ref([]);
const users = ref([]);
const history = ref([]);
const pagination = ref(null);
const sending = ref(false);
const revoking = ref(null);
const selectedItems = ref([]);
const bulkRevoking = ref(false);
const queueHealth = ref(null);
const sidebarCollapsed = ref(false);

const form = reactive({
    target_type: 'all',
    target_id: null,
    type: 'info',
    title: '',
    message: '',
    is_async: true
});

// Reset target_id when target_type changes to prevent invalid selections
watch(() => form.target_type, () => {
    form.target_id = '';
});

// Form validation
const isFormValid = computed(() => {
    // Title and message are always required
    if (!form.title?.trim() || !form.message?.trim()) {
        return false;
    }
    // target_id is required when target_type is 'role' or 'user'
    if ((form.target_type === 'role' || form.target_type === 'user') && !form.target_id) {
        return false;
    }
    return true;
});

const getBadgeVariant = (type) => {
    switch (type) {
        case 'error': return 'destructive';
        case 'warning': return 'warning';
        case 'success': return 'default';
        default: return 'secondary';
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
};

const fetchData = async () => {
    try {
        const [rolesRes, usersRes, healthRes] = await Promise.all([
            api.get('/admin/cms/roles'),
            api.get('/admin/cms/users'),
            api.get('/admin/cms/system/info')
        ]);
        roles.value = parseResponse(rolesRes).data;
        users.value = parseResponse(usersRes).data;
        queueHealth.value = parseResponse(healthRes).data?.queue_health;
    } catch (error) {
        console.error('Failed to fetch data:', error);
    }
};

const fetchHistory = async (page = 1) => {
    try {
        const res = await api.get('/admin/cms/notifications/system', {
            params: { page, limit: 10 }
        });
        const parsed = parseResponse(res);
        history.value = parsed.data;
        pagination.value = parsed.pagination;
        
        // Clear selection on page change or refresh
        selectedItems.value = [];
    } catch (error) {
        console.error('Failed to fetch history:', error);
    }
};

const isSelected = (item) => {
    return selectedItems.value.some(i => 
        i.title === item.title && 
        i.message === item.message && 
        i.created_at === item.created_at
    );
};

const toggleSelect = (item) => {
    const index = selectedItems.value.findIndex(i => 
        i.title === item.title && 
        i.message === item.message && 
        i.created_at === item.created_at
    );
    
    if (index > -1) {
        selectedItems.value.splice(index, 1);
    } else {
        selectedItems.value.push({
            title: item.title,
            message: item.message,
            created_at: item.created_at
        });
    }
};

const isAllSelected = computed(() => {
    return history.value.length > 0 && history.value.every(item => isSelected(item));
});

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedItems.value = [];
    } else {
        history.value.forEach(item => {
            if (!isSelected(item)) {
                selectedItems.value.push({
                    title: item.title,
                    message: item.message,
                    created_at: item.created_at
                });
            }
        });
    }
};

const handleBulkRevoke = async () => {
    const confirmed = await confirm({
        title: t('features.system.notifications.form.revoke'),
        message: t('features.system.notifications.confirm.revoke'),
        variant: 'destructive',
        confirmText: t('features.system.notifications.form.revoke'),
    });

    if (!confirmed) return;

    bulkRevoking.value = true;
    try {
        await api.post('/admin/cms/notifications/system/bulk-revoke', {
            broadcasts: selectedItems.value
        });
        toast.success(t('features.system.notifications.messages.revoked'));
        selectedItems.value = [];
        fetchHistory(pagination.value?.current_page || 1);
    } catch (error) {
        console.error('Failed to bulk revoke:', error);
        toast.error('Error', t('features.system.notifications.messages.failed'));
    } finally {
        bulkRevoking.value = false;
    }
};

const handleRevoke = async (notification) => {
    const confirmed = await confirm({
        title: t('features.system.notifications.form.revoke'),
        message: t('features.system.notifications.confirm.revoke'),
        variant: 'destructive',
        confirmText: t('features.system.notifications.form.revoke'),
    });

    if (!confirmed) return;

    revoking.value = notification.id;
    try {
        await api.delete('/admin/cms/notifications/system/revoke', {
            data: {
                title: notification.title,
                message: notification.message,
                created_at: notification.created_at
            }
        });
        toast.success(t('features.system.notifications.messages.revoked'));
        fetchHistory(pagination.value?.current_page || 1);
    } catch (error) {
        console.error('Failed to revoke:', error);
        toast.error('Error', t('features.system.notifications.messages.failed'));
    } finally {
        revoking.value = null;
    }
};

const handleSend = async () => {
    if (!form.title || !form.message) {
        toast.error('Error', 'Please fill in all fields');
        return;
    }

    if (form.target_type !== 'all' && !form.target_id) {
        toast.error('Error', 'Please select a target');
        return;
    }

    const confirmed = await confirm({
        title: t('features.system.notifications.create.title'),
        message: t('features.system.notifications.confirm.send'),
        variant: 'default',
        confirmText: t('features.system.notifications.form.send'),
    });

    if (!confirmed) return;

    sending.value = true;
    try {
        await api.post('/admin/cms/notifications/broadcast', form);
        toast.success(form.is_async 
            ? t('features.system.notifications.messages.sent') 
            : 'Notifikasi berhasil dikirim secara langsung'
        );
        
        // Reset form slightly but keep some defaults
        form.title = '';
        form.message = '';
        
        // Refresh history
        fetchHistory();
    } catch (error) {
        console.error('Failed to send:', error);
        toast.error('Error', t('features.system.notifications.messages.failed'));
    } finally {
        sending.value = false;
    }
};

onMounted(() => {
    fetchData();
    fetchHistory();
});
</script>
