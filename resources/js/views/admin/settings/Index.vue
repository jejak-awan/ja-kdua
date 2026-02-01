<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.settings.title') }}</h1>
            <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.settings.description') }}</p>
        </div>

        <div v-if="loading" class="bg-card border border-border rounded-lg p-12 text-center">
            <p class="text-muted-foreground">{{ $t('features.settings.loading') }}</p>
        </div>

        <div v-else class="w-full">
            <!-- Shadcn Tabs -->
            <Tabs v-model="activeTab" class="w-full">
                <div class="mb-8">
                    <TabsList class="bg-transparent p-0 h-auto gap-0 flex-wrap">
                        <TabsTrigger 
                            v-for="tab in tabs" 
                            :key="tab.id" 
                            :value="tab.id"
                            class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
                        >
                            <component :is="getTabIcon(tab.id)" class="w-4 h-4 mr-2" />
                            {{ $t('features.settings.tabs.' + tab.id) }}
                        </TabsTrigger>
                    </TabsList>
                </div>

                <!-- Tab Content -->
                <div>
                    <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div v-if="currentSettings.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">{{ $t('features.settings.noSettings') }}</p>
                    </div>

                    <!-- General Tab (New Component) -->
                    <GeneralTab
                        v-else-if="activeTab === 'general'"
                        :settings="settings"
                        v-model:form-data="formData"
                        :errors="errors"
                    />

                    <SecurityTab
                        v-else-if="activeTab === 'security'"
                        :settings="settings"
                        v-model:form-data="formData"
                        :errors="errors"
                    />

                    <!-- Discussion Tab (New Component) -->
                    <DiscussionTab
                        v-else-if="activeTab === 'comments'"
                        :settings="settings"
                        v-model:form-data="formData"
                        :errors="errors"
                    />

                    <!-- Performance Tab (New Component) -->
                    <PerformanceTab
                        v-else-if="activeTab === 'performance'"
                        :settings="settings"
                        v-model:form-data="formData"
                        :errors="errors"
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
                        v-model:form-data="formData"
                        :errors="errors"
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
                        v-model:form-data="formData"
                        :errors="errors"
                    />

                    <!-- Media Tab (New Component) -->
                    <MediaTab
                        v-else-if="activeTab === 'media'"
                        :settings="settings"
                        v-model:form-data="formData"
                        :errors="errors"
                    />

                    <!-- AI Tab -->
                    <AiTab
                        v-else-if="activeTab === 'ai'"
                        :settings="settings"
                        v-model:form-data="formData"
                        :errors="errors"
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
                            :disabled="saving || !isDirty"
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

<script setup lang="ts">
import { ref, onMounted, computed, watch, defineAsyncComponent } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import api from '@/services/api';
import { parseResponse, parseSingleResponse, ensureArray } from '@/utils/responseParser';
import {
    Tabs,
    TabsList,
    TabsTrigger,
    Button
} from '@/components/ui';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { useCmsStore } from '@/stores/cms';
import SettingsIcon from 'lucide-vue-next/dist/esm/icons/settings.js';
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import MessageSquare from 'lucide-vue-next/dist/esm/icons/message-square.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Shield from 'lucide-vue-next/dist/esm/icons/shield.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import HardDrive from 'lucide-vue-next/dist/esm/icons/hard-drive.js';
import Sparkles from 'lucide-vue-next/dist/esm/icons/sparkles.js';

// Async Tab Components
const GeneralTab = defineAsyncComponent(() => import('./tabs/GeneralTab.vue'));
const EmailTab = defineAsyncComponent(() => import('./tabs/EmailTab.vue'));
const SeoTab = defineAsyncComponent(() => import('./tabs/SeoTab.vue'));
const MediaTab = defineAsyncComponent(() => import('./tabs/MediaTab.vue'));
const SecurityTab = defineAsyncComponent(() => import('./tabs/SecurityTab.vue'));
const PerformanceTab = defineAsyncComponent(() => import('./tabs/PerformanceTab.vue'));
const DiscussionTab = defineAsyncComponent(() => import('./tabs/DiscussionTab.vue'));
const AiTab = defineAsyncComponent(() => import('./tabs/AiTab.vue'));
const EmailTestSection = defineAsyncComponent(() => import('./EmailTestSection.vue'));

interface Setting {
    id: number | string;
    key: string;
    value: any;
    type: string;
    group: string;
    description?: string;
    is_public?: number;
}

interface Tab {
    id: string;
    label: string;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const route = useRoute();
const cmsStore = useCmsStore();

const loading = ref(false);
const saving = ref(false);
// Initialize tab from query param if present (e.g., ?tab=performance)
const validTabs = ['general', 'email', 'seo', 'security', 'performance', 'media', 'comments', 'ai'];
const initialTab = validTabs.includes(route.query.tab as string) ? (route.query.tab as string) : 'general';
const activeTab = ref(initialTab);
const settings = ref<Setting[]>([]);
const formData = ref<Record<string, any>>({});
const initialFormData = ref<Record<string, any>>({}); // Track initial state
const errors = ref<Record<string, string[]>>({});

const isDirty = computed(() => {
    // Compare only keys present in currentSettings to handle tab switching correctly
    const currentKeys = currentSettings.value.map(s => s.key);
    for (const key of currentKeys) {
        if (JSON.stringify(formData.value[key]) !== JSON.stringify(initialFormData.value[key])) {
            return true;
        }
    }
    return false;
});

// Email testing state
const validatingConfig = ref(false);
const configValidation = ref<any>(null);
const testingConnection = ref(false);
const connectionResult = ref<any>(null);
const sendingTestEmail = ref(false);
const testEmailResult = ref<any>(null);
const testEmail = ref({
    to: '',
    subject: '',
    message: '',
});
const loadingQueueStatus = ref(false);
const queueStatus = ref<any>(null);
const loadingLogs = ref(false);
const emailLogs = ref<any[]>([]);

// Cache Management State
const cacheStatus = ref<any>(null);
const clearingCache = ref(false);
const warmingCache = ref(false);

const tabs: Tab[] = [
    { id: 'general', label: 'General' },
    { id: 'email', label: 'Email' },
    { id: 'comments', label: 'Discussion' },
    { id: 'seo', label: 'SEO' },
    { id: 'security', label: 'Security' },
    { id: 'performance', label: 'Performance' },
    { id: 'media', label: 'Media' },
    { id: 'ai', label: 'AI Assistance' },
];

const getTabIcon = (tabId: string) => {
    switch (tabId) {
        case 'general': return SettingsIcon;
        case 'email': return Mail;
        case 'comments': return MessageSquare;
        case 'seo': return Search;
        case 'security': return Shield;
        case 'performance': return Activity;
        case 'media': return HardDrive;
        case 'ai': return Sparkles;
        default: return SettingsIcon;
    }
};

const currentSettings = computed(() => {
    if (!settings.value || !Array.isArray(settings.value)) {
        return [];
    }
    return settings.value.filter(s => s && s.group === activeTab.value);
});


const fetchSettings = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/ja/settings');
        const { data } = parseResponse(response);
        settings.value = ensureArray(data);

        // Inject missing CDN settings with defaults
        const ensureSetting = (key: string, value: any, type: string, group: string, description = '') => {
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

        // Inject AI Settings
        ensureSetting('ai_enabled', true, 'boolean', 'ai');
        ensureSetting('gemini_api_key', '', 'password', 'ai');

        initializeFormData();
    } catch (error: any) {
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
    initialFormData.value = JSON.parse(JSON.stringify(formData.value));
};

const resetForm = () => {
    initializeFormData();
};

const handleSubmit = async () => {
    saving.value = true;
    errors.value = {};
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

        await api.post('/admin/ja/settings/bulk-update', {
            settings: settingsToUpdate,
        });
        
        toast.success.save();
        await fetchSettings();
        await cmsStore.fetchSettingsGroup(activeTab.value); // Force refresh store for reactivity

        // Refresh cache status if on performance tab
        if (activeTab.value === 'performance') {
            getCacheStatus();
        }
        initialFormData.value = JSON.parse(JSON.stringify(formData.value));
    } catch (error: any) {
        if (error.response?.status === 422) {
             errors.value = error.response.data.errors || {};
        } else {
             toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};

// Email testing functions
const validateEmailConfig = async () => {
    validatingConfig.value = true;
    configValidation.value = null;
    try {
        const response = await api.get('/admin/ja/email-test/validate-config');
        const { data } = parseResponse(response);
        configValidation.value = data;
    } catch (error: any) {
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
        const response = await api.post('/admin/ja/email-test/test-connection');
        const { data } = parseResponse(response);
        connectionResult.value = data;
    } catch (error: any) {
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
        const response = await api.post('/admin/ja/email-test/send-test', {
            to: testEmail.value.to,
            subject: testEmail.value.subject || undefined,
            message: testEmail.value.message || undefined,
        });
        const message = response.data?.message;
        testEmailResult.value = {
            success: true,
            message: message || t('features.settings.emailTest.sentSuccess'),
        };
        // Clear form
        testEmail.value.subject = '';
        testEmail.value.message = '';
        // Refresh logs
        await getRecentLogs();
    } catch (error: any) {
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
        const response = await api.get('/admin/ja/email-test/queue-status');
        const { data } = parseResponse(response);
        queueStatus.value = data;
    } catch (error: any) {
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
        const response = await api.get('/admin/ja/email-test/recent-logs?limit=10');
        const { data } = parseResponse(response);
        emailLogs.value = (data as any).logs || [];
    } catch (error: any) {
        emailLogs.value = [];
    } finally {
        loadingLogs.value = false;
    }
};

// Cache Management Methods
const getCacheStatus = async () => {
    try {
        const response = await api.get('/admin/ja/system/cache-status');
        cacheStatus.value = parseSingleResponse<any>(response);
    } catch (error: any) {
        console.error('Failed to get cache status:', error);
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
        await api.post('/admin/ja/system/cache/clear');
        toast.success.action(t('features.settings.cache.cleared'));
        getCacheStatus();
    } catch (error: any) {
        toast.error.fromResponse(error);
    } finally {
        clearingCache.value = false;
    }
};

const warmSystemCache = async () => {
    warmingCache.value = true;
    try {
        await api.post('/admin/ja/system/cache/warm');
        toast.success.action(t('features.settings.cache.warmed'));
        getCacheStatus();
    } catch (error: any) {
        toast.error.fromResponse(error);
    } finally {
        warmingCache.value = false;
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

