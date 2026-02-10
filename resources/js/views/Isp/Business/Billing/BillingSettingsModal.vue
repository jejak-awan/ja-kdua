<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-[600px] rounded-2xl">
            <DialogHeader>
                <DialogTitle>{{ t('isp.billing.settings.title') }}</DialogTitle>
                <DialogDescription>
                    {{ t('isp.billing.settings.subtitle') }}
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="save" class="space-y-6 py-4">
                <Tabs v-model="activeTab" class="w-full">
                    <TabsList class="grid w-full grid-cols-3 mb-4">
                        <TabsTrigger value="general">{{ t('isp.billing.settings.tabs.general') }}</TabsTrigger>
                        <TabsTrigger value="tax">{{ t('isp.billing.settings.tabs.tax') }}</TabsTrigger>
                        <TabsTrigger value="isolation">{{ t('isp.billing.settings.tabs.isolation') }}</TabsTrigger>
                    </TabsList>

                    <TabsContent value="general" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('isp.billing.settings.fields.payment_type') }}</Label>
                                <Select v-model="form.billing_payment_type">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="prepaid">Prepaid (Prabayar)</SelectItem>
                                        <SelectItem value="postpaid">Postpaid (Pascabayar)</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('isp.billing.settings.fields.cycle_type') }}</Label>
                                <Select v-model="form.billing_cycle_type">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="fixed">Fixed Date (e.g. 1st of Month)</SelectItem>
                                        <SelectItem value="profile">Profile Duration (30 Days)</SelectItem>
                                        <SelectItem value="monthly">Anniversary (Installation Date)</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 border p-4 rounded-xl">
                            <Checkbox id="prorata" v-model:checked="form.billing_prorata" />
                            <div class="space-y-1">
                                <Label for="prorata" class="cursor-pointer">{{ t('isp.billing.settings.fields.prorata') }}</Label>
                                <p class="text-xs text-muted-foreground">
                                    {{ t('isp.billing.settings.fields.prorata_desc') }}
                                </p>
                            </div>
                        </div>
                    </TabsContent>

                    <TabsContent value="tax" class="space-y-4">
                        <div class="flex items-center space-x-2 border p-4 rounded-xl mb-4">
                            <Checkbox id="tax_enabled" v-model:checked="form.billing_tax_enabled" />
                            <Label for="tax_enabled" class="cursor-pointer">{{ t('isp.billing.settings.fields.tax_enabled') }}</Label>
                        </div>
                        
                        <div v-if="form.billing_tax_enabled" class="space-y-2 animate-in fade-in slide-in-from-top-2">
                            <Label>{{ t('isp.billing.settings.fields.tax_rate') }} (%)</Label>
                            <div class="relative">
                                <Input type="number" v-model="form.billing_tax_rate" step="0.1" min="0" max="100" class="pr-8" />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground">%</span>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ t('isp.billing.settings.fields.tax_rate_help') }}
                            </p>
                        </div>
                    </TabsContent>

                    <TabsContent value="isolation" class="space-y-4">
                         <div class="space-y-2">
                            <Label>{{ t('isp.billing.settings.fields.suspend_behavior') }}</Label>
                            <Select v-model="form.billing_suspend_behavior">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="payment_date">On Due Date (Jatuh Tempo)</SelectItem>
                                    <SelectItem value="isolation_date">On Specific Isolation Date</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('isp.billing.settings.fields.invoice_date') }}</Label>
                                <Input type="number" v-model="form.billing_invoice_generation_date" min="1" max="28" />
                                <p class="text-xs text-muted-foreground">{{ t('isp.billing.settings.fields.invoice_date_help') }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('isp.billing.settings.fields.isolation_date') }}</Label>
                                <Input type="number" v-model="form.billing_isolation_date" min="1" max="28" />
                                <p class="text-xs text-muted-foreground">{{ t('isp.billing.settings.fields.isolation_date_help') }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>{{ t('isp.billing.settings.fields.isolation_time') }}</Label>
                            <Input type="time" v-model="form.billing_isolation_time" />
                        </div>
                    </TabsContent>
                </Tabs>

                <DialogFooter>
                     <Button type="button" variant="ghost" @click="$emit('update:open', false)">
                        {{ t('common.actions.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="loading">
                        <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                        {{ t('common.actions.save') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { 
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
    Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    Checkbox, Tabs, TabsContent, TabsList, TabsTrigger
} from '@/components/ui';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits(['update:open']);
const { t } = useI18n();
const toast = useToast();

const loading = ref(false);
const activeTab = ref('general');

const form = ref({
    billing_payment_type: 'prepaid',
    billing_cycle_type: 'fixed',
    billing_invoice_generation_date: 1,
    billing_isolation_date: 20,
    billing_isolation_time: '23:59',
    billing_prorata: false,
    billing_tax_enabled: false,
    billing_tax_rate: 11,
    billing_suspend_behavior: 'isolation_date'
});

const loadSettings = async () => {
    loading.value = true;
    try {
        const response = await api.get('/settings/groups/isp_billing');
        if (response.data.success) {
            const settings = response.data.data;
            if (Object.keys(settings).length > 0) {
                form.value = { ...form.value, ...settings };
                // Ensure number types
                form.value.billing_invoice_generation_date = Number(settings.billing_invoice_generation_date || 1);
                form.value.billing_isolation_date = Number(settings.billing_isolation_date || 20);
                form.value.billing_tax_rate = Number(settings.billing_tax_rate || 11);
            }
        }
    } catch (error) {
        console.error('Failed to load settings', error);
    } finally {
        loading.value = false;
    }
};

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        loadSettings();
    }
});

const save = async () => {
    loading.value = true;
    try {
        const settingsToUpdate = Object.entries(form.value).map(([key, value]) => {
            let type = 'string';
            if (typeof value === 'boolean') type = 'boolean';
            if (typeof value === 'number') type = 'integer';
            if (key === 'billing_tax_rate') type = 'string'; // Decimal as string usually safer, or integer for 2 decimals

            return {
                key,
                value,
                type,
                group: 'isp_billing'
            };
        });

        await api.patch('/settings/bulk', { settings: settingsToUpdate });
        toast.success.default(t('common.messages.success.saved'));
        emit('update:open', false);
    } catch (_error) {
        toast.error.default(t('common.messages.error.save'));
    } finally {
        loading.value = false;
    }
};
</script>
