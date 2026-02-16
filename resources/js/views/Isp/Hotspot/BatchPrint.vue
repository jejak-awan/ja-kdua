<template>
    <div class="p-6 bg-white min-h-screen text-black print:p-0">
        <!-- Print Toolset (Hidden on print) -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 p-4 bg-muted/20 border border-border/40 rounded-xl print:hidden">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="sm" @click="$router.back()">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Back
                </Button>
                <div class="h-6 w-px bg-border/40" />
                <div class="flex items-center gap-2">
                    <Label class="text-xs font-bold text-muted-foreground tracking-tight">Layout:</Label>
                    <select v-model="layout" class="bg-background border border-border/50 rounded-lg px-2 py-1 text-sm outline-none">
                        <option value="standard">Standard Card</option>
                        <option value="compact">Compact List</option>
                        <option value="thermal">Thermal (58mm)</option>
                        <option value="qr">QR Code Only</option>
                    </select>
                </div>
            </div>
            <Button @click="print">
                <Printer class="w-4 h-4 mr-2" />
                Print Vouchers
            </Button>
        </div>

        <!-- Vouchers Container -->
        <div :class="['grid gap-4', containerClasses]">
            <div 
                v-for="voucher in vouchers" 
                :key="voucher.id" 
                :class="['border border-black/10 rounded overflow-hidden', itemClasses]"
            >
                <!-- Standard Layout -->
                <div v-if="layout === 'standard'" class="p-4 flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-[10px] font-bold text-muted-foreground leading-none mb-1">Internet Voucher</p>
                        <h2 class="text-lg font-mono font-black tracking-widest">{{ voucher.code }}</h2>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-[9px] font-bold px-1.5 py-0.5 bg-black text-white rounded">{{ voucher.profile }}</span>
                            <span class="text-[10px] font-bold">Rp{{ formatCurrency(voucher.price) }}</span>
                        </div>
                    </div>
                    <div class="w-16 h-16 bg-muted/30 rounded flex items-center justify-center border border-black/5">
                        <QrCode class="w-10 h-10 opacity-20" />
                    </div>
                </div>

                <!-- Compact Layout -->
                <div v-if="layout === 'compact'" class="p-2 flex items-center justify-between border-b last:border-0 border-black/5">
                    <span class="font-mono font-bold">{{ voucher.code }}</span>
                    <span class="text-[10px] opacity-60">{{ voucher.profile }}</span>
                </div>

                <!-- Thermal Layout -->
                <div v-if="layout === 'thermal'" class="p-4 w-[200px] mx-auto text-center border-b border-dashed border-black/20">
                    <p class="text-[10px] font-bold mb-2">Hotspot Access</p>
                    <h2 class="text-2xl font-mono font-black leading-none my-3">{{ voucher.code }}</h2>
                    <div class="text-[10px] space-y-1">
                        <p>User: {{ voucher.code }}</p>
                        <p>Pass: {{ voucher.code }}</p>
                        <p class="font-bold border-t border-black/10 pt-1 mt-1">Rp{{ formatCurrency(voucher.price) }}</p>
                    </div>
                </div>

                <!-- QR Layout -->
                <div v-if="layout === 'qr'" class="p-4 flex flex-col items-center justify-center aspect-square w-32 mx-auto">
                    <div class="w-full aspect-square bg-muted/10 border border-black/5 rounded flex items-center justify-center mb-2">
                        <QrCode class="w-16 h-16 opacity-40" />
                    </div>
                    <span class="text-[10px] font-mono font-bold">{{ voucher.code }}</span>
                </div>
            </div>
        </div>

        <div v-if="loading" class="p-20 text-center">
            <LoaderCircle class="w-10 h-10 animate-spin mx-auto text-primary" />
            <p class="mt-4 text-muted-foreground animate-pulse">Loading batch data...</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/services/api';
import { Button, Label } from '@/components/ui';
import Printer from 'lucide-vue-next/dist/esm/icons/printer.js';
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import QrCode from 'lucide-vue-next/dist/esm/icons/qr-code.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

const route = useRoute();
const layout = ref('standard');
const loading = ref(true);
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const vouchers = ref<any[]>([]);

const batchId = computed(() => route.params.batchId as string);

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/janet/isp/vouchers', {
            params: { batch_id: batchId.value }
        });
        vouchers.value = response.data.data.data;
    } catch (error) {
        console.error('Batch fetch failed', error);
    } finally {
        loading.value = false;
    }
};

const containerClasses = computed(() => {
    switch (layout.value) {
        case 'standard': return 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 print:grid-cols-3';
        case 'compact': return 'grid-cols-1 max-w-md mx-auto';
        case 'thermal': return 'grid-cols-1 max-w-[250px] mx-auto';
        case 'qr': return 'grid-cols-2 md:grid-cols-4 lg:grid-cols-6 print:grid-cols-6';
        default: return '';
    }
});

const itemClasses = computed(() => {
    switch (layout.value) {
        case 'thermal': return 'border-none';
        case 'compact': return 'border-0 border-b';
        default: return '';
    }
});

const formatCurrency = (val: number) => new Intl.NumberFormat('id-ID').format(val);

const print = () => window.print();

onMounted(fetchData);
</script>

<style scoped>
@media print {
    .print\:hidden { display: none !important; }
    .print\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
    .print\:grid-cols-6 { grid-template-columns: repeat(6, minmax(0, 1fr)) !important; }
    body { background: white !important; }
}
</style>
