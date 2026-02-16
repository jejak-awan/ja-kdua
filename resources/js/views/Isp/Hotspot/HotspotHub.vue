<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">{{ t('isp.hotspot.title', 'Hotspot Hub') }}</h2>
                <p class="text-muted-foreground">{{ t('isp.hotspot.subtitle', 'Manage vouchers, sales, and hotspot security') }}</p>
            </div>
        </div>
        <Tabs :default-value="activeTab" @update:model-value="onTabChange" class="w-full">
            <div class="mb-8 flex items-center justify-between border-b">
                <TabsList class="bg-transparent p-0 h-auto gap-0 flex-wrap">
                    <TabsTrigger value="vouchers" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <TicketIcon class="w-4 h-4 mr-2" /> {{ t('isp.hotspot.tabs.vouchers', 'Vouchers') }}
                    </TabsTrigger>
                    <TabsTrigger value="sold" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <ReceiptIcon class="w-4 h-4 mr-2" /> {{ t('isp.hotspot.tabs.sold', 'Sold') }}
                    </TabsTrigger>
                    <TabsTrigger value="ip-bindings" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <LinkIcon class="w-4 h-4 mr-2" /> {{ t('isp.hotspot.tabs.ip_bindings', 'IP Bindings') }}
                    </TabsTrigger>
                    <TabsTrigger value="cookies" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <CookieIcon class="w-4 h-4 mr-2" /> {{ t('isp.hotspot.tabs.cookies', 'Cookies') }}
                    </TabsTrigger>
                </TabsList>
            </div>

            <TabsContent value="vouchers" class="mt-6">
                <Vouchers v-if="loaded.vouchers" />
            </TabsContent>

            <TabsContent value="sold" class="mt-6">
                <Sold v-if="loaded.sold" />
            </TabsContent>

            <TabsContent value="ip-bindings" class="mt-6">
                <IpBindings v-if="loaded.ipBindings" />
            </TabsContent>

            <TabsContent value="cookies" class="mt-6">
                <Cookies v-if="loaded.cookies" />
            </TabsContent>
        </Tabs>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { defineAsyncComponent } from 'vue';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui';
import TicketIcon from 'lucide-vue-next/dist/esm/icons/ticket.js';
import ReceiptIcon from 'lucide-vue-next/dist/esm/icons/receipt.js';
import LinkIcon from 'lucide-vue-next/dist/esm/icons/link.js';
import CookieIcon from 'lucide-vue-next/dist/esm/icons/cookie.js';
import { useI18n } from 'vue-i18n';

const Vouchers = defineAsyncComponent(() => import('./Vouchers.vue'));
const Sold = defineAsyncComponent(() => import('./Sold.vue'));
const IpBindings = defineAsyncComponent(() => import('./IpBindings.vue'));
const Cookies = defineAsyncComponent(() => import('./Cookies.vue'));

const { t } = useI18n();
const route = useRoute();
const router = useRouter();

const activeTab = ref((route.query.tab as string) || 'vouchers');
const loaded = reactive({ vouchers: true, sold: false, ipBindings: false, cookies: false });

const onTabChange = (tab: string | number | boolean) => {
    const tabStr = String(tab);
    activeTab.value = tabStr;
    router.replace({ query: { ...route.query, tab: tabStr === 'vouchers' ? undefined : tabStr } });
    if (tabStr === 'sold') loaded.sold = true;
    if (tabStr === 'ip-bindings') loaded.ipBindings = true;
    if (tabStr === 'cookies') loaded.cookies = true;
};
if (activeTab.value !== 'vouchers') onTabChange(activeTab.value);
</script>
