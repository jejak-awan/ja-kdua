<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.newsletter.title') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.newsletter.subtitle') }}</p>
            </div>
            <div class="flex gap-2">
                <Button
                    variant="outline"
                    @click="exportCsv"
                >
                    <Download class="w-4 h-4 mr-2" />
                    {{ $t('features.newsletter.actions.export') }}
                </Button>
            </div>
        </div>

        <Card>
            <!-- Filters & Search -->
            <div class="p-4 border-b border-border flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <Select
                        v-model="filters.status"
                        @update:modelValue="fetchSubscribers"
                    >
                        <SelectTrigger class="w-[180px]">
                            <SelectValue :placeholder="$t('features.newsletter.filters.allStatus')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ $t('features.newsletter.filters.allStatus') }}</SelectItem>
                            <SelectItem value="subscribed">{{ $t('features.newsletter.filters.subscribed') }}</SelectItem>
                            <SelectItem value="unsubscribed">{{ $t('features.newsletter.filters.unsubscribed') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="relative max-w-xs w-full">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input
                        v-model="filters.q"
                        @input="debounceSearch"
                        :placeholder="$t('features.newsletter.filters.search')"
                        class="pl-9"
                    />
                </div>
            </div>

            <!-- Table -->
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ $t('features.newsletter.table.subscriber') }}</TableHead>
                        <TableHead>{{ $t('features.newsletter.table.status') }}</TableHead>
                        <TableHead>{{ $t('features.newsletter.table.joinedAt') }}</TableHead>
                        <TableHead>{{ $t('features.newsletter.table.source') }}</TableHead>
                        <TableHead class="text-center">{{ $t('features.newsletter.table.actions') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="loading">
                        <TableCell colspan="5" class="h-24 text-center">
                            {{ $t('features.newsletter.messages.loading') }}
                        </TableCell>
                    </TableRow>
                    <TableRow v-else-if="subscribers.length === 0">
                        <TableCell colspan="5" class="h-24 text-center">
                            {{ $t('features.newsletter.messages.empty') }}
                        </TableCell>
                    </TableRow>
                    <TableRow v-for="subscriber in subscribers" :key="subscriber.id">
                        <TableCell>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-9 w-9">
                                    <div class="h-9 w-9 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold text-xs border border-primary/20">
                                        {{ (subscriber.name || subscriber.email).charAt(0).toUpperCase() }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-foreground">
                                        {{ subscriber.name || $t('features.newsletter.messages.noName') }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ subscriber.email }}
                                    </div>
                                </div>
                            </div>
                        </TableCell>
                        <TableCell>
                            <Badge
                                variant="outline"
                                :class="subscriber.status === 'subscribed' ? 'bg-green-500/10 text-green-500 border-green-500/20' : 'bg-red-500/10 text-red-500 border-red-500/20'"
                            >
                                {{ $t(`features.newsletter.filters.${subscriber.status}`) }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-xs text-muted-foreground">
                            {{ formatDate(subscriber.created_at) }}
                        </TableCell>
                        <TableCell class="text-xs text-muted-foreground truncated max-w-[150px]" :title="subscriber.source">
                            {{ subscriber.source }}
                        </TableCell>
                        <TableCell>
                            <div class="flex items-center justify-center">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="deleteSubscriber(subscriber)"
                                    class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                    :title="$t('features.newsletter.actions.delete')"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Pagination -->
            <Pagination
                v-if="pagination.total > 0"
                :current-page="pagination.current_page"
                :total-items="pagination.total"
                :per-page="filters.per_page"
                :show-page-numbers="true"
                @page-change="changePage"
                @update:per-page="changePerPage"
                class="mt-4 px-4 pb-4"
            />
        </Card>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse } from '../../../utils/responseParser';
import Card from '../../../components/ui/card.vue';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Select from '../../../components/ui/select.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import Badge from '../../../components/ui/badge.vue';
import Table from '../../../components/ui/table.vue';
import TableHeader from '../../../components/ui/table-header.vue';
import TableRow from '../../../components/ui/table-row.vue';
import TableHead from '../../../components/ui/table-head.vue';
import TableBody from '../../../components/ui/table-body.vue';
import TableCell from '../../../components/ui/table-cell.vue';
import Pagination from '../../../components/ui/pagination.vue';
import { Download, Search, Trash2 } from 'lucide-vue-next';
import _ from 'lodash';

const { t } = useI18n();

const loading = ref(false);
const subscribers = ref([]);
const pagination = ref({});
const filters = ref({
    status: 'all',
    q: '',
    page: 1,
    per_page: 15,
});

const fetchSubscribers = async () => {
    loading.value = true;
    try {
        const params = { ...filters.value };
        // Don't send 'all' status to API
        if (params.status === 'all') {
            delete params.status;
        }
        const response = await api.get('/admin/cms/newsletter/subscribers', { params });
        const { data, pagination: pag } = parseResponse(response);
        subscribers.value = data;
        if (pag) {
            pagination.value = pag;
        }
    } catch (error) {
        console.error('Failed to fetch subscribers:', error);
    } finally {
        loading.value = false;
    }
};

const debounceSearch = _.debounce(() => {
    filters.value.page = 1;
    fetchSubscribers();
}, 300);

const changePage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    filters.value.page = page;
    fetchSubscribers();
};

const changePerPage = (perPage) => {
    filters.value.per_page = perPage;
    filters.value.page = 1;
    fetchSubscribers();
};

const deleteSubscriber = async (subscriber) => {
    if (!confirm(t('features.newsletter.messages.deleteConfirm', { email: subscriber.email }))) return;

    try {
        await api.delete(`/admin/cms/newsletter/subscribers/${subscriber.id}`);
        fetchSubscribers();
    } catch (error) {
        alert(t('features.newsletter.messages.deleteFailed'));
    }
};

const exportCsv = async () => {
    try {
        const response = await api.get('/admin/cms/newsletter/export', {
            params: { status: filters.value.status },
            responseType: 'blob',
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `subscribers-${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        alert(t('features.newsletter.messages.exportFailed'));
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

onMounted(() => {
    fetchSubscribers();
});
</script>
