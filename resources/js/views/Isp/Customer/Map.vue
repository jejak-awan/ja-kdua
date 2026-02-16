<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('isp.customers.map_title') }}</h1>
                <p class="text-sm text-muted-foreground">{{ $t('isp.customers.map_subtitle') }}</p>
            </div>
            <div class="flex items-center gap-2 bg-muted/30 p-1 rounded-xl border border-border/40">
                <Button 
                    v-for="mode in ['markers', 'coverage', 'revenue']" 
                    :key="mode"
                    :variant="mapMode === mode ? 'default' : 'ghost'"
                    size="sm"
                    class="rounded-lg h-8 text-[11px] px-3 transition-all"
                    @click="setMapMode(mode)"
                >
                    {{ $t(`isp.customers.map_modes.${mode}`) }}
                </Button>
            </div>
            <router-link :to="{ name: 'isp-subscription-customers' }">
                <Button variant="outline" class="rounded-xl h-10">
                    <Users class="w-4 h-4 mr-2" />
                    {{ $t('isp.customers.list_view') }}
                </Button>
            </router-link>
        </div>

        <Card class="p-0 overflow-hidden border-0 shadow-lg rounded-3xl group">
            <div id="customer-map" class="h-[75vh] w-full bg-muted/20 transition-all duration-500 group-hover:scale-[1.002]"></div>
        </Card>

        <!-- Customer Details Overlay -->
        <div v-if="selectedCustomer" class="fixed bottom-10 right-10 z-[1000] w-80 transition-all animate-in slide-in-from-right-4">
            <Card class="p-6 shadow-2xl border-primary/20 bg-background/90 backdrop-blur-xl rounded-2xl">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-bold text-lg leading-tight">{{ selectedCustomer.name }}</h3>
                        <p class="text-xs text-muted-foreground font-mono">ID: #{{ selectedCustomer.id }}</p>
                    </div>
                    <Button variant="ghost" size="icon" class="h-8 w-8 rounded-full hover:bg-muted" @click="selectedCustomer = null">
                        <X class="w-4 h-4" />
                    </Button>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-muted-foreground">{{ $t('isp.customers.status') }}:</span>
                        <Badge :variant="getStatusVariant(selectedCustomer.status)" class="rounded-lg">{{ selectedCustomer.status }}</Badge>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-muted-foreground">{{ $t('isp.customers.coordinates') }}:</span>
                        <span class="font-mono text-[10px] bg-muted px-2 py-1 rounded-md">{{ selectedCustomer.lat.toFixed(6) }}, {{ selectedCustomer.lng.toFixed(6) }}</span>
                    </div>
                    <div class="pt-4 border-t border-border/40 flex gap-2">
                        <Button variant="outline" size="sm" class="flex-1 h-9 rounded-xl text-[11px]" @click="viewDetails(selectedCustomer.id)">
                            {{ $t('isp.customers.view_profile') }}
                        </Button>
                        <Button variant="default" size="sm" class="flex-1 h-9 rounded-xl text-[11px] shadow-lg shadow-primary/20" @click="contactCustomer(selectedCustomer)">
                            {{ $t('isp.customers.contact') }}
                        </Button>
                    </div>
                </div>
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Card, Button, Badge } from '@/components/ui';
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.heat';
import api from '@/services/api';
import { useRouter } from 'vue-router';
import { getStatusVariant } from '@/utils/format';

interface CustomerGeo {
    id: number;
    name: string;
    lat: number;
    lng: number;
    status: string;
}

const router = useRouter();
const mapInstance = ref<L.Map | null>(null);
const markers = ref<L.Layer[]>([]);
const heatmapLayer = ref<any>(null);
const customers = ref<CustomerGeo[]>([]);
const selectedCustomer = ref<CustomerGeo | null>(null);
const mapMode = ref('markers');

const getMarkerColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'active': return '#10b981'; // Green
        case 'pending': return '#f59e0b'; // Amber
        case 'suspended': return '#ef4444'; // Red
        default: return '#6b7280'; // Gray
    }
};

const initMap = () => {
    if (!mapInstance.value) {
        const map = L.map('customer-map').setView([-7.8667, 112.6500], 12);
        mapInstance.value = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Leaflet Icon Fix for Vite
        delete (L.Icon.Default.prototype as any)._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        });
    }
};

const setMapMode = async (mode: string) => {
    mapMode.value = mode;
    selectedCustomer.value = null;

    // Clear existing layers
    if (heatmapLayer.value) {
        heatmapLayer.value.remove();
        heatmapLayer.value = null;
    }
    markers.value.forEach(m => m.remove());
    markers.value = [];

    if (mode === 'markers') {
        fetchCustomers();
    } else {
        await fetchHeatmap(mode);
    }
};

const fetchHeatmap = async (mode: string) => {
    try {
        const response = await api.get('/admin/janet/isp/network/customers/heatmap', { params: { mode } });
        const data = response.data;
        const points = data.map((p: any) => [p.lat, p.lng, p.weight]);
        
        if (mapInstance.value && points.length > 0) {
            let gradient;
            if (mode === 'revenue') {
                gradient = { 0.4: '#3b82f6', 0.65: '#10b981', 1: '#f59e0b' }; // Blue -> Green -> Amber
            } else if (mode === 'potential') {
                gradient = { 0.4: '#a855f7', 0.65: '#ec4899', 1: '#f97316' }; // Purple -> Pink -> Orange
            } else { // Default for 'coverage' (or 'density')
                gradient = { 0.4: '#1e3a8a', 0.65: '#ef4444', 1: '#facc15' }; // Deep Blue -> Red -> Yellow
            }
                
            heatmapLayer.value = (L as any).heatLayer(points, {
                radius: 35,
                blur: 20,
                maxZoom: 16,
                gradient
            }).addTo(mapInstance.value);
            
            // Zoom to points
            const bounds = L.latLngBounds(points.map((p: any) => [p[0], p[1]]));
            mapInstance.value.fitBounds(bounds.pad(0.2));
        }
    } catch (error) {
        console.error('Failed to fetch heatmap data', error);
    }
};

const fetchCustomers = async () => {
    try {
        const response = await api.get('/admin/janet/isp/customers/geolocation');
        customers.value = response.data;

        markers.value.forEach(m => m.remove());
        markers.value = [];

        customers.value.forEach(c => {
            if (c.lat && c.lng && mapInstance.value) {
                const color = getMarkerColor(c.status);
                
                const marker = L.circleMarker([c.lat, c.lng], {
                    radius: 8,
                    fillColor: color,
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                })
                .addTo(mapInstance.value as L.Map)
                .on('click', () => {
                    selectedCustomer.value = c;
                });

                markers.value.push(marker);
            }
        });

        if (markers.value.length > 0 && mapInstance.value) {
            const group = L.featureGroup(markers.value as any);
            (mapInstance.value as L.Map).fitBounds(group.getBounds().pad(0.2));
        }
    } catch (error) {
        console.error('Failed to fetch customer geolocation', error);
    }
};

const viewDetails = (id: number) => {
    router.push({ name: 'isp-subscription-customers', query: { id: id.toString() } });
};

const contactCustomer = (customer: CustomerGeo) => {
    console.warn('Contacting customer', customer.name);
};

onMounted(async () => {
    await nextTick();
    initMap();
    fetchCustomers();
});

onUnmounted(() => {
    if (mapInstance.value) {
        mapInstance.value.remove();
    }
});
</script>

<style scoped>
#customer-map {
    z-index: 10;
}
.dark .leaflet-tile {
    filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
}
</style>
