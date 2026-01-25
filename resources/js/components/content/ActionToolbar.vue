<template>
    <div 
        class="sticky top-[64px] z-30 transition-all duration-300 ease-in-out"
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
import Button from '@/components/ui/button.vue';
import { 
    PanelRightClose, 
    PanelRightOpen, 
    Save, 
    Loader2, 
    X
} from 'lucide-vue-next';

const props = defineProps({
    isSidebarOpen: {
        type: Boolean,
        default: true
    },
    loading: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    isEdit: {
        type: Boolean,
        default: false
    }
});

defineEmits(['toggle-sidebar', 'save', 'cancel']);

const saveText = computed(() => {
    if (props.isEdit) {
        return props.loading ? 'Updating...' : 'Update Content';
    }
    return props.loading ? 'Creating...' : 'Create Content';
});
</script>
