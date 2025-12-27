<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ $t('features.file_manager.title') }}</h1>
                <Button
                    variant="ghost"
                    size="icon"
                    @click="showHelp = true"
                    class="text-muted-foreground hover:text-foreground"
                    :title="$t('features.file_manager.help.title')"
                >
                    <CircleHelp class="w-5 h-5" />
                </Button>
            </div>
            <div class="flex items-center space-x-3">
                <Button 
                    variant="outline"
                    @click="showCreateFolderModal = true"
                >
                    <FolderPlus class="w-4 h-4 mr-2" />
                    {{ $t('features.file_manager.actions.newFolder') }}
                </Button>
                <Button 
                    @click="showUploadModal = true"
                >
                    <Upload class="w-4 h-4 mr-2" />
                    {{ $t('features.file_manager.actions.upload') }}
                </Button>
            </div>
        </div>

        <!-- Help Dialog -->
        <Dialog v-model:open="showHelp">
            <DialogContent class="sm:max-w-[700px] bg-background">
                <DialogHeader>
                    <DialogTitle>{{ $t('features.file_manager.help.title') }}</DialogTitle>
                    <DialogDescription>
                        {{ $t('features.file_manager.help.sections.navigation.content') }}
                    </DialogDescription>
                </DialogHeader>
                <div class="grid gap-6 py-4">
                    <div class="grid gap-2">
                        <div class="flex items-center gap-2 font-medium">
                            <Upload class="w-4 h-4" />
                            {{ $t('features.file_manager.help.sections.upload.title') }}
                        </div>
                        <p class="text-sm text-muted-foreground">{{ $t('features.file_manager.help.sections.upload.content') }}</p>
                    </div>
                    <div class="grid gap-2">
                        <div class="flex items-center gap-2 font-medium">
                            <MoreVertical class="w-4 h-4" />
                            {{ $t('features.file_manager.help.sections.actions.title') }}
                        </div>
                        <p class="text-sm text-muted-foreground">{{ $t('features.file_manager.help.sections.actions.content') }}</p>
                    </div>
                    <div class="grid gap-2">
                        <div class="flex items-center gap-2 font-medium">
                            <List class="w-4 h-4" />
                            {{ $t('features.file_manager.help.sections.bulk.title') }}
                        </div>
                        <p class="text-sm text-muted-foreground">{{ $t('features.file_manager.help.sections.bulk.content') }}</p>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Filters & Toolbar -->
        <div class="bg-card border border-border rounded-lg p-4 mb-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-1 items-center gap-2 max-w-2xl">
                    <!-- Search -->
                    <div class="relative flex-1">
                        <SearchIcon class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="searchQuery"
                            type="text"
                            :placeholder="$t('features.file_manager.actions.search')"
                            class="pl-8 bg-background"
                        />
                    </div>
                    
                    <!-- Type Filter -->
                    <Select v-model="filterType">
                        <SelectTrigger class="w-[140px] bg-background">
                            <SelectValue :placeholder="$t('features.file_manager.filter.all')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ $t('features.file_manager.filter.all') }}</SelectItem>
                            <SelectItem value="images">{{ $t('features.file_manager.filter.images') }}</SelectItem>
                            <SelectItem value="videos">{{ $t('features.file_manager.filter.videos') }}</SelectItem>
                            <SelectItem value="documents">{{ $t('features.file_manager.filter.documents') }}</SelectItem>
                            <SelectItem value="audio">{{ $t('features.file_manager.filter.audio') }}</SelectItem>
                            <SelectItem value="archives">{{ $t('features.file_manager.filter.archives') }}</SelectItem>
                            <SelectItem value="other">{{ $t('features.file_manager.filter.other') }}</SelectItem>
                        </SelectContent>
                    </Select>

                    <!-- Sort -->
                    <Select v-model="sortBy">
                        <SelectTrigger class="w-[140px] bg-background">
                            <SelectValue :placeholder="$t('features.file_manager.sort.name')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="name">{{ $t('features.file_manager.sort.name') }}</SelectItem>
                            <SelectItem value="size">{{ $t('features.file_manager.sort.size') }}</SelectItem>
                            <SelectItem value="date">{{ $t('features.file_manager.sort.date') }}</SelectItem>
                        </SelectContent>
                    </Select>

                    <!-- View Toggle -->
                    <div class="flex items-center border border-input rounded-md bg-background p-1 shadow-sm">
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="viewMode = 'grid'"
                            :class="[
                                'h-8 w-8 p-0 rounded-sm transition-all',
                                viewMode === 'grid' ? 'bg-secondary text-secondary-foreground shadow-sm' : 'text-muted-foreground hover:bg-muted'
                            ]"
                        >
                            <LayoutGrid class="w-4 h-4" />
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="viewMode = 'list'"
                            :class="[
                                'h-8 w-8 p-0 rounded-sm transition-all',
                                viewMode === 'list' ? 'bg-secondary text-secondary-foreground shadow-sm' : 'text-muted-foreground hover:bg-muted'
                            ]"
                        >
                            <List class="w-4 h-4" />
                        </Button>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div v-if="selectedItems.length > 0" class="flex items-center gap-2 animate-in fade-in slide-in-from-right-2">
                    <span class="text-sm font-medium text-muted-foreground">
                        {{ $t('features.file_manager.bulk.label', { count: selectedItems.length }) }}
                    </span>
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="bulkDelete"
                    >
                        <Trash2 class="w-4 h-4 mr-2" />
                        {{ $t('features.file_manager.bulk.delete') }}
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="clearSelection"
                    >
                        {{ $t('features.file_manager.bulk.cancel') }}
                    </Button>
                </div>
            </div>
        </div>

        <div class="flex gap-6">
            <!-- Sidebar: Folders -->
            <div class="w-64 bg-card border border-border rounded-lg p-4 h-fit">
                <h2 class="text-sm font-semibold text-foreground mb-3 flex items-center">
                    <Folder class="w-4 h-4 mr-2" />
                    {{ $t('features.file_manager.labels.folders') }}
                </h2>
                <div class="space-y-1 max-h-[calc(100vh-300px)] overflow-y-auto pr-2">
                    <Button
                        variant="ghost"
                        @click="navigateToPath('/')"
                        :class="[
                            'w-full justify-start text-sm h-9 px-2',
                            currentPath === '/' ? 'bg-primary/10 text-primary hover:bg-primary/20' : 'text-muted-foreground hover:bg-accent'
                        ]"
                    >
                        <Folder class="w-4 h-4 mr-2" />
                        {{ $t('features.file_manager.nav.root') }}
                    </Button>
                    <div v-for="folder in allFolders" :key="folder.path" class="pl-2">
                        <Button
                            variant="ghost"
                            @click="navigateToFolder(folder.path)"
                            :class="[
                                'w-full justify-start text-sm h-9 px-2 truncate',
                                currentPath === folder.path ? 'bg-primary/10 text-primary hover:bg-primary/20' : 'text-muted-foreground hover:bg-accent'
                            ]"
                            :title="folder.name"
                        >
                            <Folder class="w-4 h-4 mr-2 flex-shrink-0" />
                            <span class="truncate">{{ folder.name }}</span>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 min-w-0">
                <!-- Breadcrumbs -->
                <div class="flex items-center gap-1 text-sm mb-4 bg-card border border-border rounded-lg p-2 px-4 shadow-sm">
                    <Button 
                        variant="ghost" 
                        size="sm" 
                        class="h-6 px-1.5 text-muted-foreground hover:text-foreground"
                        @click="navigateToPath('/')"
                    >
                        <Folder class="w-3.5 h-3.5" />
                    </Button>
                    <template v-for="(part, index) in pathParts" :key="index">
                        <span class="text-muted-foreground">/</span>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="navigateToPath(part.path)"
                            class="h-6 px-1.5 font-medium"
                            :class="index === pathParts.length - 1 ? 'text-foreground' : 'text-muted-foreground hover:text-foreground'"
                        >
                            {{ part.name }}
                        </Button>
                    </template>
                </div>

                <!-- Content Area -->
                <div class="bg-card border border-border rounded-lg min-h-[400px]">
                    <div v-if="loading" class="flex flex-col items-center justify-center p-12 text-muted-foreground h-full">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-4"></div>
                        <p>{{ $t('features.file_manager.messages.loading') }}</p>
                    </div>

                    <div v-else-if="filteredFolders.length === 0 && filteredFiles.length === 0" class="flex flex-col items-center justify-center p-12 text-muted-foreground h-full">
                        <FolderPlus class="w-12 h-12 mb-4 opacity-20" />
                        <p>{{ $t('features.file_manager.messages.noFiles') }}</p>
                    </div>

                    <div v-else>
                        <!-- Grid View -->
                        <div v-if="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 p-4">
                            <!-- Folders -->
                            <div
                                v-for="folder in paginatedFolders"
                                :key="folder.path"
                                class="group relative bg-background border border-border rounded-lg hover:border-primary/50 transition-all cursor-pointer overflow-hidden"
                                :class="{ 'ring-2 ring-primary border-primary': isSelected(folder.path) }"
                                @click="navigateToFolder(folder.path)"
                            >
                                <div class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity" :class="{ 'opacity-100': isSelected(folder.path) }">
                                    <Checkbox
                                        :checked="isSelected(folder.path)"
                                        @update:checked="(v) => toggleSelection(folder.path)"
                                        @click.stop
                                    />
                                </div>
                                <div class="aspect-square flex flex-col items-center justify-center p-4 bg-muted/30 group-hover:bg-muted/50">
                                    <Folder class="w-12 h-12 text-primary/80 mb-2 transition-transform group-hover:scale-110" />
                                </div>
                                <div class="p-3 border-t border-border bg-card">
                                    <p class="text-sm font-medium truncate text-center" :title="folder.name">{{ folder.name }}</p>
                                    <p class="text-xs text-muted-foreground text-center mt-0.5">{{ $t('features.file_manager.labels.folders') }}</p>
                                </div>
                            </div>

                            <!-- Files -->
                            <div
                                v-for="file in paginatedFiles"
                                :key="file.path"
                                class="group relative bg-background border border-border rounded-lg hover:border-primary/50 transition-all cursor-pointer overflow-hidden"
                                :class="{ 'ring-2 ring-primary border-primary': isSelected(file.path) }"
                                @click="viewFile(file)"
                            >
                                <div class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity" :class="{ 'opacity-100': isSelected(file.path) }">
                                    <Checkbox
                                        :checked="isSelected(file.path)"
                                        @update:checked="(v) => toggleSelection(file.path)"
                                        @click.stop
                                    />
                                </div>
                                <div class="aspect-square flex items-center justify-center bg-muted/30 group-hover:bg-muted/50 overflow-hidden relative">
                                    <img 
                                        v-if="isImage(file)" 
                                        :src="file.url" 
                                        :alt="file.name"
                                        class="w-full h-full object-cover transition-transform group-hover:scale-105"
                                        loading="lazy"
                                    />
                                    <FileText v-else class="w-12 h-12 text-muted-foreground/50" />
                                </div>
                                <div class="p-3 border-t border-border bg-card">
                                    <p class="text-sm font-medium truncate" :title="file.name">{{ file.name }}</p>
                                    <p class="text-xs text-muted-foreground mt-0.5 flex justify-between">
                                        <span>{{ file.extension?.toUpperCase() }}</span>
                                        <span>{{ formatFileSize(file.size) }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- List View -->
                        <div v-else class="min-w-full">
                            <table class="w-full text-sm item-center">
                                <thead class="bg-muted/50 text-muted-foreground font-medium border-b border-border">
                                    <tr>
                                        <th class="px-4 py-3 text-left w-12">
                                            <Checkbox
                                                :checked="isAllSelected"
                                                @update:checked="toggleSelectAll"
                                            />
                                        </th>
                                        <th class="px-4 py-3 text-left w-12"></th> <!-- Icon -->
                                        <th class="px-4 py-3 text-left">{{ $t('features.file_manager.sort.name') }}</th>
                                        <th class="px-4 py-3 text-left">{{ $t('features.file_manager.sort.size') }}</th>
                                        <th class="px-4 py-3 text-left">{{ $t('features.file_manager.sort.date') }}</th>
                                        <th class="px-4 py-3 text-right w-24">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr 
                                        v-for="folder in paginatedFolders" 
                                        :key="folder.path"
                                        class="hover:bg-muted/30 cursor-pointer group"
                                        :class="{ 'bg-primary/5': isSelected(folder.path) }"
                                        @click="navigateToFolder(folder.path)"
                                    >
                                        <td class="px-4 py-3" @click.stop>
                                            <Checkbox
                                                :checked="isSelected(folder.path)"
                                                @update:checked="(v) => toggleSelection(folder.path)"
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <Folder class="w-5 h-5 text-primary fill-primary/20" />
                                        </td>
                                        <td class="px-4 py-3 font-medium">{{ folder.name }}</td>
                                        <td class="px-4 py-3 text-muted-foreground">-</td>
                                        <td class="px-4 py-3 text-muted-foreground">{{ formatDate(folder.updated_at) }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <Button variant="ghost" size="icon" class="h-8 w-8 opacity-0 group-hover:opacity-100">
                                                <MoreVertical class="w-4 h-4" />
                                            </Button>
                                        </td>
                                    </tr>
                                    <tr 
                                        v-for="file in paginatedFiles" 
                                        :key="file.path"
                                        class="hover:bg-muted/30 cursor-pointer group"
                                        :class="{ 'bg-primary/5': isSelected(file.path) }"
                                        @click="viewFile(file)"
                                    >
                                        <td class="px-4 py-3" @click.stop>
                                            <Checkbox
                                                :checked="isSelected(file.path)"
                                                @update:checked="(v) => toggleSelection(file.path)"
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <img v-if="isImage(file)" :src="file.url" class="w-8 h-8 rounded object-cover border border-border" />
                                            <FileText v-else class="w-5 h-5 text-muted-foreground" />
                                        </td>
                                        <td class="px-4 py-3 font-medium">{{ file.name }}</td>
                                        <td class="px-4 py-3 text-muted-foreground">{{ formatFileSize(file.size) }}</td>
                                        <td class="px-4 py-3 text-muted-foreground">{{ formatDate(file.uploaded_at || file.updated_at) }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end opacity-0 group-hover:opacity-100">
                                                <Button variant="ghost" size="icon" class="h-8 w-8" @click.stop="deleteFile(file)">
                                                    <Trash2 class="w-4 h-4 text-destructive" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <Pagination
                    v-if="totalItems > 0"
                    v-model:currentPage="currentPage"
                    :totalItems="totalItems"
                    :perPage="itemsPerPage"
                    :perPageOptions="[5, 10, 15, 20, 25, 50, 100]"
                    @update:perPage="handlePerPageChange"
                    class="mt-4"
                />
            </div>
        </div>

        <!-- Image Preview Modal -->
        <Dialog v-model:open="showImagePreview">
            <DialogContent class="max-w-4xl p-0 overflow-hidden bg-black/95 border-none">
                <div class="relative flex items-center justify-center p-4 min-h-[50vh]">
                    <img
                        v-if="previewImage"
                        :src="previewImage.url"
                        :alt="previewImage.name"
                        class="max-w-full max-h-[80vh] object-contain rounded-sm"
                    />
                </div>
                <div class="p-4 bg-background/10 text-white backdrop-blur flex justify-between items-center absolute bottom-0 left-0 right-0">
                    <div>
                        <p class="font-medium truncate">{{ previewImage?.name }}</p>
                        <p class="text-xs opacity-70">{{ formatFileSize(previewImage?.size) }}</p>
                    </div>
                    <Button variant="ghost" size="icon" class="text-white hover:bg-white/20" @click="showImagePreview = false">
                        <X class="w-5 h-5" />
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Upload Modal -->
        <FileUploadModal
            v-if="showUploadModal"
            @close="showUploadModal = false"
            @uploaded="handleFileUploaded"
            :path="currentPath"
        />

        <!-- Create Folder Modal -->
        <CreateFolderModal
            v-if="showCreateFolderModal"
            @close="showCreateFolderModal = false"
            @created="handleFolderCreated"
            :path="currentPath"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseSingleResponse } from '../../../utils/responseParser';
import { 
    Search as SearchIcon, 
    LayoutGrid, 
    List, 
    Folder, 
    FolderPlus, 
    Upload, 
    FileText, 
    MoreVertical, 
    Download, 
    Trash2, 
    Edit,
    CircleHelp,
    X,
    File,
    Image as ImageIcon,
    File as FileIcon
} from 'lucide-vue-next';

const { t } = useI18n();
import FileUploadModal from '../../../components/file-manager/FileUploadModal.vue';
import CreateFolderModal from '../../../components/file-manager/CreateFolderModal.vue';
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Select from '@/components/ui/select.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';
import Checkbox from '@/components/ui/checkbox.vue';
import Card from '@/components/ui/card.vue';
import Dialog from '@/components/ui/dialog.vue';
import DialogContent from '@/components/ui/dialog-content.vue';
import DialogHeader from '@/components/ui/dialog-header.vue';
import DialogTitle from '@/components/ui/dialog-title.vue';
import DialogDescription from '@/components/ui/dialog-description.vue';
import Pagination from '@/components/ui/pagination.vue';


const files = ref([]);
const folders = ref([]);
const allFolders = ref([]); // Cache all folders
const filesCache = ref(new Map()); // Cache files per path
const loading = ref(false);
const currentPath = ref('/');
const showUploadModal = ref(false);
const showCreateFolderModal = ref(false);
const showImagePreview = ref(false);
const previewImage = ref(null);
const viewMode = ref('grid');
const contextMenu = ref({
    show: false,
    x: 0,
    y: 0,
    item: null,
    type: null
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Search, Filter, Sort
const searchQuery = ref('');
const filterType = ref('all');
const sortBy = ref('name');
const sortDirection = ref('asc');

// Bulk Actions
const selectedItems = ref([]);

// Pagination
const currentPage = ref(1);
const itemsPerPage = ref(10);

const handlePerPageChange = (value) => {
    // Value from Pagination component is already a number
    itemsPerPage.value = typeof value === 'number' ? value : parseInt(value, 10);
    currentPage.value = 1; // Reset to first page when changing items per page
};

// Help
const showHelp = ref(false);

const pathParts = computed(() => {
    if (currentPath.value === '/') return [];
    const parts = currentPath.value.split('/').filter(p => p);
    return parts.map((part, index) => ({
        name: part,
        path: '/' + parts.slice(0, index + 1).join('/'),
    }));
});

const foldersInCurrentPath = computed(() => {
    return folders.value.filter(f => {
        // Get parent path of this folder
        const parts = f.path.split('/').filter(p => p);
        const parentPath = parts.length > 1 ? '/' + parts.slice(0, -1).join('/') : '/';
        return parentPath === currentPath.value;
    });
});

const filesInCurrentPath = computed(() => {
    // Get from cache if available
    if (filesCache.value.has(currentPath.value)) {
        return filesCache.value.get(currentPath.value);
    }
    return files.value.filter(f => {
        // Get parent path of this file
        const parts = f.path.split('/').filter(p => p);
        const parentPath = parts.length > 1 ? '/' + parts.slice(0, -1).join('/') : '/';
        return parentPath === currentPath.value;
    });
});

const isImage = (file) => {
    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
    return imageExtensions.includes(file.extension?.toLowerCase());
};

const matchesFileType = (file, type) => {
    if (type === 'all') return true;
    
    const ext = file.extension?.toLowerCase() || '';
    
    const typeMap = {
        images: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'],
        documents: ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt', 'xls', 'xlsx', 'ppt', 'pptx'],
        videos: ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'],
        audio: ['mp3', 'wav', 'flac', 'aac', 'ogg', 'm4a'],
        archives: ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'],
    };
    
    return typeMap[type]?.includes(ext) || false;
};

// Filtered and searched items
const filteredFolders = computed(() => {
    let result = foldersInCurrentPath.value;
    
    // Search
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(f => f.name.toLowerCase().includes(query));
    }
    
    return result;
});

const filteredFiles = computed(() => {
    let result = filesInCurrentPath.value;
    
    // Search
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(f => f.name.toLowerCase().includes(query));
    }
    
    // Filter by type
    result = result.filter(f => matchesFileType(f, filterType.value));
    
    return result;
});

// Sorted items
const sortedFolders = computed(() => {
    const items = [...filteredFolders.value];
    
    items.sort((a, b) => {
        let comparison = 0;
        
        if (sortBy.value === 'name') {
            comparison = a.name.localeCompare(b.name);
        } else if (sortBy.value === 'size') {
            comparison = (a.size || 0) - (b.size || 0);
        } else if (sortBy.value === 'date') {
            comparison = new Date(a.updated_at || 0) - new Date(b.updated_at || 0);
        }
        
        return sortDirection.value === 'asc' ? comparison : -comparison;
    });
    
    return items;
});

const sortedFiles = computed(() => {
    const items = [...filteredFiles.value];
    
    items.sort((a, b) => {
        let comparison = 0;
        
        if (sortBy.value === 'name') {
            comparison = a.name.localeCompare(b.name);
        } else if (sortBy.value === 'size') {
            comparison = (a.size || 0) - (b.size || 0);
        } else if (sortBy.value === 'date') {
            comparison = new Date(a.updated_at || 0) - new Date(b.updated_at || 0);
        }
        
        return sortDirection.value === 'asc' ? comparison : -comparison;
    });
    
    return items;
});

// Pagination
const totalItems = computed(() => sortedFolders.value.length + sortedFiles.value.length);
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage.value));

const paginatedFolders = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    
    // If we have enough folders to fill the page, return them
    if (sortedFolders.value.length >= end) {
        return sortedFolders.value.slice(start, end);
    }
    
    // Otherwise return all folders up to the limit
    return sortedFolders.value.slice(start);
});

const paginatedFiles = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    
    // Calculate how many slots folders used
    const foldersCount = paginatedFolders.value.length;
    const filesStart = Math.max(0, start - sortedFolders.value.length);
    const filesEnd = end - sortedFolders.value.length;
    
    if (filesEnd <= 0) return [];
    
    return sortedFiles.value.slice(filesStart, filesEnd);
});

const fetchFiles = async (path = currentPath.value) => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/file-manager', {
            params: { path },
        });
        const data = parseSingleResponse(response) || {};
        
        const fetchedFiles = Array.isArray(data.files) ? data.files : [];
        const newFolders = Array.isArray(data.folders) ? data.folders : [];
        
        // Cache files for this path
        filesCache.value.set(path, fetchedFiles);
        
        // Update files for current path
        if (path === currentPath.value) {
            files.value = fetchedFiles;
        }
        
        // Merge with existing folders (don't replace, accumulate)
        newFolders.forEach(folder => {
            if (!allFolders.value.find(f => f.path === folder.path)) {
                allFolders.value.push(folder);
            }
        });
        
        // Update folders ref
        folders.value = allFolders.value;
        
        // Recursively fetch subfolders to build complete tree
        for (const folder of newFolders) {
            await fetchFiles(folder.path);
        }
    } catch (error) {
        console.error('Failed to fetch files:', error);
    } finally {
        if (path === currentPath.value) {
            loading.value = false;
        }
    }
};

const fetchCurrentPath = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/file-manager', {
            params: { path: currentPath.value },
        });
        const data = parseSingleResponse(response) || {};
        const fetchedFiles = Array.isArray(data.files) ? data.files : [];
        const newFolders = Array.isArray(data.folders) ? data.folders : [];
        
        // Cache files for current path
        filesCache.value.set(currentPath.value, fetchedFiles);
        files.value = fetchedFiles;
        
        // Merge with existing folders
        newFolders.forEach(folder => {
            if (!allFolders.value.find(f => f.path === folder.path)) {
                allFolders.value.push(folder);
            }
        });
        
        folders.value = allFolders.value;
    } catch (error) {
        console.error('Failed to fetch files:', error);
    } finally {
        loading.value = false;
    }
};

const navigateToFolder = (path) => {
    currentPath.value = path;
    fetchCurrentPath();
};

const navigateToPath = (path) => {
    currentPath.value = path;
    fetchCurrentPath();
};

const viewFile = (file) => {
    if (isImage(file)) {
        // Show image preview modal
        previewImage.value = file;
        showImagePreview.value = true;
    } else if (file.url) {
        // Open other files in new tab
        window.open(file.url, '_blank');
    }
};

const closeImagePreview = () => {
    showImagePreview.value = false;
    previewImage.value = null;
};

const showFolderContextMenu = (event, folder) => {
    event.preventDefault();
    contextMenu.value = {
        show: true,
        x: event.clientX,
        y: event.clientY,
        item: folder,
        type: 'folder',
    };
};

const showFileContextMenu = (event, file) => {
    event.preventDefault();
    contextMenu.value = {
        show: true,
        x: event.clientX,
        y: event.clientY,
        item: file,
        type: 'file',
    };
};

const closeContextMenu = () => {
    contextMenu.value.show = false;
};

const handleContextMenuAction = (action) => {
    const { item, type } = contextMenu.value;
    
    switch (action) {
        case 'delete':
            if (type === 'folder') {
                deleteFolderAction(item);
            } else {
                deleteFileAction(item);
            }
            break;
        case 'download':
            if (type === 'file' && item.url) {
                window.open(item.url, '_blank');
            }
            break;
        case 'rename':
            // TODO: Implement rename modal
            alert('Rename functionality coming soon');
            break;
    }
    
    closeContextMenu();
};

const deleteFolderAction = async (folder) => {
    if (!confirm(t('features.file_manager.messages.deleteFolderConfirm', { name: folder.name }))) {
        return;
    }
    
    try {
        await api.delete('/admin/cms/file-manager/folder', {
            params: { path: folder.path.replace(/^\//, '') },
        });
        
        // Remove from allFolders cache
        allFolders.value = allFolders.value.filter(f => f.path !== folder.path);
        folders.value = allFolders.value;
        
        await fetchFiles();
    } catch (error) {
        console.error('Failed to delete folder:', error);
        alert(t('features.file_manager.messages.deleteFolderFailed'));
    }
};

const deleteFileAction = async (file) => {
    if (!confirm(t('features.file_manager.messages.deleteFileConfirm', { name: file.name }))) {
        return;
    }
    
    try {
        await api.delete('/admin/cms/file-manager', {
            params: { path: file.path.replace(/^\//, '') },
        });
        await fetchFiles();
    } catch (error) {
        console.error('Failed to delete file:', error);
        alert(t('features.file_manager.messages.deleteFileFailed'));
    }
};

//Bulk Actions
const isSelected = (path) => {
    return selectedItems.value.includes(path);
};

const toggleSelection = (path) => {
    const index = selectedItems.value.indexOf(path);
    if (index > -1) {
        selectedItems.value.splice(index, 1);
    } else {
        selectedItems.value.push(path);
    }
};

const selectAllInPage = () => {
    const allPaths = [
        ...paginatedFolders.value.map(f => f.path),
        ...paginatedFiles.value.map(f => f.path)
    ];
    selectedItems.value = [...new Set([...selectedItems.value, ...allPaths])];
};

// Computed: Check if all items in current page are selected
const isAllSelected = computed(() => {
    const allPaths = [
        ...paginatedFolders.value.map(f => f.path),
        ...paginatedFiles.value.map(f => f.path)
    ];
    if (allPaths.length === 0) return false;
    return allPaths.every(path => selectedItems.value.includes(path));
});

// Toggle select all items in current page
const toggleSelectAll = (checked) => {
    const allPaths = [
        ...paginatedFolders.value.map(f => f.path),
        ...paginatedFiles.value.map(f => f.path)
    ];
    
    if (checked) {
        // Add all paths to selection
        selectedItems.value = [...new Set([...selectedItems.value, ...allPaths])];
    } else {
        // Remove all paths from selection
        selectedItems.value = selectedItems.value.filter(path => !allPaths.includes(path));
    }
};

const clearSelection = () => {
    selectedItems.value = [];
};

const bulkDelete = async () => {
    const count = selectedItems.value.length;
    if (!confirm(t('features.file_manager.bulk.confirmDelete', count, { count }))) {
        return;
    }
    
    try {
        // Delete each item
        for (const path of selectedItems.value) {
            // Check if it's a folder or file
            const isFolder = allFolders.value.find(f => f.path === path);
            
            if (isFolder) {
                await api.delete('/admin/cms/file-manager/folder', {
                    params: { path: path.replace(/^\//, '') },
                });
                // Remove from cache
                allFolders.value = allFolders.value.filter(f => f.path !== path);
            } else {
                await api.delete('/admin/cms/file-manager', {
                    params: { path: path.replace(/^\//, '') },
                });
            }
        }
        
        clearSelection();
        folders.value = allFolders.value;
        await fetchCurrentPath();
    } catch (error) {
        console.error('Failed to bulk delete:', error);
        alert('Failed to delete some items');
    }
};

// Watch for help visibility changes and save to localStorage
const saveHelpState = () => {
    localStorage.setItem('fileManagerShowHelp', showHelp.value.toString());
};

// Watch showHelp changes
watch(showHelp, saveHelpState);

// Reset page when filters change
watch([searchQuery, filterType, sortBy, sortDirection], () => {
    currentPage.value = 1;
});

const handleFileUploaded = () => {
    fetchCurrentPath();
    showUploadModal.value = false;
};

const handleFolderCreated = () => {
    fetchCurrentPath();
    showCreateFolderModal.value = false;
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

// Close context menu when clicking outside
const handleClickOutside = () => {
    if (contextMenu.value.show) {
        closeContextMenu();
    }
};

onMounted(() => {
    fetchFiles(); // Initial load - recursively fetch all
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

