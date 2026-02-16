<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.bgp_toolkit.title', 'BGP Toolkit') }}</h1>
            <p class="text-muted-foreground">{{ $t('isp.admin.bgp_toolkit.subtitle', 'ASN lookup, prefix analysis, and RouterOS address-list generator') }}</p>
        </div>

        <!-- Search Bar -->
        <Card class="border-border/40">
            <CardContent class="p-4">
                <div class="flex items-center gap-3">
                    <div class="relative flex-1 max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input
                            v-model="asnInput"
                            placeholder="Enter ASN (e.g. AS13335 or 13335)"
                            class="pl-9"
                            @keyup.enter="lookupAsn"
                        />
                    </div>
                    <Button @click="lookupAsn" :disabled="searching">
                        <LoaderCircle v-if="searching" class="w-4 h-4 mr-2 animate-spin" />
                        <Globe v-else class="w-4 h-4 mr-2" />
                        Lookup
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- ASN Info -->
        <Card v-if="asnInfo" class="border-border/40">
            <CardContent class="p-4">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-2.5 rounded-lg bg-primary/10 text-primary">
                        <Globe class="w-5 h-5" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold">{{ asnInfo.asn }}</h2>
                        <p class="text-muted-foreground text-sm">{{ asnInfo.holder }}</p>
                    </div>
                    <Badge v-if="asnInfo.country" variant="outline" class="ml-auto">{{ asnInfo.country }}</Badge>
                </div>
                <div class="flex items-center gap-2">
                    <Button size="sm" @click="fetchPrefixes" :disabled="loadingPrefixes">
                        <LoaderCircle v-if="loadingPrefixes" class="w-4 h-4 mr-2 animate-spin" />
                        <List v-else class="w-4 h-4 mr-2" />
                        Show Prefixes
                    </Button>
                    <Button size="sm" variant="outline" @click="downloadAddressList" v-if="prefixes.length > 0">
                        <Download class="w-4 h-4 mr-2" />
                        Download RouterOS Address-List
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- Prefixes Table -->
        <Card v-if="prefixes.length > 0" class="border-border/40">
            <CardContent class="p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold">Announced Prefixes</h3>
                    <Badge variant="secondary">{{ prefixes.length }} prefixes</Badge>
                </div>
                <div class="border border-border/30 rounded-lg overflow-hidden max-h-[400px] overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/30 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left">#</th>
                                <th class="px-3 py-2 text-left">Prefix</th>
                                <th class="px-3 py-2 text-left">First Seen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(p, i) in prefixes" :key="p.prefix" class="border-t border-border/20 hover:bg-muted/10">
                                <td class="px-3 py-2 text-muted-foreground">{{ i + 1 }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ p.prefix }}</td>
                                <td class="px-3 py-2 text-xs text-muted-foreground">{{ p.timelines || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <!-- Empty State -->
        <Card v-if="!asnInfo && !searching" class="border-dashed border-2 border-border/40">
            <CardContent class="p-12 text-center">
                <Globe class="w-10 h-10 mx-auto text-muted-foreground/30 mb-3" />
                <p class="text-muted-foreground">Enter an AS Number above to look up network details and prefixes.</p>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { Card, CardContent, Button, Badge, Input } from '@/components/ui';

import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import List from 'lucide-vue-next/dist/esm/icons/list.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

const { t: _t } = useI18n();
const toast = useToast();

const asnInput = ref('');
const searching = ref(false);
const loadingPrefixes = ref(false);

interface AsnInfo {
    asn: string;
    holder: string;
    country: string;
}

interface Prefix {
    prefix: string;
    timelines: string;
}

const asnInfo = ref<AsnInfo | null>(null);
const prefixes = ref<Prefix[]>([]);

const lookupAsn = async () => {
    if (!asnInput.value.trim()) return;
    searching.value = true;
    asnInfo.value = null;
    prefixes.value = [];
    try {
        const response = await api.get('/admin/janet/isp/tools/bgp/lookup', { params: { asn: asnInput.value } });
        asnInfo.value = response.data.data;
    } catch (_error) {
        toast.error.action('ASN lookup failed. Please try again.');
    } finally {
        searching.value = false;
    }
};

const fetchPrefixes = async () => {
    if (!asnInput.value.trim()) return;
    loadingPrefixes.value = true;
    try {
        const response = await api.get('/admin/janet/isp/tools/bgp/prefixes', { params: { asn: asnInput.value } });
        prefixes.value = response.data.data.prefixes || [];
    } catch (_error) {
        toast.error.action('Failed to fetch prefixes');
    } finally {
        loadingPrefixes.value = false;
    }
};

const downloadAddressList = () => {
    const asn = asnInput.value.trim();
    if (!asn) return;
    // Direct download link
    window.open(`/api/v1/admin/janet/isp/tools/bgp/download-address-list?asn=${encodeURIComponent(asn)}`, '_blank');
};
</script>
