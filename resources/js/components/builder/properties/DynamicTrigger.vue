<template>
    <Popover>
        <PopoverTrigger asChild>
            <Button 
                variant="outline" 
                size="icon" 
                class="h-8 w-8 shrink-0" 
                :class="{ 'text-primary border-primary bg-primary/5': isConnected }"
                title="Dynamic Content"
            >
                <Database class="w-3.5 h-3.5" />
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-60 p-2" align="end">
            <div class="space-y-2">
                <h4 class="font-medium text-xs text-muted-foreground uppercase">Connect to...</h4>
                <div class="grid gap-1">
                    <Button 
                        v-for="source in dynamicSources" 
                        :key="source.id"
                        variant="ghost" 
                        size="sm" 
                        class="justify-start h-8 text-xs font-normal"
                        @click="setDynamic(source.id)"
                    >
                        <component :is="sourceIcons[source.icon] || Database" class="w-3 h-3 mr-2 opacity-70" />
                        {{ source.label }}
                    </Button>
                    <Button 
                        v-if="isConnected"
                        variant="ghost" 
                        size="sm" 
                        class="justify-start h-8 text-xs font-normal text-destructive hover:text-destructive"
                        @click="setDynamic(null)"
                    >
                        <Unlink class="w-3 h-3 mr-2" />
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
import { Database, Unlink, FileText, Calendar, User, Globe, AlignLeft } from 'lucide-vue-next';
import { dynamicContent } from '@/services/DynamicContentService';

const props = defineProps({
    field: { type: Object, required: true },
    block: { type: Object, required: true }
});

const dynamicSources = dynamicContent.getSources();
const sourceIcons = { FileText, Calendar, User, Globe, AlignLeft };

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
