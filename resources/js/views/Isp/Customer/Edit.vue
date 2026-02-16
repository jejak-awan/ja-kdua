<template>
    <div class="px-4 py-6 md:px-8">
        <div v-if="loadingCustomer && !customer" class="flex flex-col items-center justify-center min-h-[400px] gap-4">
            <Loader2 class="w-8 h-8 animate-spin text-primary" />
            <p class="text-sm font-medium text-muted-foreground tracking-tight italic">Retrieving Customer Data...</p>
        </div>
        <CustomerForm 
            v-else-if="customer"
            :title="t('isp.billing.customers_manager.edit')" 
            :loading="isSaving" 
            :customer="customer"
            @save="handleSave" 
            @cancel="goBack" 
        />
        <div v-else class="flex flex-col items-center justify-center min-h-[400px] gap-4">
            <p class="text-destructive font-bold">Customer not found or failed to load.</p>
            <Button @click="goBack">Back to List</Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { Button } from '@/components/ui';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import CustomerForm from './Components/CustomerForm.vue';
import type { IspUser } from '@/types/isp';

const router = useRouter();
const route = useRoute();
const { t } = useI18n();
const toast = useToast();

const isSaving = ref(false);
const loadingCustomer = ref(true);
const customer = ref<IspUser | null>(null);

const fetchCustomer = async () => {
    loadingCustomer.value = true;
    try {
        const response = await api.get(`/admin/janet/isp/customers/${route.params.id}`);
        customer.value = response.data.data;
    } catch (error) {
        toast.error.default('Failed to load customer data');
        console.error(error);
    } finally {
        loadingCustomer.value = false;
    }
};

const goBack = () => {
    router.push({ name: 'isp-subscription-customers' });
};

const handleSave = async (data: any) => {
    isSaving.value = true;
    try {
        await api.put(`/admin/janet/isp/customers/${route.params.id}`, data);
        toast.success.default(t('common.messages.success.saved'));
        goBack();
    } catch (error) {
        toast.error.default(t('common.messages.error.save'));
        console.error('Failed to update customer:', error);
    } finally {
        isSaving.value = false;
    }
};

onMounted(fetchCustomer);
</script>
