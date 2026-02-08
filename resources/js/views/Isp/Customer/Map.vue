<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('isp.customers.map_title', 'Customer Geolocation Map') }}</h1>
                <p class="text-sm text-muted-foreground">{{ $t('isp.customers.map_subtitle', 'Real-time overview of customer distribution and status') }}</p>
            </div>
            <router-link :to="{ name: 'isp-subscription-customers' }">
                <Button variant="outline">
                    <Users class="w-4 h-4 mr-2" />
                    {{ $t('isp.customers.list_view', 'List View') }}
                </Button>
            </router-link>
        </div>

        <Card class="p-0 overflow-hidden border-0 shadow-lg">
            <div id="customer-map" class="h-[75vh] w-full bg-muted/20"></div>
        </Card>

        <!-- Customer Details Overlay -->
        <div v-if="selectedCustomer" class="fixed bottom-10 right-10 z-[1000] w-80 transition-all">
            <Card class="p-4 shadow-2xl border-primary/20 bg-background/95 backdrop-blur">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg leading-tight">{{ selectedCustomer.name }}</h3>
                        <p class="text-xs text-muted-foreground">ID: #{{ selectedCustomer.id }}</p>
                    </div>
                    <Button variant="ghost" size="icon" class="h-6 w-6" @click="selectedCustomer = null">
                        <X class="w-4 h-4" />
                    </Button>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-muted-foreground">{{ $t('isp.customers.status', 'Status') }}:</span>
                        <Badge :variant="getStatusVariant(selectedCustomer.status)">{{ selectedCustomer.status }}</Badge>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-muted-foreground">{{ $t('isp.customers.coordinates', 'Location') }}:</span>
                        <span class="font-mono text-[10px]">{{ selectedCustomer.lat.toFixed(6) }}, {{ selectedCustomer.lng.toFixed(6) }}</span>
                    </div>
                    <div class="pt-3 border-t border-border/40 flex gap-2">
                        <Button variant="outline" size="sm" class="flex-1 h-8 text-[11px]" @click="viewDetails(selectedCustomer.id)">
                            {{ $t('isp.customers.view_profile', 'View Profile') }}
                        </Button>
                        <Button variant="default" size="sm" class="flex-1 h-8 text-[11px]" @click="contactCustomer(selectedCustomer)">
                            {{ $t('isp.customers.contact', 'Contact') }}
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
import api from '@/services/api';
import { useRouter } from 'vue-router';

interface CustomerGeo {
    id: number;
    name: string;
    lat: number;
    lng: number;
    status: string;
}

const router = useRouter();
const map = ref<L.Map | null>(null);
const markers = ref<L.Marker[]>([]);
const customers = ref<CustomerGeo[]>([]);
const selectedCustomer = ref<CustomerGeo | null>(null);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const getStatusVariant = (status: string): any => {
    switch (status.toLowerCase()) {
        case 'active': return 'success';
        case 'pending': return 'warning';
        case 'suspended': return 'destructive';
        case 'inactive': return 'secondary';
        default: return 'outline';
    }
};

const getMarkerColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'active': return '#10b981'; // Green
        case 'pending': return '#f59e0b'; // Amber
        case 'suspended': return '#ef4444'; // Red
        default: return '#6b7280'; // Gray
    }
};

const initMap = () => {
    map.value = L.map('customer-map').setView([-7.8667, 112.6500], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    }).addTo(map.value as any);

    // Leaflet Icon Fix for Vite
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    (L.Icon.Default.prototype as any)._getIconUrl = undefined;
    L.Icon.Default.mergeOptions({
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    });
};

const fetchCustomers = async () => {
    try {
        const response = await api.get('/admin/ja/isp/customers/geolocation');
        customers.value = response.data;

        markers.value.forEach(m => m.remove());
        markers.value = [];

        customers.value.forEach(c => {
            if (c.lat && c.lng && map.value) {
                const color = getMarkerColor(c.status);
                
                // Custom circle marker for better aesthetics than default pins
                const marker = L.circleMarker([c.lat, c.lng], {
                    radius: 8,
                    fillColor: color,
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                })
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
                .addTo(map.value as any)
                .on('click', () => {
                    selectedCustomer.value = c;
                });

                // eslint-disable-next-line @typescript-eslint/no-explicit-any
                markers.value.push(marker as any);
            }
        });

        if (markers.value.length > 0 && map.value) {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            const group = L.featureGroup(markers.value as any);
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            (map.value as any).fitBounds(group.getBounds().pad(0.2));
        }
    } catch (error) {
        console.error('Failed to fetch customer geolocation', error);
    }
};

const viewDetails = (id: number) => {
    router.push({ name: 'isp-subscription-customers', query: { id: id.toString() } });
};

const contactCustomer = (customer: CustomerGeo) => {
    // Logic for WhatsApp or Email
    console.warn('Contacting customer', customer.name);
};

onMounted(async () => {
    await nextTick();
    initMap();
    fetchCustomers();
});

onUnmounted(() => {
    if (map.value) {
        map.value.remove();
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
