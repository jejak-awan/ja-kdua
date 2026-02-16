<template>
    <div class="px-4 py-6 md:px-8">
        <CustomerForm 
            :title="t('isp.billing.customers_manager.new')" 
            :loading="isSaving" 
            :customer="null"
            @save="handleSave" 
            @cancel="goBack" 
        />
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import CustomerForm from './Components/CustomerForm.vue';

const router = useRouter();
const { t } = useI18n();
const toast = useToast();

const isSaving = ref(false);

const goBack = () => {
    router.push({ name: 'isp-subscription-customers' });
};

const handleSave = async (data: any) => {
    isSaving.value = true;
    try {
        await api.post('/admin/janet/isp/customers', data);
        toast.success.default(t('common.messages.success.saved'));
        goBack();
    } catch (error) {
        toast.error.default(t('common.messages.error.save'));
        console.error('Failed to create customer:', error);
    } finally {
        isSaving.value = false;
    }
};
</script>
