<template>
    <Popover>
        <PopoverTrigger asChild>
            <button 
                class="opacity-50 hover:opacity-100 transition-opacity p-1 rounded-md hover:bg-muted" 
                :class="{ 'text-primary opacity-100 bg-primary/5': isConnected }"
                :title="t('features.builder.properties.tooltips.dynamicContent')"
            >
                <Database class="w-3 h-3" />
            </button>
        </PopoverTrigger>
        <PopoverContent class="w-60 p-2" align="end">
            <div class="space-y-4">
                <div v-for="(group, groupName) in groupedSources" :key="groupName" class="space-y-1">
                    <h4 class="font-bold text-[10px] text-muted-foreground px-2 py-1 bg-muted/30 rounded">{{ groupName }}</h4>
                    <div class="grid gap-0.5">
                        <Button 
                            v-for="source in group" 
                            :key="source.id"
                            variant="ghost" 
                            size="sm" 
                            class="justify-start h-8 text-xs font-normal"
                            @click="setDynamic(source.id)"
                        >
                            <component :is="sourceIcons[source.icon] || Database" class="w-3.5 h-3.5 mr-2 opacity-70" />
                            {{ source.label }}
                        </Button>
                    </div>
                </div>
                
                <div v-if="isConnected" class="pt-2 border-t">
                    <Button 
                        variant="ghost" 
                        size="sm" 
                        class="justify-start h-8 w-full text-xs font-normal text-destructive hover:text-destructive hover:bg-destructive/10"
                        @click="setDynamic(null)"
                    >
                        <Unlink class="w-3.5 h-3.5 mr-2" />
                        Disconnect
                    </Button>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>

<script setup>
import { computed } from 'vue';
import Button from '@/components/ui/button.vue';
import Popover from '@/components/ui/popover.vue';
import PopoverTrigger from '@/components/ui/popover-trigger.vue';
import PopoverContent from '@/components/ui/popover-content.vue';
import { Database, Unlink, FileText, Calendar, User, Globe, AlignLeft, Package, DollarSign, Hash, Star, Image, Quote, Tag } from 'lucide-vue-next';
import { dynamicContent } from '@/services/DynamicContentService';

const props = defineProps({
    field: { type: Object, required: true },
    block: { type: Object, required: true }
});

const dynamicSources = dynamicContent.getSources();
const sourceIcons = { FileText, Calendar, User, Globe, AlignLeft, Package, DollarSign, Hash, Star, Image, Quote, Tag };

const groupedSources = computed(() => {
    return dynamicSources.reduce((groups, source) => {
        const group = source.group || 'General';
        if (!groups[group]) groups[group] = [];
        groups[group].push(source);
        return groups;
    }, {});
});

const isConnected = computed(() => {
    return !!(props.block.dynamicSettings && props.block.dynamicSettings[props.field.key]);
});

const setDynamic = (sourceId) => {
    if (!props.block.dynamicSettings) {
        props.block.dynamicSettings = {};
    }
    
    if (sourceId) {
        props.block.dynamicSettings[props.field.key] = sourceId;
    } else {
        delete props.block.dynamicSettings[props.field.key];
    }
};
</script>
