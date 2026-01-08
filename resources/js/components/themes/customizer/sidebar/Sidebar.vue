<template>
    <div 
        class="flex flex-col bg-card border-r shadow-2xl transition-all duration-300 ease-in-out z-10"
        :class="isCollapsed ? 'w[60px]' : 'w-96'"
    >
        <!-- Header -->
        <div class="h-16 flex items-center px-4 border-b shrink-0 bg-muted/30 transition-all justify-between">
            <div class="flex items-center gap-3 overflow-hidden" :class="{'opacity-0 w-0': isCollapsed}">
                <div>
                    <h2 class="font-semibold text-lg tracking-tight whitespace-nowrap">Customize</h2>
                    <p class="text-xs text-muted-foreground truncate max-w-[150px]">{{ themeName }}</p>
                </div>
            </div>

            <div class="flex items-center gap-1">
                 <!-- Collapse Button -->
                 <button 
                    @click="isCollapsed = !isCollapsed"
                    class="p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-md transition-colors"
                    :title="isCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
                >
                    <svg 
                        class="w-5 h-5 transition-transform duration-300" 
                        :class="{'rotate-180': isCollapsed}" 
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>

                 <!-- Actions (Hidden when collapsed) -->
                 <div v-show="!isCollapsed" class="flex items-center gap-1 animate-in fade-in duration-200">
                    <div class="flex items-center border rounded-md bg-background mr-2 overflow-hidden">
                        <button 
                            @click="$emit('undo')" 
                            :disabled="!canUndo"
                            class="p-1.5 hover:bg-muted text-muted-foreground hover:text-foreground disabled:opacity-30 transition-colors"
                            title="Undo"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                        </button>
                        <div class="w-px h-4 bg-border"></div>
                        <button 
                            @click="$emit('redo')" 
                            :disabled="!canRedo"
                            class="p-1.5 hover:bg-muted text-muted-foreground hover:text-foreground disabled:opacity-30 transition-colors"
                            title="Redo"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" /></svg>
                        </button>
                    </div>

                    <button 
                        @click="$emit('reset')"
                        class="p-2 text-muted-foreground hover:text-primary transition-colors rounded-full hover:bg-muted"
                        title="Reset Default"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    </button>
                    <button 
                        @click="$emit('close')"
                        class="p-2 text-muted-foreground hover:text-destructive transition-colors rounded-full hover:bg-muted"
                        title="Close"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div v-show="!isCollapsed" class="flex-1 overflow-y-auto px-6 py-6 custom-scrollbar animate-in fade-in slide-in-from-left-4 duration-300">
            
            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <svg class="w-8 h-8 text-primary animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <!-- Settings Sections -->
            <div v-else class="space-y-6">
                 <div v-for="section in sections" :key="section.id" class="space-y-3">
                    <button 
                        @click="toggleSection(section.id)"
                        class="w-full flex items-center justify-between py-2 text-sm font-medium hover:text-primary transition-colors border-b group"
                    >
                        <span class="group-hover:translate-x-1 transition-transform">{{ section.label }}</span>
                        <svg 
                            class="w-4 h-4 transition-transform duration-200"
                            :class="{ 'rotate-180': expandedSections.includes(section.id) }"
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div 
                        v-show="expandedSections.includes(section.id)" 
                        class="space-y-5 pl-2 animate-in slide-in-from-top-2 duration-200"
                    >
                        <SettingControl 
                            v-for="setting in section.settings" 
                            :key="setting.key"
                            :setting="setting"
                            v-model="formValues[setting.key]"
                            @change="$emit('change')"
                            @pick-media="openMediaPicker(setting.key)"
                        />
                    </div>
                 </div>

                 <!-- Custom CSS Section -->
                 <div class="space-y-3 pt-6 border-t">
                    <button 
                        @click="showCustomCss = !showCustomCss"
                        class="w-full flex items-center justify-between py-2 text-sm font-medium hover:text-primary transition-colors"
                    >
                        <span>Custom CSS</span>
                        <span class="text-[10px] bg-muted px-2 py-0.5 rounded text-muted-foreground font-mono">&lt;/&gt;</span>
                    </button>
                    
                    <div v-show="showCustomCss" class="animate-in slide-in-from-top-2">
                        <textarea
                            :value="customCss"
                            @input="$emit('update:customCss', $event.target.value)"
                            @change="$emit('change')"
                            rows="8"
                            class="w-full p-3 bg-background border rounded-lg text-xs font-mono custom-scrollbar focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all leading-relaxed"
                            placeholder="/* Enter custom CSS here... */"
                            spellcheck="false"
                        ></textarea>
                     </div>
                 </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div v-show="!isCollapsed" class="p-4 border-t bg-muted/30 shrink-0 animate-in fade-in duration-300">
            <button
                @click="$emit('save')"
                :disabled="saving || !isDirty"
                class="w-full h-10 flex items-center justify-center gap-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ saving ? 'Saving Changes...' : 'Save Configuration' }}</span>
            </button>
        </div>

        <!-- Media Picker Modal -->
        <MediaPicker
            v-model:open="showMediaPicker"
            @selected="handleMediaSelect"
        >
            <template #trigger><span class="hidden"></span></template>
        </MediaPicker>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import SettingControl from './SettingControl.vue';
import MediaPicker from '../../../MediaPicker.vue';

const props = defineProps({
    sections: { type: Array, default: () => [] },
    formValues: { type: Object, required: true },
    customCss: { type: String, default: '' },
    loading: Boolean,
    saving: Boolean,
    isDirty: Boolean,
    themeName: String,
    canUndo: Boolean,
    canRedo: Boolean,
});

const emit = defineEmits(['update:customCss', 'change', 'undo', 'redo', 'save', 'reset', 'close']);

const isCollapsed = ref(false);
const expandedSections = ref(['General']);
const showCustomCss = ref(false);
const showMediaPicker = ref(false);
const activeMediaField = ref(null);

const toggleSection = (id) => {
    if (expandedSections.value.includes(id)) {
        expandedSections.value = expandedSections.value.filter(s => s !== id);
    } else {
        expandedSections.value.push(id);
    }
};

const openMediaPicker = (fieldKey) => {
    activeMediaField.value = fieldKey;
    showMediaPicker.value = true;
};

const handleMediaSelect = (media) => {
    if (activeMediaField.value) {
        // Mutate formValues directly as it's a reactive prop object from parent
        props.formValues[activeMediaField.value] = media.url;
        emit('change');
    }
    showMediaPicker.value = false;
    activeMediaField.value = null;
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: hsl(var(--muted-foreground) / 0.3);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: hsl(var(--muted-foreground) / 0.5);
}
</style>
