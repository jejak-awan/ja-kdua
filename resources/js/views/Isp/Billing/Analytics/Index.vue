<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">{{ t('isp.analytics.title', 'Analytics') }}</h2>
                <p class="text-muted-foreground">{{ t('isp.analytics.subtitle', 'Revenue, usage analytics, and business intelligence') }}</p>
            </div>
        </div>

        <Tabs :default-value="activeTab" @update:model-value="onTabChange" class="w-full">
            <div class="mb-8 flex items-center justify-between border-b">
                <TabsList class="bg-transparent p-0 h-auto gap-0 flex-wrap">
                    <TabsTrigger value="revenue" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <BarChart class="w-4 h-4 mr-2" /> {{ t('isp.analytics.tabs.revenue', 'Revenue') }}
                    </TabsTrigger>
                    <TabsTrigger value="usage" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <Activity class="w-4 h-4 mr-2" /> {{ t('isp.analytics.tabs.usage', 'Usage') }}
                    </TabsTrigger>
                    <TabsTrigger value="bi" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <Brain class="w-4 h-4 mr-2" /> {{ t('isp.analytics.tabs.bi', 'Business Intelligence') }}
                    </TabsTrigger>
                </TabsList>
            </div>

            <TabsContent value="revenue" class="mt-6">
                <Revenue v-if="loaded.revenue" />
            </TabsContent>

            <TabsContent value="usage" class="mt-6">
                <UsageDashboard v-if="loaded.usage" />
            </TabsContent>

            <TabsContent value="bi" class="mt-6">
                <BiDashboard v-if="loaded.bi" />
            </TabsContent>
        </Tabs>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { defineAsyncComponent } from 'vue';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui';
import BarChart from 'lucide-vue-next/dist/esm/icons/chart-bar.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import Brain from 'lucide-vue-next/dist/esm/icons/brain.js';
import { useI18n } from 'vue-i18n';

const Revenue = defineAsyncComponent(() => import('../Reports/Revenue.vue'));
const UsageDashboard = defineAsyncComponent(() => import('../../Dashboard/UsageDashboard.vue'));
const BiDashboard = defineAsyncComponent(() => import('./BiDashboard.vue'));

const { t } = useI18n();
const route = useRoute();
const router = useRouter();

const activeTab = ref((route.query.tab as string) || 'revenue');
const loaded = reactive({ revenue: true, usage: false, bi: false });

const onTabChange = (tab: string | number | boolean) => {
    const tabStr = String(tab);
    activeTab.value = tabStr;
    router.replace({ query: { ...route.query, tab: tabStr === 'revenue' ? undefined : tabStr } });
    if (tabStr === 'usage') loaded.usage = true;
    if (tabStr === 'bi') loaded.bi = true;
};
if (activeTab.value !== 'revenue') onTabChange(activeTab.value);
</script>
