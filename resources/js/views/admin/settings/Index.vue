<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.settings.title') }}</h1>
            <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.settings.description') }}</p>
        </div>

        <div v-if="loading" class="bg-card border border-border rounded-lg p-12 text-center">
            <p class="text-muted-foreground">{{ $t('features.settings.loading') }}</p>
        </div>

        <div v-else class="bg-card border border-border rounded-lg">
            <!-- Shadcn Tabs -->
            <Tabs v-model="activeTab" class="w-full">
                <div class="p-4 border-b border-border">
                    <TabsList class="flex-wrap">
                        <TabsTrigger v-for="tab in tabs" :key="tab.id" :value="tab.id">
                            {{ $t('features.settings.tabs.' + tab.id) }}
                        </TabsTrigger>
                    </TabsList>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div v-if="currentSettings.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">{{ $t('features.settings.noSettings') }}</p>
                    </div>

                    <!-- General Tab (New Component) -->
                    <GeneralTab
                        v-else-if="activeTab === 'general'"
                        :settings="settings"
                        v-model:form-data="formData"
                    />

                    <!-- Security Tab (New Component) -->
                    <SecurityTab
                        v-else-if="activeTab === 'security'"
                        :settings="settings"
                        :form-data="formData"
                    />

                    <!-- Performance Tab (New Component) -->
                    <PerformanceTab
                        v-else-if="activeTab === 'performance'"
                        :settings="settings"
                        :form-data="formData"
                        :cache-status="cacheStatus"
                        :clearing-cache="clearingCache"
                        :warming-cache="warmingCache"
                        @clear-cache="clearSystemCache"
                        @warm-cache="warmSystemCache"
                    />

                    <!-- Email Tab (New Component) -->
                    <EmailTab
                        v-else-if="activeTab === 'email'"
                        :settings="settings"
                        :form-data="formData"
                        :validating-config="validatingConfig"
                        :config-validation="configValidation"
                        :testing-connection="testingConnection"
                        :connection-result="connectionResult"
                        @validate-config="validateEmailConfig"
                        @test-connection="testSmtpConnection"
                    />

                    <!-- SEO Tab (New Component) -->
                    <SeoTab
                        v-else-if="activeTab === 'seo'"
                        :settings="settings"
                        :form-data="formData"
                    />

                    <!-- Media Tab (New Component) -->
                    <MediaTab
                        v-else-if="activeTab === 'media'"
                        :settings="settings"
                        :form-data="formData"
                    />



                    <!-- Email Test Section (only for email tab) -->
                    <EmailTestSection
                        v-if="activeTab === 'email'"
                        :sending-test-email="sendingTestEmail"
                        :test-email-result="testEmailResult"
                        :test-email="testEmail"
                        :queue-status="queueStatus"
                        :loading-queue-status="loadingQueueStatus"
                        :email-logs="emailLogs"
                        :loading-logs="loadingLogs"
                        @send-test-email="sendTestEmail"
                        @refresh-queue="getQueueStatus"
                        @refresh-logs="getRecentLogs"
                        @update:test-email="testEmail = $event"
                    />

                    <!-- Actions -->
                    <div class="flex justify-end space-x-4 pt-6 border-t">
                        <Button
                            type="button"
                            variant="outline"
                            @click="resetForm"
                        >
                            {{ $t('features.settings.reset') }}
                        </Button>
                        <Button
                            type="submit"
                            :disabled="saving"
                        >
                            {{ saving ? $t('features.settings.saving') : $t('features.settings.save') }}
                        </Button>
                    </div>
                </form>
            </div>
            </Tabs>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import api from '../../../services/api';
import { parseResponse, parseSingleResponse, ensureArray } from '../../../utils/responseParser';
import Tabs from '../../../components/ui/tabs.vue';
import TabsList from '../../../components/ui/tabs-list.vue';
import TabsTrigger from '../../../components/ui/tabs-trigger.vue';
import Button from '../../../components/ui/button.vue';
import { useToast } from '../../../composables/useToast';
import { useConfirm } from '../../../composables/useConfirm';

// Import tab components
import GeneralTab from './tabs/GeneralTab.vue';
import EmailTab from './tabs/EmailTab.vue';
import SeoTab from './tabs/SeoTab.vue';
import MediaTab from './tabs/MediaTab.vue';
import SecurityTab from './tabs/SecurityTab.vue';
import PerformanceTab from './tabs/PerformanceTab.vue';
import EmailTestSection from './EmailTestSection.vue';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const route = useRoute();

const loading = ref(false);
const saving = ref(false);
// Initialize tab from query param if present (e.g., ?tab=performance)
const validTabs = ['general', 'email', 'seo', 'security', 'performance', 'media'];
const initialTab = validTabs.includes(route.query.tab) ? route.query.tab : 'general';
const activeTab = ref(initialTab);
const settings = ref([]);
const formData = ref({});

// Email testing state
const validatingConfig = ref(false);
const configValidation = ref(null);
const testingConnection = ref(false);
const connectionResult = ref(null);
const sendingTestEmail = ref(false);
const testEmailResult = ref(null);
const testEmail = ref({
    to: '',
    subject: '',
    message: '',
});
const loadingQueueStatus = ref(false);
const queueStatus = ref(null);
const loadingLogs = ref(false);
const emailLogs = ref([]);

// Cache Management State
const cacheStatus = ref(null);
const loadingCacheStatus = ref(false);
const clearingCache = ref(false);
const warmingCache = ref(false);

const tabs = [
    { id: 'general', label: 'General' },
    { id: 'email', label: 'Email' },
    { id: 'seo', label: 'SEO' },
    { id: 'security', label: 'Security' },
    { id: 'performance', label: 'Performance' },
    { id: 'media', label: 'Media' },
];

const currentSettings = computed(() => {
    if (!settings.value || !Array.isArray(settings.value)) {
        return [];
    }
    return settings.value.filter(s => s && s.group === activeTab.value);
});


const fetchSettings = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/settings');
        const { data } = parseResponse(response);
        settings.value = ensureArray(data);

        // Inject missing CDN settings with defaults
        const ensureSetting = (key, value, type, group, description = '') => {
            if (!settings.value.find(s => s.key === key)) {
                settings.value.push({
                    id: 'temp_' + key,
                    key,
                    value,
                    type,
                    group,
                    description,
                    is_public: 0
                });
            }
        };

        ensureSetting('cdn_preset', 'custom', 'string', 'performance');
        ensureSetting('cdn_included_dirs', 'assets, storage', 'string', 'performance');
        ensureSetting('cdn_excluded_extensions', '.php, .json', 'string', 'performance');

        // Inject AWS/S3 Settings
        ensureSetting('aws_access_key_id', '', 'string', 'media');
        ensureSetting('aws_secret_access_key', '', 'password', 'media');
        ensureSetting('aws_default_region', 'us-east-1', 'string', 'media');
        ensureSetting('aws_bucket', '', 'string', 'media');
        ensureSetting('aws_endpoint', '', 'string', 'media');

        // Inject Google Drive Settings
        ensureSetting('google_client_id', '', 'string', 'media');
        ensureSetting('google_client_secret', '', 'password', 'media');
        ensureSetting('google_refresh_token', '', 'password', 'media');
        ensureSetting('google_folder_id', '', 'string', 'media');

        // Inject FTP Settings
        ensureSetting('ftp_host', '', 'string', 'media');
        ensureSetting('ftp_username', '', 'string', 'media');
        ensureSetting('ftp_password', '', 'password', 'media');
        ensureSetting('ftp_root', '', 'string', 'media');
        ensureSetting('ftp_port', '21', 'number', 'media');
        ensureSetting('ftp_ssl', false, 'boolean', 'media');

        // Inject Dropbox Settings
        ensureSetting('dropbox_authorization_token', '', 'password', 'media');

        initializeFormData();
    } catch (error) {
        settings.value = [];
    } finally {
        loading.value = false;
    }
};

const initializeFormData = () => {
    formData.value = {};
    settings.value.forEach(setting => {
        let value = setting.value;
        
        // Cast value based on type
        if (setting.type === 'boolean') {
            value = value === '1' || value === 1 || value === 'true' || value === true;
        } else if (setting.type === 'integer') {
            value = value ? parseInt(value) : null;
        } else if (setting.type === 'json') {
            if (typeof value === 'string') {
                try {
                    value = JSON.parse(value);
                    value = JSON.stringify(value, null, 2);
                } catch {
                    // Invalid JSON, keep original string value
                }
            } else {
                value = JSON.stringify(value, null, 2);
            }
        }
        
        formData.value[setting.key] = value;
    });
};

const resetForm = () => {
    initializeFormData();
};

const formatValue = (value, type) => {
    if (type === 'boolean') {
        return value ? t('common.labels.yes') : t('common.labels.no');
    } else if (type === 'json') {
        return typeof value === 'string' ? value : JSON.stringify(value);
    }
    return value;
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        // Prepare settings array for bulk update
        const settingsToUpdate = currentSettings.value.map(setting => {
            let value = formData.value[setting.key];
            
            // Handle JSON type
            if (setting.type === 'json' && typeof value === 'string') {
                try {
                    value = JSON.parse(value);
                } catch {
                    // Invalid JSON, keep original value
                }
            }
            
            return {
                key: setting.key,
                value: value,
                type: setting.type,
                group: setting.group,
            };
        });

        await api.post('/admin/cms/settings/bulk-update', {
            settings: settingsToUpdate,
        });
        

        
        toast.success.save();
        await fetchSettings();

        // Refresh cache status if on performance tab
        if (activeTab.value === 'performance') {
            getCacheStatus();
        }
    } catch (error) {
        toast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};

// Email testing functions
const validateEmailConfig = async () => {
    validatingConfig.value = true;
    configValidation.value = null;
    try {
        const response = await api.get('/admin/cms/email-test/validate-config');
        const { data } = parseResponse(response);
        configValidation.value = data;
    } catch (error) {
        configValidation.value = {
            valid: false,
            errors: [error.response?.data?.message || t('features.settings.emailTest.failed')],
            warnings: [],
        };
    } finally {
        validatingConfig.value = false;
    }
};

const testSmtpConnection = async () => {
    testingConnection.value = true;
    connectionResult.value = null;
    try {
        const response = await api.post('/admin/cms/email-test/test-connection');
        const { data } = parseResponse(response);
        connectionResult.value = data;
    } catch (error) {
        connectionResult.value = {
            connected: false,
            host: 'unknown',
            port: 'unknown',
            error: error.response?.data?.message || t('features.settings.emailTest.failed'),
        };
    } finally {
        testingConnection.value = false;
    }
};

const sendTestEmail = async () => {
    if (!testEmail.value.to) {
        testEmailResult.value = {
            success: false,
            message: t('features.settings.emailTest.recipientRequired'),
        };
        return;
    }

    sendingTestEmail.value = true;
    testEmailResult.value = null;
    try {
        const response = await api.post('/admin/cms/email-test/send-test', {
            to: testEmail.value.to,
            subject: testEmail.value.subject || undefined,
            message: testEmail.value.message || undefined,
        });
        const { data, message } = parseResponse(response);
        testEmailResult.value = {
            success: true,
            message: message || t('features.settings.emailTest.sentSuccess'),
        };
        // Clear form
        testEmail.value.subject = '';
        testEmail.value.message = '';
        // Refresh logs
        await getRecentLogs();
    } catch (error) {
        testEmailResult.value = {
            success: false,
            message: error.response?.data?.message || t('features.settings.emailTest.sendFailed'),
        };
    } finally {
        sendingTestEmail.value = false;
    }
};

const getQueueStatus = async () => {
    loadingQueueStatus.value = true;
    try {
        const response = await api.get('/admin/cms/email-test/queue-status');
        const { data } = parseResponse(response);
        queueStatus.value = data;
    } catch (error) {
        queueStatus.value = {
            driver: 'unknown',
            connection: 'unknown',
            pending_jobs: 'error',
            failed_jobs: 'error',
        };
    } finally {
        loadingQueueStatus.value = false;
    }
};

const getRecentLogs = async () => {
    loadingLogs.value = true;
    try {
        const response = await api.get('/admin/cms/email-test/recent-logs?limit=10');
        const { data } = parseResponse(response);
        emailLogs.value = data.logs || [];
    } catch (error) {
        emailLogs.value = [];
    } finally {
        loadingLogs.value = false;
    }
};

// Cache Management Methods
const getCacheStatus = async () => {
    loadingCacheStatus.value = true;
    try {
        const response = await api.get('/admin/cms/system/cache-status');
        cacheStatus.value = parseSingleResponse(response);
    } catch (error) {
        console.error('Failed to get cache status:', error);
    } finally {
        loadingCacheStatus.value = false;
    }
};

const clearSystemCache = async () => {
    const confirmed = await confirm({
        title: t('features.settings.cache.clearTitle', 'Clear Cache'),
        message: 'Are you sure you want to clear the system cache?',
        variant: 'warning',
        confirmText: t('features.settings.cache.clearConfirm', 'Clear Cache'),
    });

    if (!confirmed) return;
    
    clearingCache.value = true;
    try {
        await api.post('/admin/cms/system/cache/clear');
        toast.success.action(t('features.settings.cache.cleared'));
        getCacheStatus();
    } catch (error) {
        toast.error.fromResponse(error);
    } finally {
        clearingCache.value = false;
    }
};

const warmSystemCache = async () => {
    warmingCache.value = true;
    try {
        await api.post('/admin/cms/system/cache/warm');
        toast.success.action(t('features.settings.cache.warmed'));
        getCacheStatus();
    } catch (error) {
        toast.error.fromResponse(error);
    } finally {
        warmingCache.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch {
        return dateString;
    }
};

watch(activeTab, (newTab) => {
    // Reset form when switching tabs
    initializeFormData();
    // Load email test data when switching to email tab
    if (newTab === 'email') {
        getQueueStatus();
        getRecentLogs();
    } else if (newTab === 'performance') {
        getCacheStatus();
    }
});

onMounted(() => {
    fetchSettings();
    // Load email test data if email tab is active
    if (activeTab.value === 'email') {
        getQueueStatus();
        getRecentLogs();
    } else if (activeTab.value === 'performance') {
        getCacheStatus();
    }
});
</script>
