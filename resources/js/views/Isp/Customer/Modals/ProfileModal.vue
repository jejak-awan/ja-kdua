<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-[600px] rounded-2xl">
            <DialogHeader>
                <DialogTitle>{{ isEdit ? t('isp.billing.plans_manager.edit') : t('isp.billing.plans_manager.new') }}</DialogTitle>
                <DialogDescription>
                    {{ t('isp.billing.plans_manager.subtitle') }}
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="save" class="space-y-4 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2 col-span-2">
                        <Label for="name">{{ t('isp.billing.plans_manager.fields.name') }}</Label>
                        <Input id="name" v-model="form.name" required placeholder="e.g. PAKET-10M" />
                    </div>

                    <div class="space-y-2">
                        <Label for="group">
                            {{ t('isp.billing.plans_manager.fields.mikrotik_group') }}
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger>
                                        <HelpCircle class="w-3 h-3 text-muted-foreground inline-block ml-1" />
                                    </TooltipTrigger>
                                    <TooltipContent>{{ t('isp.billing.plans_manager.fields.mikrotik_group_help') }}</TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </Label>
                        <Input id="group" v-model="form.mikrotik_group" required placeholder="e.g. default" />
                    </div>

                    <div class="space-y-2">
                        <Label for="rate_limit">
                            {{ t('isp.billing.plans_manager.fields.mikrotik_rate_limit') }}
                        </Label>
                        <Input id="rate_limit" v-model="form.mikrotik_rate_limit" :placeholder="t('isp.billing.plans_manager.fields.mikrotik_rate_limit_placeholder')" />
                    </div>

                    <div class="space-y-2">
                        <Label for="shared">{{ t('isp.billing.plans_manager.fields.shared') }}</Label>
                        <Input id="shared" type="number" v-model="form.shared_users" min="1" required />
                    </div>

                    <div class="space-y-2">
                        <Label for="active_period">{{ t('isp.billing.plans_manager.fields.active_period') }}</Label>
                        <Input id="active_period" type="number" v-model="form.active_period" min="1" required />
                    </div>

                    <div class="space-y-2">
                        <Label for="type">{{ t('isp.billing.plans_manager.fields.type') }}</Label>
                        <Select v-model="form.type">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="prepaid">{{ t('isp.billing.plans_manager.options.prepaid') }}</SelectItem>
                                <SelectItem value="postpaid">{{ t('isp.billing.plans_manager.options.postpaid') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label for="status">{{ t('isp.billing.plans_manager.fields.status') }}</Label>
                        <Select v-model="form.is_active_str">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="true">{{ t('isp.billing.plans_manager.options.active') }}</SelectItem>
                                <SelectItem value="false">{{ t('isp.billing.plans_manager.options.inactive') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="border-t border-border/50 pt-4 mt-2">
                    <h4 class="text-sm font-semibold mb-3">Financial</h4>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="price">{{ t('isp.billing.plans_manager.fields.price') }}</Label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-xs">Rp</span>
                                <Input id="price" type="number" v-model="form.price" class="pl-8" min="0" required />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="cost">{{ t('isp.billing.plans_manager.fields.cost_price') }}</Label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-xs">Rp</span>
                                <Input id="cost" type="number" v-model="form.cost_price" class="pl-8" min="0" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="commission">{{ t('isp.billing.plans_manager.fields.commission') }}</Label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-xs">Rp</span>
                                <Input id="commission" type="number" v-model="form.commission" class="pl-8" min="0" />
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter class="mt-4">
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
import { ref, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
    Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    Tooltip, TooltipContent, TooltipProvider, TooltipTrigger
} from '@/components/ui';
import HelpCircle from 'lucide-vue-next/dist/esm/icons/circle-question-mark.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import type { BillingPlan } from '@/types/isp';

const props = defineProps<{
    open: boolean;
    profile: BillingPlan | null;
    loading: boolean;
}>();

const emit = defineEmits(['update:open', 'save']);
const { t } = useI18n();

const isEdit = computed(() => !!props.profile);

// Form state
const form = ref({
    name: '',
    mikrotik_group: '',
    mikrotik_rate_limit: '',
    shared_users: 1,
    active_period: 30,
    type: 'prepaid',
    price: 0,
    cost_price: 0,
    commission: 0,
    is_active_str: 'true'
});

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        if (props.profile) {
            form.value = {
                name: props.profile.name,
                mikrotik_group: props.profile.mikrotik_group || '',
                mikrotik_rate_limit: props.profile.mikrotik_rate_limit || '',
                shared_users: props.profile.shared_users || 1,
                active_period: props.profile.active_period || 30,
                type: props.profile.type || 'prepaid',
                price: props.profile.price || 0,
                cost_price: props.profile.cost_price || 0,
                commission: props.profile.commission || 0,
                is_active_str: props.profile.is_active === false ? 'false' : 'true'
            };
        } else {
            // Reset for new
            form.value = {
                name: '',
                mikrotik_group: 'default',
                mikrotik_rate_limit: '',
                shared_users: 1,
                active_period: 30,
                type: 'prepaid',
                price: 0,
                cost_price: 0,
                commission: 0,
                is_active_str: 'true'
            };
        }
    }
});

const save = () => {
    emit('save', {
        ...form.value,
        is_active: form.value.is_active_str === 'true'
    });
};
</script>
