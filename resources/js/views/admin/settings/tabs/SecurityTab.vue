<template>
    <div class="space-y-8">
        <SettingGroup
            v-for="group in securitySettingsGrouped"
            :key="group.id"
            :title="group.title"
            :description="group.description"
            :icon="group.icon"
        >
            <SettingField
                v-for="setting in group.settings"
                :key="setting.id"
                v-model="formData[setting.key]"
                :field-key="setting.key"
                :label="$t('features.settings.labels.' + setting.key)"
                :description="$t('features.settings.descriptions.' + setting.key)"
                :type="setting.type"
                :enabled-text="$t('features.settings.enabled')"
                :disabled-text="$t('features.settings.disabled')"
            />
        </SettingGroup>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import SettingGroup from '../../../../components/settings/SettingGroup.vue'
import SettingField from '../../../../components/settings/SettingField.vue'

const { t } = useI18n()

const props = defineProps({
    settings: {
        type: Array,
        required: true
    },
    formData: {
        type: Object,
        required: true
    }
})

const ShieldCheckIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>`
}

const ClockIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
}

const LockIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>`
}

const securitySettingsGrouped = computed(() => {
    const securitySettings = props.settings.filter(s => s && s.group === 'security')
    
    const groups = [
        {
            id: 'authentication',
            title: t('features.settings.groups.authentication.title'),
            description: t('features.settings.groups.authentication.description'),
            icon: ShieldCheckIcon,
            keys: ['password_min_length', 'enable_2fa', 'require_email_verification', 'enable_registration'],
            settings: [],
        },
        {
            id: 'session',
            title: t('features.settings.groups.session.title'),
            description: t('features.settings.groups.session.description'),
            icon: ClockIcon,
            keys: ['session_lifetime', 'single_session_enabled', 'max_concurrent_sessions', 'log_retention_days'],
            settings: [],
        },
        {
            id: 'access',
            title: t('features.settings.groups.access.title'),
            description: t('features.settings.groups.access.description'),
            icon: LockIcon,
            keys: ['login_attempts_limit', 'block_duration_minutes'],
            settings: [],
        },
    ]
    
    groups.forEach(group => {
        group.settings = securitySettings.filter(s => group.keys.includes(s.key))
    })
    
    return groups.filter(group => group.settings.length > 0)
})
</script>
