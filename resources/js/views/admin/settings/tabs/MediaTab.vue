<template>
    <div class="space-y-8">
        <SettingGroup
            v-for="group in mediaSettingsGrouped"
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

const UploadIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>`
}

const ImageIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>`
}

const mediaSettingsGrouped = computed(() => {
    const mediaSettings = props.settings.filter(s => s && s.group === 'media')
    
    const groups = [
        {
            id: 'upload',
            title: t('features.settings.groups.upload.title'),
            description: t('features.settings.groups.upload.description'),
            icon: UploadIcon,
            keys: ['max_upload_size', 'allowed_image_types', 'allowed_file_types', 'storage_driver'],
            settings: [],
        },
        {
            id: 'image_processing',
            title: t('features.settings.groups.imageProcessing.title'),
            description: t('features.settings.groups.imageProcessing.description'),
            icon: ImageIcon,
            keys: ['thumbnail_width', 'thumbnail_height', 'enable_watermark', 'watermark_text'],
            settings: [],
        },
    ]
    
    groups.forEach(group => {
        group.settings = mediaSettings.filter(s => group.keys.includes(s.key))
    })
    
    return groups.filter(group => group.settings.length > 0)
})
</script>
