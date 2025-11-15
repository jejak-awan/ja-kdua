<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Media Library</h1>
            <div class="flex items-center space-x-3">
                <!-- View Toggle -->
                <div class="flex border border-gray-300 rounded-md">
                    <button
                        @click="viewMode = 'grid'"
                        :class="[
                            'px-3 py-2',
                            viewMode === 'grid' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-50'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>
                    <button
                        @click="viewMode = 'list'"
                        :class="[
                            'px-3 py-2',
                            viewMode === 'list' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-50'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
                <!-- Upload Button -->
                <button
                    @click="showUploadModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Upload Media
                </button>
            </div>
        </div>

        <div class="flex gap-6">
            <!-- Sidebar: Folders -->
            <div class="w-64 bg-white shadow rounded-lg p-4">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Folders</h2>
                <div class="space-y-1">
                    <button
                        @click="selectedFolder = null"
                        :class="[
                            'w-full text-left px-3 py-2 rounded-md text-sm',
                            selectedFolder === null ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50'
                        ]"
                    >
                        All Media
                    </button>
                    <div v-for="folder in folders" :key="folder.id" class="pl-4">
                        <button
                            @click="selectedFolder = folder.id"
                            :class="[
                                'w-full text-left px-3 py-2 rounded-md text-sm',
                                selectedFolder === folder.id ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-50'
                            ]"
                        >
                            {{ folder.name }}
                        </button>
                    </div>
                </div>
                <button
                    @click="showFolderModal = true"
                    class="mt-4 w-full px-3 py-2 text-sm text-indigo-600 hover:text-indigo-700 border border-indigo-300 rounded-md hover:bg-indigo-50"
                >
                    + New Folder
                </button>
            </div>

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Filters -->
                <div class="bg-white shadow rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4 flex-1">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search media..."
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            />
                            <select
                                v-model="mimeFilter"
                                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">All Types</option>
                                <option value="image">Images</option>
                                <option value="video">Videos</option>
                                <option value="application">Documents</option>
                            </select>
                            <select
                                v-model="usageFilter"
                                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">All Media</option>
                                <option value="used">Used</option>
                                <option value="unused">Unused</option>
                            </select>
                        </div>
                        <div v-if="selectedMedia.length > 0" class="flex items-center space-x-2 ml-4">
                            <span class="text-sm text-gray-700">{{ selectedMedia.length }} selected</span>
                            <div class="relative">
                                <select
                                    v-model="bulkAction"
                                    @change="handleBulkAction"
                                    class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Bulk Actions</option>
                                    <option value="delete">Delete</option>
                                    <option value="move">Move to Folder</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media Grid/List -->
                <div v-if="loading" class="bg-white shadow rounded-lg p-12 text-center">
                    <p class="text-gray-500">Loading media...</p>
                </div>

                <div v-else-if="mediaList.length === 0" class="bg-white shadow rounded-lg p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="mt-4 text-gray-500">No media found</p>
                </div>

                <!-- Grid View -->
                <div v-else-if="viewMode === 'grid'" class="grid grid-cols-4 gap-4">
                    <div
                        v-for="media in mediaList"
                        :key="media.id"
                        @click="toggleMediaSelection(media)"
                        class="bg-white shadow rounded-lg overflow-hidden cursor-pointer hover:shadow-lg transition-shadow relative"
                        :class="isMediaSelected(media.id) ? 'ring-2 ring-indigo-500' : ''"
                    >
                        <!-- Checkbox -->
                        <div class="absolute top-2 left-2 z-10">
                            <input
                                type="checkbox"
                                :checked="isMediaSelected(media.id)"
                                @click.stop="toggleMediaSelection(media)"
                                class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            />
                        </div>
                        <div class="aspect-square bg-gray-100 flex items-center justify-center relative group">
                            <LazyImage
                                v-if="media.mime_type?.startsWith('image/')"
                                :src="media.thumbnail_url || media.url"
                                :alt="media.alt || media.name"
                                image-class="w-full h-full object-cover"
                                @error="handleImageError($event)"
                            />
                            <div v-else class="text-gray-400">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <!-- Quick Actions Overlay -->
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <div class="flex space-x-2">
                                    <button
                                        @click.stop="viewMedia(media)"
                                        class="p-2 bg-white rounded-md text-gray-700 hover:bg-gray-100"
                                        title="View"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click.stop="editMedia(media)"
                                        class="p-2 bg-white rounded-md text-gray-700 hover:bg-gray-100"
                                        title="Edit"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click.stop="deleteMedia(media)"
                                        class="p-2 bg-white rounded-md text-red-600 hover:bg-red-50"
                                        title="Delete"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="p-3">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ media.name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ formatFileSize(media.size) }}</p>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div v-else class="bg-white shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        :checked="allMediaSelected"
                                        @change="toggleSelectAll"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Media
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Size
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Folder
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="media in mediaList" :key="media.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input
                                        type="checkbox"
                                        :value="media.id"
                                        v-model="selectedMedia"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-16 h-16 bg-gray-100 rounded flex items-center justify-center">
                                        <LazyImage
                                            v-if="media.mime_type?.startsWith('image/')"
                                            :src="media.thumbnail_url || media.url"
                                            :alt="media.alt || media.name"
                                            image-class="w-full h-full object-cover rounded"
                                            @error="handleImageError($event)"
                                        />
                                        <svg v-else class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ media.name }}</div>
                                    <div v-if="media.alt" class="text-sm text-gray-500">{{ media.alt }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ media.mime_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatFileSize(media.size) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ media.folder?.name || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button
                                            @click="viewMedia(media)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            View
                                        </button>
                                        <button
                                            @click="editMedia(media)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="deleteMedia(media)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="pagination && pagination.last_page > 1" class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                    </div>
                    <div class="flex space-x-2">
                        <button
                            @click="changePage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="px-3 py-2 border border-gray-300 rounded-md text-sm disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <button
                            @click="changePage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-3 py-2 border border-gray-300 rounded-md text-sm disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <MediaUploadModal
            v-if="showUploadModal"
            @close="showUploadModal = false"
            @uploaded="handleMediaUploaded"
            :folder-id="selectedFolder"
        />

        <!-- Edit Modal -->
        <MediaEditModal
            v-if="showEditModal && editingMedia"
            @close="showEditModal = false"
            @updated="handleMediaUpdated"
            :media="editingMedia"
        />

        <!-- View Modal -->
        <MediaViewModal
            v-if="showViewModal && viewingMedia"
            @close="showViewModal = false"
            @updated="handleMediaUpdated"
            :media="viewingMedia"
        />

        <!-- Folder Modal -->
        <FolderModal
            v-if="showFolderModal"
            @close="showFolderModal = false"
            @created="handleFolderCreated"
        />

        <!-- Move to Folder Modal -->
        <MoveToFolderModal
            v-if="showMoveFolderModal"
            @close="showMoveFolderModal = false"
            @moved="handleMoveToFolder"
            :folders="folders"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import api from '../../../services/api';
import MediaUploadModal from '../../../components/media/MediaUploadModal.vue';
import MediaEditModal from '../../../components/media/MediaEditModal.vue';
import MediaViewModal from '../../../components/media/MediaViewModal.vue';
import FolderModal from '../../../components/media/FolderModal.vue';
import MoveToFolderModal from '../../../components/media/MoveToFolderModal.vue';
import LazyImage from '../../../components/LazyImage.vue';

const viewMode = ref('grid');
const loading = ref(false);
const mediaList = ref([]);
const folders = ref([]);
const selectedFolder = ref(null);
const selectedMedia = ref([]);
const pagination = ref(null);
const search = ref('');
const mimeFilter = ref('');
const usageFilter = ref('');
const bulkAction = ref('');

const showUploadModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const showFolderModal = ref(false);
const showMoveFolderModal = ref(false);
const editingMedia = ref(null);
const viewingMedia = ref(null);

const fetchMedia = async () => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value?.current_page || 1,
            view: viewMode.value,
        };

        if (selectedFolder.value !== null) {
            params.folder_id = selectedFolder.value;
        }

        if (search.value) {
            params.search = search.value;
        }

        if (mimeFilter.value) {
            params.mime_type = mimeFilter.value;
        }

        if (usageFilter.value) {
            params.usage = usageFilter.value;
        }

        const response = await api.get('/admin/cms/media', { params });
        mediaList.value = response.data.data || [];
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
        };
    } catch (error) {
        console.error('Failed to fetch media:', error);
    } finally {
        loading.value = false;
    }
};

const fetchFolders = async () => {
    try {
        const response = await api.get('/admin/cms/media-folders');
        folders.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch folders:', error);
    }
};

const changePage = (page) => {
    if (pagination.value) {
        pagination.value.current_page = page;
        fetchMedia();
    }
};

const isMediaSelected = (mediaId) => {
    return selectedMedia.value.includes(mediaId);
};

const toggleMediaSelection = (media) => {
    const index = selectedMedia.value.indexOf(media.id);
    if (index > -1) {
        selectedMedia.value.splice(index, 1);
    } else {
        selectedMedia.value.push(media.id);
    }
};

const allMediaSelected = computed(() => {
    return mediaList.value.length > 0 && selectedMedia.value.length === mediaList.value.length;
});

const toggleSelectAll = () => {
    if (allMediaSelected.value) {
        selectedMedia.value = [];
    } else {
        selectedMedia.value = mediaList.value.map(m => m.id);
    }
};

const handleBulkAction = async () => {
    if (!bulkAction.value || selectedMedia.value.length === 0) {
        bulkAction.value = '';
        return;
    }

    const action = bulkAction.value;
    const count = selectedMedia.value.length;
    
    if (action === 'delete') {
        if (!confirm(`Are you sure you want to delete ${count} media file(s)?`)) {
            bulkAction.value = '';
            return;
        }
        
        try {
            await api.post('/admin/cms/media/bulk-action', {
                action: 'delete',
                ids: selectedMedia.value,
            });
            await fetchMedia();
            selectedMedia.value = [];
            bulkAction.value = '';
        } catch (error) {
            console.error('Failed to delete media:', error);
            alert(error.response?.data?.message || 'Failed to delete media');
            bulkAction.value = '';
        }
    } else if (action === 'move') {
        showMoveFolderModal.value = true;
    }
};

const handleMoveToFolder = async (folderId) => {
    try {
        await api.post('/admin/cms/media/bulk-action', {
            action: 'move',
            ids: selectedMedia.value,
            folder_id: folderId,
        });
        await fetchMedia();
        selectedMedia.value = [];
        bulkAction.value = '';
        showMoveFolderModal.value = false;
    } catch (error) {
        console.error('Failed to move media:', error);
        alert(error.response?.data?.message || 'Failed to move media');
    }
};

const viewMedia = (media) => {
    viewingMedia.value = media;
    showViewModal.value = true;
};

const editMedia = (media) => {
    editingMedia.value = media;
    showEditModal.value = true;
};

const deleteMedia = async (media) => {
    if (!confirm(`Are you sure you want to delete "${media.name}"?`)) {
        return;
    }

    try {
        await api.delete(`/admin/cms/media/${media.id}`);
        await fetchMedia();
    } catch (error) {
        console.error('Failed to delete media:', error);
        alert('Failed to delete media');
    }
};

const handleMediaUploaded = () => {
    fetchMedia();
    showUploadModal.value = false;
};

const handleMediaUpdated = () => {
    fetchMedia();
    showEditModal.value = false;
    editingMedia.value = null;
    if (viewingMedia.value) {
        // Refresh viewing media if modal is open
        const mediaId = viewingMedia.value.id;
        fetchMedia().then(() => {
            const updatedMedia = mediaList.value.find(m => m.id === mediaId);
            if (updatedMedia) {
                viewingMedia.value = updatedMedia;
            }
        });
    }
};

const handleFolderCreated = () => {
    fetchFolders();
    showFolderModal.value = false;
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const handleImageError = (event) => {
    // If thumbnail fails to load, fallback to original URL
    const img = event.target;
    if (img.src !== img.dataset.originalUrl) {
        img.src = img.dataset.originalUrl || img.src;
    }
};

// Watch for changes
watch([selectedFolder, search, mimeFilter, usageFilter, viewMode], () => {
    if (pagination.value) {
        pagination.value.current_page = 1;
    }
    fetchMedia();
});

onMounted(() => {
    fetchMedia();
    fetchFolders();
});
</script>
