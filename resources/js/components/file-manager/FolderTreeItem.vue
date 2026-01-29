<template>
    <div 
        :style="{ marginLeft: `${depth * 0.5}rem` }"
        @dragover.prevent="(e) => onDragOver(e, folder)"
        @dragleave="onDragLeave"
        @drop="(e) => onDrop(e, folder)"
    >
        <ContextMenu>
            <ContextMenuTrigger as-child>
                <button
                    @click="navigateToPath(folder.path)"
                    :class="[
                        'w-full flex items-center gap-2 text-[13px] h-9 px-2 rounded-lg transition-colors duration-200 group/item',
                        isActive ? 'bg-primary/10 text-primary font-bold shadow-none' : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground active:scale-[0.98]',
                        dropTarget === folder.path ? 'bg-primary/20 ring-1 ring-primary' : ''
                    ]"
                    :title="folder.name"
                >
                    <div 
                        v-if="hasChildren"
                        class="w-4 h-4 flex items-center justify-center rounded hover:bg-black/10 transition-colors"
                        @click.stop="toggleExpanded(folder.path)"
                    >
                        <ChevronDown 
                            v-if="isExpanded" 
                            class="w-3.5 h-3.5 transition-transform duration-200"
                        />
                        <ChevronRight 
                            v-else 
                            class="w-3.5 h-3.5 transition-transform duration-200" 
                        />
                    </div>
                    <span v-else class="w-4"></span>
                    
                    <FolderOpen v-if="isActive || isParentOfActive" class="w-4 h-4 shrink-0 transition-transform group-hover/item:scale-110" :class="isActive ? 'text-primary-foreground' : 'text-primary'" />
                    <Folder v-else class="w-4 h-4 shrink-0 opacity-70 group-hover/item:opacity-100 transition-[opacity,transform] group-hover/item:scale-110" />
                    <span class="truncate font-medium">{{ folder.name }}</span>
                </button>
            </ContextMenuTrigger>
            <FileContextMenu :item="folder" />
        </ContextMenu>

        <div v-if="isExpanded && hasChildren">
            <FolderTreeItem 
                v-for="child in folder.children" 
                :key="child.path" 
                :folder="child"
                :depth="depth + 1"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { inject, computed } from 'vue';
import Folder from 'lucide-vue-next/dist/esm/icons/folder.js';
import FolderOpen from 'lucide-vue-next/dist/esm/icons/folder-open.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';
import Link from 'lucide-vue-next/dist/esm/icons/link.js';
import Archive from 'lucide-vue-next/dist/esm/icons/archive.js';
import Clipboard from 'lucide-vue-next/dist/esm/icons/clipboard.js';
import ClipboardPaste from 'lucide-vue-next/dist/esm/icons/clipboard-paste.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import { 
    Button,
    ContextMenu,
    ContextMenuTrigger
} from '@/components/ui';
import FileContextMenu from './FileContextMenu.vue';
import { FileManagerKey } from '@/keys';
import type { FolderItem } from '@/types/file-manager';
import { useToast } from '@/composables/useToast';
import api from '@/services/api';

const props = defineProps<{
    folder: FolderItem;
    depth: number;
}>();

const {
    currentPath,
    dropTarget,
    clipboard,
    navigateToPath,
    toggleFolderExpanded: toggleExpanded,
    isFolderExpanded,
    copyToClipboard,
    pasteFromClipboard,
    deleteItem,
    moveItem,
    fetchAllFolders,
    fetchCurrentPath
} = inject(FileManagerKey)!;

const toast = useToast();

const hasChildren = computed(() => (props.folder.children?.length ?? 0) > 0);
const isExpanded = computed(() => isFolderExpanded(props.folder.path));
const isActive = computed(() => currentPath.value === props.folder.path);
const isParentOfActive = computed(() => currentPath.value.startsWith(props.folder.path + '/'));
const clipboardCount = computed(() => clipboard.value.items.length);

const copyPath = async (item: FolderItem) => {
    try {
        await navigator.clipboard.writeText(item.path);
        toast.success.action('Path copied to clipboard');
    } catch (err) {
        toast.error.default('Failed to copy path');
    }
};

const compressItems = async (paths: string[]) => {
    try {
        await api.post('/admin/ja/file-manager/compress', { paths });
        toast.success.action('Items compressed');
        await fetchCurrentPath();
    } catch (error) {
        toast.error.default('Compression failed');
    }
};

const onDragOver = (event: DragEvent, folder: FolderItem) => {
    event.preventDefault();
    dropTarget.value = folder.path;
};

const onDragLeave = () => {
    dropTarget.value = null;
};

const onDrop = async (event: DragEvent, targetFolder: FolderItem) => {
    event.preventDefault();
    const data = event.dataTransfer?.getData('text/plain');
    if (!data) return;
    
    try {
        const { path: sourcePath, type } = JSON.parse(data);
        if (sourcePath === targetFolder.path) return;
        await moveItem(sourcePath, targetFolder.path, type);
    } catch (e) {
        console.error('Drop failed', e);
    } finally {
        dropTarget.value = null;
    }
};
</script>
