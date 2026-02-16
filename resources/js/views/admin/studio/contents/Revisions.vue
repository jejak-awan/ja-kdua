<template>
    <div class="max-w-6xl mx-auto pb-10">
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('features.content.list.revisions') }}</h1>
                <p class="text-sm text-muted-foreground flex items-center gap-2">
                    <FileText class="w-4 h-4" />
                    {{ contentTitle }}
                </p>
            </div>
            <Button variant="ghost" as-child class="w-fit">
                <router-link :to="{ name: 'contents.edit', params: { id: contentId } }">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    {{ $t('features.content.form.back') }}
                </router-link>
            </Button>
        </div>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20 bg-card/30 border border-border/40 rounded-xl space-y-4">
            <Loader2 class="w-10 h-10 animate-spin opacity-20" />
            <p class="text-sm font-medium animate-pulse text-muted-foreground">Loading revisions...</p>
        </div>

        <div v-else-if="revisions.length === 0" class="flex flex-col items-center justify-center py-20 bg-card/30 border border-border/40 rounded-xl space-y-4 text-center">
            <div class="w-16 h-16 rounded-full bg-muted/30 flex items-center justify-center">
                <History class="w-8 h-8 text-muted-foreground/50" />
            </div>
            <div class="space-y-1">
                <p class="text-lg font-semibold text-foreground">No revisions found</p>
                <p class="text-sm text-muted-foreground">This content hasn't been modified yet.</p>
            </div>
        </div>

        <Card v-else class="border-none shadow-sm overflow-hidden bg-card/50">
            <Table>
                <TableHeader>
                    <TableRow class="bg-muted/30 hover:bg-muted/30 border-b border-border/40">
                        <TableHead class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">Version</TableHead>
                        <TableHead class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">Author</TableHead>
                        <TableHead class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">Date</TableHead>
                        <TableHead class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">Changes</TableHead>
                        <TableHead class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-muted-foreground">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="revision in revisions" :key="revision.id" class="group hover:bg-muted/30 transition-colors border-b border-border/40">
                        <TableCell class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <Badge
                                    v-if="revision.is_current"
                                    variant="outline"
                                    class="bg-success/10 text-success border-none px-2 py-0.5"
                                >
                                    Current
                                </Badge>
                                <span class="text-sm font-mono font-bold text-foreground">v{{ revision.version }}</span>
                            </div>
                        </TableCell>
                        <TableCell class="px-6 py-4">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-foreground/80 font-medium">{{ revision.author?.name || 'System' }}</span>
                            </div>
                        </TableCell>
                        <TableCell class="px-6 py-4">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-sm text-foreground/80">{{ formatDate(revision.created_at) }}</span>
                                <span class="text-[11px] text-muted-foreground font-mono uppercase">{{ formatTime(revision.created_at) }}</span>
                            </div>
                        </TableCell>
                        <TableCell class="px-6 py-4">
                            <p class="text-sm text-muted-foreground line-clamp-1 italic">
                                {{ revision.changes_summary || 'No changes summary' }}
                            </p>
                        </TableCell>
                        <TableCell class="px-6 py-4 text-right">
                            <div class="flex justify-end items-center gap-2">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-primary hover:bg-primary/10"
                                    @click="viewRevision(revision)"
                                >
                                    <Eye class="w-4 h-4 mr-2" />
                                    View
                                </Button>
                                <Button
                                    v-if="!revision.is_current"
                                    variant="outline"
                                    size="sm"
                                    class="text-success hover:bg-success/10 border-success/20"
                                    @click="restoreRevision(revision)"
                                >
                                    <RotateCcw class="w-4 h-4 mr-2" />
                                    Restore
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </Card>

        <!-- Revision Detail Modal -->
        <div
            v-if="viewingRevision"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-sm animate-in fade-in transition-colors duration-300"
            @click.self="viewingRevision = null"
        >
            <Card class="max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200">
                <CardHeader class="border-b border-border/40 sticky top-0 bg-card/95 backdrop-blur-sm z-10">
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <CardTitle class="text-xl font-bold flex items-center gap-2">
                                <History class="w-5 h-5 text-primary" />
                                Revision v{{ viewingRevision.version }}
                            </CardTitle>
                            <p class="text-xs text-muted-foreground">{{ formatDate(viewingRevision.created_at) }} at {{ formatTime(viewingRevision.created_at) }}</p>
                        </div>
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="viewingRevision = null"
                        >
                            <X class="w-5 h-5" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="p-0 overflow-y-auto max-h-[calc(90vh-140px)]">
                    <div class="p-8 space-y-8">
                        <div class="space-y-2">
                            <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground">Title</Label>
                            <div class="text-2xl font-bold text-foreground">{{ viewingRevision.data?.title || '-' }}</div>
                        </div>
                        
                        <div class="space-y-2">
                            <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground">Body Content</Label>
                            <div
                                class="p-6 rounded-xl bg-muted/30 border border-border/40 text-sm prose dark:prose-invert max-w-none"
                                v-html="viewingRevision.data?.content || '-'"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground">Status</Label>
                                <div>
                                    <Badge variant="outline" class="capitalize">{{ viewingRevision.data?.status || '-' }}</Badge>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground">Type</Label>
                                <div class="text-sm font-medium capitalize">{{ viewingRevision.data?.type || '-' }}</div>
                            </div>
                        </div>
                    </div>
                </CardContent>
                <div class="p-4 border-t border-border/40 flex items-center justify-end gap-3 sticky bottom-0 bg-card/95 backdrop-blur-sm z-10">
                    <Button
                        variant="ghost"
                        @click="viewingRevision = null"
                    >
                        Close
                    </Button>
                    <Button
                        v-if="!viewingRevision.is_current"
                        variant="default"
                        class="bg-success hover:bg-success/90 shadow-sm px-6"
                        @click="restoreRevision(viewingRevision)"
                    >
                        <RotateCcw class="w-4 h-4 mr-2" />
                        Restore This Version
                    </Button>
                </div>
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    Button,
    Badge,
    Label,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui';
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import History from 'lucide-vue-next/dist/esm/icons/history.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import { useConfirm } from '@/composables/useConfirm';
import toast from '@/services/toast';

interface Revision {
    id: number;
    version: number;
    author_id?: number;
    author?: {
        id: number;
        name: string;
    } | null;
    data: {
        title?: string;
        content?: string;
        status?: string;
        type?: string;
        [key: string]: unknown;
    };
    changes_summary?: string;
    is_current: boolean;
    created_at: string;
    updated_at: string;
}

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const contentId = route.params.id as string;
const revisions = ref<Revision[]>([]);
const loading = ref(false);
const contentTitle = ref('');
const viewingRevision = ref<Revision | null>(null);
const { confirm } = useConfirm();

const fetchRevisions = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/janet/contents/${contentId}/revisions`);
        revisions.value = response.data.data || response.data;
        
        // Get content title from first revision or fetch content
        if (revisions.value.length > 0 && revisions.value[0].data?.title) {
            contentTitle.value = revisions.value[0].data.title;
        } else {
            try {
                const contentResponse = await api.get(`/admin/janet/contents/${contentId}`);
                contentTitle.value = contentResponse.data.data?.title || contentResponse.data.title || 'Content';
            } catch {
                contentTitle.value = 'Content';
            }
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch revisions:', error);
    } finally {
        loading.value = false;
    }
};

const viewRevision = async (revision: Revision) => {
    try {
        const response = await api.get(`/admin/janet/contents/${contentId}/revisions/${revision.id}`);
        viewingRevision.value = response.data.data || response.data;
    } catch (error: unknown) {
        logger.error('Failed to fetch revision detail:', error);
        viewingRevision.value = revision;
    }
};

const restoreRevision = async (revision: Revision) => {
    const confirmed = await confirm({
        title: 'Restore Revision',
        message: `Are you sure you want to restore revision v${revision.version}? This will replace the current content.`,
        confirmText: 'Restore',
        variant: 'warning',
    });

    if (!confirmed) {
        return;
    }

    try {
        await api.post(`/admin/janet/contents/${contentId}/revisions/${revision.id}/restore`);
        toast.success(t('common.messages.success.restored', { item: `v${revision.version}` }));
        router.push({ name: 'contents.edit', params: { id: contentId } });
    } catch (error: unknown) {
        logger.error('Failed to restore revision:', error);
        toast.error(t('common.messages.toast.error'), (error as { response?: { data?: { message?: string } } })?.response?.data?.message || t('features.content.messages.restoreFailed'));
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

const formatTime = (date: string) => {
    return new Date(date).toLocaleTimeString();
};

onMounted(() => {
    fetchRevisions();
});
</script>

