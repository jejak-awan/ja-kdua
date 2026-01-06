<template>
    <div class="flex flex-col h-screen bg-background text-foreground">
        <!-- Header -->
        <div class="h-14 border-b border-border px-4 flex items-center justify-between shrink-0 bg-background">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" @click="$router.back()">
                    <ArrowLeft class="w-4 h-4" />
                </Button>
                <div>
                    <h1 class="font-semibold" v-if="template">Editing: {{ template.name }}</h1>
                    <p class="text-xs text-muted-foreground capitalize">{{ part }} Template</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <Button variant="outline" @click="$router.back()">Cancel</Button>
                <Button @click="save" :disabled="saving || loading">
                    <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                    Save Changes
                </Button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex-1 flex items-center justify-center">
            <Loader2 class="w-8 h-8 animate-spin text-muted-foreground" />
        </div>
        
        <!-- Error State -->
        <div v-else-if="error" class="flex-1 flex flex-col items-center justify-center gap-4">
            <AlertCircle class="w-12 h-12 text-destructive" />
            <p class="text-destructive">{{ error }}</p>
            <Button variant="outline" @click="loadTemplate">Try Again</Button>
        </div>

        <!-- Builder -->
        <div v-else class="flex-1 overflow-hidden">
            <Builder v-model="blocks" :context="{ type: 'theme_template', id: route.params.id, part: part, builderMode: true }" />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/services/api';
import Builder from '@/components/builder/Builder.vue';
import Button from '@/components/ui/button.vue';
import { ArrowLeft, Loader2, AlertCircle } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast'; 

const route = useRoute();
const router = useRouter();
const { toast } = useToast();

const template = ref(null);
const blocks = ref([]);
const saving = ref(false);
const loading = ref(true);
const error = ref(null);

const id = route.params.id;
const part = route.params.part; // 'header', 'footer', 'body'

const loadTemplate = async () => {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await api.get(`/admin/cms/theme-templates/${id}`);
        template.value = data;
        // Load the specific part data, or empty array if null
        blocks.value = data[`${part}_data`] || []; 
        console.log('[ThemeTemplateEditor] Loaded template:', data);
        console.log('[ThemeTemplateEditor] Blocks:', blocks.value);
    } catch (e) {
        console.error('[ThemeTemplateEditor] Error:', e);
        error.value = e.response?.data?.message || e.message || 'Failed to load template';
        toast({ title: 'Error loading template', variant: 'destructive' });
    } finally {
        loading.value = false;
    }
};

onMounted(loadTemplate);

const save = async () => {
    saving.value = true;
    try {
        const payload = {
            [`${part}_data`]: blocks.value
        };
        await api.put(`/admin/cms/theme-templates/${id}`, payload);
        toast({ title: 'Saved successfully' });
    } catch (e) {
        console.error(e);
        toast({ title: 'Error saving', variant: 'destructive' });
    } finally {
        saving.value = false;
    }
};
</script>
