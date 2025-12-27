<template>
    <Dialog :open="true" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Move to Folder</DialogTitle>
                <DialogDescription>
                    Select the destination folder for the selected items.
                </DialogDescription>
            </DialogHeader>

            <div class="py-4">
                <label class="text-sm font-medium mb-2 block">Select Folder</label>
                <Select v-model="selectedFolderId">
                    <SelectTrigger>
                        <SelectValue placeholder="Select a folder" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="root">No Folder (Root)</SelectItem>
                        <SelectItem
                            v-for="folder in folders"
                            :key="folder.id"
                            :value="String(folder.id)"
                        >
                            {{ folder.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="$emit('close')">
                    Cancel
                </Button>
                <Button @click="handleMove">
                    Move
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref } from 'vue';
import Button from '../ui/button.vue';
import Dialog from '@/components/ui/dialog.vue';
import DialogContent from '@/components/ui/dialog-content.vue';
import DialogHeader from '@/components/ui/dialog-header.vue';
import DialogTitle from '@/components/ui/dialog-title.vue';
import DialogDescription from '@/components/ui/dialog-description.vue';
import DialogFooter from '@/components/ui/dialog-footer.vue';
import Select from '@/components/ui/select.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';

const props = defineProps({
    folders: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close', 'moved']);

const selectedFolderId = ref('root');

const handleMove = () => {
    // Convert 'root' back to null for API
    const folderId = selectedFolderId.value === 'root' ? null : selectedFolderId.value;
    emit('moved', folderId);
};
</script>

