<template>
    <div 
        class="sticky top-[64px] z-30 transition-colors duration-300 ease-in-out"
        :class="[
            isSidebarOpen ? 'w-full' : 'w-12',
            'bg-background/80 backdrop-blur-md border border-border shadow-sm rounded-xl overflow-hidden mb-6'
        ]"
    >
        <div class="flex items-center" :class="isSidebarOpen ? 'justify-between px-4 py-2.5' : 'flex-col py-4 gap-4 px-0'">
            <!-- Left Side / Top (Toggle) -->
            <Button 
                variant="ghost" 
                size="icon" 
                @click="$emit('toggle-sidebar')"
                :title="isSidebarOpen ? 'Collapse Settings' : 'Expand Settings'"
                class="h-8 w-8 hover:bg-accent/50"
            >
                <PanelRightClose v-if="isSidebarOpen" class="w-4 h-4 text-muted-foreground" />
                <PanelRightOpen v-else class="w-4 h-4 text-muted-foreground" />
            </Button>

            <!-- Right Side / Bottom (Actions) -->
            <div 
                class="flex items-center gap-2"
                :class="isSidebarOpen ? '' : 'flex-col w-full'"
            >
                <template v-if="isSidebarOpen">
                    <Button 
                        variant="ghost" 
                        size="sm" 
                        @click="$emit('cancel')"
                        class="text-muted-foreground hover:text-foreground h-8"
                    >
                        {{ $t('features.content.form.cancel') }}
                    </Button>
                    <Button
                        @click="$emit('save')"
                        :disabled="loading || disabled"
                        size="sm"
                        class="h-8 px-4 bg-primary hover:bg-primary/90 text-primary-foreground shadow-sm"
                    >
                        <Loader2 v-if="loading" class="w-3.5 h-3.5 mr-1.5 animate-spin" />
                        <Save v-else class="w-3.5 h-3.5 mr-1.5" />
                        {{ saveText }}
                    </Button>
                </template>
                <template v-else>
                    <!-- Icon-only mode when collapsed -->
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="$emit('save')"
                        :disabled="loading || disabled"
                        class="h-8 w-8 bg-primary/10 hover:bg-primary/20 text-primary rounded-lg"
                        :title="saveText"
                    >
                        <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
                        <Save v-else class="w-4 h-4" />
                    </Button>
                    
                    <Button 
                        variant="ghost" 
                        size="icon" 
                        @click="$emit('cancel')"
                        class="h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10"
                        title="Cancel"
                    >
                        <X class="w-4 h-4" />
                    </Button>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui';
import PanelRightClose from 'lucide-vue-next/dist/esm/icons/panel-right-close.js';
import PanelRightOpen from 'lucide-vue-next/dist/esm/icons/panel-right-open.js';
import Save from 'lucide-vue-next/dist/esm/icons/save.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';

const props = withDefaults(defineProps<{
    isSidebarOpen?: boolean;
    loading?: boolean;
    disabled?: boolean;
    isEdit?: boolean;
}>(), {
    isSidebarOpen: true,
    loading: false,
    disabled: false,
    isEdit: false
});

const emit = defineEmits<{
    (e: 'toggle-sidebar'): void;
    (e: 'save'): void;
    (e: 'cancel'): void;
}>();

const saveText = computed(() => {
    if (props.isEdit) {
        return props.loading ? 'Updating...' : 'Update Content';
    }
    return props.loading ? 'Creating...' : 'Create Content';
});
</script>
