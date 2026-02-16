<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    Tabs, TabsList, TabsTrigger, TabsContent
} from '@/components/ui';
import NodesInventoryTab from './Tabs/NodesInventoryTab.vue';
import RouterManagementTab from './Tabs/RouterManagementTab.vue';
import VpnTab from './Tabs/VpnTab.vue';
import RadiusTab from './Tabs/RadiusTab.vue';

const { t } = useI18n();
const activeTab = ref('inventory');

</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-foreground">
                    {{ t('isp.infra.title') }}
                </h2>
                <p class="text-muted-foreground">
                    {{ t('isp.infra.subtitle') }}
                </p>
            </div>
        </div>

        <Tabs v-model="activeTab" class="w-full">
            <div class="mb-8 flex items-center justify-between border-b">
                <TabsList class="bg-transparent p-0 h-auto gap-0 flex-wrap">
                    <TabsTrigger value="inventory" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        {{ t('isp.infra.inventory') }}
                    </TabsTrigger>
                    <TabsTrigger value="router" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        {{ t('isp.infra.router.title') }}
                    </TabsTrigger>
                    <TabsTrigger value="vpn" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        {{ t('isp.infra.vpn') }}
                    </TabsTrigger>
                    <TabsTrigger value="radius" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        {{ t('isp.radius.title') }}
                    </TabsTrigger>
                </TabsList>
            </div>

            <TabsContent value="inventory" class="mt-0 border-none p-0">
                <NodesInventoryTab />
            </TabsContent>
            <TabsContent value="router" class="mt-0 border-none p-0">
                <RouterManagementTab />
            </TabsContent>

            <TabsContent value="vpn" class="mt-0 border-none p-0">
                <VpnTab />
            </TabsContent>

            <TabsContent value="radius" class="mt-0 border-none p-0">
                <RadiusTab />
            </TabsContent>
        </Tabs>
    </div>
</template>


