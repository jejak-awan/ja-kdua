<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">{{ t('isp.billing.payments.title') }}</h1>
                <p class="text-muted-foreground">{{ t('isp.billing.payments.subtitle') }}</p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="exportPayments">
                    <Download class="w-4 h-4 mr-2" />
                    {{ t('common.actions.export') }}
                </Button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>{{ t('isp.billing.payments.stats.today') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600">{{ formatCurrency(stats.today) }}</div>
                    <p class="text-xs text-muted-foreground">{{ stats.todayCount }} {{ t('isp.billing.payments.transactions') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>{{ t('isp.billing.payments.stats.this_month') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatCurrency(stats.month) }}</div>
                    <p class="text-xs text-muted-foreground">{{ stats.monthCount }} {{ t('isp.billing.payments.transactions') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>{{ t('isp.billing.payments.stats.pending') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-yellow-600">{{ formatCurrency(stats.pending) }}</div>
                    <p class="text-xs text-muted-foreground">{{ stats.pendingCount }} {{ t('isp.billing.payments.awaiting') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>{{ t('isp.billing.payments.stats.failed') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-red-600">{{ formatCurrency(stats.failed) }}</div>
                    <p class="text-xs text-muted-foreground">{{ stats.failedCount }} {{ t('isp.billing.payments.failed_tx') }}</p>
                </CardContent>
            </Card>
        </div>

        <!-- Filters -->
        <Card>
            <CardContent class="pt-6">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <Input
                            v-model="filters.search"
                            :placeholder="t('isp.billing.payments.search_placeholder')"
                            class="w-full"
                        />
                    </div>
                    <Select v-model="filters.status">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue :placeholder="t('common.status.all')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('common.status.all') }}</SelectItem>
                            <SelectItem value="success">{{ t('isp.billing.payments.status.success') }}</SelectItem>
                            <SelectItem value="pending">{{ t('isp.billing.payments.status.pending') }}</SelectItem>
                            <SelectItem value="failed">{{ t('isp.billing.payments.status.failed') }}</SelectItem>
                            <SelectItem value="expired">{{ t('isp.billing.payments.status.expired') }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filters.gateway">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue :placeholder="t('isp.billing.payments.all_gateways')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('isp.billing.payments.all_gateways') }}</SelectItem>
                            <SelectItem value="midtrans">Midtrans</SelectItem>
                            <SelectItem value="xendit">Xendit</SelectItem>
                            <SelectItem value="manual">Manual</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardContent>
        </Card>

        <!-- Payments Table -->
        <Card>
            <CardContent class="p-0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('isp.billing.payments.columns.id') }}</TableHead>
                            <TableHead>{{ t('isp.billing.payments.columns.customer') }}</TableHead>
                            <TableHead>{{ t('isp.billing.payments.columns.invoice') }}</TableHead>
                            <TableHead>{{ t('isp.billing.payments.columns.amount') }}</TableHead>
                            <TableHead>{{ t('isp.billing.payments.columns.gateway') }}</TableHead>
                            <TableHead>{{ t('isp.billing.payments.columns.status') }}</TableHead>
                            <TableHead>{{ t('isp.billing.payments.columns.date') }}</TableHead>
                            <TableHead class="text-right">{{ t('common.actions.title') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="payment in payments" :key="payment.id">
                            <TableCell class="font-mono text-sm">{{ payment.transaction_id }}</TableCell>
                            <TableCell>
                                <div class="font-medium">{{ payment.customer_name }}</div>
                                <div class="text-xs text-muted-foreground">{{ payment.customer_id }}</div>
                            </TableCell>
                            <TableCell>
                                <a href="#" class="text-primary hover:underline">{{ payment.invoice_number }}</a>
                            </TableCell>
                            <TableCell class="font-medium">{{ formatCurrency(payment.amount) }}</TableCell>
                            <TableCell>
                                <Badge variant="outline">{{ payment.gateway }}</Badge>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="getStatusVariant(payment.status)">
                                    {{ t(`isp.billing.payments.status.${payment.status}`) }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-muted-foreground">{{ formatDate(payment.created_at) }}</TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" @click="viewPayment(payment)">
                                    <Eye class="w-4 h-4" />
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="payments.length === 0">
                            <TableCell colspan="8" class="text-center py-8 text-muted-foreground">
                                {{ t('isp.billing.payments.no_data') }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <!-- Pagination -->
        <div class="flex justify-center">
            <Pagination
                v-if="pagination.total > 0"
                :current-page="pagination.currentPage"
                :total-pages="pagination.totalPages"
                :items-per-page="pagination.perPage"
                :total-items="pagination.total"
                @page-change="handlePageChange"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import {
    Card, CardContent, CardHeader, CardDescription,
    Table, TableHeader, TableBody, TableRow, TableHead, TableCell,
    Button, Input, Badge, Pagination,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';
import api from '@/services/api';

const { t } = useI18n();

interface Payment {
    id: number;
    transaction_id: string;
    customer_id: string;
    customer_name: string;
    invoice_number: string;
    amount: number;
    gateway: string;
    status: 'success' | 'pending' | 'failed' | 'expired';
    created_at: string;
}

const payments = ref<Payment[]>([]);
const loading = ref(false);

const stats = reactive({
    today: 0,
    todayCount: 0,
    month: 0,
    monthCount: 0,
    pending: 0,
    pendingCount: 0,
    failed: 0,
    failedCount: 0
});

const filters = reactive({
    search: '',
    status: 'all',
    gateway: 'all'
});

const pagination = reactive({
    currentPage: 1,
    totalPages: 1,
    perPage: 15,
    total: 0
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
        success: 'default',
        pending: 'secondary',
        failed: 'destructive',
        expired: 'outline'
    };
    return variants[status] || 'outline';
};

const loadPayments = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/isp/payments', { params: { ...filters, page: pagination.currentPage } });
        if (response.data.success) {
            payments.value = response.data.data.items || [];
            pagination.total = response.data.data.total || 0;
            pagination.totalPages = response.data.data.last_page || 1;
            Object.assign(stats, response.data.data.stats || {});
        }
    } catch (error) {
        console.error('Failed to load payments:', error);
    } finally {
        loading.value = false;
    }
};

const handlePageChange = (page: number) => {
    pagination.currentPage = page;
    loadPayments();
};

const exportPayments = () => {
    window.open(`/api/admin/isp/payments/export?${new URLSearchParams(filters as Record<string, string>).toString()}`, '_blank');
};

const viewPayment = (payment: Payment) => {
    console.warn('View payment:', payment);
};

onMounted(() => {
    loadPayments();
});
</script>
