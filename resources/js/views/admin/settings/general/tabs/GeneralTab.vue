<template>
    <div class="space-y-4">
        <SettingGroup
            v-for="group in generalSettingsGrouped"
            :key="group.id"
            :title="group.title"
            :description="group.description"
            :icon="(group.icon as any)"
            :color="group.color"
            :default-expanded="group.defaultExpanded"
        >
            <SettingField
                v-for="setting in group.settings"
                v-show="isMaintenanceSettingVisible(setting.key)"
                :key="setting.id"
                :model-value="(formData[setting.key] as any)"
                @update:model-value="(value) => updateField(setting.key, value)"
                :field-key="setting.key"
                :label="$t('features.settings.labels.' + setting.key)"
                :description="$t('features.settings.descriptions.' + setting.key)"
                :type="setting.type"
                :enabled-text="$t('features.settings.enabled')"
                :disabled-text="$t('features.settings.disabled')"
                :error="errors?.[setting.key]"
            />
        </SettingGroup>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import SettingGroup from '@/components/settings/SettingGroup.vue'
import SettingField from '@/components/settings/SettingField.vue'

interface Setting {
    id: number | string;
    key: string;
    value: unknown;
    type: string;
    group: string;
}

interface Props {
    settings: Setting[];
    formData: Record<string, unknown>;
    errors?: Record<string, string[]>;
}

const { t } = useI18n()

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'update:formData', value: Record<string, unknown>): void;
}>()

const updateField = (key: string, value: unknown) => {
    emit('update:formData', { ...props.formData, [key]: value })
}

// SVG Icon Components
const GlobeIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 2.25c-2.998 0-5.74 1.1-7.843 2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" /></svg>`
}

const ClockIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
}

const ToolIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.999.922l-7.126 7.126a.908.908 0 01-1.287 0l-1.287-1.287a.908.908 0 010-1.287l7.126-7.126c.851-.735 1.013-1.923.922-2.999a4.5 4.5 0 014.484-4.884 4.5 4.5 0 014.884 4.884zM11.64 12.36L9.64 10.36" /><path stroke-linecap="round" stroke-linejoin="round" d="M7 17l-5 5" /><path stroke-linecap="round" stroke-linejoin="round" d="M12.5 12.5l5.5-5.5" /></svg>`
}

interface SettingGroupData {
    id: string;
    title: string;
    description: string;
    icon: unknown;
    color: 'primary' | 'blue' | 'emerald' | 'amber' | 'red' | 'purple' | 'indigo' | 'orange' | 'pink';
    keys: string[];
    settings: Setting[];
    defaultExpanded: boolean;
}

const isMaintenanceSettingVisible = (key: string) => {
    // Basic switches for master maintenance mode
    const maintenanceSubSettings = ['maintenance_title', 'maintenance_message', 'maintenance_countdown_enabled', 'maintenance_end_time'];
    
    if (maintenanceSubSettings.includes(key)) {
        if (!props.formData.maintenance_mode) return false;
        
        // Additional check for countdown end time
        if (key === 'maintenance_end_time' && !props.formData.maintenance_countdown_enabled) {
            return false;
        }
    }
    
    return true;
}

// General settings grouped by category
const generalSettingsGrouped = computed(() => {
    const generalSettings = props.settings.filter(s => s && s.group === 'general')
    
    const groups: SettingGroupData[] = [
        {
            id: 'site',
            title: t('features.settings.groups.siteInfo.title'),
            description: t('features.settings.groups.siteInfo.description'),
            icon: GlobeIcon,
            color: 'blue',
            keys: ['site_name', 'site_logo', 'site_favicon', 'site_description', 'site_url', 'admin_email'],
            settings: [],
            defaultExpanded: true,
        },
        {
            id: 'localization',
            title: t('features.settings.groups.localization.title'),
            description: t('features.settings.groups.localization.description'),
            icon: ClockIcon,
            color: 'amber',
            keys: ['timezone', 'date_format', 'time_format', 'items_per_page'],
            settings: [],
            defaultExpanded: false,
        },
        {
            id: 'maintenance',
            title: t('features.settings.groups.maintenance.title'),
            description: t('features.settings.groups.maintenance.description'),
            icon: ToolIcon,
            color: 'orange',
            keys: ['maintenance_mode', 'maintenance_title', 'maintenance_message', 'maintenance_countdown_enabled', 'maintenance_end_time'],
            settings: [],
            defaultExpanded: false,
        },
    ]

    groups.forEach(group => {
        group.settings = generalSettings.filter(s => group.keys.includes(s.key))
        
        // Ensure settings are in logical order
        if (group.id === 'site') {
            const order = ['site_name', 'site_logo', 'site_favicon', 'site_description', 'site_url', 'admin_email'];
            group.settings.sort((a, b) => order.indexOf(a.key) - order.indexOf(b.key));
        } else if (group.id === 'maintenance') {
            const order = ['maintenance_mode', 'maintenance_title', 'maintenance_message', 'maintenance_countdown_enabled', 'maintenance_end_time'];
            group.settings.sort((a, b) => order.indexOf(a.key) - order.indexOf(b.key));
        }
    })

    return groups.filter(group => group.settings.length > 0)
})
</script>
