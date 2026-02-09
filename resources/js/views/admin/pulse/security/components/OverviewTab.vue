<template>
    <div class="space-y-6">
        <!-- Statistics -->
        <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-500/10 rounded-lg">
                            <ShieldAlert class="h-6 w-6 text-red-500" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.security.statistics.events') }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ statistics.total_events || 0 }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-500/10 rounded-lg">
                            <ShieldX class="h-6 w-6 text-yellow-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.security.statistics.blockedIps') }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ blocklistCount }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-orange-500/10 rounded-lg">
                            <UserX class="h-6 w-6 text-orange-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.security.statistics.failedLogins') }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ statistics.failed_logins || 0 }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-500/10 rounded-lg">
                            <ShieldCheck class="h-6 w-6 text-green-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.security.whitelist.title') }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ whitelistCount }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- IP Management -->
        <Card>
            <CardHeader>
                <CardTitle class="text-lg">{{ $t('features.security.ipManagement.title') }}</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <Label>{{ $t('features.security.ipManagement.block.label') }}</Label>
                        <div class="flex space-x-2">
                            <Input
                                v-model="ipToBlock"
                                type="text"
                                :placeholder="$t('features.security.ipManagement.block.placeholder')"
                            />
                            <Button variant="destructive" @click="handleBlockIP" :disabled="!ipToBlock.trim()">
                                {{ $t('features.security.ipManagement.block.button') }}
                            </Button>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ $t('features.security.ipManagement.check.label') }}</Label>
                        <div class="flex space-x-2">
                            <Input
                                v-model="ipToCheck"
                                type="text"
                                :placeholder="$t('features.security.ipManagement.check.placeholder')"
                            />
                            <Button @click="handleCheckIP" :disabled="!ipToCheck.trim()">
                                {{ $t('features.security.ipManagement.check.button') }}
                            </Button>
                        </div>
                        <div v-if="ipStatus" class="mt-2">
                            <Badge
                                :variant="ipStatus.is_blocked ? 'destructive' : 'default'"
                                class="w-full justify-center py-2 bg-muted/50 text-foreground"
                            >
                                {{ $t('features.security.ipManagement.status.label') }}: {{ ipStatus.is_blocked ? $t('features.security.ipManagement.status.blocked') : $t('features.security.ipManagement.status.allowed') }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Security Logs Table -->
        <Card>
            <CardHeader class="flex flex-row items-center justify-between pb-4">
                <div class="flex items-center space-x-4">
                    <CardTitle class="text-lg">{{ $t('features.security.logs.title') }}</CardTitle>
                    <Badge v-if="selectedLogIds.length > 0" variant="secondary" class="bg-muted/50 text-foreground">
                        {{ $t('features.security.bulkActions.selected', { count: selectedLogIds.length }) }}
                    </Badge>
                    <Button
                        v-if="selectedLogIds.length > 0"
                        variant="destructive"
                        size="sm"
                        @click="$emit('bulk-block', selectedLogIds)"
                    >
                        {{ $t('features.security.bulkActions.blockSelected') }}
                    </Button>
                </div>
                <div class="flex items-center space-x-2">
                    <Select v-model="logFilter">
                        <SelectTrigger class="w-48">
                            <SelectValue :placeholder="$t('features.security.logs.all')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ $t('features.security.logs.all') }}</SelectItem>
                            <SelectItem value="login_failed">{{ $t('features.security.logs.failedLogin') }}</SelectItem>
                            <SelectItem value="ip_blocked">{{ $t('features.security.logs.blockedIp') }}</SelectItem>
                            <SelectItem value="suspicious_activity">{{ $t('features.security.logs.suspiciousActivity') }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <div class="relative w-64">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input v-model="logSearch" :placeholder="$t('features.security.logs.search')" class="pl-10" />
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <DataTable
                    :table="table"
                    :loading="loading"
                    :empty-message="t('features.security.logs.empty')"
                />
            </CardContent>
            <Pagination
                v-if="filteredLogs.length > 0"
                :current-page="currentPage"
                :total-items="filteredLogs.length"
                v-model:per-page="perPage"
                @page-change="(val: number) => currentPage = val"
                class="border-none shadow-none px-6 py-4"
            />
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, h, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';
import {
    Card, CardHeader, CardTitle, CardContent,
    Button, Input, Label, Badge, Checkbox, DataTable, Pagination,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';

import ShieldAlert from 'lucide-vue-next/dist/esm/icons/shield-alert.js';
import ShieldX from 'lucide-vue-next/dist/esm/icons/shield-x.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import UserX from 'lucide-vue-next/dist/esm/icons/user-x.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Log {
    id: number;
    event_type: string;
    ip_address: string;
    user_id?: number | null;
    user?: User | null;
    details: string;
    created_at: string;
}

interface Statistics {
    total_events: number;
    failed_logins: number;
    blocked_ips: number;
}

interface IpStatus {
    is_blocked: boolean;
    reason?: string | null;
}

const props = defineProps<{
    logs: Log[];
    statistics: Statistics | null;
    loading: boolean;
    blocklistCount: number;
    whitelistCount: number;
}>();

const emit = defineEmits<{
    'block-ip': [ip: string];
    'check-ip': [ip: string];
    'block-from-log': [ip: string];
    'bulk-block': [ips: string[]];
}>();

const { t } = useI18n();

// Local state
const ipToBlock = ref('');
const ipToCheck = ref('');
const ipStatus = ref<IpStatus | null>(null);
const logFilter = ref('all');
const logSearch = ref('');
const selectedLogIds = ref<string[]>([]);
const currentPage = ref(1);
const perPage = ref(25);
const sorting = ref<SortingState>([]);

// Watch for filter changes to reset pagination
watch([logFilter, logSearch], () => {
    currentPage.value = 1;
});

const handleBlockIP = () => {
    if (ipToBlock.value.trim()) {
        emit('block-ip', ipToBlock.value);
        ipToBlock.value = '';
    }
};

const handleCheckIP = async () => {
    if (ipToCheck.value.trim()) {
        emit('check-ip', ipToCheck.value);
        // Parent will update ipStatus via exposed method or callback
    }
};

// Filtered logs
const filteredLogs = computed(() => {
    let filtered = props.logs;
    
    if (logFilter.value && logFilter.value !== 'all') {
        filtered = filtered.filter(log => log.event_type === logFilter.value);
    }
    
    if (logSearch.value) {
        const searchLower = logSearch.value.toLowerCase();
        filtered = filtered.filter(log => 
            log.ip_address?.toLowerCase().includes(searchLower) ||
            log.details?.toLowerCase().includes(searchLower) ||
            log.user?.name?.toLowerCase().includes(searchLower)
        );
    }
    
    return filtered;
});

// Paginated data
const paginatedLogs = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = Math.min(start + perPage.value, filteredLogs.value.length);
    return filteredLogs.value.slice(start, end);
});

// Helper functions
const getEventLabel = (eventType: string): string => {
    const key = `features.security.logs.eventTypes.${eventType}`;
    const translated = t(key);
    return translated !== key ? translated : eventType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getEventClass = (eventType: string): string => {
    const classes: Record<string, string> = {
        'login_failed': 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20',
        'login_success': 'bg-green-500/10 text-green-600 dark:text-green-400 border-green-500/20',
        'ip_blocked': 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20',
        'ip_unblocked': 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
        'suspicious_activity': 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20',
    };
    return classes[eventType] || 'bg-muted/50 text-muted-foreground border border-border';
};

const formatDate = (date: string | null | undefined): string => {
    if (!date) return '-';
    return new Date(date).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// TanStack Table
const columnHelper = createColumnHelper<Log>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: () => h(Checkbox, {
            checked: selectedLogIds.value.length === paginatedLogs.value.length && paginatedLogs.value.length > 0,
            'onUpdate:checked': (val: boolean) => {
                selectedLogIds.value = val ? paginatedLogs.value.map(log => log.ip_address) : [];
            }
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: selectedLogIds.value.includes(row.original.ip_address),
            'onUpdate:checked': (val: boolean) => {
                if (val) {
                    selectedLogIds.value.push(row.original.ip_address);
                } else {
                    selectedLogIds.value = selectedLogIds.value.filter(id => id !== row.original.ip_address);
                }
            }
        })
    }),
    columnHelper.accessor('event_type', {
        header: t('features.security.logs.table.event'),
        cell: ({ row }) => h(Badge, { class: getEventClass(row.original.event_type), variant: 'outline' }, () => getEventLabel(row.original.event_type))
    }),
    columnHelper.accessor('ip_address', {
        header: t('features.security.logs.table.ip'),
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.original.ip_address)
    }),
    columnHelper.accessor(row => row.user?.name, {
        id: 'user',
        header: t('features.security.logs.table.user'),
        cell: ({ row }) => h('span', { class: 'text-sm' }, row.original.user?.name || '-')
    }),
    columnHelper.accessor('details', {
        header: t('features.security.logs.table.details'),
        cell: ({ row }) => h('span', { class: 'max-w-xs truncate text-muted-foreground text-sm', title: row.original.details }, row.original.details)
    }),
    columnHelper.accessor('created_at', {
        header: t('features.security.logs.table.date'),
        cell: ({ row }) => h('span', { class: 'text-muted-foreground whitespace-nowrap text-sm' }, formatDate(row.original.created_at))
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('features.security.logs.table.actions')),
        cell: ({ row }) => h('div', { class: 'text-right' }, [
            h(Button, {
                variant: 'ghost',
                size: 'sm',
                class: 'text-destructive hover:text-destructive hover:bg-destructive/10 h-8',
                onClick: () => emit('block-from-log', row.original.ip_address)
            }, () => t('features.security.logs.actions.blockIp'))
        ])
    })
];

const table = useVueTable({
    get data() { return paginatedLogs.value },
    columns,
    state: {
        get sorting() { return sorting.value }
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
});

// Expose methods for parent
defineExpose({
    setIpStatus: (status: IpStatus | null) => { ipStatus.value = status; },
    clearSelection: () => { selectedLogIds.value = []; }
});
</script>
