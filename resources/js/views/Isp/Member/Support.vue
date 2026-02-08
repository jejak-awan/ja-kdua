<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-card p-6 rounded-2xl border border-border shadow-sm">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ t('isp.support.title') }}</h1>
                <p class="text-muted-foreground">{{ t('isp.support.subtitle') }}</p>
            </div>
            <Dialog v-model:open="isCreateDialogOpen">
                <DialogTrigger as-child>
                    <Button class="gap-2 rounded-xl">
                        <Plus class="w-4 h-4" />
                        {{ t('isp.support.open_ticket') }}
                    </Button>
                </DialogTrigger>
                <DialogContent class="sm:max-w-[500px] rounded-2xl">
                    <DialogHeader>
                        <DialogTitle>{{ t('isp.support.new_ticket') }}</DialogTitle>
                        <DialogDescription>
                            {{ t('isp.support.new_ticket_desc') }}
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitTicket" class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="subject">{{ t('isp.support.fields.subject') }}</Label>
                            <Input id="subject" v-model="form.subject" required />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="category">{{ t('isp.support.fields.category') }}</Label>
                                <Select v-model="form.category">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="(label, value) in categories" :key="value" :value="value">
                                            {{ label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label for="priority">{{ t('isp.support.fields.priority') }}</Label>
                                <Select v-model="form.priority">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="(label, value) in priorities" :key="value" :value="value">
                                            {{ label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="message">{{ t('isp.support.fields.message') }}</Label>
                            <Textarea id="message" v-model="form.message" rows="4" required />
                        </div>
                        <DialogFooter>
                            <Button type="submit" :disabled="isSubmitting" class="w-full rounded-xl">
                                <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin mr-2" />
                                {{ t('isp.support.submit') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <!-- Tickets List -->
        <div class="bg-card rounded-2xl border border-border shadow-sm overflow-hidden">
            <div class="p-6 border-b border-border">
                <h2 class="text-lg font-semibold">{{ t('isp.support.history') }}</h2>
            </div>
            
            <div v-if="loading" class="p-12 flex justify-center">
                <Loader2 class="w-8 h-8 animate-spin text-primary" />
            </div>

            <div v-else-if="tickets.length === 0" class="p-12 text-center text-muted-foreground">
                <TicketIcon class="w-12 h-12 mx-auto mb-4 opacity-20" />
                <p>{{ t('isp.support.no_tickets') }}</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-xs uppercase text-muted-foreground bg-muted/30">
                            <th class="px-6 py-4 font-medium">{{ t('isp.support.headers.id') }}</th>
                            <th class="px-6 py-4 font-medium">{{ t('isp.support.headers.subject') }}</th>
                            <th class="px-6 py-4 font-medium">{{ t('isp.support.headers.status') }}</th>
                            <th class="px-6 py-4 font-medium">{{ t('isp.support.headers.created') }}</th>
                            <th class="px-6 py-4 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="ticket in tickets" :key="ticket.id" class="hover:bg-muted/30 transition-colors">
                            <td class="px-6 py-4 text-sm font-mono text-muted-foreground">#{{ ticket.id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-sm">{{ ticket.subject }}</div>
                                <div class="text-xs text-muted-foreground capitalize">
                                    {{ t(`isp.support.categories.${ticket.category}`) }} • {{ t(`isp.support.priorities.${ticket.priority}`) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <Badge :variant="getStatusVariant(ticket.status)">
                                    {{ t(`isp.support.statuses.${ticket.status}`) }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-muted-foreground">
                                {{ formatDate(ticket.created_at) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Button variant="ghost" size="sm" class="rounded-lg" @click="viewTicket(ticket)" :aria-label="t('common.actions.view')">
                                    <Eye class="w-4 h-4" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ticket Detail Modal -->
        <Dialog v-model:open="isDetailOpen">
            <DialogContent class="sm:max-w-[600px] rounded-2xl">
                <DialogHeader>
                    <div class="flex justify-between items-start mr-6">
                        <div>
                            <DialogTitle>{{ selectedTicket?.subject }}</DialogTitle>
                            <DialogDescription>
                                #{{ selectedTicket?.id }} • {{ selectedTicket ? formatDate(selectedTicket.created_at) : '' }}
                            </DialogDescription>
                        </div>
                        <Badge :variant="selectedTicket ? getStatusVariant(selectedTicket.status) : 'default'">
                            {{ selectedTicket ? t(`isp.support.statuses.${selectedTicket.status}`) : '' }}
                        </Badge>
                    </div>
                </DialogHeader>
                <div class="space-y-6 py-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="space-y-1">
                            <span class="text-muted-foreground">{{ t('isp.support.fields.category') }}</span>
                            <p class="font-medium">{{ selectedTicket ? t(`isp.support.categories.${selectedTicket.category}`) : '' }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-muted-foreground">{{ t('isp.support.fields.priority') }}</span>
                            <p class="font-medium">{{ selectedTicket ? t(`isp.support.priorities.${selectedTicket.priority}`) : '' }}</p>
                        </div>
                    </div>
                    <div class="space-y-2 p-4 bg-muted/30 rounded-xl border border-border/50">
                        <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">{{ t('isp.support.fields.message') }}</span>
                        <p class="text-sm whitespace-pre-wrap leading-relaxed">{{ selectedTicket?.message }}</p>
                    </div>
                    
                    <!-- Admin Response (If any) -->
                    <div v-if="selectedTicket?.admin_response" class="space-y-2 p-4 bg-primary/5 rounded-xl border border-primary/10">
                        <span class="text-xs font-bold uppercase tracking-wider text-primary">{{ t('isp.support.fields.response') }}</span>
                        <p class="text-sm whitespace-pre-wrap leading-relaxed">{{ selectedTicket.admin_response }}</p>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isDetailOpen = false" class="w-full rounded-xl">
                        {{ t('common.actions.close') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { 
    Button, 
    Input, 
    Label, 
    Textarea, 
    Badge,
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import TicketIcon from 'lucide-vue-next/dist/esm/icons/ticket.js';
import dayjs from 'dayjs';
import { ensureArray } from '@/utils/responseParser';
import type { IspSupportTicket } from '@/types/isp';

const { t } = useI18n();
const toast = useToast();

const tickets = ref<IspSupportTicket[]>([]);
const loading = ref(true);
const isCreateDialogOpen = ref(false);
const isSubmitting = ref(false);
const isDetailOpen = ref(false);
const selectedTicket = ref<IspSupportTicket | null>(null);

const form = ref({
    subject: '',
    category: 'Technical',
    priority: 'Medium',
    message: ''
});

const categories = computed(() => ({
    'Technical': t('isp.support.categories.Technical'),
    'Billing': t('isp.support.categories.Billing'),
    'Sales': t('isp.support.categories.Sales')
}));

const priorities = computed(() => ({
    'Low': t('isp.support.priorities.Low'),
    'Medium': t('isp.support.priorities.Medium'),
    'High': t('isp.support.priorities.High')
}));

const fetchTickets = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/ja/isp/support/tickets');
        if (response.data.success) {
            tickets.value = ensureArray(response.data.data.data) as IspSupportTicket[];
        }
    } catch (_error) {
        // console.error('Failed to fetch tickets:', error);
        toast.error.default(t('isp.support.messages.error_fetch'));
    } finally {
        loading.value = false;
    }
};

const submitTicket = async () => {
    isSubmitting.value = true;
    try {
        await api.post('/admin/ja/isp/support/tickets', form.value);
        toast.success.default(t('isp.support.messages.success_submit'));
        isCreateDialogOpen.value = false;
        form.value = { subject: '', category: 'Technical', priority: 'Medium', message: '' };
        fetchTickets();
    } catch (error: unknown) {
        const err = error as { response?: { data?: { message?: string } } };
        const message = err.response?.data?.message || t('isp.support.messages.error_submit');
        toast.error.default(message);
    } finally {
        isSubmitting.value = false;
    }
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'Open': return 'outline';
        case 'In Progress': return 'secondary';
        case 'Resolved': return 'success';
        case 'Closed': return 'outline';
        default: return 'default';
    }
};

const formatDate = (date: string) => {
    return dayjs(date).format('MMM D, YYYY');
};

const viewTicket = (ticket: IspSupportTicket) => {
    selectedTicket.value = ticket;
    isDetailOpen.value = true;
};

onMounted(() => {
    fetchTickets();
});
</script>
