<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('isp.infra.map_title') }}</h1>
                <p class="text-sm text-muted-foreground">{{ $t('isp.infra.map_subtitle') }}</p>
            </div>
            <router-link :to="{ name: 'isp.infra' }">
                <Button variant="outline">
                    <List class="w-4 h-4 mr-2" />
                    {{ $t('isp.infra.list_view') }}
                </Button>
            </router-link>
        </div>

        <Card class="p-0 overflow-hidden border-0 shadow-lg">
            <div id="infra-map" class="h-[50vh] md:h-[70vh] w-full bg-muted/20"></div>
        </Card>

        <!-- Node Details Overlay (Optional) -->
        <div v-if="selectedNode" class="fixed bottom-10 right-10 z-[1000] w-72 transition-all">
            <Card class="p-4 shadow-2xl border-primary/20 bg-background/95 backdrop-blur">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold text-lg">{{ selectedNode.name }}</h3>
                    <Button variant="ghost" size="icon" class="h-6 w-6" @click="selectedNode = null">
                        <X class="w-4 h-4" />
                    </Button>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">{{ $t('isp.infra.type') }}:</span>
                        <Badge variant="outline">{{ selectedNode.type }}</Badge>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">IP:</span>
                        <span class="font-mono text-primary">{{ selectedNode.ip_address }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">{{ $t('isp.infra.status') }}:</span>
                        <Badge :variant="getStatusVariant(selectedNode.status)">{{ selectedNode.status }}</Badge>
                    </div>
                    <div class="pt-2 border-t border-border/40 mt-2">
                        <router-link :to="{ name: 'isp.infra' }" class="w-full">
                            <Button variant="outline" size="sm" class="w-full h-8 text-[10px]">
                                {{ $t('isp.infra.view_details') }}
                            </Button>
                        </router-link>
                    </div>
                </div>
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Card, Button, Badge } from '@/components/ui';
import List from 'lucide-vue-next/dist/esm/icons/list.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import L from 'leaflet';
import api from '@/services/api';

interface NetworkNode {
    id: number;
    name: string;
    type: string;
    ip_address: string;
    status: string;
    location_lat: number;
    location_lng: number;
    metadata?: Record<string, unknown>;
}

const map = ref<L.Map | null>(null);
const markers = ref<L.Marker[]>([]);
const nodes = ref<NetworkNode[]>([]);
const selectedNode = ref<NetworkNode | null>(null);

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'active': return 'success';
        case 'maintenance': return 'warning';
        case 'inactive': return 'destructive';
        default: return 'secondary';
    }
};

const initMap = () => {
    // Default center (e.g., Central High POP from seeder)
    map.value = L.map('infra-map').setView([-7.8667, 112.6500], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map.value as L.Map);

    // Fix for Leaflet default icon issues with Vite
    type LeafletIconPrototype = { _getIconUrl?: string };
    delete (L.Icon.Default.prototype as LeafletIconPrototype)._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    });
};

const fetchNodes = async () => {
    if (!map.value) return;
    
    try {
        const response = await api.get('/admin/ja/isp/infra', { params: { per_page: 100 } });
        nodes.value = response.data.data.data || response.data.data;
        
        // Remove existing markers
        markers.value.forEach(m => m.remove());
        markers.value = [];

        // Add new markers
        nodes.value.forEach(node => {
            if (node.location_lat && node.location_lng && map.value) {
                const marker = L.marker([node.location_lat, node.location_lng])
                    .addTo(map.value as L.Map)
                    .on('click', () => {
                        selectedNode.value = node;
                    });
                
                markers.value.push(marker);
            }
        });

        // Fit bounds if we have markers
        if (markers.value.length > 0 && map.value) {
            const group = L.featureGroup(markers.value as unknown as L.Layer[]);
            map.value.fitBounds(group.getBounds().pad(0.1));
        }
    } catch (error) {
        console.error('Failed to fetch nodes for map', error);
    }
};

onMounted(async () => {
    await nextTick();
    initMap();
    fetchNodes();
});

onUnmounted(() => {
    if (map.value) {
        map.value.remove();
    }
});
</script>

<style scoped>
#infra-map {
    z-index: 10;
}
/* Ensure dark mode colors for map if needed */
.dark .leaflet-tile {
    filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
}
</style>
