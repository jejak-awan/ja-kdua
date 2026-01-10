<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Sparkles class="w-5 h-5 text-indigo-500" />
                    <span>Magic AI Assist</span>
                </DialogTitle>
                <DialogDescription>
                    Use AI to improve your writing. Select an option or type a custom command.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <!-- Presets -->
                <div class="grid grid-cols-2 gap-2">
                    <Button variant="outline" class="justify-start gap-2" @click="handleCommand('Fix grammar and spelling')">
                        <CheckCircle2 class="w-4 h-4 text-green-500" />
                        Fix Grammar
                    </Button>
                    <Button variant="outline" class="justify-start gap-2" @click="handleCommand('Paraphrase this text to be more engaging')">
                        <RefreshCw class="w-4 h-4 text-blue-500" />
                        Re-write
                    </Button>
                    <Button variant="outline" class="justify-start gap-2" @click="handleCommand('Summarize this text in 1 sentence')">
                        <FileText class="w-4 h-4 text-orange-500" />
                        Summarize
                    </Button>
                    <Button variant="outline" class="justify-start gap-2" @click="handleCommand('Expand this text with more details')">
                        <Maximize2 class="w-4 h-4 text-purple-500" />
                        Expand
                    </Button>
                </div>

                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t" />
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-background px-2 text-muted-foreground">Or type custom command</span>
                    </div>
                </div>

                <!-- Custom Input -->
                <div class="flex gap-2">
                    <Input url="" v-model="customPrompt" placeholder="e.g. Translate to Indonesian..." @keydown.enter="handleCommand(customPrompt)" />
                    <Button :disabled="!customPrompt || loading" @click="handleCommand(customPrompt)">
                        <ArrowRight class="w-4 h-4" />
                    </Button>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="flex items-center justify-center p-4 text-sm text-muted-foreground animate-pulse">
                    <Sparkles class="w-4 h-4 mr-2 animate-spin" />
                    Generating magic...
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref } from 'vue';
import { Sparkles, CheckCircle2, RefreshCw, FileText, Maximize2, ArrowRight } from 'lucide-vue-next';
import Dialog from '@/components/ui/dialog.vue';
import DialogContent from '@/components/ui/dialog-content.vue';
import DialogHeader from '@/components/ui/dialog-header.vue';
import DialogTitle from '@/components/ui/dialog-title.vue';
import DialogDescription from '@/components/ui/dialog-description.vue';
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    open: Boolean,
    context: String
});

const emit = defineEmits(['update:open', 'result']);
const toast = useToast();

const customPrompt = ref('');
const loading = ref(false);

const handleCommand = async (prompt) => {
    if (!prompt) return;
    
    loading.value = true;
    try {
        const response = await axios.post('/api/admin/ja/ai/generate', {
            prompt: prompt,
            context: props.context
        });

        if (response.data.success) {
            emit('result', response.data.data.content); // Accessed nested content
            emit('update:open', false);
            customPrompt.value = '';
        }
    } catch (error) {
        console.error('AI Ops Error:', error);
        toast.service.error('AI Error', error.response?.data?.message || 'Failed to generate content.');
    } finally {
        loading.value = false;
    }
};
</script>
