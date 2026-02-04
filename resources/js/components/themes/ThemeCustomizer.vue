<template>
    <div class="fixed inset-0 z-50 flex overflow-hidden bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <Sidebar 
            :sections="settingsSections"
            :form-values="formValues"
            v-model:custom-css="customCss"
            :loading="loading"
            :saving="saving"
            :is-dirty="isDirty"
            :theme-name="fullTheme.name"
            :can-undo="canUndo"
            :can-redo="canRedo"
            @change="pushHistory"
            @undo="undo"
            @redo="redo"
            @save="saveSettings"
            @reset="resetSettings"
            @close="$emit('close')"
        />

        <PreviewArea 
            :preview-theme="previewTheme"
            :preview-url="previewUrl"
        />
    </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import api from '../../services/api';
import Sidebar from './customizer/sidebar/Sidebar.vue';
import PreviewArea from './customizer/preview/PreviewArea.vue';
import { toast } from '../../services/toast';
import { useConfirm } from '../../composables/useConfirm';
import type { Theme, ThemeSection } from '@/types/theme';

const props = defineProps<{
    theme: Theme;
    previewUrl?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

// State
const fullTheme = ref<Theme>({ ...props.theme });
const formValues = ref<Record<string, unknown>>({});
const customCss = ref('');
const saving = ref(false);
const loading = ref(true);
const previewTheme = ref<Theme>({ ...props.theme });
const initialSettings = ref<Record<string, unknown> | null>(null);
const initialCss = ref('');
const availableMenus = ref<{ value: string | number; label: string }[]>([]);
const { confirm } = useConfirm();

// History State
const history = ref<string[]>([]);
const historyIndex = ref(-1);
const isHistoryChange = ref(false);

const isDirty = computed(() => {
    const settingsChanged = JSON.stringify(formValues.value) !== JSON.stringify(initialSettings.value);
    const cssChanged = customCss.value !== initialCss.value;
    return settingsChanged || cssChanged;
});

// --- Logic ---

const fetchMenus = async () => {
    try {
        const response = await api.get('/admin/ja/menus');
        const data = response.data.data || response.data;
        availableMenus.value = (Array.isArray(data) ? data : []).map((m: { id: number | string, name: string }) => ({
            value: m.id,
            label: m.name
        }));
        availableMenus.value.unshift({ value: '', label: 'Select a Menu...' });
    } catch (error) {
        logger.error('Failed to fetch menus:', error);
    }
};

const fetchThemeDetails = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/ja/themes/${props.theme.slug}`);
        const data = response.data.data || response.data;
        fullTheme.value = data;
        loadSettings();
    } catch (error) {
        logger.error('Failed to fetch theme details:', error);
        toast.error('Error', 'Failed to load theme configuration.');
    } finally {
        loading.value = false;
    }
};

const settingsSections = computed<ThemeSection[]>(() => {
    const schema = fullTheme.value.manifest?.settings_schema || {};
    const sectionsMap: Record<string, ThemeSection> = {};

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

    const categoryOrder = ['General', 'Layout', 'Colors', 'Typography', 'Hero Section', 'Footer', 'Social Media'];
    return Object.values(sectionsMap).sort((a, b) => {
        const idxA = categoryOrder.indexOf(a.label);
        const idxB = categoryOrder.indexOf(b.label);
        return (idxA === -1 ? 99 : idxA) - (idxB === -1 ? 99 : idxB);
    });
});

const loadSettings = () => {
    const defaults: Record<string, unknown> = {};
    if (fullTheme.value.manifest?.settings_schema) {
        Object.keys(fullTheme.value.manifest.settings_schema).forEach(key => {
            defaults[key] = fullTheme.value.manifest!.settings_schema![key].default ?? '';
        });
    }

    formValues.value = { ...defaults, ...(fullTheme.value.settings || {}) };
    initialSettings.value = JSON.parse(JSON.stringify(formValues.value));
    
    customCss.value = fullTheme.value.custom_css || '';
    initialCss.value = customCss.value;
    
    updatePreviewTheme();
    
    // Initial history
    history.value = [];
    historyIndex.value = -1;
    pushHistory();
};

const updatePreviewTheme = () => {
    previewTheme.value = {
        ...fullTheme.value,
        settings: { ...formValues.value },
        custom_css: customCss.value,
    };
};

// --- History Logic ---

const pushHistory = () => {
    updatePreviewTheme(); // Render preview live
    if (isHistoryChange.value) return;

    if (historyIndex.value < history.value.length - 1) {
        history.value = history.value.slice(0, historyIndex.value + 1);
    }
    if (history.value.length > 50) history.value.shift();

    const snapshot = JSON.stringify({
        settings: formValues.value,
        css: customCss.value
    });
    
    if (history.value.length > 0 && history.value[history.value.length - 1] === snapshot) return;

    history.value.push(snapshot);
    historyIndex.value = history.value.length - 1;
};

const undo = () => {
    if (historyIndex.value > 0) {
        historyIndex.value--;
        restoreSnapshot(history.value[historyIndex.value]);
    }
};

const redo = () => {
    if (historyIndex.value < history.value.length - 1) {
        historyIndex.value++;
        restoreSnapshot(history.value[historyIndex.value]);
    }
};

const canUndo = computed(() => historyIndex.value > 0);
const canRedo = computed(() => historyIndex.value < history.value.length - 1);

const restoreSnapshot = (snapshotJson: string) => {
    isHistoryChange.value = true;
    const snapshot = JSON.parse(snapshotJson);
    formValues.value = { ...snapshot.settings };
    customCss.value = snapshot.css;
    updatePreviewTheme();
    nextTick(() => {
        isHistoryChange.value = false;
    });
};

// --- Actions ---

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
        pushHistory();
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        await api.put(`/admin/ja/themes/${props.theme.slug}/settings`, { settings: formValues.value });
        if (customCss.value !== fullTheme.value.custom_css) {
            await api.put(`/admin/ja/themes/${props.theme.slug}/custom-css`, { custom_css: customCss.value });
        }
        toast.success('Success', 'Theme settings saved successfully.');
        initialSettings.value = JSON.parse(JSON.stringify(formValues.value));
        initialCss.value = customCss.value;
        emit('saved');
    } catch (error) {
        logger.error('Failed to save settings:', error);
        toast.error('Error', 'Failed to save settings. Please try again.');
    } finally {
        saving.value = false;
    }
};

watch(() => props.theme, (newTheme) => {
    if (newTheme.id !== fullTheme.value.id) {
        fetchThemeDetails();
    }
}, { deep: true });

onMounted(() => {
    fetchThemeDetails();
    fetchMenus();
});
</script>

