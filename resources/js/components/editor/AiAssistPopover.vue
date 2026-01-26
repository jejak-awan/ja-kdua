<template>
    <div>
        <div @click="toggleOpen">
            <slot name="trigger" />
        </div>

        <Teleport to="body">
            <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-center justify-center pointer-events-none">
                <!-- Draggable Window -->
                <div 
                    ref="popoverRef"
                    class="bg-background border border-border shadow-2xl rounded-xl w-[480px] overflow-hidden pointer-events-auto flex flex-col transition-opacity duration-200"
                    :style="windowStyle"
                >
                    <!-- Header (Draggable) -->
                    <div 
                        class="p-4 border-b border-border/50 bg-muted/20 flex items-center justify-between cursor-move select-none"
                        @mousedown="startDrag"
                    >
                        <div class="flex items-center gap-2 font-medium text-foreground">
                            <Sparkles class="w-4 h-4 text-indigo-500 fill-indigo-500/20" />
                            <span>Magic AI Assist</span>
                        </div>
                        <button 
                            class="text-muted-foreground hover:text-foreground transition-colors p-1 rounded-md hover:bg-muted"
                            @click="close"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Filter Toolbar -->
                    <div class="p-4 border-b border-border/50 bg-card/50 grid grid-cols-2 gap-3">
                        <Select v-model="selectedProvider" @update:model-value="fetchModels">
                            <SelectTrigger class="h-9">
                                <SelectValue placeholder="Select Provider" />
                            </SelectTrigger>
                            <SelectContent class="z-[200]">
                                <SelectItem v-for="p in activeProviders" :key="p.id" :value="p.id">
                                    <div class="flex items-center gap-2">
                                        <img v-if="p.logo" :src="p.logo" class="w-4 h-4 object-contain" />
                                        {{ p.name }}
                                    </div>
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="selectedModel" :disabled="loadingModels">
                            <SelectTrigger class="h-9">
                                <SelectValue :placeholder="loadingModels ? 'Loading Models...' : 'Select Model'" />
                            </SelectTrigger>
                            <SelectContent class="z-[200] max-h-[250px]">
                                <SelectItem v-for="m in currentModels" :key="m.id" :value="m.id">
                                    {{ m.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                
                    <!-- Content -->
                    <div class="p-4 space-y-4 bg-background">
                        <!-- Presets -->
                         <div class="grid grid-cols-2 gap-3">
                            <Button variant="outline" class="justify-start gap-2.5 h-auto py-2.5 px-3 hover:bg-primary/5 hover:text-primary hover:border-primary/20 transition-colors group" @click="handleCommand('Fix grammar and spelling')">
                                <CheckCircle2 class="w-4 h-4 text-muted-foreground group-hover:text-indigo-500" />
                                <span class="font-normal truncate">Fix Grammar</span>
                            </Button>
                            <Button variant="outline" class="justify-start gap-2.5 h-auto py-2.5 px-3 hover:bg-info/5 hover:text-info hover:border-info/20 transition-colors group" @click="handleCommand('Paraphrase this text to be more engaging')">
                                <RefreshCw class="w-4 h-4 text-muted-foreground group-hover:text-blue-500" />
                                <span class="font-normal truncate">Re-write</span>
                            </Button>
                            <Button variant="outline" class="justify-start gap-2.5 h-auto py-2.5 px-3 hover:bg-warning/5 hover:text-warning hover:border-warning/20 transition-colors group" @click="handleCommand('Summarize this text in 1 sentence')">
                                <FileText class="w-4 h-4 text-muted-foreground group-hover:text-orange-500" />
                                <span class="font-normal truncate">Summarize</span>
                            </Button>
                            <Button variant="outline" class="justify-start gap-2.5 h-auto py-2.5 px-3 hover:bg-primary/5 hover:text-primary hover:border-primary/20 transition-colors group" @click="handleCommand('Expand this text with more details')">
                                <Maximize2 class="w-4 h-4 text-muted-foreground group-hover:text-purple-500" />
                                <span class="font-normal truncate">Expand</span>
                            </Button>
                        </div>

                        <div class="relative py-2">
                            <div class="absolute inset-0 flex items-center">
                                <span class="w-full border-t border-border/60" />
                            </div>
                            <div class="relative flex justify-center text-[10px] font-medium tracking-wider uppercase text-muted-foreground">
                                <span class="bg-background px-2">Custom Command</span>
                            </div>
                        </div>

                        <!-- Custom Input -->
                        <div class="space-y-3">
                            <Textarea 
                                v-model="customPrompt" 
                                placeholder="Describe what you want the AI to do..." 
                                class="min-h-[80px] text-sm resize-none focus-visible:ring-offset-0 focus-visible:ring-1"
                                @keydown.enter.exact.prevent="handleCommand(customPrompt)" 
                            />
                            <Button class="w-full h-10 shadow-sm" :disabled="!customPrompt || loading" @click="handleCommand(customPrompt)">
                                <span v-if="!loading" class="flex items-center gap-2 font-medium">
                                     Generate with {{ selectedProviderName }} <ArrowRight class="w-4 h-4" />
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    <Loader2 class="w-4 h-4 animate-spin" />
                                    <span>Generating...</span>
                                </span>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick, onUnmounted } from 'vue';
import { Sparkles, CheckCircle2, RefreshCw, FileText, Maximize2, ArrowRight, X, Loader2 } from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Textarea from '@/components/ui/textarea.vue';
import Select from '@/components/ui/select.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { useCmsStore } from '@/stores/cms';

const props = defineProps({
    context: String,
    disabled: { type: Boolean, default: false }
});

const emit = defineEmits(['result']);
const toast = useToast();
const cmsStore = useCmsStore();

const isOpen = ref(false);
const customPrompt = ref('');
const loading = ref(false);

const providers = ref([]);
const selectedProvider = ref('gemini');
const selectedModel = ref('');
const models = ref({}); // Cache models: { provider: [models] }
const loadingModels = ref(false);

const activeProviders = computed(() => {
    return providers.value.filter(p => {
        // Check if provider API key is set in settings
        const key = cmsStore.settings?.[`${p.id}_api_key`];
        return !!key;
    });
});

const currentModels = computed(() => models.value[selectedProvider.value] || []);

const selectedProviderName = computed(() => {
    return providers.value.find(p => p.id === selectedProvider.value)?.name || selectedProvider.value;
});

// Dragging Logic
const popoverRef = ref(null);
const position = ref({ x: 0, y: 0 });
const isDragging = ref(false);
const dragOffset = ref({ x: 0, y: 0 });

const windowStyle = computed(() => {
    // If dragging or moved, use absolute position. 
    // Wait, initially we want center. 
    // We can use transform.
    return {
        position: 'absolute',
        top: `${position.value.y}px`,
        left: `${position.value.x}px`,
        margin: 0,
        // Remove transform if we are setting top/left directly
    };
});

const toggleOpen = async () => {
    if (props.disabled) return;
    
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        await fetchProviders();
        await nextTick();
        centerPopover();
    }
};

const close = () => {
    isOpen.value = false;
};

const centerPopover = () => {
    if (popoverRef.value) {
        const { width, height } = popoverRef.value.getBoundingClientRect();
        position.value = {
            x: (window.innerWidth - width) / 2,
            y: (window.innerHeight - height) / 2
        };
    }
};

const startDrag = (e) => {
    isDragging.value = true;
    const rect = popoverRef.value.getBoundingClientRect();
    dragOffset.value = {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };
    
    window.addEventListener('mousemove', onDrag);
    window.addEventListener('mouseup', stopDrag);
};

const onDrag = (e) => {
    if (!isDragging.value) return;
    position.value = {
        x: e.clientX - dragOffset.value.x,
        y: e.clientY - dragOffset.value.y
    };
};

const stopDrag = () => {
    isDragging.value = false;
    window.removeEventListener('mousemove', onDrag);
    window.removeEventListener('mouseup', stopDrag);
};

const fetchProviders = async () => {
    try {
        const response = await axios.get('/api/v1/admin/ja/ai/providers');
        providers.value = response.data.data;
        
        // Ensure selected provider is valid/active
        if (activeProviders.value.length === 0) {
            // No active providers
            toast.service.warning('No active AI providers found. Please configure them in settings.');
        } else {
             // Use default provider from settings if available & active
            const defaultProvider = cmsStore.settings?.ai_default_provider;
            const isDefaultActive = activeProviders.value.find(p => p.id === defaultProvider);
           
            if (isDefaultActive) {
                selectedProvider.value = defaultProvider;
            } else if (!activeProviders.value.find(p => p.id === selectedProvider.value)) {
                 // If current selected is not active, switch to first active
                 selectedProvider.value = activeProviders.value[0].id;
            }
            
            fetchModels();
        }
    } catch (e) {
        console.error('Failed to init AI', e);
    }
};

const fetchModels = async () => {
    const provider = selectedProvider.value;
    if (models.value[provider]) {
        // Already cached, set default if needed
        if (!selectedModel.value && models.value[provider].length > 0) {
            selectedModel.value = models.value[provider][0].id;
        }
        return;
    }
    
    loadingModels.value = true;
    try {
        const response = await axios.get(`/api/v1/admin/ja/ai/models/${provider}`);
        models.value[provider] = response.data.data;
        
        if (models.value[provider].length > 0) {
            selectedModel.value = models.value[provider][0].id;
        }
    } catch (e) {
        console.error('Failed to load models', e);
    } finally {
        loadingModels.value = false;
    }
};

const handleCommand = async (prompt) => {
    if (!prompt) return;
    
    loading.value = true;
    try {
        const response = await axios.post('/api/v1/admin/ja/ai/generate', {
            prompt: prompt,
            context: props.context,
            provider: selectedProvider.value,
            model: selectedModel.value
        });

        if (response.data.success) {
            emit('result', response.data.data.content);
            close(); // Close on success? User might want to try again? User said "popover" so maybe keeps open. But usually modals close on action. Keeping close() for now.
            customPrompt.value = '';
        }
    } catch (error) {
        console.error('AI Ops Error:', error);
        toast.service.error('AI Error', error.response?.data?.message || 'Failed to generate content.');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    // Do not fetch immediately, fetch on open
});
</script>
