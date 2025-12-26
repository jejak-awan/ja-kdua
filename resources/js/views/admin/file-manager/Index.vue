<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.file_manager.title') }}</h1>
            <div class="flex items-center space-x-2">
                <Button
                    variant="outline"
                    @click="showCreateFolderModal = true"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                    {{ $t('features.file_manager.actions.newFolder') }}
                </Button>
                <Button
                    @click="showUploadModal = true"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    {{ $t('features.file_manager.actions.upload') }}
                </Button>
                <Button
                    variant="ghost"
                    size="icon"
                    @click="showHelp = !showHelp"
                    :class="showHelp ? 'text-primary' : 'text-muted-foreground'"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.443.1.201.201.3.411.332.617L13 19h-2v-4a1 1 0 011-1 1 1 0 100-2 1.637 1.637 0 011-1.637C13 10.328 11.66 9.1 10.114 9.1c-1.164 0-2.146.685-2.5 1.5l1.614 1.068C9.328 11.535 9.1 11.1 9c1.068-1.5 2.5-2.1 2.5-2.1z" />
                        <circle cx="12" cy="19" r="1.5" fill="currentColor" />
                    </svg>
                </Button>
            </div>
        </div>

        <!-- Help Modal -->
        <div
            v-if="showHelp"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            @click="showHelp = false"
        >
            <div class="bg-card rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="sticky top-0 bg-card border-b border-border p-6 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-foreground">{{ $t('features.file_manager.help.title') }}</h2>
                    <button
                        @click="showHelp = false"
                        class="text-muted-foreground hover:text-foreground"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Navigation -->
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-foreground mb-2">{{ $t('features.file_manager.help.sections.navigation.title') }}</h3>
                            <p class="text-sm text-muted-foreground">{{ $t('features.file_manager.help.sections.navigation.content') }}</p>
                        </div>
                    </div>

                    <!-- Upload -->
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-foreground mb-2">{{ $t('features.file_manager.help.sections.upload.title') }}</h3>
                            <p class="text-sm text-muted-foreground">{{ $t('features.file_manager.help.sections.upload.content') }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-foreground mb-2">{{ $t('features.file_manager.help.sections.actions.title') }}</h3>
                            <p class="text-sm text-muted-foreground">{{ $t('features.file_manager.help.sections.actions.content') }}</p>
                        </div>
                    </div>

                    <!-- Bulk Operations -->
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-foreground mb-2">{{ $t('features.file_manager.help.sections.bulk.title') }}</h3>
                            <p class="text-sm text-muted-foreground">{{ $t('features.file_manager.help.sections.bulk.content') }}</p>
                        </div>
                    </div>

                    <!-- Search & Filter -->
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-foreground mb-2">{{ $t('features.file_manager.help.sections.search.title') }}</h3>
                            <p class="text-sm text-muted-foreground">{{ $t('features.file_manager.help.sections.search.content') }}</p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-border p-6">
                    <button
                        @click="showHelp = false"
                        class="w-full px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90"
                    >
                        {{ $t('features.file_manager.actions.close') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Search, Filter, Sort Toolbar -->
        <div class="mb-4 flex flex-wrap gap-3 items-center">
            <!-- Search -->
            <div class="flex-1 max-w-md">
                <Input
                    v-model="searchQuery"
                    type="text"
                    :placeholder="$t('features.file_manager.actions.search')"
                />
            </div>
            <div class="flex items-center space-x-3">
                <Select v-model="filterType">
                    <SelectTrigger class="w-[140px]">
                        <SelectValue :placeholder="$t('features.file_manager.filter.all')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ $t('features.file_manager.filter.all') }}</SelectItem>
                        <SelectItem value="image">{{ $t('features.file_manager.filter.images') }}</SelectItem>
                        <SelectItem value="video">{{ $t('features.file_manager.filter.videos') }}</SelectItem>
                        <SelectItem value="pdf">{{ $t('features.file_manager.filter.documents') }}</SelectItem>
                        <SelectItem value="other">{{ $t('features.file_manager.filter.other') }}</SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="sortBy">
                    <SelectTrigger class="w-[140px]">
                        <SelectValue :placeholder="$t('features.file_manager.sort.name')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="name">{{ $t('features.file_manager.sort.name') }}</SelectItem>
                        <SelectItem value="size">{{ $t('features.file_manager.sort.size') }}</SelectItem>
                        <SelectItem value="date">{{ $t('features.file_manager.sort.date') }}</SelectItem>
                    </SelectContent>
                </Select>
                <div class="flex p-1 border border-input rounded-md bg-card">
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="viewMode = 'grid'"
                        :class="[
                            'w-8 h-8 p-0',
                            viewMode === 'grid' ? 'bg-primary text-primary-foreground hover:bg-primary' : 'text-muted-foreground'
                        ]"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="viewMode = 'list'"
                        :class="[
                            'w-8 h-8 p-0',
                            viewMode === 'list' ? 'bg-primary text-primary-foreground hover:bg-primary' : 'text-muted-foreground'
                        ]"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </Button>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Toolbar -->
        <div v-if="selectedItems.length > 0" class="mb-4 bg-primary/10 border border-primary/20 rounded-lg p-4 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <span class="text-sm font-medium text-muted-foreground">{{ $t('features.file_manager.bulk.label', { count: selectedItems.length }) }}</span>
                <div class="flex items-center space-x-2">
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="bulkDelete"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        {{ $t('features.file_manager.bulk.delete') }}
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="clearSelection"
                        class="text-muted-foreground"
                    >
                        {{ $t('features.file_manager.bulk.cancel') }}
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="selectAllInPage"
                    >
                        {{ $t('features.file_manager.bulk.selectAll') }}
                    </Button>
                </div>
            </div>
        </div>

        <div class="flex gap-6">
            <!-- Sidebar: Folders -->
            <div class="w-64 bg-card border border-border rounded-lg p-4">
                <h2 class="text-sm font-semibold text-foreground mb-3">{{ $t('features.file_manager.labels.folders') }}</h2>
                <div class="space-y-1">
                    <button
                        @click="currentPath = '/'"
                        :class="[
                            'w-full text-left px-3 py-2 rounded-md text-sm',
                            currentPath === '/' ? 'bg-primary/10 text-primary' : 'text-foreground hover:bg-muted'
                        ]"
                    >
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        {{ $t('features.file_manager.nav.root') }}
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
                                currentPath === folder.path ? 'bg-primary/10 text-primary' : 'text-foreground hover:bg-muted'
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
                <div class="bg-card border border-border rounded-lg p-4 mb-4">
                    <div class="flex items-center space-x-2">
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="currentPath = '/'"
                            class="p-2 h-8 w-8"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </Button>
                        <div v-for="(part, index) in breadcrumbs" :key="index" class="flex items-center">
                            <span class="mx-1 text-muted-foreground">/</span>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="currentPath = part.path"
                                class="px-2 py-1 h-8"
                            >
                                {{ part.name }}
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Files & Folders -->
                <div class="bg-card border border-border rounded-lg">
                    <div v-if="loading" class="p-12 text-center">
                        <p class="text-muted-foreground">{{ $t('features.file_manager.messages.loading') }}</p>
                    </div>
                    <div v-else-if="filesInCurrentPath.length === 0 && foldersInCurrentPath.length === 0" class="p-12 text-center">
                        <p class="text-muted-foreground">{{ $t('features.file_manager.messages.noFiles') }}</p>
                    </div>
                    <div v-else class="grid grid-cols-4 gap-4 p-4">
                        <!-- Folders -->
                        <div
                            v-for="folder in paginatedFolders"
                            :key="folder.path"
                            class="p-4 border border-border rounded-lg hover:bg-muted cursor-pointer relative group"
                            :class="{ 'ring-2 ring-primary': isSelected(folder.path) }"
                        >
                            <div class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Checkbox
                                    :checked="isSelected(folder.path)"
                                    @update:checked="toggleSelection(folder.path)"
                                />
                            </div>
                            <div class="text-center" @click="navigateToFolder(folder.path)" @contextmenu.prevent="showFolderContextMenu($event, folder)">
                                <svg class="w-12 h-12 mx-auto text-primary/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                <p class="mt-2 text-sm font-medium text-foreground truncate">{{ folder.name }}</p>
                            </div>
                        </div>
                        <!-- Files -->
                        <div
                            v-for="file in paginatedFiles"
                            :key="file.path"
                            class="p-4 border border-border rounded-lg hover:bg-muted cursor-pointer relative group"
                            :class="{ 'ring-2 ring-primary': isSelected(file.path) }"
                        >
                            <div class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Checkbox
                                    :checked="isSelected(file.path)"
                                    @update:checked="toggleSelection(file.path)"
                                />
                            </div>
                            <div class="text-center" @click="viewFile(file)" @contextmenu.prevent="showFileContextMenu($event, file)">
                                <!-- Image Thumbnail -->
                                <div v-if="isImage(file)" class="w-full h-24 flex items-center justify-center overflow-hidden rounded mb-2">
                                    <img :src="file.url" :alt="file.name" class="max-w-full max-h-full object-contain" />
                                </div>
                                <!-- File Icon -->
                                <svg v-else class="w-12 h-12 mx-auto text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm font-medium text-foreground truncate">{{ file.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ formatFileSize(file.size) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">
                        {{ $t('features.file_manager.pagination.showing', { 
                            from: ((currentPage - 1) * itemsPerPage) + 1, 
                            to: Math.min(currentPage * itemsPerPage, totalItems), 
                            total: totalItems 
                        }) }}
                    </div>
                    <div class="flex items-center space-x-2">
                        <Button
                            variant="outline"
                            @click="currentPage--"
                            :disabled="currentPage === 1"
                        >
                            {{ $t('common.pagination.previous') }}
                        </Button>
                        <div class="flex items-center space-x-1">
                            <Button
                                v-for="page in totalPages"
                                :key="page"
                                variant="outline"
                                :class="[
                                    'w-10 h-10 p-0',
                                    currentPage === page ? 'bg-primary text-primary-foreground hover:bg-primary' : 'text-muted-foreground'
                                ]"
                                @click="currentPage = page"
                            >
                                {{ page }}
                            </Button>
                        </div>
                        <Button
                            variant="outline"
                            @click="currentPage++"
                            :disabled="currentPage === totalPages"
                        >
                            {{ $t('common.pagination.next') }}
                        </Button>
                    </div>
                    <select
                        v-model="itemsPerPage"
                        class="px-3 py-2 border border-input rounded-md bg-card text-foreground"
                    >
                        <option :value="12">12 {{ $t('features.file_manager.pagination.perPage') }}</option>
                        <option :value="24">24 {{ $t('features.file_manager.pagination.perPage') }}</option>
                        <option :value="48">48 {{ $t('features.file_manager.pagination.perPage') }}</option>
                        <option :value="96">96 {{ $t('features.file_manager.pagination.perPage') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Image Preview Modal -->
        <div
            v-if="showImagePreview && previewImage"
            class="fixed inset-0 bg-background/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            @click="closeImagePreview"
        >
            <div class="relative max-w-7xl max-h-full" @click.stop>
                <button
                    @click="closeImagePreview"
                    class="absolute top-4 right-4 bg-background/50 text-foreground rounded-full p-2 hover:bg-background/80"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img
                    :src="previewImage.url"
                    :alt="previewImage.name"
                    class="max-w-full max-h-[90vh] object-contain rounded-lg"
                />
                <div class="mt-4 text-center text-foreground">
                    <p class="text-lg font-medium">{{ previewImage.name }}</p>
                    <p class="text-sm text-muted-foreground">{{ formatFileSize(previewImage.size) }}</p>
                </div>
            </div>
        </div>

        <!-- Context Menu -->
        <div
            v-if="contextMenu.show"
            class="fixed bg-card border border-border rounded-lg shadow-lg py-1 z-50"
            :style="{ top: contextMenu.y + 'px', left: contextMenu.x + 'px' }"
            @click.stop
        >
            <Button
                variant="ghost"
                class="w-full justify-start text-sm h-9 px-3"
                @click="handleContextMenuAction('rename')"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                {{ $t('features.file_manager.actions.rename') }}
            </Button>
            <Button
                v-if="contextMenu.type === 'file'"
                variant="ghost"
                class="w-full justify-start text-sm h-9 px-3"
                @click="handleContextMenuAction('download')"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4-4m0 0L8 8m4-4v12" />
                </svg>
                {{ $t('features.file_manager.actions.download') }}
            </Button>
            <Button
                variant="ghost"
                class="w-full justify-start text-sm h-9 px-3 text-destructive hover:text-destructive hover:bg-destructive/10"
                @click="handleContextMenuAction('delete')"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                {{ $t('features.file_manager.actions.delete') }}
            </Button>
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
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseSingleResponse } from '../../../utils/responseParser';

const { t } = useI18n();
import FileUploadModal from '../../../components/file-manager/FileUploadModal.vue';
import CreateFolderModal from '../../../components/file-manager/CreateFolderModal.vue';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Select from '../../../components/ui/select.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';
import Checkbox from '../../../components/ui/checkbox.vue';

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
const contextMenu = ref({
    show: false,
    x: 0,
    y: 0,
    item: null,
    type: null, // 'file' or 'folder'
});

// Search, Filter, Sort
const searchQuery = ref('');
const filterType = ref('all');
const sortBy = ref('name');
const sortDirection = ref('asc');

// Bulk Actions
const selectedItems = ref([]);

// Pagination
const currentPage = ref(1);
const itemsPerPage = ref(24);

// Help
const showHelp = ref(localStorage.getItem('fileManagerShowHelp') === 'true');

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

