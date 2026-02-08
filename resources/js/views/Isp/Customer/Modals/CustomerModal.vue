<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        
        <DialogContent class="sm:max-w-[800px] h-[90vh] flex flex-col p-0 gap-0 rounded-2xl overflow-hidden">
            <DialogHeader class="p-6 pb-2">
                <DialogTitle>{{ isEdit ? t('isp.billing.customers_manager.edit') : t('isp.billing.customers_manager.new') }}</DialogTitle>
                <DialogDescription>
                    {{ t('isp.billing.customers_manager.subtitle') }}
                </DialogDescription>
            </DialogHeader>
            
            <div class="flex-1 overflow-y-auto p-6 pt-2">
                <form id="customer-form" @submit.prevent="save" class="space-y-6">
                    <Tabs v-model="activeTab" class="w-full">
                        <TabsList class="grid w-full grid-cols-5 mb-4">
                            <TabsTrigger value="identity">{{ t('isp.billing.customers_manager.tabs.identity') }}</TabsTrigger>
                            <TabsTrigger value="address">{{ t('isp.billing.customers_manager.tabs.address') }}</TabsTrigger>
                            <TabsTrigger value="internet">{{ t('isp.billing.customers_manager.tabs.internet') }}</TabsTrigger>
                            <TabsTrigger value="billing">{{ t('isp.billing.customers_manager.tabs.billing') }}</TabsTrigger>
                            <TabsTrigger value="invoice">{{ t('isp.billing.customers_manager.tabs.invoice') }}</TabsTrigger>
                        </TabsList>

                        <!-- Identity Tab -->
                        <TabsContent value="identity" class="space-y-4 animate-in slide-in-from-right-2 duration-300">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2 col-span-2">
                                    <Label>
                                        {{ t('isp.billing.customers_manager.fields.name') }} <span class="text-destructive">*</span>
                                    </Label>
                                    <Input v-model="form.name" required />
                                </div>
                                <div class="space-y-2">
                                    <Label>
                                        {{ t('isp.billing.customers_manager.fields.email') }} <span class="text-destructive">*</span>
                                    </Label>
                                    <Input type="email" v-model="form.email" required :disabled="isEdit" />
                                </div>
                                <div class="space-y-2">
                                    <Label>{{ t('isp.billing.customers_manager.fields.phone') }}</Label>
                                    <Input v-model="form.phone" />
                                </div>
                                <div class="space-y-2" v-if="!isEdit">
                                    <Label>
                                        {{ t('isp.billing.customers_manager.fields.password') }} <span class="text-destructive">*</span>
                                    </Label>
                                    <Input type="password" v-model="form.password" required />
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-4 border-t pt-4">
                                <div class="space-y-2">
                                    <Label>{{ t('isp.billing.customers_manager.fields.identity_type') }}</Label>
                                    <Select v-model="form.identity_type">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="KTP">KTP</SelectItem>
                                            <SelectItem value="SIM">SIM</SelectItem>
                                            <SelectItem value="Passport">Passport</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2 col-span-2">
                                    <Label>{{ t('isp.billing.customers_manager.fields.identity_number') }}</Label>
                                    <Input v-model="form.identity_number" />
                                </div>
                            </div>
                        </TabsContent>

                        <!-- Address Tab -->
                        <TabsContent value="address" class="space-y-4 animate-in slide-in-from-right-2 duration-300">
                            <div class="flex flex-col md:grid md:grid-cols-3 gap-4 h-auto md:h-[400px]">
                                <div class="col-span-2 relative h-[300px] md:h-full rounded-md overflow-hidden border order-2 md:order-1">
                                    <div id="map" class="w-full h-full z-10"></div>
                                    <div class="absolute top-2 right-2 z-[400] bg-white p-2 rounded shadow text-xs">
                                        {{ form.coordinates || t('isp.billing.customers_manager.fields.coordinates_none') }}
                                    </div>
                                </div>
                                <div class="space-y-4 overflow-y-auto order-1 md:order-2">
                                    <div class="space-y-2">
                                        <Label>{{ t('isp.billing.customers_manager.fields.address_street') }} <span class="text-destructive">*</span></Label>
                                        <Textarea v-model="form.address_street" rows="3" required />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>{{ t('isp.billing.customers_manager.fields.address_village') }}</Label>
                                        <Input v-model="form.address_village" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>{{ t('isp.billing.customers_manager.fields.address_district') }}</Label>
                                        <Input v-model="form.address_district" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>{{ t('isp.billing.customers_manager.fields.address_city') }}</Label>
                                        <Input v-model="form.address_city" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>{{ t('isp.billing.customers_manager.fields.address_province') }}</Label>
                                        <Input v-model="form.address_province" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>{{ t('isp.billing.customers_manager.fields.address_postal_code') }}</Label>
                                        <Input v-model="form.address_postal_code" />
                                    </div>
                                </div>
                            </div>
                        </TabsContent>

                        <!-- Internet Tab (Hardware & Network) -->
                        <TabsContent value="internet" class="space-y-4 animate-in slide-in-from-right-2 duration-300">
                             <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label>{{ t('isp.billing.customers_manager.fields.reseller') }}</Label>
                                    <Select v-model="form.reseller_id_str">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="t('isp.billing.customers_manager.placeholders.select_reseller')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">- None -</SelectItem>
                                            <SelectItem v-for="r in resellers" :key="r.id" :value="String(r.id)">{{ r.name }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label>{{ t('isp.billing.customers_manager.fields.router') }}</Label>
                                    <Select v-model="form.router_id_str">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="t('isp.billing.customers_manager.placeholders.select_router')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="r in routers" :key="r.id" :value="String(r.id)">{{ r.name }} ({{ r.ip_address }})</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label>{{ t('isp.billing.customers_manager.fields.server') }}</Label>
                                    <Select v-model="form.server_id_str">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="t('isp.billing.customers_manager.placeholders.select_server')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="s in servers" :key="s.id" :value="String(s.id)">{{ s.name }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label>{{ t('isp.billing.customers_manager.fields.address_list') }}</Label>
                                    <Input v-model="form.address_list" :placeholder="t('isp.billing.customers_manager.placeholders.address_list')" />
                                </div>
                                <div class="space-y-2">
                                    <Label>{{ t('isp.billing.customers_manager.fields.service_category') }}</Label>
                                    <Select v-model="form.service_category">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="t('isp.billing.customers_manager.placeholders.select_category')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Fiber">{{ t('isp.billing.customers_manager.options.fiber') }}</SelectItem>
                                            <SelectItem value="Wireless">{{ t('isp.billing.customers_manager.options.wireless') }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div class="border-t pt-4">
                                <h3 class="text-sm font-medium mb-2">{{ t('isp.billing.customers_manager.headers.mikrotik_auth') }}</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label>{{ t('isp.billing.customers_manager.fields.mikrotik_login') }}</Label>
                                        <Input v-model="form.mikrotik_login" placeholder="username" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>{{ t('isp.billing.customers_manager.fields.mikrotik_password') }}</Label>
                                        <div class="relative">
                                            <Input :type="showMikrotikPass ? 'text' : 'password'" v-model="form.mikrotik_password" />
                                            <Button 
                                                type="button" 
                                                variant="ghost" 
                                                size="sm" 
                                                class="absolute right-0 top-0 h-full px-3"
                                                @click="showMikrotikPass = !showMikrotikPass"
                                            >
                                                <Eye v-if="!showMikrotikPass" class="w-4 h-4" />
                                                <EyeOff v-else class="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </TabsContent>

                        <!-- Billing Tab -->
                        <TabsContent value="billing" class="space-y-4 animate-in slide-in-from-right-2 duration-300">
                            <div class="space-y-2">
                                <Label>
                                    {{ t('isp.billing.customers_manager.fields.status') }}
                                </Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="isolated">Isolated</SelectItem>
                                        <SelectItem value="inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 border-t pt-4">
                                <div class="space-y-2">
                                     <Label>{{ t('isp.billing.customers_manager.fields.billing_due_date') }}</Label>
                                     <Input type="number" v-model="form.billing_due_date" min="1" max="28" :placeholder="t('isp.billing.customers_manager.placeholders.billing_due_date')" />
                                </div>
                                <div class="space-y-2">
                                     <Label>{{ t('isp.billing.customers_manager.fields.unique_code') }}</Label>
                                     <Input type="number" v-model="form.unique_code" />
                                </div>
                                <div class="flex items-center space-x-2 pt-8">
                                    <Checkbox id="tax" :checked="form.is_taxed" @update:checked="form.is_taxed = $event" />
                                    <Label for="tax">{{ t('isp.billing.customers_manager.fields.is_taxed') }}</Label>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <Label>{{ t('isp.billing.customers_manager.fields.billing_notes') }}</Label>
                                <Textarea v-model="form.billing_notes" />
                            </div>
                        </TabsContent>

                        <!-- Invoice Tab -->
                        <TabsContent value="invoice" class="space-y-4 animate-in slide-in-from-right-2 duration-300">
                             <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium">{{ t('isp.billing.customers_manager.invoice_items.title') }}</h3>
                                        <p class="text-sm text-muted-foreground">{{ t('isp.billing.customers_manager.invoice_items.subtitle') }}</p>
                                    </div>
                                    <Button type="button" size="sm" variant="outline" @click="addInvoiceItem">
                                        <Plus class="w-4 h-4 mr-2" />
                                        {{ t('isp.billing.customers_manager.invoice_items.add_item') }}
                                    </Button>
                                </div>

                                <div class="rounded-md border">
                                    <table class="w-full text-sm">
                                        <thead class="bg-muted/50 border-b">
                                            <tr>
                                                <th class="p-3 text-left font-medium">{{ t('isp.billing.customers_manager.invoice_items.fields.name') }}</th>
                                                <th class="p-3 text-right font-medium w-32">{{ t('isp.billing.customers_manager.invoice_items.fields.price') }}</th>
                                                <th class="p-3 text-center font-medium w-24">{{ t('isp.billing.customers_manager.invoice_items.fields.qty') }}</th>
                                                <th class="p-3 text-right font-medium w-32">{{ t('isp.billing.customers_manager.invoice_items.fields.total') }}</th>
                                                <th class="p-3 text-center font-medium w-16">{{ t('isp.billing.customers_manager.invoice_items.fields.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y">
                                            <tr v-for="(item, index) in form.invoice_items" :key="index">
                                                <td class="p-2">
                                                    <Input v-model="item.name" class="h-8" :placeholder="t('isp.billing.customers_manager.invoice_items.placeholders.item_name')" />
                                                </td>
                                                <td class="p-2">
                                                    <Input type="number" v-model="item.price" class="h-8 text-right" />
                                                </td>
                                                <td class="p-2">
                                                    <Input type="number" v-model="item.qty" class="h-8 text-center" min="1" />
                                                </td>
                                                <td class="p-2 text-right">
                                                    {{ formatCurrency(item.price * item.qty) }}
                                                </td>
                                                <td class="p-2 text-center">
                                                    <Button type="button" variant="ghost" size="sm" class="h-8 w-8 p-0 text-destructive" @click="removeInvoiceItem(index)">
                                                        <Trash2 class="w-4 h-4" />
                                                    </Button>
                                                </td>
                                            </tr>
                                            <tr v-if="form.invoice_items.length === 0">
                                                <td colspan="5" class="p-8 text-center text-muted-foreground italic">
                                                    {{ t('isp.billing.customers_manager.messages.invoice_feature_soon') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-muted/30 font-medium" v-if="form.invoice_items.length > 0">
                                            <tr>
                                                <td colspan="3" class="p-3 text-right">Grand Total:</td>
                                                <td class="p-3 text-right">{{ formatCurrency(invoiceItemsTotal) }}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                             </div>
                        </TabsContent>
                    </Tabs>
                </form>
            </div>

            <DialogFooter class="p-6 pt-2 border-t bg-muted/20">
                 <Button type="button" variant="ghost" @click="$emit('update:open', false)">
                    {{ t('common.actions.cancel') }}
                </Button>
                <Button type="submit" form="customer-form" :disabled="loading">
                    <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                    {{ t('common.actions.save') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, computed, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { 
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
    Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    Textarea, Tabs, TabsContent, TabsList, TabsTrigger, Checkbox
} from '@/components/ui';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import EyeOff from 'lucide-vue-next/dist/esm/icons/eye-off.js';
import type { BillingPlan, IspUser } from '@/types/isp';
import * as L from 'leaflet';

const props = defineProps<{
    open: boolean;
    customer: IspUser | null; 
    loading: boolean;
}>();

const emit = defineEmits(['update:open', 'save']);
const { t } = useI18n();

const isEdit = computed(() => !!props.customer);
const activeTab = ref('identity');
const showMikrotikPass = ref(false);
const plans = ref<BillingPlan[]>([]);
interface DropdownItem {
    id: number;
    name: string;
    ip_address?: string;
}

const resellers = ref<DropdownItem[]>([]);
const routers = ref<DropdownItem[]>([]);
const servers = ref<DropdownItem[]>([]);

// Map state
let map: L.Map | null = null;
let marker: L.Marker | null = null;

// Form state
const form = ref({
    name: '',
    email: '',
    phone: '',
    password: '',
    identity_type: 'KTP',
    identity_number: '',
    address_street: '',
    address_village: '',
    address_district: '',
    address_city: '',
    address_province: '',
    address_postal_code: '',
    coordinates: '',
    billing_plan_id_str: '',
    billing_cycle_start: 1,
    installation_date: '',
    status: 'active',
    mikrotik_login: '',
    mikrotik_password: '',
    // Phase 8.5
    reseller_id_str: '',
    router_id_str: '',
    server_id_str: '',
    address_list: '',
    service_category: '', // Fiber, Wireless
    billing_due_date: '',
    billing_notes: '',
    is_taxed: false,
    unique_code: 0,
    invoice_items: [] as { name: string, price: number, qty: number }[]
});

const addInvoiceItem = () => {
    form.value.invoice_items.push({ name: '', price: 0, qty: 1 });
};

const removeInvoiceItem = (index: number) => {
    form.value.invoice_items.splice(index, 1);
};

const invoiceItemsTotal = computed(() => {
    return form.value.invoice_items.reduce((sum, item) => sum + (item.price * item.qty), 0);
});

// Fetch resources
const fetchResources = async () => {
    try {
        const [planRes, resellerRes, routerRes, serverRes] = await Promise.all([
            api.get('/admin/ja/isp/billing/plans'),
            api.get('/admin/ja/users?role=partner'), // Assuming endpoint exists or filters
            api.get('/admin/ja/isp/routers'),
            api.get('/admin/ja/isp/data-servers')
        ]);
        plans.value = planRes.data.data;
        resellers.value = resellerRes.data.data; // Might need adjustment based on real API
        routers.value = routerRes.data.data;
        servers.value = serverRes.data.data;
    } catch (e) {
        console.error('Failed to load resources', e);
    }
};

const initMap = () => {
    if (map) return;
    
    // Default Jakarta
    const lat = -6.2088;
    const lng = 106.8456;
    
    const container = document.getElementById('map');
    if (!container) return;

    map = L.map('map').setView([lat, lng], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    map.on('click', (e) => {
        const { lat, lng } = e.latlng;
        updateMarker(lat, lng);
    });
};

const updateMarker = (lat: number, lng: number) => {
    if (!map) return;
    
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng]).addTo(map);
    }
    form.value.coordinates = `${lat}, ${lng}`;
};

watch(() => form.value.coordinates, (val) => {
    if (val && map) {
        const [lat, lng] = val.split(',').map(s => parseFloat(s.trim()));
        if (!isNaN(lat) && !isNaN(lng)) {
             if (!marker) {
                marker = L.marker([lat, lng]).addTo(map);
            } else {
                marker.setLatLng([lat, lng]);
            }
            map.setView([lat, lng], 15);
        }
    }
});

watch(() => activeTab.value, (tab) => {
    if (tab === 'address') {
        nextTick(() => { 
            initMap();
            map?.invalidateSize();
        });
    }
});

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        fetchResources();
        
        if (props.customer) {
            const c = props.customer;
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            const profile = (c.customer || c.isp_customer || {}) as any;
            
            form.value = {
                name: c.name,
                email: c.email,
                phone: c.phone || '',
                password: '',
                identity_type: profile.identity_type || 'KTP',
                identity_number: profile.identity_number || '',
                address_street: profile.address_street || '',
                address_village: profile.address_village || '',
                address_district: profile.address_district || '',
                address_city: profile.address_city || '',
                address_province: profile.address_province || '',
                address_postal_code: profile.address_postal_code || '',
                coordinates: profile.coordinates || '',
                billing_plan_id_str: profile.billing_plan_id ? String(profile.billing_plan_id) : '',
                billing_cycle_start: profile.billing_cycle_start || 1,
                installation_date: profile.installation_date || '',
                status: profile.status || 'active',
                mikrotik_login: profile.mikrotik_login || '',
                mikrotik_password: profile.mikrotik_password || '',
                reseller_id_str: profile.reseller_id ? String(profile.reseller_id) : '',
                router_id_str: profile.router_id ? String(profile.router_id) : '',
                server_id_str: profile.server_id ? String(profile.server_id) : '',
                address_list: profile.address_list || '',
                service_category: profile.service_category || '',
                billing_due_date: profile.billing_due_date ? String(profile.billing_due_date) : '',
                billing_notes: profile.billing_notes || '',
                is_taxed: !!profile.is_taxed,
                unique_code: profile.unique_code || 0,
                invoice_items: []
            };
        } else {
             form.value = {
                name: '',
                email: '',
                phone: '',
                password: '',
                identity_type: 'KTP',
                identity_number: '',
                address_street: '',
                address_village: '',
                address_district: '',
                address_city: '',
                address_province: '',
                address_postal_code: '',
                coordinates: '',
                billing_plan_id_str: '',
                billing_cycle_start: 1,
                installation_date: new Date().toISOString().split('T')[0],
                status: 'active',
                mikrotik_login: '',
                mikrotik_password: '',
                reseller_id_str: '',
                router_id_str: '',
                server_id_str: '',
                address_list: '',
                service_category: '',
                billing_due_date: '',
                billing_notes: '',
                is_taxed: false,
                unique_code: Math.floor(Math.random() * 900) + 100, // Random 3 digit
                invoice_items: []
            };
        }
        activeTab.value = 'identity';
        
        // Reset Map
        if (map) {
            map.remove();
            map = null;
            marker = null;
        }
    }
});

const save = () => {
    emit('save', {
        ...form.value,
        billing_plan_id: Number(form.value.billing_plan_id_str),
        reseller_id: form.value.reseller_id_str ? Number(form.value.reseller_id_str) : null,
        router_id: form.value.router_id_str ? Number(form.value.router_id_str) : null,
        server_id: form.value.server_id_str ? Number(form.value.server_id_str) : null,
        is_taxed: form.value.is_taxed ? 1 : 0
    });
};

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};
</script>
