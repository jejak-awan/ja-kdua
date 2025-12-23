<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.media.title') }}</h1>
            <div class="flex items-center space-x-3">
                <!-- View Toggle -->
                <div class="flex border border-input bg-card text-foreground rounded-md">
                    <button
                        @click="viewMode = 'grid'"
                        :class="[
                            'px-3 py-2',
                            viewMode === 'grid' ? 'bg-indigo-600 text-white' : 'text-foreground hover:bg-muted'
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
                            viewMode === 'list' ? 'bg-indigo-600 text-white' : 'text-foreground hover:bg-muted'
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
                    {{ $t('features.media.upload') }}
                </button>
            </div>
        </div>

        <div class="flex gap-6">
            <!-- Sidebar: Folders -->
            <div class="w-64 bg-card shadow rounded-lg p-4">
                <h2 class="text-sm font-semibold text-foreground mb-3">{{ $t('features.media.folders') }}</h2>
                <div class="space-y-1">
                    <button
                        @click="selectedFolder = null"
                        :class="[
                            'w-full text-left px-3 py-2 rounded-md text-sm',
                            selectedFolder === null ? 'bg-indigo-100 text-indigo-700' : 'text-foreground hover:bg-muted'
                        ]"
                    >
                        {{ $t('features.media.allMedia') }}
                    </button>
                    <div v-for="folder in folders" :key="folder.id" class="pl-4">
                        <button
                            @click="selectedFolder = folder.id"
                            :class="[
                                'w-full text-left px-3 py-2 rounded-md text-sm',
                                selectedFolder === folder.id ? 'bg-indigo-100 text-indigo-700' : 'text-foreground hover:bg-muted'
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
                    + {{ $t('features.media.newFolder') }}
                </button>
            </div>

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Filters -->
                <div class="bg-card shadow rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4 flex-1">
                            <input
                                v-model="search"
                                type="text"
                                :placeholder="$t('features.media.search')"
                                class="flex-1 px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <select
                                v-model="mimeFilter"
                                class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            >
                                <option value="">{{ $t('features.media.filter.allTypes') }}</option>
                                <option value="image">{{ $t('features.media.filter.images') }}</option>
                                <option value="video">{{ $t('features.media.filter.videos') }}</option>
                                <option value="application">{{ $t('features.media.filter.documents') }}</option>
                            </select>
                            <select
                                v-model="usageFilter"
                                class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            >
                                <option value="">{{ $t('features.media.filter.allStatus') }}</option>
                                <option value="used">{{ $t('features.media.filter.used') }}</option>
                                <option value="unused">{{ $t('features.media.filter.unused') }}</option>
                            </select>
                        </div>
                        <div v-if="selectedMedia.length > 0" class="flex items-center space-x-2 ml-4">
                            <span class="text-sm text-foreground">{{ t('features.media.selected', { count: selectedMedia.length }) }}</span>
                            <div class="relative">
                                <select
                                    v-model="bulkAction"
                                    @change="handleBulkAction"
                                    class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">{{ $t('features.media.actions.bulk') }}</option>
                                    <option value="delete">{{ $t('features.media.actions.delete') }}</option>
                                    <option value="move">{{ $t('features.media.actions.move') }}</option>
                                    <option value="update_alt">{{ $t('features.media.actions.updateAlt') }}</option>
                                    <option value="download">{{ $t('features.media.actions.downloadZip') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media Grid/List -->
                <div v-if="loading" class="bg-card shadow rounded-lg p-12 text-center">
                    <p class="text-muted-foreground">{{ $t('features.media.loading') }}</p>
                </div>

                <div v-else-if="mediaList.length === 0" class="bg-card shadow rounded-lg p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="mt-4 text-muted-foreground">{{ $t('features.media.empty') }}</p>
                </div>

                <!-- Grid View -->
                <div v-else-if="viewMode === 'grid'" class="grid grid-cols-4 gap-4">
                    <div
                        v-for="media in mediaList"
                        :key="media.id"
                        @click="toggleMediaSelection(media)"
                        class="bg-card shadow rounded-lg overflow-hidden cursor-pointer hover:shadow-lg transition-shadow relative"
                        :class="isMediaSelected(media.id) ? 'ring-2 ring-indigo-500' : ''"
                    >
                        <!-- Checkbox -->
                        <div class="absolute top-2 left-2 z-10">
                            <input
                                type="checkbox"
                                :checked="isMediaSelected(media.id)"
                                @click.stop="toggleMediaSelection(media)"
                                class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                            >
                        </div>
                        <div class="aspect-square bg-secondary flex items-center justify-center relative group" :data-media-id="media.id">
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
                                        class="p-2 bg-card rounded-md text-foreground hover:bg-accent"
                                        :title="$t('features.media.actions.view')"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click.stop="editMedia(media)"
                                        class="p-2 bg-card rounded-md text-foreground hover:bg-accent"
                                        :title="$t('features.media.actions.edit')"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click.stop="deleteMedia(media)"
                                        class="p-2 bg-card rounded-md text-red-600 hover:bg-red-50"
                                        :title="$t('features.media.actions.delete')"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="p-3">
                            <p class="text-sm font-medium text-foreground truncate">{{ media.name }}</p>
                            <p class="text-xs text-muted-foreground mt-1">{{ formatFileSize(media.size) }}</p>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div v-else class="bg-card shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-muted">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        :checked="allMediaSelected"
                                        @change="toggleSelectAll"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                                    >
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                    {{ $t('features.media.table.media') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                    {{ $t('features.media.table.name') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                    {{ $t('features.media.table.type') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                    {{ $t('features.media.table.size') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                    {{ $t('features.media.table.folder') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                    {{ $t('features.media.table.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-card divide-y divide-border">
                            <tr v-for="media in mediaList" :key="media.id" class="hover:bg-muted">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input
                                        type="checkbox"
                                        :value="media.id"
                                        v-model="selectedMedia"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                                    >
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-16 h-16 bg-secondary rounded flex items-center justify-center" :data-media-id="media.id">
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
                                    <div class="text-sm font-medium text-foreground">{{ media.name }}</div>
                                    <div v-if="media.alt" class="text-sm text-muted-foreground">{{ media.alt }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-secondary text-secondary-foreground">
                                        {{ media.mime_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                    {{ formatFileSize(media.size) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                    {{ media.folder?.name || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button
                                            @click="viewMedia(media)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            {{ $t('features.media.actions.view') }}
                                        </button>
                                        <button
                                            @click="editMedia(media)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            {{ $t('features.media.actions.edit') }}
                                        </button>
                                        <button
                                            @click="deleteMedia(media)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            {{ $t('features.media.actions.delete') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="pagination && pagination.last_page > 1" class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-foreground">
                        {{ $t('common.pagination.showing', { from: pagination.from, to: pagination.to, total: pagination.total }) }}
                    </div>
                    <div class="flex space-x-2">
                        <button
                            @click="changePage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm disabled:opacity-50"
                        >
                            {{ $t('common.pagination.previous') }}
                        </button>
                        <button
                            @click="changePage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm disabled:opacity-50"
                        >
                            {{ $t('common.pagination.next') }}
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

        <div
            v-if="showUpdateAltModal"
            class="fixed inset-0 bg-black/50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
            @click.self="showUpdateAltModal = false"
        >
            <div class="relative w-full max-w-md p-6 border shadow-lg rounded-md bg-card">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-foreground mb-4">{{ t('features.media.modals.updateAlt.title') }}</h3>
                    <p class="text-sm text-muted-foreground mb-4">
                        {{ t('features.media.modals.updateAlt.description', { count: selectedMedia.length }) }}
                    </p>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-foreground mb-2">
                            {{ t('features.media.modals.updateAlt.label') }}
                        </label>
                        <input
                            v-model="bulkAltText"
                            type="text"
                            :placeholder="t('features.media.modals.updateAlt.placeholder')"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="showUpdateAltModal = false; bulkAltText = ''"
                            class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                        >
                            {{ t('features.media.modals.updateAlt.cancel') }}
                        </button>
                        <button
                            @click="handleUpdateAltText"
                            :disabled="bulkProcessing"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ bulkProcessing ? t('features.media.modals.updateAlt.updating') : t('features.media.modals.updateAlt.update') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Processing Progress -->
        <div
            v-if="bulkProcessing"
            class="fixed bottom-4 right-4 bg-card shadow-lg rounded-lg p-4 w-80 z-50"
        >
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-foreground">{{ t('features.media.modals.bulk.processing') }}</span>
                <span class="text-sm text-muted-foreground">{{ bulkProgress }}%</span>
            </div>
            <div class="w-full bg-muted rounded-full h-2">
                <div
                    class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: bulkProgress + '%' }"
                ></div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import MediaUploadModal from '../../../components/media/MediaUploadModal.vue';
import MediaEditModal from '../../../components/media/MediaEditModal.vue';
import MediaViewModal from '../../../components/media/MediaViewModal.vue';
import FolderModal from '../../../components/media/FolderModal.vue';
import MoveToFolderModal from '../../../components/media/MoveToFolderModal.vue';
import LazyImage from '../../../components/LazyImage.vue';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const { t } = useI18n();
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
const bulkAltText = ref('');

const showUploadModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const showFolderModal = ref(false);
const showMoveFolderModal = ref(false);
const showUpdateAltModal = ref(false);
const editingMedia = ref(null);
const viewingMedia = ref(null);
const bulkProcessing = ref(false);
const bulkProgress = ref(0);

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
        const { data, pagination: paginationData } = parseResponse(response);
        mediaList.value = ensureArray(data);
        if (paginationData) {
            pagination.value = paginationData;
        }
    } catch (error) {
        console.error('Failed to fetch media:', error);
    } finally {
        loading.value = false;
    }
};

const fetchFolders = async () => {
    try {
        const response = await api.get('/admin/cms/media-folders');
        const { data } = parseResponse(response);
        folders.value = ensureArray(data);
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
        if (!confirm(t('features.media.messages.deleteConfirm', { count }))) {
            bulkAction.value = '';
            return;
        }
        
        bulkProcessing.value = true;
        bulkProgress.value = 0;
        try {
            await api.post('/admin/cms/media/bulk-action', {
                action: 'delete',
                ids: selectedMedia.value,
            });
            bulkProgress.value = 100;
            await fetchMedia();
            selectedMedia.value = [];
            bulkAction.value = '';
            alert(t('features.media.messages.deleteSuccess', { count }));
        } catch (error) {
            console.error('Failed to delete media:', error);
            alert(error.response?.data?.message || t('features.media.messages.deleteFailed'));
            bulkAction.value = '';
        } finally {
            bulkProcessing.value = false;
            bulkProgress.value = 0;
        }
    } else if (action === 'move') {
        showMoveFolderModal.value = true;
    } else if (action === 'update_alt') {
        showUpdateAltModal.value = true;
    } else if (action === 'download') {
        bulkProcessing.value = true;
        bulkProgress.value = 0;
        try {
            // Download ZIP file
            const response = await api.post(
                '/admin/cms/media/download-zip',
                { ids: selectedMedia.value },
                { responseType: 'blob' }
            );
            
            // Create download link
            const blob = new Blob([response.data], { type: 'application/zip' });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `media-${new Date().toISOString().slice(0, 10)}.zip`);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
            
            bulkProgress.value = 100;
            selectedMedia.value = [];
            bulkAction.value = '';
            alert(t('features.media.messages.downloadSuccess', { count }));
        } catch (error) {
            console.error('Failed to download media:', error);
            alert(error.response?.data?.message || t('features.media.messages.downloadFailed'));
            bulkAction.value = '';
        } finally {
            bulkProcessing.value = false;
            bulkProgress.value = 0;
        }
    }
};

const handleMoveToFolder = async (folderId) => {
    const count = selectedMedia.value.length;
    bulkProcessing.value = true;
    bulkProgress.value = 0;
    try {
        await api.post('/admin/cms/media/bulk-action', {
            action: 'move',
            ids: selectedMedia.value,
            folder_id: folderId,
        });
        bulkProgress.value = 100;
        await fetchMedia();
        selectedMedia.value = [];
        bulkAction.value = '';
        showMoveFolderModal.value = false;
        alert(t('features.media.messages.moveSuccess', { count }));
    } catch (error) {
        console.error('Failed to move media:', error);
        alert(error.response?.data?.message || t('features.media.messages.moveFailed'));
    } finally {
        bulkProcessing.value = false;
        bulkProgress.value = 0;
    }
};

const handleUpdateAltText = async () => {
    if (selectedMedia.value.length === 0) {
        return;
    }

    bulkProcessing.value = true;
    bulkProgress.value = 0;
    try {
        await api.post('/admin/cms/media/bulk-action', {
            action: 'update_alt',
            ids: selectedMedia.value,
            alt_text: bulkAltText.value,
        });
        bulkProgress.value = 100;
        await fetchMedia();
        selectedMedia.value = [];
        bulkAction.value = '';
        bulkAltText.value = '';
        showUpdateAltModal.value = false;
        alert(t('features.media.messages.updateAltSuccess'));
    } catch (error) {
        console.error('Failed to update alt text:', error);
        alert(error.response?.data?.message || t('features.media.messages.updateAltFailed'));
    } finally {
        bulkProcessing.value = false;
        bulkProgress.value = 0;
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
    if (!confirm(t('features.media.messages.deleteSingleConfirm', { name: media.name }))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/media/${media.id}`);
        await fetchMedia();
    } catch (error) {
        console.error('Failed to delete media:', error);
        alert(t('features.media.messages.deleteFailed'));
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
    const currentSrc = img.src || img.getAttribute('src');
    
    // Check if this is a thumbnail URL
    if (currentSrc && currentSrc.includes('_thumb.')) {
        // Try to get original URL from media object
        const mediaId = img.closest('[data-media-id]')?.getAttribute('data-media-id');
        if (mediaId) {
            const media = mediaList.value.find(m => m.id == mediaId);
            if (media && media.url && media.url !== currentSrc) {
                img.src = media.url;
                return;
            }
        }
        
        // Fallback: replace thumbnail path with original
        const originalSrc = currentSrc.replace('_thumb.', '.').replace('/thumbnails/', '/');
        if (originalSrc !== currentSrc) {
            img.src = originalSrc;
            return;
        }
    }
    
    if (img.src !== img.dataset?.originalUrl) {
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
