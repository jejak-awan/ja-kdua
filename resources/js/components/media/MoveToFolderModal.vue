<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card border border-border shadow-lg rounded-lg max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">Move to Folder</h3>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="$emit('close')"
                    >
                        <X class="w-5 h-5" />
                    </Button>
                </div>

                <div class="p-6">
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Select Folder
                    </label>
                    <select
                        v-model="selectedFolderId"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option :value="null">No Folder (Root)</option>
                        <option
                            v-for="folder in folders"
                            :key="folder.id"
                            :value="folder.id"
                        >
                            {{ folder.name }}
                        </option>
                    </select>
                </div>

                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <Button
                        variant="outline"
                        @click="$emit('close')"
                    >
                        Cancel
                    </Button>
                    <Button
                        @click="handleMove"
                    >
                        Move
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { X } from 'lucide-vue-next';
import Button from '../ui/button.vue';

const props = defineProps({
    folders: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close', 'moved']);

const selectedFolderId = ref(null);

const handleMove = () => {
    emit('moved', selectedFolderId.value);
};
</script>

