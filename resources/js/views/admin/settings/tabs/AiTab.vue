<template>
    <div class="space-y-6">
        <SettingGroup
            :title="$t('features.settings.ai.title', 'AI Assistance')"
            :description="$t('features.settings.ai.description', 'Configure AI features for content generation.')"
            :icon="Sparkles"
            color="indigo"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Enable AI -->
                <SettingField
                    fieldKey="ai_enabled"
                    type="boolean"
                    :label="$t('features.settings.ai.enable_ai', 'Enable AI Features')"
                    :description="$t('features.settings.ai.enable_ai_desc', 'Enable or disable AI assistance in the editor.')"
                    v-model="formData.ai_enabled"
                    :error="errors?.ai_enabled"
                />
                
                <!-- Default Provider -->
                <div>
                     <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.settings.ai.default_provider', 'Default Provider') }}
                    </label>
                    <p class="text-xs text-muted-foreground mb-2">
                        {{ $t('features.settings.ai.default_provider_desc', 'Select which AI provider to use by default.') }}
                    </p>
                    <Select v-model="formData.ai_default_provider" :disabled="!formData.ai_enabled">
                        <SelectTrigger>
                            <SelectValue :placeholder="$t('features.settings.ai.default_provider')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="provider in providers" :key="provider.id" :value="provider.id">
                                <div class="flex items-center gap-2">
                                    <img v-if="provider.logo" :src="provider.logo" class="w-4 h-4 object-contain" />
                                    {{ provider.name }}
                                </div>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </SettingGroup>

        <!-- Providers Accordion -->
        <div class="space-y-4" :class="{ 'opacity-50 pointer-events-none': !formData.ai_enabled }">
            <h3 class="text-lg font-medium">{{ $t('features.settings.ai.providers', 'AI Providers') }}</h3>
            
            <div v-for="provider in providers" :key="provider.id" class="border rounded-lg overflow-hidden bg-card text-card-foreground shadow-sm">
                <button 
                    type="button"
                    @click="toggleProvider(provider.id)"
                    class="w-full flex items-center justify-between p-4 hover:bg-muted/50 transition-colors text-left"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-md bg-muted flex items-center justify-center p-1.5 border">
                            <img v-if="provider.logo" :src="provider.logo" class="w-full h-full object-contain" />
                             <Bot v-else class="w-5 h-5 text-muted-foreground" />
                        </div>
                        <div>
                            <span class="font-medium block">{{ provider.name }}</span>
                            <span class="text-xs text-muted-foreground" v-if="formData[`${provider.id}_model`]">
                                Using: {{ formData[`${provider.id}_model`] }}
                            </span>
                        </div>
                    </div>
                    <ChevronDown 
                        class="w-5 h-5 text-muted-foreground transition-transform duration-200" 
                        :class="{ 'rotate-180': expandedProvider === provider.id }"
                    />
                </button>

                <div v-show="expandedProvider === provider.id" class="p-4 border-t bg-muted/10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Col: API Key -->
                        <div class="space-y-1">
                             <label class="block text-sm font-medium text-foreground">
                                {{ $t('features.settings.ai.api_key', 'API Key') }}
                            </label>
                            <div class="relative">
                                <Input 
                                    :type="showKey[provider.id] ? 'text' : 'password'"
                                    :placeholder="$t('features.settings.ai.api_key_placeholder', 'Enter API Key')"
                                    v-model="formData[`${provider.id}_api_key`]"
                                    class="pr-10"
                                />
                                <button 
                                    type="button"
                                    class="absolute right-0 top-0 h-full px-3 text-muted-foreground hover:text-foreground transition-colors"
                                    @click="toggleKeyVisibility(provider.id)"
                                >
                                    <EyeOff v-if="showKey[provider.id]" class="w-4 h-4" />
                                    <Eye v-else class="w-4 h-4" />
                                </button>
                            </div>
                            <p class="text-xs text-muted-foreground" v-if="errors?.[`${provider.id}_api_key`]">
                                {{ errors[`${provider.id}_api_key`] }}
                            </p>
                        </div>

                        <!-- Right Col: Model Selection & Actions -->
                        <div class="space-y-1">
                             <label class="block text-sm font-medium text-foreground">
                                {{ $t('features.settings.ai.model_select', 'Default Model') }}
                            </label>
                            <div class="flex gap-2">
                                <Select 
                                    v-model="formData[`${provider.id}_model`]" 
                                    :disabled="!formData[`${provider.id}_api_key`] || loadingModels[provider.id]"
                                    @update:open="(open) => { if(open) fetchModels(provider.id) }"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue :placeholder="loadingModels[provider.id] ? 'Loading...' : (formData[`${provider.id}_model`] || 'Select Model')" />
                                    </SelectTrigger>
                                    <SelectContent class="max-h-[300px]">
                                        <SelectItem v-for="model in availableModels[provider.id] || []" :key="model.id" :value="model.id">
                                            {{ model.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                
                                <!-- Text Connection -->
                                <Button 
                                    size="icon" 
                                    variant="outline" 
                                    type="button" 
                                    :title="$t('features.settings.ai.test_connection')"
                                    @click="testConnection(provider.id)"
                                    :disabled="!formData[`${provider.id}_api_key`] || testing[provider.id]"
                                >
                                    <Loader2 v-if="testing[provider.id]" class="w-4 h-4 animate-spin" />
                                    <Wifi v-else class="w-4 h-4" :class="testing[provider.id] === false ? 'text-green-600' : ''" /> <!-- showing green if just tested successfully? naive logic --> 
                                </Button>

                                <!-- Fetch Models -->
                                <Button 
                                    size="icon" 
                                    variant="ghost" 
                                    type="button" 
                                    :title="$t('features.settings.ai.fetch_models')"
                                    @click="fetchModels(provider.id, true)"
                                    :disabled="!formData[`${provider.id}_api_key`] || loadingModels[provider.id]"
                                >
                                    <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loadingModels[provider.id] }" />
                                </Button>
                            </div>
                            <p class="text-xs text-muted-foreground mt-1" v-if="!formData[`${provider.id}_api_key`]">
                                Enter API Key to select model.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Sparkles, Info, ChevronDown, CheckCircle2, Loader2, RefreshCw, Bot, Eye, EyeOff, Wifi } from 'lucide-vue-next';
import SettingGroup from '@/components/settings/SettingGroup.vue';
import SettingField from '@/components/settings/SettingField.vue';
import Select from '@/components/ui/select.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import { useToast } from '@/composables/useToast';
import axios from 'axios';

const props = defineProps({
    settings: { type: Array, required: true },
    formData: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
});

const { service: toast } = useToast();
const providers = ref([]);
const expandedProvider = ref('gemini'); // Default expand
const availableModels = ref({});
const loadingModels = ref({});
const testing = ref({});
const testSuccess = ref({});
const showKey = ref({});

const toggleProvider = (id) => {
    expandedProvider.value = expandedProvider.value === id ? null : id;
};

const toggleKeyVisibility = (id) => {
    showKey.value[id] = !showKey.value[id];
};

// Fetch Providers
const fetchProviders = async () => {
    try {
        const response = await axios.get('/api/v1/admin/ja/ai/providers');
        providers.value = response.data.data;
        
        // Ensure default provider is set
        if (!props.formData.ai_default_provider && providers.value.length > 0) {
            props.formData.ai_default_provider = 'gemini';
        }
    } catch (error) {
        console.error('Failed to fetch AI providers', error);
    }
};

// Fetch Models
const fetchModels = async (providerId, force = false) => {
    // Return early if already loaded and not forcing
    if (availableModels.value[providerId] && availableModels.value[providerId].length > 0 && !force) return;
    
    // Return if no API key
    if (!props.formData[`${providerId}_api_key`]) return;

    loadingModels.value = { ...loadingModels.value, [providerId]: true };
    try {
        // Pass API key if available in form data, to allow fetching without saving first
        const apiKey = props.formData[`${providerId}_api_key`];
        const response = await axios.get(`/api/v1/admin/ja/ai/models/${providerId}`, {
            params: { api_key: apiKey }
        });
        
        availableModels.value = { ...availableModels.value, [providerId]: response.data.data };
        
        if (force) {
            toast.success('Success', 'Models fetched successfully');
        }
    } catch (error) {
        // If 401/403, might be invalid key
    } finally {
        loadingModels.value = { ...loadingModels.value, [providerId]: false };
    }
};

// Test Connection
const testConnection = async (providerId) => {
    testing.value = { ...testing.value, [providerId]: true };
    testSuccess.value = { ...testSuccess.value, [providerId]: false };
    
    try {
         await axios.post('/api/v1/admin/ja/ai/test', {
            provider: providerId,
            api_key: props.formData[`${providerId}_api_key`]
        });
        toast.success('Success', 'Connection successful');
        testSuccess.value = { ...testSuccess.value, [providerId]: true };
        fetchModels(providerId, true); // Auto fetch models on success
    } catch (error) {
        toast.error('Connection failed', error.body?.message || error.message);
    } finally {
        testing.value = { ...testing.value, [providerId]: false };
    }
};

onMounted(() => {
    fetchProviders();
});
</script>
