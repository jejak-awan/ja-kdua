<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.print_templates.title', 'Print Templates') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.admin.print_templates.subtitle', 'Create and manage custom print layouts for vouchers and invoices') }}</p>
            </div>
            <Button @click="openModal()">
                <Plus class="w-4 h-4 mr-2" />
                {{ $t('isp.admin.print_templates.add', 'New Template') }}
            </Button>
        </div>

        <!-- Template Cards Grid -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Card v-for="tpl in templates" :key="tpl.id" class="border-border/40 group hover:shadow-md transition-shadow">
                <CardContent class="p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded bg-primary/10 text-primary">
                                <FileText class="w-4 h-4" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-sm leading-tight">{{ tpl.name }}</h3>
                                <p class="text-xs text-muted-foreground">{{ tpl.paper_size }} · {{ tpl.orientation }} · {{ tpl.columns_per_row }} col</p>
                            </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon" class="h-7 w-7 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Ellipsis class="w-4 h-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="openModal(tpl)">
                                    <Pencil class="w-4 h-4 mr-2" /> Edit
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="previewTemplate(tpl.id)">
                                    <Eye class="w-4 h-4 mr-2" /> Preview
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="setDefault(tpl)" v-if="!tpl.is_default">
                                    <Star class="w-4 h-4 mr-2" /> Set Default
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="deleteTemplate(tpl.id)" class="text-destructive">
                                    <Trash2 class="w-4 h-4 mr-2" /> Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge :variant="tpl.type === 'voucher' ? 'default' : 'secondary'">{{ tpl.type }}</Badge>
                        <Badge v-if="tpl.is_default" variant="outline" class="text-amber-600 border-amber-300 bg-amber-50">
                            <Star class="w-3 h-3 mr-1" /> Default
                        </Badge>
                    </div>
                    <!-- Preview Mini -->
                    <div class="mt-3 border border-border/30 rounded p-2 bg-muted/10 max-h-[120px] overflow-hidden text-[8px] leading-tight opacity-60" v-html="tpl.html_content.substring(0, 300)"></div>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-if="!loading && templates.length === 0" class="border-dashed border-2 border-border/40 col-span-full">
                <CardContent class="p-10 text-center">
                    <FileText class="w-10 h-10 mx-auto text-muted-foreground/30 mb-3" />
                    <p class="text-muted-foreground">No templates yet. Create one to get started.</p>
                </CardContent>
            </Card>
        </div>

        <div v-if="loading" class="p-10 text-center">
            <LoaderCircle class="w-8 h-8 animate-spin mx-auto text-primary" />
        </div>

        <!-- Create/Edit Modal -->
        <Dialog v-model:open="showModal">
            <DialogContent class="sm:max-w-[700px] max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{{ editingTemplate ? 'Edit Template' : 'New Template' }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Name *</Label>
                            <Input v-model="form.name" placeholder="Voucher Card Standard" />
                        </div>
                        <div class="space-y-2">
                            <Label>Type</Label>
                            <Select v-model="form.type">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="voucher">Voucher</SelectItem>
                                    <SelectItem value="invoice">Invoice</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label>Paper Size</Label>
                            <Select v-model="form.paper_size">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="A4">A4</SelectItem>
                                    <SelectItem value="A5">A5</SelectItem>
                                    <SelectItem value="thermal">Thermal (58mm)</SelectItem>
                                    <SelectItem value="custom">Custom</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Orientation</Label>
                            <Select v-model="form.orientation">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="portrait">Portrait</SelectItem>
                                    <SelectItem value="landscape">Landscape</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Columns / Row</Label>
                            <Input v-model="form.columns_per_row" type="number" min="1" max="10" />
                        </div>
                    </div>
                    <!-- Variables Reference -->
                    <div class="p-3 bg-muted/30 rounded-lg border border-border/30">
                        <p class="text-xs font-bold text-muted-foreground mb-2">Available Variables (click to insert):</p>
                        <div class="flex flex-wrap gap-1">
                            <button
                                v-for="(desc, varKey) in availableVariables"
                                :key="varKey"
                                type="button"
                                class="px-1.5 py-0.5 rounded bg-primary/10 text-primary text-[10px] font-mono hover:bg-primary/20 cursor-pointer transition-colors"
                                :title="desc"
                                @click="insertVariable(varKey)"
                            >
                                {{ varKey }}
                            </button>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>HTML Content *</Label>
                        <textarea
                            ref="htmlEditor"
                            v-model="form.html_content"
                            rows="10"
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-mono ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            placeholder="<div class='voucher-card'>&#10;  <h3>#profile#</h3>&#10;  <p>Code: #code#</p>&#10;  <p>Price: #price#</p>&#10;</div>"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label>CSS (optional)</Label>
                        <textarea
                            v-model="form.css_content"
                            rows="5"
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-mono ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            placeholder=".voucher-card { padding: 16px; border: 1px solid #ddd; }"
                        />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="ghost" @click="showModal = false">Cancel</Button>
                    <Button @click="saveTemplate" :disabled="submitting">
                        <LoaderCircle v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                        Save
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Preview Modal -->
        <Dialog v-model:open="showPreview">
            <DialogContent class="sm:max-w-[800px] max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Template Preview</DialogTitle>
                </DialogHeader>
                <div class="border border-border/40 rounded-lg p-6 bg-white text-black min-h-[200px]" v-html="previewHtml"></div>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import {
    Card, CardContent,
    Button, Badge, Input, Label,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem
} from '@/components/ui';

import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Ellipsis from 'lucide-vue-next/dist/esm/icons/ellipsis.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import Star from 'lucide-vue-next/dist/esm/icons/star.js';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const submitting = ref(false);

interface Template {
    id: number;
    name: string;
    type: string;
    paper_size: string;
    orientation: string;
    columns_per_row: number;
    html_content: string;
    css_content: string | null;
    is_default: boolean;
}

const templates = ref<Template[]>([]);
const showModal = ref(false);
const showPreview = ref(false);
const editingTemplate = ref<Template | null>(null);
const previewHtml = ref('');
const htmlEditor = ref<HTMLTextAreaElement | null>(null);

const form = ref({
    name: '',
    type: 'voucher',
    paper_size: 'A4',
    orientation: 'portrait',
    columns_per_row: 3,
    html_content: '',
    css_content: '',
});

const voucherVars: Record<string, string> = {
    '#username#': 'Voucher username/code',
    '#password#': 'Voucher password',
    '#profile#': 'Service profile name',
    '#price#': 'Price (formatted)',
    '#code#': 'Voucher code',
    '#batch_code#': 'Batch code',
    '#expired#': 'Expiry date',
    '#quota#': 'Data quota',
    '#duration#': 'Session duration',
    '#company_name#': 'Company name',
};

const invoiceVars: Record<string, string> = {
    '#invoice_number#': 'Invoice number',
    '#customer_name#': 'Customer name',
    '#customer_address#': 'Customer address',
    '#plan_name#': 'Plan name',
    '#amount#': 'Amount (formatted)',
    '#due_date#': 'Due date',
    '#period#': 'Billing period',
    '#status#': 'Payment status',
    '#company_name#': 'Company name',
};

const availableVariables = computed(() =>
    form.value.type === 'invoice' ? invoiceVars : voucherVars
);

const insertVariable = (varKey: string) => {
    const el = htmlEditor.value;
    if (el) {
        const start = el.selectionStart;
        const end = el.selectionEnd;
        const text = form.value.html_content;
        form.value.html_content = text.substring(0, start) + varKey + text.substring(end);
        // Restore cursor position
        requestAnimationFrame(() => {
            el.focus();
            el.setSelectionRange(start + varKey.length, start + varKey.length);
        });
    } else {
        form.value.html_content += varKey;
    }
};

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/ja/isp/print-templates');
        templates.value = response.data.data || [];
    } catch (error) {
        console.error('Fetch failed', error);
        toast.error.action(t('common.errors.fetch', 'Failed to fetch data'));
    } finally {
        loading.value = false;
    }
};

const openModal = (tpl?: Template) => {
    editingTemplate.value = tpl || null;
    form.value = tpl
        ? { name: tpl.name, type: tpl.type, paper_size: tpl.paper_size, orientation: tpl.orientation, columns_per_row: tpl.columns_per_row, html_content: tpl.html_content, css_content: tpl.css_content || '' }
        : { name: '', type: 'voucher', paper_size: 'A4', orientation: 'portrait', columns_per_row: 3, html_content: '', css_content: '' };
    showModal.value = true;
};

const saveTemplate = async () => {
    if (!form.value.name || !form.value.html_content) {
        toast.error.action('Name and HTML content are required');
        return;
    }
    submitting.value = true;
    try {
        if (editingTemplate.value) {
            await api.put(`/admin/ja/isp/print-templates/${editingTemplate.value.id}`, form.value);
            toast.success.action('Template updated');
        } else {
            await api.post('/admin/ja/isp/print-templates', form.value);
            toast.success.action('Template created');
        }
        showModal.value = false;
        await fetchData();
    } catch (error) {
        toast.error.action(error);
    } finally {
        submitting.value = false;
    }
};

const previewTemplate = async (id: number) => {
    try {
        const response = await api.get(`/admin/ja/isp/print-templates/${id}/preview`);
        previewHtml.value = response.data.data.html;
        showPreview.value = true;
    } catch (error) {
        toast.error.action(error);
    }
};

const setDefault = async (tpl: Template) => {
    try {
        await api.put(`/admin/ja/isp/print-templates/${tpl.id}`, { is_default: true });
        toast.success.action(`"${tpl.name}" set as default`);
        await fetchData();
    } catch (error) {
        toast.error.action(error);
    }
};

const deleteTemplate = async (id: number) => {
    const confirmed = await confirm({ title: 'Delete', message: 'Delete this template?', variant: 'danger', confirmText: 'Delete' });
    if (!confirmed) return;
    try {
        await api.delete(`/admin/ja/isp/print-templates/${id}`);
        toast.success.action('Template deleted');
        await fetchData();
    } catch (error) { toast.error.action(error); }
};

onMounted(fetchData);
</script>
