<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">File Manager</h1>
            <div class="flex items-center space-x-2">
                <button
                    @click="showCreateFolderModal = true"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Folder
                </button>
                <button
                    @click="showUploadModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Upload Files
                </button>
            </div>
        </div>

        <div class="flex gap-6">
            <!-- Sidebar: Folders -->
            <div class="w-64 bg-white shadow rounded-lg p-4">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Folders</h2>
                <div class="space-y-1">
                    <button
                        @click="currentPath = '/'"
                        :class="[
                            'w-full text-left px-3 py-2 rounded-md text-sm',
                            currentPath === '/' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50'
                        ]"
                    >
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        Root
                    </button>
                    <div
                        v-for="folder in folders"
                        :key="folder.path"
                        class="pl-4"
                    >
                        <button
                            @click="navigateToFolder(folder.path)"
                            :class="[
                                'w-full text-left px-3 py-2 rounded-md text-sm',
                                currentPath === folder.path ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50'
                            ]"
                        >
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                            {{ folder.name }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Breadcrumb -->
                <div class="bg-white shadow rounded-lg p-4 mb-4">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2">
                            <li>
                                <button
                                    @click="currentPath = '/'"
                                    class="text-gray-500 hover:text-gray-700"
                                >
                                    Root
                                </button>
                            </li>
                            <li
                                v-for="(part, index) in pathParts"
                                :key="index"
                                class="flex items-center"
                            >
                                <svg class="w-4 h-4 text-gray-400 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <button
                                    @click="navigateToPath(part.path)"
                                    class="text-gray-500 hover:text-gray-700"
                                >
                                    {{ part.name }}
                                </button>
                            </li>
                        </ol>
                    </nav>
                </div>

                <!-- Files & Folders -->
                <div class="bg-white shadow rounded-lg">
                    <div v-if="loading" class="p-12 text-center">
                        <p class="text-gray-500">Loading...</p>
                    </div>
                    <div v-else-if="files.length === 0" class="p-12 text-center">
                        <p class="text-gray-500">No files found</p>
                    </div>
                    <div v-else class="grid grid-cols-4 gap-4 p-4">
                        <!-- Folders -->
                        <div
                            v-for="folder in foldersInCurrentPath"
                            :key="folder.path"
                            @click="navigateToFolder(folder.path)"
                            @contextmenu.prevent="showFolderContextMenu($event, folder)"
                            class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                        >
                            <div class="text-center">
                                <svg class="w-12 h-12 mx-auto text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                <p class="mt-2 text-sm font-medium text-gray-900 truncate">{{ folder.name }}</p>
                            </div>
                        </div>
                        <!-- Files -->
                        <div
                            v-for="file in filesInCurrentPath"
                            :key="file.path"
                            @click="viewFile(file)"
                            @contextmenu.prevent="showFileContextMenu($event, file)"
                            class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                        >
                            <div class="text-center">
                                <svg class="w-12 h-12 mx-auto text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm font-medium text-gray-900 truncate">{{ file.name }}</p>
                                <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
import { ref, onMounted, computed } from 'vue';
import api from '../../../services/api';
import { parseSingleResponse } from '../../../utils/responseParser';
import FileUploadModal from '../../../components/file-manager/FileUploadModal.vue';
import CreateFolderModal from '../../../components/file-manager/CreateFolderModal.vue';

const files = ref([]);
const folders = ref([]);
const loading = ref(false);
const currentPath = ref('/');
const showUploadModal = ref(false);
const showCreateFolderModal = ref(false);

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
        const folderPath = f.path.replace(/\/$/, '') || '/';
        return folderPath === currentPath.value && f.type === 'directory';
    });
});

const filesInCurrentPath = computed(() => {
    return files.value.filter(f => {
        const filePath = f.path.replace(/\/[^/]+$/, '') || '/';
        return filePath === currentPath.value && f.type === 'file';
    });
});

const fetchFiles = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/file-manager', {
            params: { path: currentPath.value },
        });
        const data = parseSingleResponse(response) || {};
        files.value = Array.isArray(data.files) ? data.files : [];
        folders.value = Array.isArray(data.folders) ? data.folders : [];
    } catch (error) {
        console.error('Failed to fetch files:', error);
    } finally {
        loading.value = false;
    }
};

const navigateToFolder = (path) => {
    currentPath.value = path;
    fetchFiles();
};

const navigateToPath = (path) => {
    currentPath.value = path;
    fetchFiles();
};

const viewFile = (file) => {
    window.open(file.url || file.path, '_blank');
};

const showFolderContextMenu = (event, folder) => {
    // Context menu for folders (delete, rename, etc.)
    if (confirm(`Delete folder "${folder.name}"?`)) {
        deleteFolder(folder);
    }
};

const showFileContextMenu = (event, file) => {
    // Context menu for files (delete, download, etc.)
    if (confirm(`Delete file "${file.name}"?`)) {
        deleteFile(file);
    }
};

const deleteFolder = async (folder) => {
    try {
        await api.delete(`/admin/cms/file-manager/folder`, {
            params: { path: folder.path },
        });
        await fetchFiles();
    } catch (error) {
        console.error('Failed to delete folder:', error);
        alert('Failed to delete folder');
    }
};

const deleteFile = async (file) => {
    try {
        await api.delete(`/admin/cms/file-manager/file`, {
            params: { path: file.path },
        });
        await fetchFiles();
    } catch (error) {
        console.error('Failed to delete file:', error);
        alert('Failed to delete file');
    }
};

const handleFileUploaded = () => {
    fetchFiles();
    showUploadModal.value = false;
};

const handleFolderCreated = () => {
    fetchFiles();
    showCreateFolderModal.value = false;
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

onMounted(() => {
    fetchFiles();
});
</script>

