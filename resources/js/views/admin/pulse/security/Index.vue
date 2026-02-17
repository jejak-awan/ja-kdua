<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <router-link to="/admin/journal-dashboard">
                        <ArrowLeft class="w-5 h-5" />
                    </router-link>
                </Button>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.security.title') }}</h1>
            </div>
            <div class="flex items-center space-x-2">
                <Button 
                    v-if="showClearButton" 
                    variant="destructive" 
                    @click="handleClearLogs"
                >
                    <Trash2 class="w-4 h-4 mr-2" />
                    {{ $t('features.system.logs.clear') }}
                </Button>
                <Button @click="refreshAll" :disabled="loading">
                    <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                    <RefreshCw v-else class="w-4 h-4 mr-2" />
                    <span>{{ $t('common.actions.refresh') }}</span>
                </Button>
            </div>
        </div>

        <div class="w-full">
            <Tabs v-model="activeTab" class="w-full">
                <div class="mb-10 flex items-center justify-between">
                    <TabsList class="bg-transparent p-0 h-auto gap-0">
                        <TabsTrigger value="overview" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                            <BarChart3 class="w-4 h-4 mr-2" />
                            {{ $t('features.security.tabs.overview') }}
                        </TabsTrigger>
                        <TabsTrigger value="blocklist" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                            <ShieldX class="w-4 h-4 mr-2" />
                            {{ $t('features.security.tabs.blocklist') }}
                        </TabsTrigger>
                        <TabsTrigger value="whitelist" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                            <ShieldCheck class="w-4 h-4 mr-2" />
                            {{ $t('features.security.tabs.whitelist') }}
                        </TabsTrigger>
                        <TabsTrigger value="csp-reports" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                            <FileWarning class="w-4 h-4 mr-2" />
                            {{ $t('features.security.tabs.cspReports') }}
                        </TabsTrigger>
                        <TabsTrigger value="slow-queries" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                            <Timer class="w-4 h-4 mr-2" />
                            {{ $t('features.security.tabs.slowQueries') }}
                        </TabsTrigger>
                        <TabsTrigger value="vulnerabilities" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                            <ShieldAlert class="w-4 h-4 mr-2" />
                            {{ $t('features.security.tabs.vulnerabilities') }}
                        </TabsTrigger>
                        <TabsTrigger value="shield-journal" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                            <ShieldCheck class="w-4 h-4 mr-2" />
                            {{ $t('features.security.tabs.shieldJournal') }}
                        </TabsTrigger>
                    </TabsList>
                </div>

                <!-- Overview Tab -->
                <TabsContent value="overview">
                    <OverviewTab
                        ref="overviewTabRef"
                        :logs="logs"
                        :statistics="statistics"
                        :loading="loading"
                        :blocklist-count="blocklist.length"
                        :whitelist-count="whitelist.length"
                        @block-ip="blockIP"
                        @check-ip="checkIPStatus"
                        @block-from-log="blockIPFromLog"
                        @bulk-block="bulkBlockFromLogs"
                    />
                </TabsContent>

                <!-- Blocklist Tab -->
                <TabsContent value="blocklist">
                    <BlocklistTab
                        ref="blocklistTabRef"
                        :blocklist="blocklist"
                        :loading="loading"
                        @remove="removeFromBlocklist"
                        @move-to-whitelist="moveToWhitelist"
                        @bulk-unblock="bulkUnblock"
                    />
                </TabsContent>

                <!-- Whitelist Tab -->
                <TabsContent value="whitelist">
                    <WhitelistTab
                        ref="whitelistTabRef"
                        :whitelist="whitelist"
                        :loading="loading"
                        @add="addToWhitelist"
                        @remove="removeFromWhitelist"
                        @bulk-remove="bulkRemoveWhitelist"
                    />
                </TabsContent>

                <!-- CSP Reports Tab -->
                <TabsContent value="csp-reports">
                    <CspReportsTab
                        :reports="cspReports"
                        :stats="cspStats"
                        :loading="cspLoading"
                        :pagination="cspPagination"
                        :filters="cspFilters"
                        @refresh="fetchCspReports"
                        @apply-filters="applyCspFilters"
                        @reset-filters="resetCspFilters"
                        @bulk-action="cspBulkAction"
                        @page-change="(val) => { cspFilters.page = val; fetchCspReports(); }"
                        @per-page-change="(val) => { cspFilters.per_page = val; cspFilters.page = 1; fetchCspReports(); }"
                    />
                </TabsContent>

                <!-- Slow Queries Tab -->
                <TabsContent value="slow-queries">
                    <SlowQueriesTab
                        :queries="slowQueries"
                        :stats="slowQueryStats"
                        :loading="slowQueryLoading"
                        :pagination="slowQueryPagination"
                        :filters="slowQueryFilters"
                        @refresh="fetchSlowQueries"
                        @apply-filters="applySlowQueryFilters"
                        @reset-filters="resetSlowQueryFilters"
                        @page-change="(val) => { slowQueryFilters.page = val; fetchSlowQueries(); }"
                        @per-page-change="(val) => { slowQueryFilters.per_page = val; slowQueryFilters.page = 1; fetchSlowQueries(); }"
                    />
                </TabsContent>

                <!-- Vulnerabilities Tab -->
                <TabsContent value="vulnerabilities">
                    <VulnerabilitiesTab
                        :vulnerabilities="vulnerabilities"
                        :stats="vulnStats"
                        :loading="vulnLoading"
                        :audit-running="auditRunning"
                        :pagination="vulnPagination"
                        :filters="vulnFilters"
                        @refresh="fetchVulnerabilities"
                        @run-audit="runDependencyAudit"
                        @apply-filters="applyVulnFilters"
                        @reset-filters="resetVulnFilters"
                        @update-status="updateVulnStatus"
                        @page-change="(val) => { vulnFilters.page = val; fetchVulnerabilities(); }"
                        @per-page-change="(val) => { vulnFilters.per_page = val; vulnFilters.page = 1; fetchVulnerabilities(); }"
                    />
                </TabsContent>

                <!-- Shield Journal Tab -->
                <TabsContent value="shield-journal">
                    <ShieldJournalTab
                        :logs="shieldLogs"
                        :stats="shieldStats"
                        :loading="shieldLoading"
                        :pagination="shieldPagination"
                        @refresh="fetchShieldLogs"
                        @page-change="(val) => { shieldPage = val; fetchShieldLogs(); }"
                        @block-ip="blockIP"
                    />
                </TabsContent>
            </Tabs>
        </div>
    </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, reactive, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { parseResponse, ensureArray, parseSingleResponse } from '@/utils/responseParser';
import {
    Tabs, TabsList, TabsTrigger, TabsContent, Button
} from '@/components/ui';

// Tab Components
import OverviewTab from './components/OverviewTab.vue';
import BlocklistTab from './components/BlocklistTab.vue';
import WhitelistTab from './components/WhitelistTab.vue';
import CspReportsTab from './components/CspReportsTab.vue';
import SlowQueriesTab from './components/SlowQueriesTab.vue';
import VulnerabilitiesTab from './components/VulnerabilitiesTab.vue';
import ShieldJournalTab from './components/ShieldJournalTab.vue';

// Icons
import ShieldAlert from 'lucide-vue-next/dist/esm/icons/shield-alert.js';
import ShieldX from 'lucide-vue-next/dist/esm/icons/shield-x.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import BarChart3 from 'lucide-vue-next/dist/esm/icons/chart-bar-stacked.js';
import FileWarning from 'lucide-vue-next/dist/esm/icons/file-x.js';
import Timer from 'lucide-vue-next/dist/esm/icons/timer.js';

import type { ShieldLog, ShieldStats, PaginationInfo as SecurityPaginationInfo } from '@/types/security';

// Types
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

interface IpManagementItem {
    id: number | string;
    ip_address: string;
    reason?: string | null;
    creator?: User | null;
    created_at: string;
}

interface IpStatus {
    is_blocked: boolean;
    reason?: string | null;
}

interface CspReport {
    id: number;
    violated_directive: string;
    blocked_uri: string | null;
    document_uri: string;
    ip_address: string;
    status: string;
    created_at: string;
}

interface CspStats {
    total?: number;
    new?: number;
    by_directive?: { violated_directive: string; count: number }[];
    recent_trend?: { date: string; count: number }[];
}

interface SlowQuery {
    id: number;
    route: string | null;
    duration: number;
    user_id?: number | null;
    user?: User | null;
    query: string;
    created_at: string;
}

interface SlowQueryStats {
    total?: number;
    avg_duration?: number;
    max_duration?: number;
    today?: number;
}

interface Vulnerability {
    id: number;
    package_name: string;
    version: string;
    severity: string;
    cve: string | null;
    source: string;
    status: string;
}

interface VulnStats {
    total: number;
    critical: number;
    high: number;
    medium: number;
    low: number;
}

interface PaginationInfo {
    total: number;
    current_page: number;
    last_page: number;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

// Refs for child components
const overviewTabRef = ref<InstanceType<typeof OverviewTab> | null>(null);
const blocklistTabRef = ref<InstanceType<typeof BlocklistTab> | null>(null);
const whitelistTabRef = ref<InstanceType<typeof WhitelistTab> | null>(null);

// Core Data
const logs = ref<Log[]>([]);
const statistics = ref<Statistics | null>(null);
const blocklist = ref<IpManagementItem[]>([]);
const whitelist = ref<IpManagementItem[]>([]);
const loading = ref(false);
const activeTab = ref('overview');

// CSP Reports
const cspReports = ref<CspReport[]>([]);
const cspStats = ref<CspStats | null>(null);
const cspLoading = ref(false);
const cspPagination = ref<PaginationInfo>({ total: 0, current_page: 1, last_page: 1 });

// Slow Queries
const slowQueries = ref<SlowQuery[]>([]);
const slowQueryStats = ref<SlowQueryStats | null>(null);
const slowQueryLoading = ref(false);
const slowQueryPagination = ref<PaginationInfo>({ total: 0, current_page: 1, last_page: 1 });

// Vulnerabilities
const vulnerabilities = ref<Vulnerability[]>([]);
const vulnStats = ref<VulnStats>({ total: 0, critical: 0, high: 0, medium: 0, low: 0 });
const vulnLoading = ref(false);
const auditRunning = ref(false);
const vulnPagination = ref<PaginationInfo>({ total: 0, current_page: 1, last_page: 1 });

// Shield Journal
const shieldLogs = ref<ShieldLog[]>([]);
const shieldStats = ref<ShieldStats>({ verifications: 0, failures: 0, honeypot: 0, currentDifficulty: 4, isScaling: false });
const shieldLoading = ref(false);
const shieldPagination = ref<SecurityPaginationInfo>({ total: 0, current_page: 1, last_page: 1 });
const shieldPage = ref(1);

// Filters state
const cspFilters = reactive({ status: 'new', directive: '', date_from: '', date_to: '', page: 1, per_page: 50 });
const slowQueryFilters = reactive({ route: '', min_duration: '', date_from: '', date_to: '', page: 1, per_page: 50 });
const vulnFilters = reactive({ source: 'all', severity: 'all', status: 'all', package: '', page: 1, per_page: 50 });

// ========================================
// CORE FETCH FUNCTIONS
// ========================================
const fetchShieldLogs = async (): Promise<void> => {
    shieldLoading.value = true;
    try {
        const response = await api.get('/admin/janet/security/shield/journal', { params: { page: shieldPage.value } });
        const result = response.data?.data ? response.data.data : response.data;
        shieldLogs.value = (result.data as ShieldLog[]) || [];
        shieldPagination.value = { total: result.total || 0, current_page: result.current_page || 1, last_page: result.last_page || 1 };
    } catch (error: unknown) {
        logger.error('Failed to fetch shield logs:', error);
    } finally {
        shieldLoading.value = false;
    }
};

const fetchShieldStats = async (): Promise<void> => {
    try {
        const response = await api.get('/admin/janet/security/shield/stats');
        shieldStats.value = (response.data?.data as ShieldStats) || { verifications: 0, failures: 0, honeypot: 0, currentDifficulty: 4, isScaling: false };
    } catch (error: unknown) {
        logger.error('Failed to fetch shield stats:', error);
    }
};
const fetchLogs = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get('/admin/janet/security/journal', { params: { per_page: 100 } });
        const { data } = parseResponse<Log[]>(response);
        logs.value = ensureArray(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch logs:', error);
    } finally {
        loading.value = false;
    }
};

const clearLogs = async (): Promise<void> => {
    const confirmed = await confirm({
        title: t('features.system.logs.actions.clear'),
        message: t('features.system.logs.confirm.clear'),
        variant: 'danger',
        confirmText: t('common.actions.clear'),
    });
    if (!confirmed) return;
    try {
        await api.delete('/admin/janet/security/journal');
        toast.success.action(t('features.system.logs.messages.cleared'));
        fetchLogs();
    } catch (error: unknown) {
        logger.error('Failed to clear logs:', error);
        toast.error.fromResponse(error);
    }
};

const clearShieldLogs = async (): Promise<void> => {
    const confirmed = await confirm({
        title: t('common.actions.clear'),
        message: t('common.dialogs.confirmDelete', 'Are you sure you want to clear all shield logs?'),
        variant: 'danger',
        confirmText: t('common.actions.clear'),
    });
    if (!confirmed) return;

    try {
        await api.post('/security/shield/clear');
        toast.success.default(t('features.security.logs.cleared', 'Shield logs cleared successfully'));
        fetchShieldLogs();
    } catch (error) {
        toast.error.default(t('common.errors.generic'));
    }
};

const handleClearLogs = () => {
    if (activeTab.value === 'shield-journal') {
        clearShieldLogs();
    } else {
        clearLogs();
    }
};

const showClearButton = computed(() => {
    return ['overview', 'shield-journal'].includes(activeTab.value);
});

const fetchStats = async (): Promise<void> => {
    try {
        const response = await api.get('/admin/janet/security/stats');
        statistics.value = parseSingleResponse<Statistics>(response) as Statistics || null;
    } catch (error: unknown) {
        logger.error('Failed to fetch stats:', error);
    }
};

const fetchBlocklist = async (): Promise<void> => {
    try {
        const response = await api.get('/admin/janet/security/blocklist');
        blocklist.value = ensureArray(parseSingleResponse<IpManagementItem[]>(response)) as IpManagementItem[];
    } catch (error: unknown) {
        logger.error('Failed to fetch blocklist:', error);
    }
};

const fetchWhitelist = async (): Promise<void> => {
    try {
        const response = await api.get('/admin/janet/security/whitelist');
        whitelist.value = ensureArray(parseSingleResponse<IpManagementItem[]>(response)) as IpManagementItem[];
    } catch (error: unknown) {
        logger.error('Failed to fetch whitelist:', error);
    }
};

// ========================================
// IP ACTIONS (from OverviewTab)
// ========================================
const blockIP = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('features.security.ipManagement.block.button'),
        message: t('features.security.messages.confirmBlock', { ip }),
        variant: 'danger',
        confirmText: t('features.security.ipManagement.block.button'),
    });
    if (!confirmed) return;
    try {
        await api.post('/admin/janet/security/block-ip', { ip_address: ip });
        toast.success.action(t('features.security.messages.blockSuccess'));
        await fetchBlocklist();
        await fetchLogs();
    } catch (error: unknown) {
        logger.error('Failed to block IP:', error);
        toast.error.fromResponse(error);
    }
};

const checkIPStatus = async (ip: string): Promise<void> => {
    try {
        const response = await api.get('/admin/janet/security/check-ip', { params: { ip_address: ip } });
        const status = parseSingleResponse<IpStatus>(response) as IpStatus || null;
        overviewTabRef.value?.setIpStatus(status);
    } catch (error: unknown) {
        logger.error('Failed to check IP status:', error);
        toast.error.fromResponse(error);
    }
};

const blockIPFromLog = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('features.security.logs.actions.blockIp'),
        message: t('features.security.messages.confirmBlock', { ip }),
        variant: 'danger',
        confirmText: t('features.security.logs.actions.blockIp'),
    });
    if (!confirmed) return;
    try {
        await api.post('/admin/janet/security/block-ip', { ip_address: ip });
        toast.success.action(t('features.security.messages.blockSuccess'));
        await fetchBlocklist();
        await fetchLogs();
    } catch (error: unknown) {
        logger.error('Failed to block IP:', error);
        toast.error.fromResponse(error);
    }
};

const bulkBlockFromLogs = async (ips: string[]): Promise<void> => {
    if (ips.length === 0) return;
    const confirmed = await confirm({
        title: t('features.security.bulkActions.blockSelected'),
        message: t('features.security.messages.confirmBulkBlock', { count: ips.length }),
        variant: 'danger',
        confirmText: t('features.security.bulkActions.blockSelected'),
    });
    if (!confirmed) return;
    try {
        await api.post('/admin/janet/security/bulk-block', { ips });
        toast.success.action(t('features.security.messages.bulkBlockSuccess'));
        overviewTabRef.value?.clearSelection();
        await fetchBlocklist();
        await fetchLogs();
    } catch (error: unknown) {
        logger.error('Failed to bulk block:', error);
        toast.error.fromResponse(error);
    }
};

// ========================================
// BLOCKLIST ACTIONS
// ========================================
const removeFromBlocklist = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('features.security.blocklist.actions.unblock'),
        message: t('features.security.messages.confirmUnblock', { ip }),
        variant: 'warning',
        confirmText: t('features.security.blocklist.actions.unblock'),
    });
    if (!confirmed) return;
    try {
        await api.post('/admin/janet/security/unblock-ip', { ip_address: ip });
        toast.success.action(t('features.security.messages.unblockSuccess'));
        await fetchBlocklist();
    } catch (error: unknown) {
        logger.error('Failed to remove from blocklist:', error);
        toast.error.fromResponse(error);
    }
};

const moveToWhitelist = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('features.security.blocklist.actions.moveToWhitelist'),
        message: t('features.security.messages.confirmMoveToWhitelist', { ip }),
        variant: 'info',
        confirmText: t('common.actions.move'),
    });
    if (!confirmed) return;
    try {
        await api.post('/admin/janet/security/unblock-ip', { ip_address: ip });
        await api.post('/admin/janet/security/whitelist-ip', { ip_address: ip });
        toast.success.action(t('features.security.messages.movedToWhitelist'));
        await fetchBlocklist();
        await fetchWhitelist();
    } catch (error: unknown) {
        logger.error('Failed to move to whitelist:', error);
        toast.error.fromResponse(error);
    }
};

const bulkUnblock = async (ips: string[]): Promise<void> => {
    if (ips.length === 0) return;
    const confirmed = await confirm({
        title: t('features.security.bulkActions.unblockSelected'),
        message: t('features.security.messages.confirmBulkUnblock', { count: ips.length }),
        variant: 'warning',
        confirmText: t('features.security.bulkActions.unblockSelected'),
    });
    if (!confirmed) return;
    try {
        await api.post('/admin/janet/security/bulk-unblock', { ips });
        toast.success.action(t('features.security.messages.bulkUnblockSuccess'));
        blocklistTabRef.value?.clearSelection();
        await fetchBlocklist();
    } catch (error: unknown) {
        logger.error('Failed to bulk unblock:', error);
        toast.error.fromResponse(error);
    }
};

// ========================================
// WHITELIST ACTIONS
// ========================================
const addToWhitelist = async (ip: string): Promise<void> => {
    try {
        await api.post('/admin/janet/security/whitelist-ip', { ip_address: ip });
        toast.success.action(t('features.security.messages.whitelistSuccess'));
        await fetchWhitelist();
    } catch (error: unknown) {
        logger.error('Failed to add to whitelist:', error);
        toast.error.fromResponse(error);
    }
};

const removeFromWhitelist = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('features.security.whitelist.actions.remove'),
        message: t('features.security.messages.confirmRemoveWhitelist', { ip }),
        variant: 'danger',
        confirmText: t('common.actions.remove'),
    });
    if (!confirmed) return;
    try {
        await api.post('/admin/janet/security/remove-whitelist', { data: { ip_address: ip } });
        toast.success.action(t('features.security.messages.whitelistRemoveSuccess'));
        await fetchWhitelist();
    } catch (error: unknown) {
        logger.error('Failed to remove from whitelist:', error);
        toast.error.fromResponse(error);
    }
};

const bulkRemoveWhitelist = async (ips: string[]): Promise<void> => {
    if (ips.length === 0) return;
    const confirmed = await confirm({
        title: t('features.security.bulkActions.removeSelected'),
        message: t('features.security.messages.confirmBulkRemoveWhitelist', { count: ips.length }),
        variant: 'danger',
        confirmText: t('common.actions.remove'),
    });
    if (!confirmed) return;
    try {
        await api.post('/admin/janet/security/bulk-remove-whitelist', { ips });
        toast.success.action(t('features.security.messages.bulkWhitelistRemoveSuccess'));
        whitelistTabRef.value?.clearSelection();
        await fetchWhitelist();
    } catch (error: unknown) {
        logger.error('Failed to bulk remove whitelist:', error);
        toast.error.fromResponse(error);
    }
};

// ========================================
// CSP REPORTS
// ========================================
const fetchCspReports = async (): Promise<void> => {
    cspLoading.value = true;
    try {
        const params: Record<string, string | number> = { ...cspFilters };
        if (params.status === 'all') params.status = '';
        const response = await api.get('/admin/janet/security/csp-reports', { params });
        const result = response.data?.data ? response.data.data : response.data;
        cspReports.value = (result.data as CspReport[]) || [];
        cspPagination.value = { total: result.total || 0, current_page: result.current_page || 1, last_page: result.last_page || 1 };
    } catch (error: unknown) {
        logger.error('Failed to fetch CSP reports:', error);
    } finally {
        cspLoading.value = false;
    }
};

const fetchCspStats = async (): Promise<void> => {
    try {
        const response = await api.get('/admin/janet/security/csp-reports/statistics');
        cspStats.value = (response.data?.data as CspStats) || {};
    } catch (error: unknown) {
        logger.error('Failed to fetch CSP stats:', error);
    }
};

const applyCspFilters = (): void => { cspFilters.page = 1; fetchCspReports(); };
const resetCspFilters = (): void => {
    cspFilters.status = 'all';
    cspFilters.directive = '';
    cspFilters.page = 1;
    fetchCspReports();
};

const cspBulkAction = async (action: string, ids: number[]): Promise<void> => {
    if (ids.length === 0) return;
    const confirmed = await confirm({
        title: t('common.actions.confirm'),
        message: t('features.security.cspReports.confirmBulkAction', { count: ids.length, action: action.replace('_', ' ') }),
        variant: 'danger',
        confirmText: t('common.actions.confirm'),
    });
    if (!confirmed) return;
    try {
        await api.post('/admin/janet/security/csp-reports/bulk-action', { ids, action });
        toast.success.action(t('common.messages.success.actionSuccess', { item: 'Reports', action: action.replace('_', ' ') }));
        fetchCspReports();
        fetchCspStats();
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    }
};

// ========================================
// SLOW QUERIES
// ========================================
const fetchSlowQueries = async (): Promise<void> => {
    slowQueryLoading.value = true;
    try {
        const response = await api.get('/admin/janet/security/slow-queries', { params: slowQueryFilters });
        slowQueries.value = (response.data?.data?.data as SlowQuery[]) || [];
        slowQueryPagination.value = { total: response.data?.data?.total || 0, current_page: response.data?.data?.current_page || 1, last_page: response.data?.data?.last_page || 1 };
    } catch (error: unknown) {
        logger.error('Failed to fetch slow queries:', error);
    } finally {
        slowQueryLoading.value = false;
    }
};

const fetchSlowQueryStats = async (): Promise<void> => {
    try {
        const response = await api.get('/admin/janet/security/slow-queries/statistics');
        slowQueryStats.value = (response.data?.data as SlowQueryStats) || {};
    } catch (error: unknown) {
        logger.error('Failed to fetch slow query stats:', error);
    }
};

const applySlowQueryFilters = (): void => {
    slowQueryFilters.page = 1;
    fetchSlowQueries();
};
const resetSlowQueryFilters = (): void => {
    Object.assign(slowQueryFilters, { route: '', min_duration: '', date_from: '', date_to: '', page: 1 });
    fetchSlowQueries();
};

// ========================================
// VULNERABILITIES
// ========================================
const fetchVulnerabilities = async (): Promise<void> => {
    vulnLoading.value = true;
    try {
        const params: Record<string, string | number> = { ...vulnFilters };
        if (params.source === 'all') params.source = '';
        if (params.severity === 'all') params.severity = '';
        if (params.status === 'all') params.status = '';
        const response = await api.get('/admin/janet/security/dependency-vulnerabilities', { params });
        const result = response.data?.data ? response.data.data : response.data;
        vulnerabilities.value = (result.data as Vulnerability[]) || [];
        vulnPagination.value = { total: result.total || 0, current_page: result.current_page || 1, last_page: result.last_page || 1 };
    } catch (error: unknown) {
        logger.error('Failed to fetch vulnerabilities:', error);
    } finally {
        vulnLoading.value = false;
    }
};

const fetchVulnStats = async (): Promise<void> => {
    try {
        const response = await api.get('/admin/janet/security/dependency-vulnerabilities/statistics');
        vulnStats.value = (response.data?.data as VulnStats) || { total: 0, critical: 0, high: 0, medium: 0, low: 0 };
    } catch (error: unknown) {
        logger.error('Failed to fetch vulnerability stats:', error);
    }
};

const runDependencyAudit = async (): Promise<void> => {
    auditRunning.value = true;
    try {
        await api.post('/admin/janet/security/run-dependency-audit');
        toast.success.action(t('features.security.vulnerabilities.auditCompleted'));
        fetchVulnerabilities();
        fetchVulnStats();
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    } finally {
        auditRunning.value = false;
    }
};

const updateVulnStatus = async (vuln: Vulnerability, status: string): Promise<void> => {
    try {
        await api.put(`/admin/janet/security/dependency-vulnerabilities/${vuln.id}`, { status });
        vuln.status = status;
        toast.success.action(t('common.messages.success.updated', { item: 'Status' }));
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    }
};

const applyVulnFilters = (): void => {
    vulnFilters.page = 1;
    fetchVulnerabilities();
};
const resetVulnFilters = (): void => {
    Object.assign(vulnFilters, { source: 'all', severity: 'all', status: 'all', package: '', page: 1 });
    fetchVulnerabilities();
};

// ========================================
// LIFECYCLE & WATCHERS
// ========================================
const refreshAll = async (): Promise<void> => {
    await Promise.all([fetchLogs(), fetchStats(), fetchBlocklist(), fetchWhitelist()]);
    if (activeTab.value === 'shield-journal') {
        await Promise.all([fetchShieldLogs(), fetchShieldStats()]);
    }
};

watch(activeTab, (newTab: string) => {
    if (newTab === 'csp-reports' && cspReports.value.length === 0) {
        fetchCspReports();
        fetchCspStats();
    } else if (newTab === 'slow-queries' && slowQueries.value.length === 0) {
        fetchSlowQueries();
        fetchSlowQueryStats();
    } else if (newTab === 'vulnerabilities' && vulnerabilities.value.length === 0) {
        fetchVulnerabilities();
        fetchVulnStats();
    } else if (newTab === 'shield-journal' && shieldLogs.value.length === 0) {
        fetchShieldLogs();
        fetchShieldStats();
    }
});

onMounted(() => {
    fetchLogs();
    fetchStats();
    fetchBlocklist();
    fetchWhitelist();
});
</script>
