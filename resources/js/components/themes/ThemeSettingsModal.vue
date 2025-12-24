<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg max-w-4xl w-full max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between p-6 border-b border-border sticky top-0 bg-card z-10">
                    <h3 class="text-lg font-semibold text-foreground">
                        Theme Settings: {{ theme?.name }}
                    </h3>
                    <button
                        @click="$emit('close')"
                        class="text-muted-foreground hover:text-muted-foreground dark:hover:text-foreground"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="flex-1 overflow-y-auto p-6 space-y-6">
                    <div v-if="settings.length === 0" class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <p class="mt-2 text-muted-foreground">No settings available for this theme</p>
                    </div>

                    <div v-else class="space-y-6">
                        <!-- Group settings by category if available -->
                        <div
                            v-for="(group, groupIndex) in groupedSettings"
                            :key="groupIndex"
                            class="space-y-4"
                        >
                            <h4 v-if="group.category" class="text-sm font-semibold text-foreground tracking-wider border-b border-border pb-2">
                                {{ group.category }}
                            </h4>

                            <div class="space-y-4">
                                <div
                                    v-for="setting in group.settings"
                                    :key="setting.key"
                                    class="space-y-2"
                                >
                                    <label class="block text-sm font-medium text-foreground">
                                        {{ setting.label }}
                                        <span v-if="setting.required" class="text-red-500">*</span>
                                    </label>

                                    <!-- Color Picker -->
                                    <div v-if="setting.type === 'color'" class="flex items-center gap-3">
                                        <input
                                            v-model="form[setting.key]"
                                            type="color"
                                            class="h-12 w-20 border border-input rounded-md cursor-pointer"
                                            @input="updateForm(setting.key, $event.target.value)"
                                        >
                                        <input
                                            v-model="form[setting.key]"
                                            type="text"
                                            pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                                            placeholder="#000000"
                                            class="flex-1 px-3 py-2 border border-input rounded-md bg-card text-foreground focus:outline-none focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400 font-mono text-sm"
                                            @input="updateForm(setting.key, $event.target.value)"
                                        >
                                        <div
                                            class="h-10 w-10 rounded border border-input"
                                            :style="{ backgroundColor: form[setting.key] || setting.default || '#000000' }"
                                        ></div>
                                    </div>

                                    <!-- Text Input -->
                                    <input
                                        v-else-if="setting.type === 'text' || setting.type === 'email' || setting.type === 'url'"
                                        v-model="form[setting.key]"
                                        :type="setting.type"
                                        :placeholder="setting.placeholder || ''"
                                        class="w-full px-3 py-2 border border-input rounded-md bg-card text-foreground placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400"
                                        @input="updateForm(setting.key, $event.target.value)"
                                    >

                                    <!-- Number Input -->
                                    <input
                                        v-else-if="setting.type === 'number'"
                                        v-model.number="form[setting.key]"
                                        type="number"
                                        :min="setting.min"
                                        :max="setting.max"
                                        :step="setting.step || 1"
                                        :placeholder="setting.placeholder || ''"
                                        class="w-full px-3 py-2 border border-input rounded-md bg-card text-foreground placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400"
                                        @input="updateForm(setting.key, parseFloat($event.target.value))"
                                    >

                                    <!-- Textarea -->
                                    <textarea
                                        v-else-if="setting.type === 'textarea'"
                                        v-model="form[setting.key]"
                                        :rows="setting.rows || 3"
                                        :placeholder="setting.placeholder || ''"
                                        class="w-full px-3 py-2 border border-input rounded-md bg-card text-foreground placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400"
                                        @input="updateForm(setting.key, $event.target.value)"
                                    />

                                    <!-- Select -->
                                    <select
                                        v-else-if="setting.type === 'select'"
                                        v-model="form[setting.key]"
                                        class="w-full px-3 py-2 border border-input rounded-md bg-card text-foreground focus:outline-none focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400"
                                        @change="updateForm(setting.key, $event.target.value)"
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
                                                v-model="form[setting.key]"
                                                type="radio"
                                                :value="option.value"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input"
                                                @change="updateForm(setting.key, option.value)"
                                            >
                                            <span class="ml-2 text-sm text-foreground">{{ option.label }}</span>
                                        </label>
                                    </div>

                                    <!-- Checkbox -->
                                    <div v-else-if="setting.type === 'checkbox'" class="flex items-start">
                                        <input
                                            v-model="form[setting.key]"
                                            type="checkbox"
                                            class="h-4 w-4 mt-1 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                                            @change="updateForm(setting.key, $event.target.checked)"
                                        >
                                        <div class="ml-3">
                                            <span class="text-sm text-foreground">{{ setting.description || setting.label }}</span>
                                        </div>
                                    </div>

                                    <!-- Range/Slider -->
                                    <div v-else-if="setting.type === 'range'" class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-muted-foreground">{{ form[setting.key] || setting.default || setting.min || 0 }}</span>
                                            <span class="text-sm text-muted-foreground">{{ setting.max || 100 }}</span>
                                        </div>
                                        <input
                                            v-model.number="form[setting.key]"
                                            type="range"
                                            :min="setting.min || 0"
                                            :max="setting.max || 100"
                                            :step="setting.step || 1"
                                            class="w-full h-2 bg-muted rounded-lg appearance-none cursor-pointer"
                                            @input="updateForm(setting.key, parseFloat($event.target.value))"
                                        >
                                    </div>

                                    <!-- Description -->
                                    <p v-if="setting.description && setting.type !== 'checkbox'" class="text-xs text-muted-foreground">
                                        {{ setting.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="flex items-center justify-end space-x-3 p-6 border-t border-border sticky bottom-0 bg-card">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-input rounded-md text-foreground hover:bg-muted"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 disabled:opacity-50"
                    >
                        {{ saving ? 'Saving...' : 'Save Settings' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import api from '../../services/api';

const props = defineProps({
    theme: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const settings = ref([]);
const form = ref({});
const saving = ref(false);

// Group settings by category
const groupedSettings = computed(() => {
    if (settings.value.length === 0) {
        return [];
    }

    const groups = {};
    
    settings.value.forEach(setting => {
        const category = setting.category || 'General';
        if (!groups[category]) {
            groups[category] = {
                category,
                settings: [],
            };
        }
        groups[category].settings.push(setting);
    });

    return Object.values(groups);
});

const fetchSettings = async () => {
    if (!props.theme) return;
    
    try {
        // Get theme manifest for settings schema
        const themeResponse = await api.get(`/admin/cms/themes/${props.theme.id}`);
        const theme = themeResponse.data?.data || themeResponse.data;
        
        if (theme.manifest?.settings_schema) {
            // Convert manifest settings_schema to settings array
            const schema = theme.manifest.settings_schema;
            settings.value = Object.keys(schema).map(key => ({
                key,
                ...schema[key],
                category: schema[key].category || 'General',
            }));
        } else {
            // Fallback to old method if manifest not available
            try {
                const response = await api.get(`/admin/cms/themes/${props.theme.id}/settings`);
                settings.value = response.data.settings || [];
            } catch (error) {
                settings.value = [];
            }
        }
        
        // Load current values
        if (theme.settings) {
            form.value = { ...theme.settings };
        }
        
        // Set defaults from schema
        if (theme.manifest?.settings_schema) {
            Object.keys(theme.manifest.settings_schema).forEach(key => {
                if (form.value[key] === undefined) {
                    const setting = theme.manifest.settings_schema[key];
                    form.value[key] = setting.default ?? null;
                }
            });
        }
    } catch (error) {
        console.error('Failed to fetch theme settings:', error);
        settings.value = [];
    }
};

const updateForm = (key, value) => {
    form.value[key] = value;
};

const handleSubmit = async () => {
    if (!props.theme) return;
    
    saving.value = true;
    try {
        await api.put(`/admin/cms/themes/${props.theme.id}/settings`, {
            settings: form.value,
        });
        emit('saved');
    } catch (error) {
        console.error('Failed to save settings:', error);
        alert('Failed to save settings');
    } finally {
        saving.value = false;
    }
};

watch(() => props.theme, () => {
    if (props.theme) {
        fetchSettings();
    }
}, { immediate: true });

onMounted(() => {
    if (props.theme) {
        fetchSettings();
    }
});
</script>
