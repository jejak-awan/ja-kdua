<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.comments.list.title') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.comments.list.subtitle') }}</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div v-if="statistics" class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <Card class="p-4">
                <p class="text-2xl font-bold">{{ statistics.total }}</p>
                <p class="text-xs text-muted-foreground">{{ $t('features.comments.stats.total') }}</p>
            </Card>
            <Card 
                class="p-4 cursor-pointer hover:bg-yellow-500/5 transition-colors border-yellow-500/20" 
                @click="statusFilter = 'pending'"
                :class="{ 'ring-2 ring-yellow-500/50': statusFilter === 'pending' }"
            >
                <p class="text-2xl font-bold text-yellow-500">{{ statistics.pending }}</p>
                <p class="text-xs text-yellow-500/70">{{ $t('features.comments.stats.pending') }}</p>
            </Card>
            <Card 
                class="p-4 cursor-pointer hover:bg-green-500/5 transition-colors border-green-500/20" 
                @click="statusFilter = 'approved'"
                :class="{ 'ring-2 ring-green-500/50': statusFilter === 'approved' }"
            >
                <p class="text-2xl font-bold text-green-500">{{ statistics.approved }}</p>
                <p class="text-xs text-green-500/70">{{ $t('features.comments.stats.approved') }}</p>
            </Card>
            <Card 
                class="p-4 cursor-pointer hover:bg-red-500/5 transition-colors border-red-500/20" 
                @click="statusFilter = 'rejected'"
                :class="{ 'ring-2 ring-red-500/50': statusFilter === 'rejected' }"
            >
                <p class="text-2xl font-bold text-red-500">{{ statistics.rejected }}</p>
                <p class="text-xs text-red-500/70">{{ $t('features.comments.stats.rejected') }}</p>
            </Card>
            <Card 
                class="p-4 cursor-pointer hover:bg-muted/50 transition-colors" 
                @click="statusFilter = 'spam'"
                :class="{ 'ring-2 ring-muted-foreground/50': statusFilter === 'spam' }"
            >
                <p class="text-2xl font-bold text-muted-foreground">{{ statistics.spam }}</p>
                <p class="text-xs text-muted-foreground">{{ $t('features.comments.stats.spam') }}</p>
            </Card>
        </div>

        <!-- Filters -->
        <Card class="p-4 mb-6">
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        :placeholder="$t('features.comments.filter.searchPlaceholder')"
                        class="pl-9"
                    />
                </div>
                <Select v-model="statusFilter">
                    <SelectTrigger class="w-full md:w-[200px]">
                        <SelectValue :placeholder="$t('features.comments.filter.allStatus')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">{{ $t('features.newsletter.filters.allStatus') }}</SelectItem>
                        <SelectItem value="pending">{{ $t('features.comments.status.pending') }}</SelectItem>
                        <SelectItem value="approved">{{ $t('features.comments.status.approved') }}</SelectItem>
                        <SelectItem value="rejected">{{ $t('features.comments.status.rejected') }}</SelectItem>
                        <SelectItem value="spam">{{ $t('features.comments.status.spam') }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <!-- Bulk Actions -->
            <div v-if="selectedIds.length > 0" class="mt-4 flex items-center flex-wrap gap-2 pt-4 border-t">
                <span class="text-sm text-muted-foreground mr-2">{{ selectedIds.length }} selected:</span>
                <Button variant="outline" size="sm" @click="bulkAction('approve')" class="text-green-600 hover:text-green-600 hover:bg-green-500/10 border-green-500/20">
                    <Check class="w-4 h-4 mr-2" />
                    {{ $t('features.comments.actions.approveAll') }}
                </Button>
                <Button variant="outline" size="sm" @click="bulkAction('reject')" class="text-red-600 hover:text-red-600 hover:bg-red-500/10 border-red-500/20">
                    <X class="w-4 h-4 mr-2" />
                    {{ $t('features.comments.actions.rejectAll') }}
                </Button>
                <Button variant="outline" size="sm" @click="bulkAction('spam')">
                    <AlertTriangle class="w-4 h-4 mr-2" />
                    {{ $t('features.comments.actions.markSpam') }}
                </Button>
                <Button variant="destructive" size="sm" @click="bulkAction('delete')">
                    <Trash2 class="w-4 h-4 mr-2" />
                    {{ $t('common.actions.delete') }}
                </Button>
                <Button variant="ghost" size="sm" @click="selectedIds = []">
                    {{ $t('common.actions.clear') }}
                </Button>
            </div>
        </Card>

        <!-- Comments List -->
        <div v-if="loading" class="bg-card border border-border rounded-lg p-12 text-center">
            <p class="text-muted-foreground">{{ $t('common.loading.default') }}</p>
        </div>

        <Card v-else-if="comments.length === 0" class="p-12 text-center">
            <MessageSquare class="mx-auto h-12 w-12 text-muted-foreground opacity-20" />
            <p class="mt-4 text-muted-foreground">{{ $t('features.comments.list.empty') }}</p>
        </Card>

        <div v-else class="space-y-4">
            <Card
                v-for="comment in comments"
                :key="comment.id"
                class="p-0 overflow-hidden"
            >
                <div class="p-6">
                    <!-- Comment Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start space-x-3 flex-1">
                            <!-- Checkbox for selection -->
                            <div class="flex-shrink-0 pt-1">
                                <Checkbox
                                    :checked="selectedIds.includes(comment.id)"
                                    @update:checked="toggleSelection(comment.id)"
                                />
                            </div>
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center border border-primary/20">
                                    <span class="text-primary font-semibold text-sm">
                                        {{ ((comment.user?.name || comment.name || 'U')?.charAt(0) || 'U').toUpperCase() }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    <p class="text-sm font-semibold text-foreground">
                                        {{ comment.user?.name || comment.name || 'Anonymous' }}
                                    </p>
                                    <Badge
                                        variant="outline"
                                        :class="{
                                            'bg-yellow-500/10 text-yellow-500 border-yellow-500/20': comment.status === 'pending',
                                            'bg-green-500/10 text-green-500 border-green-500/20': comment.status === 'approved',
                                            'bg-red-500/10 text-red-500 border-red-500/20': comment.status === 'rejected',
                                            'bg-muted text-muted-foreground': comment.status === 'spam',
                                        }"
                                    >
                                        {{ comment.status }}
                                    </Badge>
                                </div>
                                <div class="flex items-center gap-x-3 mt-1 text-xs text-muted-foreground">
                                    <span>{{ comment.user?.email || comment.email || 'No email' }}</span>
                                    <span class="flex items-center">
                                        <clock class="w-3 h-3 mr-1" />
                                        {{ formatDate(comment.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <Button
                                v-if="comment.status === 'pending' || comment.status === 'rejected'"
                                variant="ghost"
                                size="sm"
                                @click="approveComment(comment)"
                                class="h-8 text-green-600 hover:text-green-600 hover:bg-green-500/10"
                            >
                                <Check class="w-4 h-4 mr-1" />
                                Approve
                            </Button>
                            <Button
                                v-if="comment.status === 'pending' || comment.status === 'approved'"
                                variant="ghost"
                                size="sm"
                                @click="rejectComment(comment)"
                                class="h-8 text-yellow-600 hover:text-yellow-600 hover:bg-yellow-500/10"
                            >
                                <X class="w-4 h-4 mr-1" />
                                Reject
                            </Button>
                            <Button
                                v-if="comment.status !== 'spam'"
                                variant="ghost"
                                size="sm"
                                @click="markAsSpam(comment)"
                                class="h-8 text-muted-foreground"
                            >
                                <AlertTriangle class="w-4 h-4 mr-1" />
                                Spam
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="deleteComment(comment)"
                                class="h-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                            >
                                <Trash2 class="w-4 h-4 mr-1" />
                                Delete
                            </Button>
                        </div>
                    </div>

                    <!-- Comment Body -->
                    <div class="mb-4 pl-[52px]">
                        <p class="text-sm text-foreground leading-relaxed">{{ comment.body }}</p>
                    </div>

                    <!-- Comment Meta -->
                    <div class="flex items-center justify-between text-[10px] text-muted-foreground pt-4 border-t pl-[52px]">
                        <div class="flex items-center space-x-4">
                            <span v-if="comment.content" class="flex items-center">
                                <ArrowUpRight class="w-3 h-3 mr-1" />
                                {{ $t('features.comments.list.on') }}: 
                                <router-link
                                    :to="{ name: 'contents.edit', params: { id: comment.content.id } }"
                                    class="text-primary hover:underline ml-1 font-medium"
                                >
                                    {{ comment.content.title }}
                                </router-link>
                            </span>
                            <span v-if="comment.parent" class="flex items-center">
                                <Reply class="w-3 h-3 mr-1" />
                                {{ $t('features.comments.list.replyTo') }}: <b>{{ comment.parent.user?.name || comment.parent.name || 'Anonymous' }}</b>
                            </span>
                        </div>
                        <div class="font-medium">
                            <span v-if="comment.replies_count > 0">
                                {{ comment.replies_count }} {{ comment.replies_count === 1 ? 'reply' : 'replies' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Replies (if any) -->
                <div v-if="comment.replies && comment.replies.length > 0" class="bg-muted/30 border-t border-border p-6 pl-16 space-y-4">
                    <div
                        v-for="reply in comment.replies"
                        :key="reply.id"
                        class="relative"
                    >
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-background border border-border flex items-center justify-center">
                                    <span class="text-muted-foreground font-semibold text-[10px]">
                                        {{ ((reply.user?.name || reply.name || 'U')?.charAt(0) || 'U').toUpperCase() }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-1">
                                    <p class="text-xs font-semibold text-foreground">
                                        {{ reply.user?.name || reply.name || 'Anonymous' }}
                                    </p>
                                    <Badge
                                        variant="outline"
                                        class="text-[10px] h-4 px-1"
                                        :class="{
                                            'bg-yellow-500/10 text-yellow-500 border-yellow-500/20': reply.status === 'pending',
                                            'bg-green-500/10 text-green-500 border-green-500/20': reply.status === 'approved',
                                            'bg-red-500/10 text-red-500 border-red-500/20': reply.status === 'rejected',
                                        }"
                                    >
                                        {{ reply.status }}
                                    </Badge>
                                </div>
                                <p class="text-xs text-foreground/80 leading-relaxed">{{ reply.body }}</p>
                                <p class="text-[10px] text-muted-foreground mt-1">{{ formatDate(reply.created_at) }}</p>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 hover:opacity-100 transition-opacity">
                                <Button
                                    v-if="reply.status === 'pending'"
                                    variant="ghost"
                                    size="icon"
                                    class="w-6 h-6 text-green-600"
                                    @click="approveComment(reply)"
                                >
                                    <Check class="w-3 h-3" />
                                </Button>
                                <Button
                                    v-if="reply.status === 'pending'"
                                    variant="ghost"
                                    size="icon"
                                    class="w-6 h-6 text-yellow-600"
                                    @click="rejectComment(reply)"
                                >
                                    <X class="w-3 h-3" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="w-6 h-6 text-destructive"
                                    @click="deleteComment(reply)"
                                >
                                    <Trash2 class="w-3 h-3" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Pagination -->
            <Card v-if="pagination && pagination.last_page > 1" class="p-4 flex items-center justify-between">
                <div class="text-xs text-muted-foreground">
                    {{ $t('common.pagination.showing', { from: pagination.from, to: pagination.to, total: pagination.total }) }}
                </div>
                <div class="flex space-x-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="changePage(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                    >
                        <ChevronLeft class="w-4 h-4 mr-1" />
                        {{ $t('common.pagination.previous') }}
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                    >
                        {{ $t('common.pagination.next') }}
                        <ChevronRight class="w-4 h-4 ml-1" />
                    </Button>
                </div>
            </Card>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import Card from '../../../components/ui/card.vue';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Select from '../../../components/ui/select.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import Badge from '../../../components/ui/badge.vue';
import Checkbox from '../../../components/ui/checkbox.vue';
import { 
    MessageSquare, Check, X, AlertTriangle, 
    Trash2, Search, ArrowUpRight, Reply,
    Clock, ChevronLeft, ChevronRight 
} from 'lucide-vue-next';

const { t } = useI18n();

const loading = ref(false);
const comments = ref([]);
const search = ref('');
const statusFilter = ref('pending');
const pagination = ref(null);
const statistics = ref(null);
const selectedIds = ref([]);

const fetchStatistics = async () => {
    try {
        const response = await api.get('/admin/cms/comments/statistics');
        statistics.value = response.data?.data || response.data;
    } catch (error) {
        console.error('Failed to fetch statistics:', error);
    }
};

const bulkAction = async (action) => {
    if (selectedIds.value.length === 0) return;
    
    const count = selectedIds.value.length;
    let confirmMsg = '';
    
    switch (action) {
        case 'delete':
            confirmMsg = t('features.comments.messages.bulkDeleteConfirm', { count });
            break;
        case 'approve':
            confirmMsg = t('features.comments.messages.bulkApproveConfirm', { count });
            break;
        case 'reject':
            confirmMsg = t('features.comments.messages.bulkRejectConfirm', { count });
            break;
        case 'spam':
            confirmMsg = t('features.comments.messages.bulkSpamConfirm', { count });
            break;
    }
    
    if (!confirm(confirmMsg)) return;
    
    try {
        await api.post('/admin/cms/comments/bulk', {
            ids: selectedIds.value,
            action: action
        });
        selectedIds.value = [];
        await fetchComments();
        await fetchStatistics();
    } catch (error) {
        console.error('Bulk action failed:', error);
        alert(t('features.comments.messages.bulkFailed'));
    }
};

const fetchComments = async () => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value?.current_page || 1,
        };

        if (statusFilter.value) {
            params.status = statusFilter.value;
        }

        const response = await api.get('/admin/cms/comments', { params });
        const { data, pagination: paginationData } = parseResponse(response);
        comments.value = ensureArray(data);
        if (paginationData) {
            pagination.value = paginationData;
        }
    } catch (error) {
        console.error('Failed to fetch comments:', error);
    } finally {
        loading.value = false;
    }
};

const changePage = (page) => {
    if (pagination.value) {
        pagination.value.current_page = page;
        fetchComments();
    }
};

const approveComment = async (comment) => {
    try {
        await api.put(`/admin/cms/comments/${comment.id}/approve`);
        await fetchComments();
    } catch (error) {
        console.error('Failed to approve comment:', error);
        alert(t('features.comments.messages.approveFailed'));
    }
};

const rejectComment = async (comment) => {
    try {
        await api.put(`/admin/cms/comments/${comment.id}/reject`);
        await fetchComments();
        await fetchStatistics();
    } catch (error) {
        console.error('Failed to reject comment:', error);
        alert(t('features.comments.messages.rejectFailed'));
    }
};

const markAsSpam = async (comment) => {
    try {
        await api.put(`/admin/cms/comments/${comment.id}/spam`);
        await fetchComments();
        await fetchStatistics();
    } catch (error) {
        console.error('Failed to mark as spam:', error);
        alert(t('features.comments.messages.spamFailed'));
    }
};

const toggleSelection = (commentId) => {
    const index = selectedIds.value.indexOf(commentId);
    if (index > -1) {
        selectedIds.value.splice(index, 1);
    } else {
        selectedIds.value.push(commentId);
    }
};

const deleteComment = async (comment) => {
    if (!confirm(t('features.comments.messages.deleteConfirm'))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/comments/${comment.id}`);
        await fetchComments();
    } catch (error) {
        console.error('Failed to delete comment:', error);
        alert(t('features.comments.messages.deleteFailed'));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

watch([statusFilter, search], () => {
    if (pagination.value) {
        pagination.value.current_page = 1;
    }
    fetchComments();
});

onMounted(() => {
    fetchComments();
    fetchStatistics();
});
</script>

