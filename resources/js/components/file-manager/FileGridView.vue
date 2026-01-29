<template>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(150px,1fr))] gap-4 p-4">
        <!-- Folders -->
        <div
            v-for="folder in paginatedFolders"
            :key="folder.path"
            v-memo="[isSelected(folder.path), folder.name, dropTarget === folder.path]"
            class="group relative"
        >
            <ContextMenu>
                <ContextMenuTrigger as-child>
                    <div
                        class="group relative bg-background border border-border/40 rounded-xl overflow-hidden cursor-pointer transition-colors duration-200 hover:border-primary/40 shadow-none hover:bg-accent/5"
                        :class="{ 
                            'bg-primary/5 border-primary/40 shadow-none': isSelected(folder.path),
                            'ring-2 ring-primary/20 border-primary/50 bg-primary/5': dropTarget === folder.path
                        }"
                        draggable="true"
                        @click="selectItem(folder); navigateToPath(folder.path)"
                        @contextmenu="selectItem(folder)"
                        @dragstart="(e) => onDragStart(e, folder, 'folder')"
                        @dragend="onDragEnd"
                        @dragover.prevent="(e) => onDragOver(e, folder)"
                        @dragleave="onDragLeave"
                        @drop="(e) => onDrop(e, folder)"
                    >
                        <div class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-[opacity,transform] scale-90 group-hover:scale-100" :class="{ 'opacity-100 scale-100': isSelected(folder.path) }">
                            <Checkbox
                                :checked="isSelected(folder.path)"
                                @update:checked="(v) => toggleSelection(folder.path)"
                                @click.stop
                                class="bg-background/80 backdrop-blur-sm border-primary/30"
                            />
                        </div>
                        <div class="aspect-square flex flex-col items-center justify-center p-4 transition-colors">
                            <Folder class="w-12 h-12 text-muted-foreground/40 transition-transform duration-300 group-hover:scale-105 group-active:scale-95" stroke-width="1.5" />
                        </div>
                        <div class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-[opacity,transform] scale-90 group-hover:scale-100" @click.stop>
                            <FileActionDropdown :item="folder">
                                <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg bg-background/80 backdrop-blur-sm border border-border/40 shadow-sm hover:bg-accent hover:text-accent-foreground">
                                    <MoreVertical class="w-4 h-4" />
                                </Button>
                            </FileActionDropdown>
                        </div>
                        <div class="p-3 border-t border-border/40 bg-transparent">
                            <p class="text-xs font-semibold truncate text-center text-foreground/90 px-1" :title="folder.name">{{ folder.name }}</p>
                            <p class="text-[10px] text-muted-foreground text-center mt-1 uppercase tracking-tight font-medium opacity-70">Folder</p>
                        </div>
                    </div>
                </ContextMenuTrigger>
                <FileContextMenu :item="folder" />
            </ContextMenu>
        </div>

        <!-- Files -->
        <div
            v-for="file in paginatedFiles"
            :key="file.path"
            v-memo="[isSelected(file.path), file.name]"
            class="group relative"
        >
            <ContextMenu>
                <ContextMenuTrigger as-child>
                    <div
                        class="group relative bg-background border border-border/40 rounded-xl hover:border-primary/40 transition-colors duration-200 cursor-pointer overflow-hidden shadow-none hover:bg-accent/5"
                        :class="{ 'bg-primary/5 border-primary/40 shadow-none': isSelected(file.path) }"
                        draggable="true"
                        @click="selectItem(file); $emit('preview', file)"
                        @contextmenu="selectItem(file)"
                        @dragstart="(e) => onDragStart(e, file, 'file')"
                        @dragend="onDragEnd"
                    >
                        <div class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-[opacity,transform] scale-90 group-hover:scale-100" :class="{ 'opacity-100 scale-100': isSelected(file.path) }">
                            <Checkbox
                                :checked="isSelected(file.path)"
                                @update:checked="toggleSelection(file.path)"
                                @click.stop
                                class="bg-background/80 backdrop-blur-sm border-primary/30"
                            />
                        </div>
                        <div class="aspect-square flex items-center justify-center overflow-hidden relative">
                            <img 
                                v-if="isImage(file)" 
                                :src="file.url" 
                                :alt="file.name"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                loading="lazy"
                            />
                            <div v-else-if="isVideo(file)" class="relative w-full h-full flex items-center justify-center">
                                <Video class="w-12 h-12 text-muted-foreground/50 transition-transform group-hover:scale-110" stroke-width="1.5" />
                                <div class="absolute inset-0 bg-black/5 flex items-center justify-center">
                                    <PlayCircle class="w-8 h-8 text-white/50 opacity-0 group-hover:opacity-100 transition-opacity" />
                                </div>
                                <div class="absolute bottom-2 right-2 bg-black/70 text-white text-[9px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider backdrop-blur-sm">
                                    {{ file.extension?.toUpperCase() }}
                                </div>
                            </div>
                            <div v-else class="flex flex-col items-center">
                                <FileText class="w-12 h-12 text-muted-foreground/30 transition-transform duration-300 group-hover:scale-110" stroke-width="1.5" />
                                <span class="mt-2 text-[10px] uppercase font-bold text-muted-foreground/50 tracking-widest">{{ file.extension }}</span>
                            </div>
                            <div class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-[opacity,transform] scale-90 group-hover:scale-100" @click.stop>
                                <FileActionDropdown :item="file" @preview="$emit('preview', file)">
                                    <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg bg-background/80 backdrop-blur-sm border border-border/40 shadow-sm hover:bg-accent hover:text-accent-foreground">
                                        <MoreVertical class="w-4 h-4" />
                                    </Button>
                                </FileActionDropdown>
                            </div>
                        </div>
                        <div class="p-3 border-t border-border/40 bg-transparent">
                            <p class="text-xs font-semibold truncate text-foreground/90 px-1" :title="file.name">{{ file.name }}</p>
                            <p class="text-[10px] text-muted-foreground mt-1 flex justify-between items-center px-1 font-medium italic">
                                <span>{{ formatFileSize(file.size) }}</span>
                                <span class="uppercase text-[9px] not-italic font-bold tracking-tighter opacity-60">{{ file.extension }}</span>
                            </p>
                        </div>
                    </div>
                </ContextMenuTrigger>
                <FileContextMenu :item="file" @preview="$emit('preview', file)" />
            </ContextMenu>
        </div>
    </div>
</template>

<script setup lang="ts">
import { inject, computed } from 'vue';
import Folder from 'lucide-vue-next/dist/esm/icons/folder.js';
import FolderOpen from 'lucide-vue-next/dist/esm/icons/folder-open.js';
import Video from 'lucide-vue-next/dist/esm/icons/video.js';
import PlayCircle from 'lucide-vue-next/dist/esm/icons/circle-play.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import Link from 'lucide-vue-next/dist/esm/icons/link.js';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import PackageOpen from 'lucide-vue-next/dist/esm/icons/package-open.js';
import Archive from 'lucide-vue-next/dist/esm/icons/archive.js';
import Clipboard from 'lucide-vue-next/dist/esm/icons/clipboard.js';
import ClipboardPaste from 'lucide-vue-next/dist/esm/icons/clipboard-paste.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import { 
    Checkbox, 
    ContextMenu, 
    ContextMenuTrigger,
    Button
} from '@/components/ui';
import FileContextMenu from './FileContextMenu.vue';
import FileActionDropdown from './FileActionDropdown.vue';
import { useToast } from '@/composables/useToast';
import MoreVertical from 'lucide-vue-next/dist/esm/icons/ellipsis-vertical.js';
import api from '@/services/api';
import type { FileItem, FolderItem } from '@/types/file-manager';
import { FileManagerKey } from '@/keys';

const {
    paginatedFolders,
    paginatedFiles,
    selectedItems,
    dropTarget,
    clipboard,
    navigateToPath,
    toggleSelection,
    copyToClipboard,
    pasteFromClipboard,
    deleteItem,
    moveItem,
    fetchCurrentPath,
    formatFileSize,
    isImage,
    isVideo,
    isArchive,
    selectItem
} = inject(FileManagerKey)!;

const toast = useToast();
const clipboardCount = computed(() => clipboard.value.items.length);

const isSelected = (path: string) => selectedItems.value.some((item: any) => item.path === path);

const onDragStart = (event: DragEvent, item: FileItem | FolderItem, type: 'file' | 'folder') => {
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', JSON.stringify({ path: item.path, type }));
    }
};

const onDragEnd = (event: DragEvent) => {
    // Handled by global state
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

const extractFile = async (file: FileItem) => {
    try {
        await api.post('/admin/ja/file-manager/extract', { path: file.path.replace(/^\//, '') });
        toast.success.action('Archive extracted');
        await fetchCurrentPath();
    } catch (error) {
        toast.error.default('Extraction failed');
    }
};

const compressItems = async (paths: string[]) => {
    try {
        await api.post('/admin/ja/file-manager/compress', { paths: paths.map(p => p.replace(/^\//, '')) });
        toast.success.action('Items compressed');
        await fetchCurrentPath();
    } catch (error) {
        toast.error.default('Compression failed');
    }
};
</script>
