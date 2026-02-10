<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ t('isp.network.topology.title') }}</h1>
                <p class="text-muted-foreground">{{ t('isp.network.topology.subtitle') }}</p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="refreshTopology">
                    <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" />
                    {{ t('common.actions.refresh') }}
                </Button>
            </div>
        </div>

        <Card class="h-[70vh] w-full overflow-hidden border-border/40 shadow-sm relative bg-muted/5">
            <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-background/50 z-50 backdrop-blur-sm">
                <Loader2 class="w-10 h-10 animate-spin text-primary" />
            </div>
            
            <div id="topology-container" class="w-full h-full cursor-grab active:cursor-grabbing">
                <!-- D3 Graph will render here -->
                <svg width="100%" height="100%"></svg>
            </div>

            <!-- Legend -->
            <div class="absolute bottom-4 left-4 bg-background/90 backdrop-blur p-4 rounded-xl border shadow-lg space-y-2 text-xs">
                <div class="font-bold mb-2">{{ t('isp.network.topology.legend') }}</div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                    <span>OLT / Core</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-purple-500"></div>
                    <span>ODP / POP</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    <span>Customer Router</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <span>Offline</span>
                </div>
            </div>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { Card, Button } from '@/components/ui';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
// We'll assume a basic D3 or similar implementation for now, or just placeholders
// In a real implementation we'd import d3 here

const { t } = useI18n();
const loading = ref(false);

const refreshTopology = async () => {
    loading.value = true;
    setTimeout(() => {
        // Mock data fetch
        loading.value = false;
        renderGraph();
    }, 1500);
};

// Placeholder for graph rendering logic
const renderGraph = () => {
    // Logic to render nodes (OLT -> ODP -> Customers)
    console.warn('Rendering topology graph...');
};

onMounted(() => {
    refreshTopology();
});
</script>
