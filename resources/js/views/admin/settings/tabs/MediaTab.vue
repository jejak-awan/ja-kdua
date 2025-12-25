<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import SettingGroup from '../../../../components/settings/SettingGroup.vue'
import SettingField from '../../../../components/settings/SettingField.vue'
import Button from '../../../../components/ui/button.vue'

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

// Removed emit to prevent object replacement overhead
// const emit = defineEmits(['update:formData'])

const updateField = (key, value) => {
    // Direct mutation for stable input performance
    props.formData[key] = value
}

const UploadIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>`
}

const ImageIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>`
}

const CloudIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" /></svg>`
}

const ServerIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z" /></svg>`
}

const mediaSettingsGrouped = computed(() => {
    const mediaSettings = props.settings.filter(s => s && s.group === 'media')
    
    // Check drivers
    const driver = props.formData.storage_driver;

    // Base groups
    const groups = [
        {
            id: 'upload',
            title: t('features.settings.groups.upload.title'),
            description: t('features.settings.groups.upload.description'),
            icon: UploadIcon,
            keys: ['max_upload_size', 'allowed_image_types', 'allowed_file_types', 'storage_driver'],
            settings: [],
        },
    ]

    // S3 Config
    if (driver === 's3') {
        groups.push({
            id: 's3_config',
            title: 'S3 Configuration',
            description: 'Configure S3 bucket connection details',
            icon: CloudIcon,
            keys: ['aws_access_key_id', 'aws_secret_access_key', 'aws_default_region', 'aws_bucket', 'aws_endpoint'],
            settings: [],
            isExternal: true
        });
    }

    // Google Drive Config
    if (driver === 'google') {
        groups.push({
            id: 'google_config',
            title: 'Google Drive Configuration',
            description: 'Configure Google Drive API details',
            icon: CloudIcon,
            keys: ['google_client_id', 'google_client_secret', 'google_refresh_token', 'google_folder_id'],
            settings: [],
            isExternal: true
        });
    }

    // FTP Config
    if (driver === 'ftp') {
        groups.push({
            id: 'ftp_config',
            title: 'FTP Configuration',
            description: 'Configure FTP server details',
            icon: ServerIcon,
            keys: ['ftp_host', 'ftp_username', 'ftp_password', 'ftp_root', 'ftp_port', 'ftp_ssl'],
            settings: [],
            isExternal: true
        });
    }

    // Dropbox Config
    if (driver === 'dropbox') {
        groups.push({
            id: 'dropbox_config',
            title: 'Dropbox Configuration',
            description: 'Configure Dropbox API token',
            icon: CloudIcon,
            keys: ['dropbox_authorization_token'],
            settings: [],
            isExternal: true
        });
    }

    // Add Image Processing group (last)
    groups.push({
        id: 'image_processing',
        title: t('features.settings.groups.imageProcessing.title'),
        description: t('features.settings.groups.imageProcessing.description'),
        icon: ImageIcon,
        keys: ['thumbnail_width', 'thumbnail_height', 'enable_watermark', 'watermark_text'],
        settings: [],
    })
    
    
    groups.forEach(group => {
        group.settings = mediaSettings.filter(s => group.keys.includes(s.key))
    })
    
    return groups.filter(group => group.settings.length > 0)
})

const testingConnection = ref(false);
const testResult = ref(null);

const testConnection = async () => {
    testingConnection.value = true;
    testResult.value = null;
    try {
        // Send current formData to test endpoint
        const response = await axios.post('/api/admin/settings/test-storage', {
            driver: props.formData.storage_driver,
            config: props.formData
        });
        testResult.value = { success: true, message: response.data.message };
    } catch (error) {
        testResult.value = { 
            success: false, 
            message: error.response?.data?.message || 'Connection failed. Please check your credentials.' 
        };
    } finally {
        testingConnection.value = false;
    }
};

// --- Storage Migration Logic ---
const migrationStatus = ref('idle'); // idle, scanning, migrating, completed, error
const totalFiles = ref(0);
const processedFiles = ref(0);
const migrationLogs = ref([]);
const stopMigration = ref(false);

const migrationProgress = computed(() => {
    if (totalFiles.value === 0) return 0;
    return Math.round((processedFiles.value / totalFiles.value) * 100);
});

const startMigration = async () => {
    if (!confirm('This will copy all files from Local Storage to the currently configured External Storage. Process may take time. Continue?')) return;

    migrationStatus.value = 'scanning';
    migrationLogs.value = [];
    processedFiles.value = 0;
    totalFiles.value = 0;
    stopMigration.value = false;

    try {
        // 1. Scan files
        const response = await axios.get('/api/storage/migration/files');
        const files = response.data.data; // Array of paths
        totalFiles.value = files.length;

        if (totalFiles.value === 0) {
            migrationStatus.value = 'completed';
            migrationLogs.value.push({ type: 'info', message: 'No files to migrate.' });
            return;
        }

        migrationStatus.value = 'migrating';
        
        // 2. Process in batches
        const batchSize = 10;
        for (let i = 0; i < files.length; i += batchSize) {
            if (stopMigration.value) {
                migrationLogs.value.push({ type: 'warning', message: 'Migration stopped by user.' });
                break;
            }

            const batch = files.slice(i, i + batchSize);
            try {
                const res = await axios.post('/api/storage/migration/batch', { files: batch });
                const result = res.data.data;
                
                // Log failures
                Object.entries(result.failed).forEach(([file, error]) => {
                     migrationLogs.value.push({ type: 'error', message: `Failed ${file}: ${error}` });
                });
                
                processedFiles.value += batch.length;
            } catch (err) {
                 migrationLogs.value.push({ type: 'error', message: `Batch failed: ${err.message}` });
            }
        }
        
        if (!stopMigration.value) {
            migrationStatus.value = 'completed';
            migrationLogs.value.push({ type: 'success', message: 'Migration completed successfully.' });
        }

    } catch (error) {
        migrationStatus.value = 'error';
        migrationLogs.value.push({ type: 'error', message: error.response?.data?.message || error.message });
    }
};

const handleStopMigration = () => {
    stopMigration.value = true;
};
</script>

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
                :model-value="formData[setting.key]"
                @update:model-value="(value) => updateField(setting.key, value)"
                :field-key="setting.key"
                :label="$t('features.settings.labels.' + setting.key)"
                :description="$t('features.settings.descriptions.' + setting.key)"
                :type="setting.type"
                :enabled-text="$t('features.settings.enabled')"
                :disabled-text="$t('features.settings.disabled')"
            />

            <!-- Test Connection Button for External Drivers -->
            <div v-if="group.isExternal" class="col-span-1 md:col-span-2 mt-4">
                <div class="flex items-center gap-4">
                    <Button 
                        @click="testConnection" 
                        :disabled="testingConnection"
                        variant="secondary"
                    >
                        <span v-if="testingConnection">Testing...</span>
                        <span v-else>Test Connection</span>
                    </Button>
                    
                    <div v-if="testResult" :class="['text-sm', testResult.success ? 'text-green-600' : 'text-red-500']">
                        {{ testResult.message }}
                    </div>
                </div>
            </div>
        </SettingGroup>

        <!-- Migration Tool (Show only if driver is NOT local) -->
        <div v-if="formData.storage_driver && formData.storage_driver !== 'local'" class="p-6 bg-card border border-border rounded-lg">
            <h3 class="text-lg font-medium text-foreground mb-2">Storage Migration</h3>
            <p class="text-sm text-muted-foreground mb-4">
                Migrate files from Local Storage to {{ formData.storage_driver }}. 
                This will copy existing files to the new storage.
            </p>

            <div v-if="migrationStatus === 'idle' || migrationStatus === 'completed' || migrationStatus === 'error'" class="flex gap-4">
                 <Button @click="startMigration" :disabled="migrationStatus === 'scanning'">
                    Start Migration
                 </Button>
            </div>

            <div v-if="migrationStatus === 'scanning' || migrationStatus === 'migrating'" class="space-y-4">
                <div class="flex justify-between text-sm text-foreground">
                    <span>
                        <span v-if="migrationStatus === 'scanning'">Scanning local files...</span>
                        <span v-else>Migrating: {{ processedFiles }} / {{ totalFiles }} files</span>
                    </span>
                    <span>{{ migrationProgress }}%</span>
                </div>
                
                <!-- Progress Bar -->
                <div class="w-full bg-secondary rounded-full h-2.5 dark:bg-gray-700">
                    <div class="bg-primary h-2.5 rounded-full transition-all duration-300" :style="{ width: migrationProgress + '%' }"></div>
                </div>

                <Button variant="destructive" size="sm" @click="handleStopMigration">Stop</Button>
            </div>

            <!-- Logs -->
            <div v-if="migrationLogs.length > 0" class="mt-4 p-3 bg-muted rounded-md text-xs max-h-40 overflow-y-auto font-mono">
                <div v-for="(log, index) in migrationLogs" :key="index" :class="{
                    'text-green-600': log.type === 'success',
                    'text-red-500': log.type === 'error',
                    'text-yellow-600': log.type === 'warning',
                    'text-foreground': log.type === 'info'
                }">
                    [{{ log.type.toUpperCase() }}] {{ log.message }}
                </div>
            </div>
        </div>
    </div>
</template>
