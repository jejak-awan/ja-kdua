<template>
    <section 
        :class="['transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <Tabs :defaultValue="tabs[0]?.id || 'tab-0'" class="w-full">
                <TabsList :class="['w-full', tabsListClass]">
                    <TabsTrigger 
                        v-for="(tab, index) in tabs" 
                        :key="tab.id || 'tab-' + index"
                        :value="tab.id || 'tab-' + index"
                        :class="tabsTriggerClass"
                    >
                        <component v-if="tab.icon" :is="getIcon(tab.icon)" class="w-4 h-4 mr-2" />
                        {{ tab.title || 'Tab ' + (index + 1) }}
                    </TabsTrigger>
                </TabsList>
                <TabsContent 
                    v-for="(tab, index) in tabs" 
                    :key="'content-' + (tab.id || index)"
                    :value="tab.id || 'tab-' + index"
                    class="mt-6 focus-visible:outline-none"
                >
                    <div class="prose prose-sm max-w-none dark:prose-invert" v-html="tab.content || 'Tab content goes here...'"></div>
                </TabsContent>
            </Tabs>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { computed } from 'vue';
import Tabs from '@/components/ui/tabs.vue';
import TabsList from '@/components/ui/tabs-list.vue';
import TabsTrigger from '@/components/ui/tabs-trigger.vue';
import TabsContent from '@/components/ui/tabs-content.vue';
import { Home, User, Settings, Star, Heart, Zap, Mail, Phone } from 'lucide-vue-next';

const props = defineProps({
    tabs: { 
        type: Array, 
        default: () => [
            { id: 'tab-1', title: 'Tab 1', content: '<p>Content for tab 1</p>', icon: '' },
            { id: 'tab-2', title: 'Tab 2', content: '<p>Content for tab 2</p>', icon: '' },
            { id: 'tab-3', title: 'Tab 3', content: '<p>Content for tab 3</p>', icon: '' }
        ] 
    },
    style: { type: String, default: 'underline' },
    width: { type: String, default: 'max-w-4xl' },
    padding: { type: String, default: 'py-12' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const icons = { home: Home, user: User, settings: Settings, star: Star, heart: Heart, zap: Zap, mail: Mail, phone: Phone };
const getIcon = (name) => icons[name] || null;

const tabsListClass = computed(() => ({
    'underline': 'bg-transparent border-b border-border rounded-none gap-4 justify-start',
    'pills': 'bg-muted p-1 rounded-lg gap-1',
    'boxed': 'bg-transparent gap-2'
}[props.style] || 'bg-transparent'));

const tabsTriggerClass = computed(() => ({
    'underline': 'rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent px-4 pb-3',
    'pills': 'rounded-md data-[state=active]:bg-background data-[state=active]:shadow-sm px-4 py-2',
    'boxed': 'border rounded-lg data-[state=active]:border-primary data-[state=active]:bg-primary/5 px-4 py-2'
}[props.style] || ''));
</script>
