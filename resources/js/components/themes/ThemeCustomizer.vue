<template>
    <div class="fixed inset-0 z-50 flex overflow-hidden bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <!-- Sidebar -->
        <div class="w-96 flex flex-col bg-card border-r shadow-2xl animate-in slide-in-from-left duration-300">
            <!-- Header -->
            <div class="h-16 flex items-center justify-between px-6 border-b shrink-0 bg-muted/30">
                <div>
                    <h2 class="font-semibold text-lg tracking-tight">Customize</h2>
                    <p class="text-xs text-muted-foreground">{{ fullTheme.name }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        @click="resetSettings"
                        class="p-2 text-muted-foreground hover:text-primary transition-colors rounded-full hover:bg-muted"
                        title="Reset Default"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    </button>
                    <button 
                        @click="$emit('close')"
                        class="p-2 text-muted-foreground hover:text-destructive transition-colors rounded-full hover:bg-muted"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto px-6 py-6 custom-scrollbar">
                
                <!-- Loading State -->
                <div v-if="loading" class="flex items-center justify-center py-12">
                    <svg class="w-8 h-8 text-primary animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Settings Sections -->
                <div v-else class="space-y-6">
                     <div v-for="section in settingsSections" :key="section.id" class="space-y-3">
                        <button 
                            @click="toggleSection(section.id)"
                            class="w-full flex items-center justify-between py-2 text-sm font-medium hover:text-primary transition-colors border-b"
                        >
                            <span>{{ section.label }}</span>
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
                            <div v-for="setting in section.settings" :key="setting.key" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-medium text-foreground tracking-wide">
                                        {{ setting.label }}
                                    </label>
                                    <span v-if="setting.required" class="text-[10px] text-destructive">*</span>
                                </div>

                                <!-- Color Picker -->
                                <div v-if="setting.type === 'color'" class="flex gap-2">
                                    <div class="relative w-10 h-10 rounded-lg overflow-hidden border shadow-sm shrink-0 group cursor-pointer">
                                        <input
                                            type="color"
                                            :value="formValues[setting.key]"
                                            class="absolute inset-0 w-[150%] h-[150%] -top-[25%] -left-[25%] p-0 m-0 opacity-0 cursor-pointer"
                                            @input="updateSetting(setting.key, $event.target.value)"
                                        >
                                        <div 
                                            class="w-full h-full"
                                            :style="{ backgroundColor: formValues[setting.key] }"
                                        ></div>
                                    </div>
                                    <input
                                        type="text"
                                        :value="formValues[setting.key]"
                                        @input="updateSetting(setting.key, $event.target.value)"
                                        class="flex-1 h-10 px-3 py-2 bg-background border rounded-lg text-sm font-mono focus:ring-1 focus:ring-inset focus:ring-primary focus:border-primary outline-none transition-all"
                                    >
                                </div>

                                <!-- Select -->
                                <div v-else-if="setting.type === 'select'" class="relative">
                                    <select
                                        :value="formValues[setting.key]"
                                        @change="updateSetting(setting.key, $event.target.value)"
                                        class="w-full h-9 pl-3 pr-8 bg-background border rounded-lg text-sm appearance-none focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all cursor-pointer"
                                    >
                                        <option v-for="opt in setting.options" :key="opt.value" :value="opt.value">
                                            {{ opt.label }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted-foreground">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" /></svg>
                                    </div>
                                </div>
                                
                                <!-- Range Slider -->
                                <div v-else-if="setting.type === 'range'" class="flex items-center gap-3">
                                    <input 
                                        type="range"
                                        :min="setting.min || 0"
                                        :max="setting.max || 100"
                                        :step="setting.step || 1"
                                        :value="formValues[setting.key]"
                                        @input="updateSetting(setting.key, $event.target.value)"
                                        class="flex-1 h-2 bg-secondary rounded-lg appearance-none cursor-pointer accent-primary"
                                    >
                                    <span class="text-xs font-mono bg-muted px-2 py-1 rounded text-muted-foreground min-w-[3ch] text-center">
                                        {{ formValues[setting.key] }}
                                    </span>
                                </div>

                                <!-- Textarea -->
                                <textarea 
                                    v-else-if="setting.type === 'textarea'"
                                    :value="formValues[setting.key]"
                                    @input="updateSetting(setting.key, $event.target.value)"
                                    rows="3"
                                    class="w-full p-3 bg-background border rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-y min-h-[80px]"
                                ></textarea>

                                <!-- Toggle Switch -->
                                <label v-else-if="setting.type === 'checkbox'" class="flex items-center cursor-pointer gap-3 p-2 border rounded-lg bg-background/50 hover:bg-background transition-colors">
                                    <div class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus-within:ring-2 focus-within:ring-primary/20 focus-within:ring-offset-2" :class="formValues[setting.key] ? 'bg-primary' : 'bg-muted'">
                                        <input 
                                            type="checkbox" 
                                            class="sr-only" 
                                            :checked="formValues[setting.key]"
                                            @change="updateSetting(setting.key, $event.target.checked)"
                                        >
                                        <span class="translate-x-1 inline-block h-3 w-3 transform rounded-full bg-white transition-transform" :class="formValues[setting.key] ? 'translate-x-5' : 'translate-x-1'"></span>
                                    </div>
                                    <span class="text-sm font-medium text-foreground select-none">{{ formValues[setting.key] ? 'Enabled' : 'Disabled' }}</span>
                                </label>

                                <!-- Media Picker -->
                                <div v-else-if="setting.type === 'media'" class="space-y-2">
                                    <div v-if="formValues[setting.key]" class="relative group aspect-video bg-muted rounded-lg overflow-hidden border shadow-sm">
                                        <img :src="formValues[setting.key]" class="w-full h-full object-contain" alt="Preview">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                            <button @click="openMediaPicker(setting.key)" class="p-2 bg-white/20 hover:bg-white/40 rounded-full text-white backdrop-blur-sm transition-colors" title="Change Image">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </button>
                                            <button @click="updateSetting(setting.key, '')" class="p-2 bg-white/20 hover:bg-white/40 rounded-full text-white backdrop-blur-sm transition-colors" title="Remove">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <button 
                                        v-else
                                        @click="openMediaPicker(setting.key)"
                                        class="w-full h-20 border-2 border-dashed rounded-lg flex flex-col items-center justify-center gap-1 text-muted-foreground hover:text-primary hover:border-primary/50 transition-all bg-muted/10 hover:bg-muted/20"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        <span class="text-[10px] font-medium">Select Media</span>
                                    </button>
                                </div>

                                <!-- Default Input (Text/URL/etc) -->
                                <input
                                    v-else
                                    :type="setting.type || 'text'"
                                    :value="formValues[setting.key]"
                                    @input="updateSetting(setting.key, $event.target.value)"
                                    :placeholder="setting.placeholder"
                                    class="w-full h-9 px-3 bg-background border rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                                >
                                
                                <p v-if="setting.description" class="text-[10px] text-muted-foreground leading-snug">
                                    {{ setting.description }}
                                </p>
                            </div>
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
                                v-model="customCss"
                                rows="8"
                                class="w-full p-3 bg-background border rounded-lg text-xs font-mono custom-scrollbar focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all leading-relaxed"
                                placeholder="/* Enter custom CSS here... */"
                                @input="updateCustomCss"
                                spellcheck="false"
                            ></textarea>
                         </div>
                     </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="p-4 border-t bg-muted/30 shrink-0">
                <button
                    @click="saveSettings"
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
        </div>

        <!-- Preview Area -->
        <div class="flex-1 bg-muted/50 p-8 h-full overflow-hidden flex flex-col">
            <div class="flex-1 rounded-xl shadow-2xl overflow-hidden bg-background ring-1 ring-border/50">
                 <ThemePreview
                    :theme="previewTheme"
                    :preview-url="previewUrl"
                />
            </div>
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
import { ref, computed, watch, onMounted } from 'vue';
import api from '../../services/api';
import ThemePreview from './ThemePreview.vue';
import MediaPicker from '../MediaPicker.vue';
import { toast } from '../../services/toast';
import { useConfirm } from '../../composables/useConfirm';

const props = defineProps({
    theme: { type: Object, required: true },
    previewUrl: { type: String, default: '/' },
});

const emit = defineEmits(['close', 'saved']);

const fullTheme = ref({ ...props.theme });
const formValues = ref({});
const customCss = ref('');
const showCustomCss = ref(false);
const saving = ref(false);
const loading = ref(true);
const previewTheme = ref({});
const expandedSections = ref(['General']); // Default open

const showMediaPicker = ref(false);
const activeMediaField = ref(null);

const initialSettings = ref(null);
const initialCss = ref('');
const availableMenus = ref([]);

const openMediaPicker = (fieldKey) => {
    activeMediaField.value = fieldKey;
    showMediaPicker.value = true;
};

const handleMediaSelect = (media) => {
    if (activeMediaField.value) {
        updateSetting(activeMediaField.value, media.url);
    }
    showMediaPicker.value = false;
    activeMediaField.value = null;
};

const fetchMenus = async () => {
    try {
        const response = await api.get('/admin/cms/menus');
        const data = response.data.data || response.data;
        availableMenus.value = (Array.isArray(data) ? data : []).map(m => ({
            value: m.id,
            label: m.name
        }));
        // Add empty option
        availableMenus.value.unshift({ value: '', label: 'Select a Menu...' });
    } catch (error) {
        console.error('Failed to fetch menus:', error);
    }
};

const isDirty = computed(() => {
    const settingsChanged = JSON.stringify(formValues.value) !== JSON.stringify(initialSettings.value);
    const cssChanged = customCss.value !== initialCss.value;
    return settingsChanged || cssChanged;
});

// Fetch full theme details including manifest
const fetchThemeDetails = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/themes/${props.theme.id}`);
        // Handle both wrapped and unwrapped responses typically from Laravel resources
        const data = response.data.data || response.data;
        fullTheme.value = data;
        loadSettings();
    } catch (error) {
        console.error('Failed to fetch theme details:', error);
        toast.error('Error', 'Failed to load theme configuration.');
    } finally {
        loading.value = false;
    }
};

// Organize settings into sections by category
const settingsSections = computed(() => {
    const schema = fullTheme.value.manifest?.settings_schema || {};
    const sectionsMap = {};

    // 1. Process regular settings
    Object.keys(schema).forEach(key => {
        const setting = schema[key];
        const category = setting.category || 'General';
        
        if (!sectionsMap[category]) {
            sectionsMap[category] = {
                id: category,
                label: category,
                settings: [],
            };
        }
        
        sectionsMap[category].settings.push({ key, ...setting });
    });

    // 2. Inject Menu Locations if available
    if (fullTheme.value.manifest?.menus) {
        const menuSettings = Object.entries(fullTheme.value.manifest.menus).map(([locKey, locLabel]) => ({
            key: `menu_location_${locKey}`,
            label: `${locLabel}`,
            type: 'select',
            category: 'Menus',
            options: availableMenus.value,
            description: `Assign a menu to the ${locLabel} position`
        }));

        if (menuSettings.length > 0) {
            sectionsMap['Menus'] = {
                id: 'Menus',
                label: 'Menus',
                settings: menuSettings
            };
        }
    }

    const categoryOrder = ['General', 'Colors', 'Typography', 'Layout', 'Hero Section', 'Footer', 'Social Media'];
    return Object.values(sectionsMap).sort((a, b) => {
        const idxA = categoryOrder.indexOf(a.label);
        const idxB = categoryOrder.indexOf(b.label);
        return (idxA === -1 ? 99 : idxA) - (idxB === -1 ? 99 : idxB);
    });
});

const toggleSection = (id) => {
    if (expandedSections.value.includes(id)) {
        expandedSections.value = expandedSections.value.filter(s => s !== id);
    } else {
        expandedSections.value.push(id);
    }
};

const loadSettings = () => {
    // 1. Start with defaults
    const defaults = {};
    if (fullTheme.value.manifest?.settings_schema) {
        Object.keys(fullTheme.value.manifest.settings_schema).forEach(key => {
            defaults[key] = fullTheme.value.manifest.settings_schema[key].default ?? '';
        });
    }

    // 2. Override with saved settings
    formValues.value = { ...defaults, ...(fullTheme.value.settings || {}) };
    initialSettings.value = JSON.parse(JSON.stringify(formValues.value));
    
    customCss.value = fullTheme.value.custom_css || '';
    initialCss.value = customCss.value;
    
    updatePreviewTheme();
};

const updateSetting = (key, value) => {
    formValues.value[key] = value;
    updatePreviewTheme();
};

const updateCustomCss = () => {
    updatePreviewTheme();
};

const updatePreviewTheme = () => {
    previewTheme.value = {
        ...fullTheme.value,
        settings: { ...formValues.value },
        custom_css: customCss.value,
    };
};

const { confirm } = useConfirm();

const resetSettings = async () => {
    const confirmed = await confirm({
        title: 'Reset Theme Settings',
        message: 'Are you sure you want to reset all theme settings to defaults? This action cannot be undone.',
        confirmText: 'Yes, Reset',
        cancelText: 'Cancel',
        variant: 'warning'
    });

    if (confirmed) {
        formValues.value = {};
        customCss.value = '';
        loadSettings();
        toast.info('Settings Reset', 'All settings have been restored to defaults.');
        updatePreviewTheme(); // Force update immediately
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        await api.put(`/admin/cms/themes/${props.theme.id}/settings`, { settings: formValues.value });
        if (customCss.value !== fullTheme.value.custom_css) {
            await api.put(`/admin/cms/themes/${props.theme.id}/custom-css`, { custom_css: customCss.value });
        }
        toast.success('Success', 'Theme settings saved successfully.');
        initialSettings.value = JSON.parse(JSON.stringify(formValues.value));
        initialCss.value = customCss.value;
        emit('saved');
    } catch (error) {
        console.error('Failed to save settings:', error);
        toast.error('Error', 'Failed to save settings. Please try again.');
    } finally {
        saving.value = false;
    }
};

watch(() => props.theme, (newTheme) => {
    // Determine if we actually need to refetch (e.g., if ID changed)
    if (newTheme.id !== fullTheme.value.id) {
        fetchThemeDetails();
    }
}, { deep: true });

onMounted(() => {
    fetchThemeDetails();
    fetchMenus();
});
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

