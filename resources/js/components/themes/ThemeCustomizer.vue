<template>
    <div class="theme-customizer">
        <div class="flex h-screen">
            <!-- Settings Panel -->
            <div class="w-80 bg-white border-r border-gray-200 overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-gray-200 p-4 z-10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Customize Theme</h3>
                        <button
                            @click="$emit('close')"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-4 space-y-6">
                    <!-- Settings Sections -->
                    <div
                        v-for="section in settingsSections"
                        :key="section.id"
                        class="space-y-4 border-b border-gray-200 pb-6 last:border-b-0"
                    >
                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            {{ section.label }}
                        </h4>

                        <div class="space-y-4">
                            <div
                                v-for="setting in section.settings"
                                :key="setting.key"
                                class="space-y-2"
                            >
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ setting.label }}
                                    <span v-if="setting.required" class="text-red-500">*</span>
                                </label>

                                <!-- Color Picker -->
                                <div v-if="setting.type === 'color'" class="flex items-center gap-2">
                                    <input
                                        v-model="formValues[setting.key]"
                                        type="color"
                                        class="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                                        @input="updateSetting(setting.key, $event.target.value)"
                                    >
                                    <input
                                        v-model="formValues[setting.key]"
                                        type="text"
                                        pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                                        placeholder="#000000"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm font-mono"
                                        @input="updateSetting(setting.key, $event.target.value)"
                                    >
                                    <div
                                        class="h-10 w-10 rounded border border-gray-300"
                                        :style="{ backgroundColor: formValues[setting.key] || setting.default || '#000000' }"
                                    ></div>
                                </div>

                                <!-- Text Input -->
                                <input
                                    v-else-if="setting.type === 'text' || setting.type === 'email' || setting.type === 'url'"
                                    v-model="formValues[setting.key]"
                                    :type="setting.type"
                                    :placeholder="setting.placeholder || ''"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                    @input="updateSetting(setting.key, $event.target.value)"
                                >

                                <!-- Number Input -->
                                <input
                                    v-else-if="setting.type === 'number'"
                                    v-model.number="formValues[setting.key]"
                                    type="number"
                                    :min="setting.min"
                                    :max="setting.max"
                                    :step="setting.step || 1"
                                    :placeholder="setting.placeholder || ''"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                    @input="updateSetting(setting.key, parseFloat($event.target.value))"
                                >

                                <!-- Textarea -->
                                <textarea
                                    v-else-if="setting.type === 'textarea'"
                                    v-model="formValues[setting.key]"
                                    :rows="setting.rows || 3"
                                    :placeholder="setting.placeholder || ''"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                    @input="updateSetting(setting.key, $event.target.value)"
                                />

                                <!-- Select -->
                                <select
                                    v-else-if="setting.type === 'select'"
                                    v-model="formValues[setting.key]"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                    @change="updateSetting(setting.key, $event.target.value)"
                                >
                                    <option value="">{{ setting.placeholder || 'Select...' }}</option>
                                    <option
                                        v-for="option in setting.options"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>

                                <!-- Radio -->
                                <div v-else-if="setting.type === 'radio'" class="space-y-2">
                                    <label
                                        v-for="option in setting.options"
                                        :key="option.value"
                                        class="flex items-center"
                                    >
                                        <input
                                            v-model="formValues[setting.key]"
                                            type="radio"
                                            :value="option.value"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                            @change="updateSetting(setting.key, option.value)"
                                        >
                                        <span class="ml-2 text-sm text-gray-700">{{ option.label }}</span>
                                    </label>
                                </div>

                                <!-- Checkbox -->
                                <div v-else-if="setting.type === 'checkbox'" class="flex items-start">
                                    <input
                                        v-model="formValues[setting.key]"
                                        type="checkbox"
                                        class="h-4 w-4 mt-1 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        @change="updateSetting(setting.key, $event.target.checked)"
                                    >
                                    <div class="ml-3">
                                        <span class="text-sm text-gray-700">{{ setting.description || setting.label }}</span>
                                    </div>
                                </div>

                                <!-- Range/Slider -->
                                <div v-else-if="setting.type === 'range'" class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">{{ formValues[setting.key] || setting.default || setting.min || 0 }}</span>
                                        <span class="text-sm text-gray-600">{{ setting.max || 100 }}</span>
                                    </div>
                                    <input
                                        v-model.number="formValues[setting.key]"
                                        type="range"
                                        :min="setting.min || 0"
                                        :max="setting.max || 100"
                                        :step="setting.step || 1"
                                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                        @input="updateSetting(setting.key, parseFloat($event.target.value))"
                                    >
                                </div>

                                <!-- Description -->
                                <p v-if="setting.description && setting.type !== 'checkbox'" class="text-xs text-gray-500">
                                    {{ setting.description }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Custom CSS -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                Custom CSS
                            </h4>
                            <button
                                @click="showCustomCss = !showCustomCss"
                                class="text-xs text-indigo-600 hover:text-indigo-800"
                            >
                                {{ showCustomCss ? 'Hide' : 'Show' }}
                            </button>
                        </div>
                        <textarea
                            v-if="showCustomCss"
                            v-model="customCss"
                            rows="6"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm font-mono"
                            placeholder="/* Custom CSS */"
                            @input="updateCustomCss"
                        />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4 space-y-2">
                    <button
                        @click="saveSettings"
                        :disabled="saving"
                        class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <button
                        @click="resetSettings"
                        class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
                    >
                        Reset to Defaults
                    </button>
                </div>
            </div>

            <!-- Preview Panel -->
            <div class="flex-1 bg-gray-100">
                <ThemePreview
                    :theme="previewTheme"
                    :preview-url="previewUrl"
                    @close="$emit('close')"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import api from '../../services/api';
import ThemePreview from './ThemePreview.vue';

const props = defineProps({
    theme: {
        type: Object,
        required: true,
    },
    previewUrl: {
        type: String,
        default: '/',
    },
});

const emit = defineEmits(['close', 'saved']);

const formValues = ref({});
const customCss = ref('');
const showCustomCss = ref(false);
const saving = ref(false);
const previewTheme = ref({});

// Organize settings into sections by category
const settingsSections = computed(() => {
    if (!props.theme.manifest?.settings_schema) {
        return [];
    }

    const schema = props.theme.manifest.settings_schema;
    const sectionsMap = {};

    // Group settings by category
    Object.keys(schema).forEach(key => {
        const setting = schema[key];
        const category = setting.category || 'General';
        
        if (!sectionsMap[category]) {
            sectionsMap[category] = {
                id: category.toLowerCase().replace(/\s+/g, '-'),
                label: category,
                settings: [],
            };
        }
        
        sectionsMap[category].settings.push({
            key,
            ...setting,
        });
    });

    // Convert to array and sort by predefined order
    const categoryOrder = [
        'General',
        'Colors',
        'Typography',
        'Layout',
        'Footer',
        'Content',
        'Performance',
    ];

    const sections = Object.values(sectionsMap);
    
    // Sort sections by predefined order
    sections.sort((a, b) => {
        const aIndex = categoryOrder.indexOf(a.label);
        const bIndex = categoryOrder.indexOf(b.label);
        
        if (aIndex === -1 && bIndex === -1) return 0;
        if (aIndex === -1) return 1;
        if (bIndex === -1) return -1;
        
        return aIndex - bIndex;
    });

    return sections;
});

// Load current settings
const loadSettings = () => {
    if (props.theme.settings) {
        formValues.value = { ...props.theme.settings };
    }

    // Load defaults from manifest
    if (props.theme.manifest?.settings_schema) {
        Object.keys(props.theme.manifest.settings_schema).forEach(key => {
            if (formValues.value[key] === undefined) {
                const setting = props.theme.manifest.settings_schema[key];
                formValues.value[key] = setting.default ?? null;
            }
        });
    }

    customCss.value = props.theme.custom_css || '';
    
    // Update preview theme
    updatePreviewTheme();
};

// Update setting and preview
const updateSetting = (key, value) => {
    formValues.value[key] = value;
    updatePreviewTheme();
};

// Update custom CSS and preview
const updateCustomCss = () => {
    updatePreviewTheme();
};

// Update preview theme object and apply CSS variables
const updatePreviewTheme = () => {
    previewTheme.value = {
        ...props.theme,
        settings: { ...formValues.value },
        custom_css: customCss.value,
    };

    // Apply CSS variables to preview iframe
    applyCssVariablesToPreview();
};

// Apply CSS variables to preview iframe for live preview
const applyCssVariablesToPreview = () => {
    // This will be handled by ThemePreview component
    // We emit the CSS variables as part of the theme object
    const variables = [];
    const schema = props.theme.manifest?.settings_schema || {};

    Object.keys(schema).forEach(key => {
        const setting = schema[key];
        if (setting.type === 'color' && formValues.value[key]) {
            const cssKey = '--theme-' + key.replace(/_/g, '-');
            variables.push(`${cssKey}: ${formValues.value[key]};`);
        }
    });

    // Add typography variables
    if (formValues.value.heading_font) {
        variables.push(`--theme-heading-font: ${formValues.value.heading_font};`);
    }
    if (formValues.value.body_font) {
        variables.push(`--theme-body-font: ${formValues.value.body_font};`);
    }
    if (formValues.value.font_size_base) {
        variables.push(`--theme-font-size-base: ${formValues.value.font_size_base}px;`);
    }

    // Add layout variables
    if (formValues.value.container_width) {
        variables.push(`--theme-container-width: ${formValues.value.container_width};`);
    }

    if (variables.length > 0) {
        previewTheme.value.css_variables = `:root {\n  ${variables.join('\n  ')}\n}`;
    }
};

// Save settings
const saveSettings = async () => {
    saving.value = true;
    try {
        // Save settings
        await api.put(`/admin/cms/themes/${props.theme.id}/settings`, {
            settings: formValues.value,
        });

        // Save custom CSS
        if (customCss.value !== props.theme.custom_css) {
            await api.put(`/admin/cms/themes/${props.theme.id}/custom-css`, {
                custom_css: customCss.value,
            });
        }

        emit('saved');
    } catch (error) {
        console.error('Failed to save settings:', error);
        alert('Failed to save settings');
    } finally {
        saving.value = false;
    }
};

// Reset to defaults
const resetSettings = () => {
    if (confirm('Reset all settings to defaults?')) {
        formValues.value = {};
        customCss.value = '';
        loadSettings();
    }
};

// Watch for theme changes
watch(() => props.theme, () => {
    loadSettings();
}, { deep: true, immediate: true });

onMounted(() => {
    loadSettings();
});
</script>

<style scoped>
.theme-customizer {
    position: fixed;
    inset: 0;
    z-index: 50;
    background-color: white;
}
</style>

