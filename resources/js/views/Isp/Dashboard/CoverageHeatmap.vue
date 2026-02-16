<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Market Intelligence Heatmap</h1>
                <p class="text-muted-foreground">Geospatial visualization of customer coverage and revenue distribution.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex p-1 bg-muted rounded-xl">
                    <Button 
                        v-for="mode in modes" 
                        :key="mode.id"
                        variant="ghost" 
                        size="sm"
                        :class="['h-8 px-3 transition-all rounded-lg', currentMode === mode.id ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground']"
                        @click="currentMode = mode.id"
                    >
                        <component :is="mode.icon" class="w-3.5 h-3.5 mr-2" />
                        {{ mode.name }}
                    </Button>
                </div>
                <Button variant="outline" size="sm" class="rounded-xl" @click="fetchData">
                    <RefreshCw :class="['w-4 h-4 mr-2', loading && 'animate-spin']" />
                    Sync Data
                </Button>
            </div>
        </div>

        <Card class="overflow-hidden border border-border/40 bg-card/60 backdrop-blur-xl relative rounded-xl">
            <div id="heatmap-container" class="w-full h-[600px] z-0"></div>
            
            <!-- Floating Map Controls Overlay -->
            <div class="absolute top-4 right-4 z-[1000] flex flex-col gap-2">
                <Card class="p-3 bg-background/80 backdrop-blur-md border border-border/20 shadow-2xl space-y-3 w-48 rounded-xl">
                    <p class="text-[10px] font-bold text-muted-foreground">Map Statistics</p>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-muted-foreground">Total Points:</span>
                            <span class="font-bold">{{ heatmapData[currentMode]?.length || 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-muted-foreground">Status:</span>
                            <Badge variant="outline" class="text-[10px] h-5 bg-green-500/10 text-green-500 border-green-500/20">Live</Badge>
                        </div>
                    </div>
                    <Separator class="bg-border/10" />
                    <div class="space-y-1">
                        <p class="text-[10px] font-bold text-muted-foreground mb-2">Legend</p>
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.5)]"></div>
                            <span class="text-[10px]">High Density / Peak</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-yellow-500"></div>
                            <span class="text-[10px]">Moderate</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
                            <span class="text-[10px]">Emerging</span>
                        </div>
                    </div>
                </Card>
            </div>

            <div v-if="loading" class="absolute inset-0 z-[1001] bg-background/20 backdrop-blur-sm flex items-center justify-center">
                <div class="flex flex-col items-center gap-4">
                    <Loader2 class="w-10 h-10 animate-spin text-primary" />
                    <p class="text-sm font-medium animate-pulse">Calculating geospatial vectors...</p>
                </div>
            </div>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, onUnmounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.heat';
import api from '@/services/api';
import { Button, Card, Badge, Separator } from '@/components/ui';
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import DollarSign from 'lucide-vue-next/dist/esm/icons/dollar-sign.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

const loading = ref(true);
const map = ref<L.Map | null>(null);
const heatLayer = ref<any>(null);
const currentMode = ref('coverage');
const heatmapData = ref<any>({ coverage: [], revenue: [] });

const modes = [
    { id: 'coverage', name: 'Coverage Density', icon: Users },
    { id: 'revenue', name: 'Revenue Concentration', icon: DollarSign }
];

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/janet/isp/monitor/heatmap');
        heatmapData.value = response.data.data;
        updateHeatmap();
    } catch (error) {
        console.error('Failed to fetch heatmap data', error);
    } finally {
        loading.value = false;
    }
};

const initMap = () => {
    // Default center (Jakarta as placeholder, will auto-center if points exist)
    map.value = L.map('heatmap-container', {
        zoomControl: false,
        attributionControl: false
    }).setView([-6.2088, 106.8456], 12);

    // Modern dark-themed tile layer
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19
    }).addTo(map.value as any);

    L.control.zoom({ position: 'bottomright' }).addTo(map.value as any);
};

const updateHeatmap = () => {
    if (!map.value) return;

    if (heatLayer.value) {
        map.value.removeLayer(heatLayer.value);
    }

    const data = heatmapData.value[currentMode.value] || [];
    
    // Scale intensity for revenue mode to avoid saturation
    const scaledData = currentMode.value === 'revenue' 
        ? data.map((p: any) => [p[0], p[1], Math.min(p[2] / 500000, 1)]) // Scale 500k to max intensity
        : data;

    heatLayer.value = (L as any).heatLayer(scaledData, {
        radius: 25,
        blur: 15,
        maxZoom: 17,
        gradient: {
            0.4: '#3b82f6', // blue
            0.6: '#eab308', // yellow
            1.0: '#ef4444'  // red
        }
    }).addTo(map.value);

    // Auto-fit bounds if we have points
    if (data.length > 0) {
        const bounds = L.latLngBounds(data.map((p: any) => [p[0], p[1]]));
        map.value.fitBounds(bounds, { padding: [50, 50] });
    }
};

watch(currentMode, () => {
    updateHeatmap();
});

onMounted(() => {
    initMap();
    fetchData();
});

onUnmounted(() => {
    if (map.value) {
        map.value.remove();
    }
});
</script>

<style scoped>
#heatmap-container {
    background: transparent;
}
/* Ensure leaflet doesn't break glassmorphism containers */
:deep(.leaflet-container) {
    background: transparent !important;
}
</style>
