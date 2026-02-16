<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.wa.title', 'WhatsApp Blast') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.admin.wa.subtitle', 'Manage message templates, blast campaigns, and schedules') }}</p>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="flex gap-1 p-1 border rounded-lg bg-muted/30 w-fit">
            <Button v-for="tab in tabs" :key="tab.key" :variant="activeTab === tab.key ? 'default' : 'ghost'" size="sm" @click="activeTab = tab.key" class="gap-2">
                <component :is="tab.icon" class="w-4 h-4" />
                {{ tab.label }}
            </Button>
        </div>

        <!-- Templates Tab -->
        <template v-if="activeTab === 'templates'">
            <div class="flex justify-end">
                <Button @click="openTemplateModal()">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('isp.admin.wa.create_template', 'New Template') }}
                </Button>
            </div>
            <Card class="border-border/40 shadow-sm">
                <CardContent class="p-0">
                    <DataTable :table="templateTable" :loading="loading" :empty-message="'No templates found.'" />
                </CardContent>
            </Card>

            <!-- Template Modal -->
            <Dialog v-model:open="showTemplateModal">
                <DialogContent class="sm:max-w-[550px]">
                    <DialogHeader>
                        <DialogTitle>{{ editingTemplate ? 'Edit Template' : 'New Template' }}</DialogTitle>
                    </DialogHeader>
                    <div class="space-y-4 py-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>Name *</Label>
                                <Input v-model="templateForm.name" placeholder="Billing Reminder" />
                            </div>
                            <div class="space-y-2">
                                <Label>Category</Label>
                                <Select v-model="templateForm.category">
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="billing">Billing</SelectItem>
                                        <SelectItem value="outage">Outage</SelectItem>
                                        <SelectItem value="promo">Promo</SelectItem>
                                        <SelectItem value="reminder">Reminder</SelectItem>
                                        <SelectItem value="custom">Custom</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label>Message Body *</Label>
                            <textarea v-model="templateForm.body" rows="6" class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" placeholder="Halo {name}, tagihan Anda sebesar Rp{amount}..." />
                            <p class="text-xs text-muted-foreground">Available variables: {name}, {amount}, {due_date}, {plan_name}</p>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="ghost" @click="showTemplateModal = false">Cancel</Button>
                        <Button @click="saveTemplate" :disabled="submitting">
                            <LoaderCircle v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                            Save
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </template>

        <!-- Blasts Tab -->
        <template v-if="activeTab === 'blasts'">
            <div class="grid gap-4 md:grid-cols-3">
                <Card class="border-border/40">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-muted-foreground">Total Blasts</p>
                                <p class="text-2xl font-bold">{{ blasts.length }}</p>
                            </div>
                            <div class="p-2 rounded bg-primary/10"><Send class="w-4 h-4 text-primary" /></div>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-border/40">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-muted-foreground">Messages Sent</p>
                                <p class="text-2xl font-bold">{{ blasts.reduce((s: number, b: Blast) => s + b.sent_count, 0) }}</p>
                            </div>
                            <div class="p-2 rounded bg-green-500/10"><CircleCheck class="w-4 h-4 text-green-500" /></div>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-border/40">
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-muted-foreground">Failed</p>
                                <p class="text-2xl font-bold">{{ blasts.reduce((s: number, b: Blast) => s + b.failed_count, 0) }}</p>
                            </div>
                            <div class="p-2 rounded bg-red-500/10"><CircleX class="w-4 h-4 text-red-500" /></div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="flex justify-end">
                <Button @click="openBlastModal()">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('isp.admin.wa.create_blast', 'New Blast') }}
                </Button>
            </div>
            <Card class="border-border/40 shadow-sm">
                <CardContent class="p-0">
                    <DataTable :table="blastTable" :loading="loading" :empty-message="'No blast campaigns found.'" />
                </CardContent>
            </Card>

            <!-- Blast Modal -->
            <Dialog v-model:open="showBlastModal">
                <DialogContent class="sm:max-w-[550px]">
                    <DialogHeader>
                        <DialogTitle>New Blast Campaign</DialogTitle>
                    </DialogHeader>
                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label>Campaign Name *</Label>
                            <Input v-model="blastForm.name" placeholder="Billing Reminder Feb 2026" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>Template</Label>
                                <Select v-model="blastForm.template_id">
                                    <SelectTrigger><SelectValue placeholder="Choose template..." /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="tpl in templates" :key="tpl.id" :value="String(tpl.id)">{{ tpl.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label>Target</Label>
                                <Select v-model="blastForm.target_filter">
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Customers</SelectItem>
                                        <SelectItem value="active">Active Only</SelectItem>
                                        <SelectItem value="unpaid">Unpaid Invoices</SelectItem>
                                        <SelectItem value="inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label>Custom Message (optional, overrides template)</Label>
                            <textarea v-model="blastForm.message" rows="4" class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" placeholder="Leave empty to use template..." />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="ghost" @click="showBlastModal = false">Cancel</Button>
                        <Button @click="createBlast" :disabled="submitting">
                            <LoaderCircle v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                            Create Blast
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </template>

        <!-- Schedules Tab -->
        <template v-if="activeTab === 'schedules'">
            <div class="flex justify-end">
                <Button @click="openScheduleModal()">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('isp.admin.wa.create_schedule', 'New Schedule') }}
                </Button>
            </div>
            <Card class="border-border/40 shadow-sm">
                <CardContent class="p-0">
                    <DataTable :table="scheduleTable" :loading="loading" :empty-message="'No schedules found.'" />
                </CardContent>
            </Card>

            <!-- Schedule Modal -->
            <Dialog v-model:open="showScheduleModal">
                <DialogContent class="sm:max-w-[550px]">
                    <DialogHeader>
                        <DialogTitle>{{ editingSchedule ? 'Edit Schedule' : 'New Schedule' }}</DialogTitle>
                    </DialogHeader>
                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label>Name *</Label>
                            <Input v-model="scheduleForm.name" placeholder="Daily Billing Reminder" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>Template</Label>
                                <Select v-model="scheduleForm.template_id">
                                    <SelectTrigger><SelectValue placeholder="Choose template..." /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="tpl in templates" :key="tpl.id" :value="String(tpl.id)">{{ tpl.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label>Target</Label>
                                <Select v-model="scheduleForm.target_filter">
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Customers</SelectItem>
                                        <SelectItem value="active">Active Only</SelectItem>
                                        <SelectItem value="unpaid">Unpaid Invoices</SelectItem>
                                        <SelectItem value="due_soon">Due Soon</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label>Frequency</Label>
                                <Select v-model="scheduleForm.frequency">
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="daily">Daily</SelectItem>
                                        <SelectItem value="weekly">Weekly</SelectItem>
                                        <SelectItem value="monthly">Monthly</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label>Time</Label>
                                <Input v-model="scheduleForm.time" type="time" />
                            </div>
                            <div class="space-y-2">
                                <Label>Day Offset</Label>
                                <Input v-model="scheduleForm.day_offset" type="number" min="0" max="30" />
                            </div>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="ghost" @click="showScheduleModal = false">Cancel</Button>
                        <Button @click="saveSchedule" :disabled="submitting">
                            <LoaderCircle v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                            Save
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </template>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h, markRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';
import {
    Card, CardContent,
    Button, Badge, Input, Label, DataTable,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem
} from '@/components/ui';

import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Ellipsis from 'lucide-vue-next/dist/esm/icons/ellipsis.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Send from 'lucide-vue-next/dist/esm/icons/send.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import CalendarClock from 'lucide-vue-next/dist/esm/icons/calendar-clock.js';
import Play from 'lucide-vue-next/dist/esm/icons/play.js';
import CircleCheck from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import CircleX from 'lucide-vue-next/dist/esm/icons/circle-x.js';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const submitting = ref(false);

const activeTab = ref<'templates' | 'blasts' | 'schedules'>('templates');
const tabs = [
    { key: 'templates' as const, label: 'Templates', icon: markRaw(FileText) },
    { key: 'blasts' as const, label: 'Blast Campaigns', icon: markRaw(Send) },
    { key: 'schedules' as const, label: 'Schedules', icon: markRaw(CalendarClock) }
];

// ============================================================
// TYPES
// ============================================================
interface WaTemplate {
    id: number;
    name: string;
    slug: string;
    category: string;
    body: string;
    is_active: boolean;
}

interface Blast {
    id: number;
    name: string;
    template_id: number | null;
    template?: WaTemplate;
    message: string;
    target_filter: string;
    total_targets: number;
    sent_count: number;
    failed_count: number;
    status: string;
    scheduled_at: string | null;
    created_at: string;
}

interface Schedule {
    id: number;
    name: string;
    template_id: number | null;
    template?: WaTemplate;
    target_filter: string;
    frequency: string;
    day_offset: number;
    time: string;
    is_active: boolean;
    next_run_at: string | null;
}

// ============================================================
// DATA
// ============================================================
const templates = ref<WaTemplate[]>([]);
const blasts = ref<Blast[]>([]);
const schedules = ref<Schedule[]>([]);

// Template form
const showTemplateModal = ref(false);
const editingTemplate = ref<WaTemplate | null>(null);
const templateForm = ref({ name: '', category: 'billing', body: '' });

// Blast form
const showBlastModal = ref(false);
const blastForm = ref({ name: '', template_id: '', target_filter: 'all', message: '' });

// Schedule form
const showScheduleModal = ref(false);
const editingSchedule = ref<Schedule | null>(null);
const scheduleForm = ref({ name: '', template_id: '', target_filter: 'unpaid', frequency: 'daily', time: '08:00', day_offset: 0 });

const templateSorting = ref<SortingState>([]);
const blastSorting = ref<SortingState>([]);
const scheduleSorting = ref<SortingState>([]);

const statusVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    switch (status) {
        case 'completed': return 'default';
        case 'processing': return 'secondary';
        case 'failed': return 'destructive';
        default: return 'secondary';
    }
};

// ============================================================
// TEMPLATE TABLE
// ============================================================
const tplColumnHelper = createColumnHelper<WaTemplate>();
const templateColumns = [
    tplColumnHelper.accessor('name', {
        header: 'Name',
        cell: ({ row }) => h('div', { class: 'flex items-center gap-2' }, [
            h('div', { class: 'p-1.5 rounded bg-primary/10 text-primary' }, [h(FileText, { class: 'w-3.5 h-3.5' })]),
            h('span', { class: 'font-medium' }, row.original.name)
        ])
    }),
    tplColumnHelper.accessor('category', {
        header: 'Category',
        cell: ({ row }) => h(Badge, { variant: 'secondary' }, () => row.original.category)
    }),
    tplColumnHelper.accessor('body', {
        header: 'Preview',
        cell: ({ row }) => h('span', { class: 'text-xs text-muted-foreground truncate max-w-[200px] block' }, row.original.body.substring(0, 60) + '...')
    }),
    tplColumnHelper.accessor('is_active', {
        header: 'Status',
        cell: ({ row }) => h(Badge, { variant: row.original.is_active ? 'default' : 'destructive' }, () => row.original.is_active ? 'Active' : 'Inactive')
    }),
    tplColumnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Actions'),
        cell: ({ row }) => h('div', { class: 'flex items-center justify-end gap-1' }, [
            h(DropdownMenu, {}, {
                default: () => [
                    h(DropdownMenuTrigger, { asChild: true }, () =>
                        h(Button, { variant: 'ghost', size: 'icon', class: 'h-8 w-8' }, () => h(Ellipsis, { class: 'w-4 h-4' }))
                    ),
                    h(DropdownMenuContent, { align: 'end' }, () => [
                        h(DropdownMenuItem, { onClick: () => openTemplateModal(row.original) }, () => [h(Pencil, { class: 'w-4 h-4 mr-2' }), 'Edit']),
                        h(DropdownMenuItem, { onClick: () => deleteTemplate(row.original.id), class: 'text-destructive' }, () => [h(Trash2, { class: 'w-4 h-4 mr-2' }), 'Delete'])
                    ])
                ]
            })
        ])
    })
];

const templateTable = useVueTable({
    get data() { return templates.value },
    columns: templateColumns,
    state: { get sorting() { return templateSorting.value } },
    onSortingChange: u => { templateSorting.value = typeof u === 'function' ? u(templateSorting.value) : u },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
});

// ============================================================
// BLAST TABLE
// ============================================================
const blastColumnHelper = createColumnHelper<Blast>();
const blastColumns = [
    blastColumnHelper.accessor('name', {
        header: 'Campaign',
        cell: ({ row }) => h('div', { class: 'flex items-center gap-2' }, [
            h('div', { class: 'p-1.5 rounded bg-primary/10 text-primary' }, [h(Send, { class: 'w-3.5 h-3.5' })]),
            h('div', {}, [
                h('span', { class: 'font-medium' }, row.original.name),
                h('p', { class: 'text-xs text-muted-foreground' }, row.original.created_at ? new Date(row.original.created_at).toLocaleDateString('id-ID') : '')
            ])
        ])
    }),
    blastColumnHelper.accessor('target_filter', {
        header: 'Target',
        cell: ({ row }) => h(Badge, { variant: 'secondary' }, () => row.original.target_filter)
    }),
    blastColumnHelper.accessor('total_targets', {
        header: () => h('div', { class: 'text-center' }, 'Targets'),
        cell: ({ row }) => h('div', { class: 'text-center font-mono text-sm' }, String(row.original.total_targets))
    }),
    blastColumnHelper.accessor('sent_count', {
        header: () => h('div', { class: 'text-center' }, 'Sent'),
        cell: ({ row }) => h('div', { class: 'text-center' }, [
            h('span', { class: 'text-green-600 font-mono text-sm' }, String(row.original.sent_count)),
            row.original.failed_count > 0 ? h('span', { class: 'text-red-500 text-xs ml-1' }, `(${row.original.failed_count} fail)`) : null
        ])
    }),
    blastColumnHelper.accessor('status', {
        header: 'Status',
        cell: ({ row }) => h(Badge, { variant: statusVariant(row.original.status) }, () => row.original.status)
    }),
    blastColumnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Actions'),
        cell: ({ row }) => h('div', { class: 'flex items-center justify-end gap-1' }, [
            h(DropdownMenu, {}, {
                default: () => [
                    h(DropdownMenuTrigger, { asChild: true }, () =>
                        h(Button, { variant: 'ghost', size: 'icon', class: 'h-8 w-8' }, () => h(Ellipsis, { class: 'w-4 h-4' }))
                    ),
                    h(DropdownMenuContent, { align: 'end' }, () => [
                        row.original.status === 'draft'
                            ? h(DropdownMenuItem, { onClick: () => sendBlast(row.original.id) }, () => [h(Play, { class: 'w-4 h-4 mr-2 text-green-500' }), 'Send Now'])
                            : null,
                        h(DropdownMenuItem, { onClick: () => deleteBlast(row.original.id), class: 'text-destructive' }, () => [h(Trash2, { class: 'w-4 h-4 mr-2' }), 'Delete'])
                    ])
                ]
            })
        ])
    })
];

const blastTable = useVueTable({
    get data() { return blasts.value },
    columns: blastColumns,
    state: { get sorting() { return blastSorting.value } },
    onSortingChange: u => { blastSorting.value = typeof u === 'function' ? u(blastSorting.value) : u },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
});

// ============================================================
// SCHEDULE TABLE
// ============================================================
const schedColumnHelper = createColumnHelper<Schedule>();
const scheduleColumns = [
    schedColumnHelper.accessor('name', {
        header: 'Name',
        cell: ({ row }) => h('div', { class: 'flex items-center gap-2' }, [
            h('div', { class: 'p-1.5 rounded bg-primary/10 text-primary' }, [h(CalendarClock, { class: 'w-3.5 h-3.5' })]),
            h('span', { class: 'font-medium' }, row.original.name)
        ])
    }),
    schedColumnHelper.accessor('frequency', {
        header: 'Frequency',
        cell: ({ row }) => h(Badge, { variant: 'secondary' }, () => `${row.original.frequency} @ ${row.original.time}`)
    }),
    schedColumnHelper.accessor('target_filter', {
        header: 'Target',
        cell: ({ row }) => h(Badge, { variant: 'secondary' }, () => row.original.target_filter)
    }),
    schedColumnHelper.accessor('is_active', {
        header: 'Status',
        cell: ({ row }) => h(Badge, { variant: row.original.is_active ? 'default' : 'destructive' }, () => row.original.is_active ? 'Active' : 'Paused')
    }),
    schedColumnHelper.accessor('next_run_at', {
        header: 'Next Run',
        cell: ({ row }) => h('span', { class: 'text-xs text-muted-foreground' }, row.original.next_run_at ? new Date(row.original.next_run_at).toLocaleString('id-ID') : '-')
    }),
    schedColumnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Actions'),
        cell: ({ row }) => h('div', { class: 'flex items-center justify-end gap-1' }, [
            h(DropdownMenu, {}, {
                default: () => [
                    h(DropdownMenuTrigger, { asChild: true }, () =>
                        h(Button, { variant: 'ghost', size: 'icon', class: 'h-8 w-8' }, () => h(Ellipsis, { class: 'w-4 h-4' }))
                    ),
                    h(DropdownMenuContent, { align: 'end' }, () => [
                        h(DropdownMenuItem, { onClick: () => openScheduleModal(row.original) }, () => [h(Pencil, { class: 'w-4 h-4 mr-2' }), 'Edit']),
                        h(DropdownMenuItem, { onClick: () => toggleSchedule(row.original) }, () => [h(row.original.is_active ? CircleX : CircleCheck, { class: 'w-4 h-4 mr-2' }), row.original.is_active ? 'Pause' : 'Activate']),
                        h(DropdownMenuItem, { onClick: () => deleteSchedule(row.original.id), class: 'text-destructive' }, () => [h(Trash2, { class: 'w-4 h-4 mr-2' }), 'Delete'])
                    ])
                ]
            })
        ])
    })
];

const scheduleTable = useVueTable({
    get data() { return schedules.value },
    columns: scheduleColumns,
    state: { get sorting() { return scheduleSorting.value } },
    onSortingChange: u => { scheduleSorting.value = typeof u === 'function' ? u(scheduleSorting.value) : u },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
});

// ============================================================
// FETCH DATA
// ============================================================
const fetchData = async () => {
    loading.value = true;
    try {
        const [tplRes, blastRes, schedRes] = await Promise.all([
            api.get('/admin/janet/isp/wa/templates'),
            api.get('/admin/janet/isp/wa/blasts'),
            api.get('/admin/janet/isp/wa/schedules')
        ]);
        templates.value = tplRes.data.data || [];
        blasts.value = blastRes.data.data || [];
        schedules.value = schedRes.data.data || [];
    } catch (error) {
        console.error('WA data fetch failed', error);
        toast.error.action(t('common.errors.fetch', 'Failed to fetch data'));
    } finally {
        loading.value = false;
    }
};

// ============================================================
// TEMPLATE ACTIONS
// ============================================================
const openTemplateModal = (tpl?: WaTemplate) => {
    editingTemplate.value = tpl || null;
    templateForm.value = tpl ? { name: tpl.name, category: tpl.category, body: tpl.body } : { name: '', category: 'billing', body: '' };
    showTemplateModal.value = true;
};

const saveTemplate = async () => {
    if (!templateForm.value.name || !templateForm.value.body) {
        toast.error.action('Name and body are required');
        return;
    }
    submitting.value = true;
    try {
        if (editingTemplate.value) {
            await api.put(`/admin/janet/isp/wa/templates/${editingTemplate.value.id}`, templateForm.value);
            toast.success.action('Template updated');
        } else {
            await api.post('/admin/janet/isp/wa/templates', templateForm.value);
            toast.success.action('Template created');
        }
        showTemplateModal.value = false;
        await fetchData();
    } catch (error) {
        toast.error.action(error);
    } finally {
        submitting.value = false;
    }
};

const deleteTemplate = async (id: number) => {
    const confirmed = await confirm({ title: 'Delete', message: 'Delete this template?', variant: 'danger', confirmText: 'Delete' });
    if (!confirmed) return;
    try {
        await api.delete(`/admin/janet/isp/wa/templates/${id}`);
        toast.success.action('Template deleted');
        await fetchData();
    } catch (error) { toast.error.action(error); }
};

// ============================================================
// BLAST ACTIONS
// ============================================================
const openBlastModal = () => {
    blastForm.value = { name: '', template_id: '', target_filter: 'all', message: '' };
    showBlastModal.value = true;
};

const createBlast = async () => {
    if (!blastForm.value.name) {
        toast.error.action('Campaign name is required');
        return;
    }
    submitting.value = true;
    try {
        const payload = {
            ...blastForm.value,
            template_id: blastForm.value.template_id ? Number(blastForm.value.template_id) : null
        };
        await api.post('/admin/janet/isp/wa/blasts', payload);
        toast.success.action('Blast campaign created');
        showBlastModal.value = false;
        await fetchData();
    } catch (error) {
        toast.error.action(error);
    } finally {
        submitting.value = false;
    }
};

const sendBlast = async (id: number) => {
    const confirmed = await confirm({ title: 'Send Blast', message: 'Send this blast campaign now?', confirmText: 'Send' });
    if (!confirmed) return;
    try {
        await api.post(`/admin/janet/isp/wa/blasts/${id}/send`);
        toast.success.action('Blast sent');
        await fetchData();
    } catch (error) { toast.error.action(error); }
};

const deleteBlast = async (id: number) => {
    const confirmed = await confirm({ title: 'Delete', message: 'Delete this blast?', variant: 'danger', confirmText: 'Delete' });
    if (!confirmed) return;
    try {
        await api.delete(`/admin/janet/isp/wa/blasts/${id}`);
        toast.success.action('Blast deleted');
        await fetchData();
    } catch (error) { toast.error.action(error); }
};

// ============================================================
// SCHEDULE ACTIONS
// ============================================================
const openScheduleModal = (sched?: Schedule) => {
    editingSchedule.value = sched || null;
    scheduleForm.value = sched
        ? { name: sched.name, template_id: sched.template_id ? String(sched.template_id) : '', target_filter: sched.target_filter, frequency: sched.frequency, time: sched.time, day_offset: sched.day_offset }
        : { name: '', template_id: '', target_filter: 'unpaid', frequency: 'daily', time: '08:00', day_offset: 0 };
    showScheduleModal.value = true;
};

const saveSchedule = async () => {
    if (!scheduleForm.value.name) {
        toast.error.action('Name is required');
        return;
    }
    submitting.value = true;
    try {
        const payload = {
            ...scheduleForm.value,
            template_id: scheduleForm.value.template_id ? Number(scheduleForm.value.template_id) : null
        };
        if (editingSchedule.value) {
            await api.put(`/admin/janet/isp/wa/schedules/${editingSchedule.value.id}`, payload);
            toast.success.action('Schedule updated');
        } else {
            await api.post('/admin/janet/isp/wa/schedules', payload);
            toast.success.action('Schedule created');
        }
        showScheduleModal.value = false;
        await fetchData();
    } catch (error) {
        toast.error.action(error);
    } finally {
        submitting.value = false;
    }
};

const toggleSchedule = async (sched: Schedule) => {
    try {
        await api.put(`/admin/janet/isp/wa/schedules/${sched.id}`, { is_active: !sched.is_active });
        toast.success.action(sched.is_active ? 'Schedule paused' : 'Schedule activated');
        await fetchData();
    } catch (error) { toast.error.action(error); }
};

const deleteSchedule = async (id: number) => {
    const confirmed = await confirm({ title: 'Delete', message: 'Delete this schedule?', variant: 'danger', confirmText: 'Delete' });
    if (!confirmed) return;
    try {
        await api.delete(`/admin/janet/isp/wa/schedules/${id}`);
        toast.success.action('Schedule deleted');
        await fetchData();
    } catch (error) { toast.error.action(error); }
};

onMounted(fetchData);
</script>
