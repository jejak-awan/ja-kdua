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
                <Button
                    variant="ghost"
                    size="icon"
                    @click="showTrashView = !showTrashView"
                    :class="showTrashView ? 'text-destructive bg-destructive/10' : 'text-muted-foreground hover:text-foreground'"
                    title="Recycle Bin"
                >
                    <Trash2 class="w-5 h-5" />
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
        <div class="bg-card border border-border/40 rounded-xl p-4 mb-4 shadow-none">
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                <!-- Search (Left) -->
                <div class="relative flex-1 max-w-xs">
                    <SearchIcon class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="searchQuery"
                        type="text"
                        :placeholder="$t('features.file_manager.actions.search')"
                        class="pl-8 bg-background"
                    />
                </div>
                
                <!-- Filters, Sort, View Toggle (Right) -->
                <div class="flex items-center gap-2 ml-auto flex-wrap">
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
                        <SelectTrigger class="w-[120px] bg-background">
                            <SelectValue :placeholder="$t('features.file_manager.sort.name')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="name">{{ $t('features.file_manager.sort.name') }}</SelectItem>
                            <SelectItem value="size">{{ $t('features.file_manager.sort.size') }}</SelectItem>
                            <SelectItem value="date">{{ $t('features.file_manager.sort.date') }}</SelectItem>
                        </SelectContent>
                    </Select>

                    <!-- View Toggle -->
                    <div class="flex items-center border border-border/40 rounded-xl bg-background p-1 shadow-none">
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="viewMode = 'grid'"
                            :class="[
                                'h-8 w-8 p-0 rounded-lg transition-colors',
                                viewMode === 'grid' ? 'bg-secondary text-secondary-foreground shadow-none' : 'text-muted-foreground hover:bg-muted'
                            ]"
                        >
                            <LayoutGrid class="w-4 h-4" stroke-width="1.5" />
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="viewMode = 'list'"
                            :class="[
                                'h-8 w-8 p-0 rounded-lg transition-colors',
                                viewMode === 'list' ? 'bg-secondary text-secondary-foreground shadow-none' : 'text-muted-foreground hover:bg-muted'
                            ]"
                        >
                            <List class="w-4 h-4" stroke-width="1.5" />
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

        <!-- Trash View -->
        <div v-if="showTrashView" class="bg-card border border-border rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <Trash2 class="w-6 h-6 text-destructive" />
                    <h2 class="text-xl font-semibold">Recycle Bin</h2>
                    <span class="text-sm text-muted-foreground">({{ trashItems.length }} items)</span>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="fetchTrash"
                        :disabled="trashLoading"
                    >
                        <RotateCcw class="w-4 h-4 mr-1" :class="{ 'animate-spin': trashLoading }" />
                        Refresh
                    </Button>
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="emptyTrash"
                        :disabled="trashItems.length === 0"
                    >
                        Empty Trash
                    </Button>
                </div>
            </div>
            
            <div v-if="trashLoading" class="flex items-center justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
            
            <div v-else-if="trashItems.length === 0" class="text-center py-12 text-muted-foreground">
                <Trash2 class="w-12 h-12 mx-auto mb-4 opacity-50" />
                <p class="text-lg font-medium">Recycle Bin is empty</p>
                <p class="text-sm">Deleted files and folders will appear here</p>
            </div>
            
            <div v-else class="space-y-2">
                <div 
                    v-for="item in trashItems" 
                    :key="item.id"
                    class="flex items-center justify-between p-4 bg-background rounded-lg border border-border hover:border-primary/30 transition-colors"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded flex items-center justify-center bg-muted">
                            <Folder v-if="item.type === 'folder'" class="w-5 h-5 text-primary" />
                            <FileText v-else class="w-5 h-5 text-muted-foreground" />
                        </div>
                        <div>
                            <p class="font-medium">{{ item.name }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ item.original_path }} • 
                                {{ item.formatted_size || '-' }} • 
                                Deleted {{ new Date(item.deleted_at).toLocaleDateString() }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="restoreItem(item)"
                            title="Restore"
                        >
                            <RotateCcw class="w-4 h-4 mr-1" />
                            Restore
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="deleteFromTrash(item)"
                            title="Delete Permanently"
                        >
                            <Trash2 class="w-4 h-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="flex gap-6">
            <!-- Sidebar: Folders (Collapsible) -->
            <div 
                :class="[
                    'bg-card border border-border rounded-lg h-fit',
                    isReady ? 'transition-[width,padding] duration-300 ease-in-out' : '',
                    sidebarCollapsed ? 'w-12 p-2' : 'w-64 p-4'
                ]"
            >
                <div class="flex items-center justify-between mb-3">
                    <h2 v-if="!sidebarCollapsed" class="text-sm font-semibold text-foreground flex items-center">
                        <Folder class="w-4 h-4 mr-2" />
                        {{ $t('features.file_manager.labels.folders') }}
                    </h2>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="toggleSidebar"
                        class="h-8 w-8 text-muted-foreground hover:text-foreground"
                        :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
                    >
                        <PanelLeft v-if="sidebarCollapsed" class="w-4 h-4" />
                        <PanelLeftClose v-else class="w-4 h-4" />
                    </Button>
                </div>
                <div v-if="!sidebarCollapsed" class="space-y-0.5 max-h-[calc(100vh-300px)] overflow-y-auto pr-2">
                    <!-- Root folder -->
                    <div 
                        @dragover.prevent="(e) => onDragOver(e, { path: '/' })"
                        @dragleave="onDragLeave"
                        @drop="(e) => onDrop(e, { path: '/' })"
                    >
                        <button
                            @click="navigateToPath('/')"
                            :class="[
                                'w-full flex items-center gap-1 text-sm h-8 px-2 rounded-md transition-colors',
                                currentPath === '/' ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-accent',
                                dropTarget === '/' ? 'bg-green-500/20 text-green-500 ring-1 ring-green-500' : ''
                            ]"
                        >
                            <ChevronDown 
                                v-if="folderTree.length > 0" 
                                class="w-4 h-4 flex-shrink-0" 
                            />
                            <span v-else class="w-4"></span>
                            <FolderOpen v-if="currentPath === '/'" class="w-4 h-4 flex-shrink-0 text-primary" />
                            <Folder v-else class="w-4 h-4 flex-shrink-0" />
                            <span class="truncate font-medium">{{ $t('features.file_manager.nav.root') }}</span>
                        </button>
                    </div>
                    
                    <!-- Tree level 1 -->
                    <template v-for="folder in folderTree" :key="folder.path">
                        <div 
                            class="ml-2"
                            @dragover.prevent="(e) => onDragOver(e, folder)"
                            @dragleave="onDragLeave"
                            @drop="(e) => onDrop(e, folder)"
                        >
                            <button
                                @click="navigateToFolder(folder.path)"
                                @contextmenu.prevent="(e) => showContextMenu(e, folder, 'folder')"
                                :class="[
                                    'w-full flex items-center gap-1 text-sm h-8 px-2 rounded-md transition-colors',
                                    currentPath === folder.path ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-accent',
                                    dropTarget === folder.path ? 'bg-green-500/20 text-green-500 ring-1 ring-green-500' : ''
                                ]"
                                :title="folder.name"
                            >
                                <ChevronDown 
                                    v-if="folder.children?.length > 0 && isFolderExpanded(folder.path)" 
                                    class="w-4 h-4 flex-shrink-0 cursor-pointer" 
                                    @click.stop="toggleFolderExpanded(folder.path)"
                                />
                                <ChevronRight 
                                    v-else-if="folder.children?.length > 0" 
                                    class="w-4 h-4 flex-shrink-0 cursor-pointer" 
                                    @click.stop="toggleFolderExpanded(folder.path)"
                                />
                                <span v-else class="w-4"></span>
                                <FolderOpen v-if="currentPath === folder.path || currentPath.startsWith(folder.path + '/')" class="w-4 h-4 flex-shrink-0 text-primary" />
                                <Folder v-else class="w-4 h-4 flex-shrink-0" />
                                <span class="truncate">{{ folder.name }}</span>
                            </button>
                        </div>
                        
                        <!-- Tree level 2 (children) -->
                        <template v-if="folder.children?.length > 0 && isFolderExpanded(folder.path)">
                            <template v-for="child in folder.children" :key="child.path">
                                <div 
                                    class="ml-6"
                                    @dragover.prevent="(e) => onDragOver(e, child)"
                                    @dragleave="onDragLeave"
                                    @drop="(e) => onDrop(e, child)"
                                >
                                    <button
                                        @click="navigateToFolder(child.path)"
                                        @contextmenu.prevent="(e) => showContextMenu(e, child, 'folder')"
                                        :class="[
                                            'w-full flex items-center gap-1 text-sm h-8 px-2 rounded-md transition-colors',
                                            currentPath === child.path ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-accent',
                                            dropTarget === child.path ? 'bg-green-500/20 text-green-500 ring-1 ring-green-500' : ''
                                        ]"
                                        :title="child.name"
                                    >
                                        <ChevronDown 
                                            v-if="child.children?.length > 0 && isFolderExpanded(child.path)" 
                                            class="w-4 h-4 flex-shrink-0 cursor-pointer" 
                                            @click.stop="toggleFolderExpanded(child.path)"
                                        />
                                        <ChevronRight 
                                            v-else-if="child.children?.length > 0" 
                                            class="w-4 h-4 flex-shrink-0 cursor-pointer" 
                                            @click.stop="toggleFolderExpanded(child.path)"
                                        />
                                        <span v-else class="w-4"></span>
                                        <FolderOpen v-if="currentPath === child.path || currentPath.startsWith(child.path + '/')" class="w-4 h-4 flex-shrink-0 text-primary" />
                                        <Folder v-else class="w-4 h-4 flex-shrink-0" />
                                        <span class="truncate">{{ child.name }}</span>
                                    </button>
                                </div>
                                
                                <!-- Tree level 3 (grandchildren) -->
                                <template v-if="child.children?.length > 0 && isFolderExpanded(child.path)">
                                    <div 
                                        v-for="grandChild in child.children" 
                                        :key="grandChild.path"
                                        class="ml-10"
                                        @dragover.prevent="(e) => onDragOver(e, grandChild)"
                                        @dragleave="onDragLeave"
                                        @drop="(e) => onDrop(e, grandChild)"
                                    >
                                        <button
                                            @click="navigateToFolder(grandChild.path)"
                                            @contextmenu.prevent="(e) => showContextMenu(e, grandChild, 'folder')"
                                            :class="[
                                                'w-full flex items-center gap-1 text-sm h-8 px-2 rounded-md transition-colors',
                                                currentPath === grandChild.path ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-accent',
                                                dropTarget === grandChild.path ? 'bg-green-500/20 text-green-500 ring-1 ring-green-500' : ''
                                            ]"
                                            :title="grandChild.name"
                                        >
                                            <span class="w-4"></span>
                                            <FolderOpen v-if="currentPath === grandChild.path" class="w-4 h-4 flex-shrink-0 text-primary" />
                                            <Folder v-else class="w-4 h-4 flex-shrink-0" />
                                            <span class="truncate">{{ grandChild.name }}</span>
                                        </button>
                                    </div>
                                </template>
                            </template>
                        </template>
                    </template>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 min-w-0">
                <!-- Breadcrumbs -->
                <div class="flex items-center gap-1 text-sm mb-5 px-1">
                    <Button 
                        variant="ghost" 
                        size="sm" 
                        class="h-6 px-1.5 text-muted-foreground hover:text-foreground"
                        @click="navigateToPath('/')"
                    >
                        <Folder class="w-3.5 h-3.5" stroke-width="1.5" />
                    </Button>
                    <template v-for="(part, index) in pathParts" :key="index">
                        <ChevronRight class="w-3 h-3 text-muted-foreground" />
                        <Button 
                            variant="ghost" 
                            size="sm" 
                            class="h-6 px-1.5 text-muted-foreground hover:text-foreground font-medium"
                            @click="navigateToPath(part.path)"
                            :class="index === pathParts.length - 1 ? 'text-foreground' : 'text-muted-foreground hover:text-foreground'"
                        >
                            {{ part.name }}
                        </Button>
                    </template>
                </div>

                <!-- Content Area -->
                <div class="bg-card border border-border/40 rounded-xl min-h-[400px]">
                    <div v-if="loading" class="flex flex-col items-center justify-center p-12 text-muted-foreground h-full">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-4"></div>
                        <p>{{ $t('features.file_manager.messages.loading') }}</p>
                    </div>

                    <div v-else-if="filteredFolders.length === 0 && filteredFiles.length === 0" class="flex flex-col items-center justify-center p-12 text-muted-foreground h-full" @contextmenu.prevent="showBackgroundContextMenu">
                        <FolderPlus class="w-12 h-12 mb-4 opacity-20" />
                        <p>{{ $t('features.file_manager.messages.noFiles') }}</p>
                    </div>

                    <div v-else>
                        <!-- Grid View -->
                        <div v-if="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 p-4" @contextmenu.prevent="showBackgroundContextMenu">
                            <!-- Folders -->
                            <div
                                v-for="folder in paginatedFolders"
                                :key="folder.path"
                                class="group relative bg-background border border-border/40 rounded-xl overflow-hidden cursor-pointer transition-[border-color,background-color] duration-200 hover:border-primary/50 shadow-none hover:bg-accent/5"
                                :class="{ 
                                    'ring-2 ring-primary border-primary': isSelected(folder.path),
                                    'ring-2 ring-green-500 border-green-500 bg-green-500/10': dropTarget === folder.path
                                }"
                                draggable="true"
                                @click="navigateToFolder(folder.path)"
                                @contextmenu.prevent.stop="(e) => showContextMenu(e, folder, 'folder')"
                                @dragstart="(e) => onDragStart(e, folder, 'folder')"
                                @dragend="onDragEnd"
                                @dragover.prevent="(e) => onDragOver(e, folder)"
                                @dragleave="onDragLeave"
                                @drop="(e) => onDrop(e, folder)"
                            >
                                <div class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity" :class="{ 'opacity-100': isSelected(folder.path) }">
                                    <Checkbox
                                        :checked="isSelected(folder.path)"
                                        @update:checked="(v) => toggleSelection(folder.path)"
                                        @click.stop
                                    />
                                </div>
                                <div class="aspect-square flex flex-col items-center justify-center p-4 bg-muted/10 group-hover:bg-muted/20">
                                    <Folder class="w-12 h-12 text-blue-500 transition-transform group-hover:scale-110" stroke-width="1.5" />
                                </div>
                                <div class="p-3 border-t border-border/40 bg-card">
                                    <p class="text-sm font-medium truncate text-center" :title="folder.name">{{ folder.name }}</p>
                                    <p class="text-xs text-muted-foreground text-center mt-0.5">{{ $t('features.file_manager.labels.folders') }}</p>
                                </div>
                            </div>

                            <!-- Files -->
                            <div
                                v-for="file in paginatedFiles"
                                :key="file.path"
                                class="group relative bg-background border border-border rounded-lg hover:border-primary/50 transition-shadow cursor-pointer overflow-hidden shadow-sm"
                                :class="{ 'ring-2 ring-primary border-primary': isSelected(file.path) }"
                                draggable="true"
                                @click="viewFile(file)"
                                @contextmenu.prevent.stop="(e) => showContextMenu(e, file, 'file')"
                                @dragstart="(e) => onDragStart(e, file, 'file')"
                                @dragend="onDragEnd"
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
                                    <div v-else-if="isVideo(file)" class="relative w-full h-full flex items-center justify-center bg-muted/50">
                                        <Video class="w-12 h-12 text-muted-foreground/50" />
                                        <div class="absolute bottom-1 right-1 bg-black/70 text-white text-[10px] px-1.5 py-0.5 rounded">
                                            {{ file.extension?.toUpperCase() }}
                                        </div>
                                    </div>
                                    <FileText v-else class="w-12 h-12 text-muted-foreground/50" />
                                </div>
                                <div class="p-3 border-t border-border/40 bg-card">
                                    <p class="text-sm font-medium truncate" :title="file.name">{{ file.name }}</p>
                                    <p class="text-xs text-muted-foreground mt-0.5 flex justify-between">
                                        <span>{{ file.extension?.toUpperCase() }}</span>
                                        <span>{{ formatFileSize(file.size) }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- List View -->
                        <div v-else class="min-w-full" @contextmenu.prevent="showBackgroundContextMenu">
                            <table class="w-full text-sm item-center divide-y divide-border/40">
                                <thead class="bg-transparent text-muted-foreground font-medium border-b border-border/40">
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
                                <tbody class="divide-y divide-border/40">
                                    <tr 
                                        v-for="folder in paginatedFolders" 
                                        :key="folder.path"
                                        class="hover:bg-muted/30 cursor-pointer group"
                                        :class="{ 'bg-primary/5': isSelected(folder.path) }"
                                        @click="navigateToFolder(folder.path)"
                                        @contextmenu.prevent.stop="(e) => showContextMenu(e, folder, 'folder')"
                                    >
                                        <td class="px-4 py-3" @click.stop>
                                            <Checkbox
                                                :checked="isSelected(folder.path)"
                                                @update:checked="(v) => toggleSelection(folder.path)"
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <Folder class="w-5 h-5 text-muted-foreground/60" stroke-width="1.5" />
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
                                        @contextmenu.prevent.stop="(e) => showContextMenu(e, file, 'file')"
                                    >
                                        <td class="px-4 py-3" @click.stop>
                                            <Checkbox
                                                :checked="isSelected(file.path)"
                                                @update:checked="(v) => toggleSelection(file.path)"
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <img v-if="isImage(file)" :src="file.url" class="w-8 h-8 rounded object-cover border border-border" />
                                            <Video v-else-if="isVideo(file)" class="w-5 h-5 text-muted-foreground" />
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

        <!-- Media Preview Modal -->
        <Dialog v-model:open="showImagePreview">
            <DialogContent class="max-w-4xl p-0 overflow-hidden bg-black/95 border-none">
                <div class="relative flex items-center justify-center p-4 min-h-[50vh]">
                    <!-- Image Preview -->
                    <img
                        v-if="previewImage && isImage(previewImage)"
                        :src="previewImage.url"
                        :alt="previewImage.name"
                        class="max-w-full max-h-[80vh] object-contain rounded-sm"
                    />
                    <!-- Video Preview -->
                    <video
                        v-else-if="previewImage && isVideo(previewImage)"
                        :src="previewImage.url"
                        controls
                        class="max-w-full max-h-[80vh] rounded-sm"
                        preload="metadata"
                    >
                        Your browser does not support the video tag.
                    </video>
                    <!-- Other File Types -->
                    <div v-else class="flex flex-col items-center text-white/70">
                        <FileText class="w-24 h-24 mb-4" />
                        <p class="text-lg">{{ previewImage?.name }}</p>
                    </div>
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

        <!-- Context Menu -->
        <Teleport to="body">
            <div
                v-if="contextMenu.show"
                ref="contextMenuRef"
                class="fixed z-50 min-w-[180px] bg-background border border-border rounded-lg shadow-lg py-1 animate-in fade-in zoom-in-95 max-h-[calc(100vh-20px)] overflow-y-auto"
                :class="{ 'invisible': contextMenu.invisible }"
                :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }"
                @click.stop
            >
                <!-- Trash specific options -->
                <template v-if="showTrashView">
                    <button
                        class="w-full px-3 py-2 text-sm text-left flex items-center gap-2 hover:bg-accent text-foreground"
                        @click="contextMenuAction('restore')"
                    >
                        <RotateCcw class="w-4 h-4" />
                        Restore
                    </button>
                    <div class="h-px bg-border my-1"></div>
                    <button
                        class="w-full px-3 py-2 text-sm text-left flex items-center gap-2 hover:bg-accent text-destructive"
                        @click="contextMenuAction('delete')"
                    >
                        <Trash2 class="w-4 h-4" />
                        Delete Permanently
                    </button>
                </template>

                <template v-else>
                    <!-- Normal options -->
                    <!-- File-specific options -->
                    <template v-if="contextMenu.type === 'file'">
                        <button
                            class="w-full px-3 py-2 text-sm text-left flex items-center gap-2 hover:bg-accent text-foreground"
                            @click="contextMenuAction('open')"
                        >
                            <Eye class="w-4 h-4" />
                            Open / Preview
                        </button>
                        <button
                            class="w-full px-3 py-2 text-sm text-left flex items-center gap-2 hover:bg-accent text-foreground"
                            @click="contextMenuAction('download')"
                        >
                            <Download class="w-4 h-4" />
                            Download
                        </button>
                        <div class="h-px bg-border my-1"></div>
                    </template>
    
                    <!-- Folder-specific options -->
                    <template v-if="contextMenu.type === 'folder'">
                        <button
                            class="w-full px-3 py-2 text-sm text-left flex items-center gap-2 hover:bg-accent text-foreground"
                            @click="contextMenuAction('openFolder')"
                        >
                            <Folder class="w-4 h-4" />
                            Open Folder
                        </button>
                        <div class="h-px bg-border my-1"></div>
                    </template>
    
                    <!-- Common options -->
                    <button
                        class="w-full px-3 py-2 text-sm text-left flex items-center gap-2 hover:bg-accent text-foreground"
                        @click="contextMenuAction('copyPath')"
                    >
                        <Link class="w-4 h-4" />
                        Copy Path
                    </button>
                    <button
                        v-if="contextMenu.type === 'file'"
                        class="w-full px-3 py-2 text-sm text-left flex items-center gap-2 hover:bg-accent text-foreground"
                        @click="contextMenuAction('copyUrl')"
                    >
                        <Copy class="w-4 h-4" />
                        Copy URL
                    </button>
                    
                    <!-- Extract option for archive files -->
                    <button
                        v-if="contextMenu.type === 'file' && isArchive(contextMenu.item)"
                        class="w-full px-3 py-2 text-sm text-left flex items-center gap-2 hover:bg-accent text-foreground"
                        @click="contextMenuAction('extract')"
                    >
                        <PackageOpen class="w-4 h-4" />
                        Extract Here
                    </button>
                    
                    <!-- Compress option for files and folders -->
                    <button
                        v-if="contextMenu.type !== 'background'"
                        class="w-full px-3 py-2 text-sm text-left flex items-center gap-2 hover:bg-accent text-foreground"
                        @click="contextMenuAction('compress')"
                    >
                        <Archive class="w-4 h-4" />
                        Compress to ZIP
                    </button>
                    
                    <div class="h-px bg-border my-1"></div>
    
                    <!-- Copy option -->
                    <button
                        v-if="contextMenu.type !== 'background'"
                        class="w-full px-3 py-2 text-sm text-left flex items-center gap-2 hover:bg-accent text-foreground"
                        @click="contextMenuAction('copy')"
                    >
                        <Clipboard class="w-4 h-4" />
                        Copy
                    </button>
    
                    <!-- Paste option (only for folders or background) -->
                    <button
                        v-if="(contextMenu.type === 'folder' || contextMenu.type === 'background') && clipboardCount > 0"
                        class="w-full px-3 py-2 text-sm text-left flex items-center gap-2 hover:bg-accent text-foreground"
                        @click="contextMenuAction('paste')"
                    >
                        <ClipboardPaste class="w-4 h-4" />
                        Paste ({{ clipboardCount }})
                    </button>
    
                    <div class="h-px bg-border my-1"></div>
                    <button
                        v-if="contextMenu.type !== 'background'"
                        class="w-full px-3 py-2 text-sm text-left flex items-center gap-2 hover:bg-accent text-destructive"
                        @click="contextMenuAction('delete')"
                    >
                        <Trash2 class="w-4 h-4" />
                        Delete
                    </button>
                </template>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { useConfirm } from '../../../composables/useConfirm';
import api from '../../../services/api';
import toast from '../../../services/toast';
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
    File as FileIcon,
    Video,
    PanelLeft,
    PanelLeftClose,
    Copy,
    ExternalLink,
    Pencil,
    ArrowRightLeft,
    Eye,
    Link,
    ChevronRight,
    ChevronDown,
    FolderOpen,
    RotateCcw,
    AlertTriangle,
    Archive,
    PackageOpen,
    Clipboard,
    ClipboardPaste
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const { t } = useI18n();
const { confirm } = useConfirm();

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
const isReady = ref(false);
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

// Trash/Recycle Bin state
const showTrashView = ref(false);
const trashItems = ref([]);
const trashLoading = ref(false);

// Clipboard state (for copy/paste)
const clipboard = ref({
    items: [], // array of { path, type }
    action: 'copy' // 'copy' or 'move'
});
const clipboardCount = computed(() => clipboard.value.items.length);
const sidebarCollapsed = ref(localStorage.getItem('fileManagerSidebarCollapsed') === 'true');
const contextMenu = ref({
    show: false,
    x: 0,
    y: 0,
    item: null,
    type: null // 'file' or 'folder'
});

// Drag & Drop state
const draggedItem = ref(null);
const draggedType = ref(null);
const dropTarget = ref(null);

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

// Folder tree expanded state
const expandedFolders = ref(new Set(['/'])); // Root is expanded by default

// Build hierarchical folder tree from flat list
const folderTree = computed(() => {
    const tree = [];
    const folderMap = new Map();
    
    // First, create a map of all folders
    allFolders.value.forEach(folder => {
        folderMap.set(folder.path, { ...folder, children: [] });
    });
    
    // Then, build the tree structure
    allFolders.value.forEach(folder => {
        const parentPath = folder.path.substring(0, folder.path.lastIndexOf('/')) || '/';
        const node = folderMap.get(folder.path);
        
        if (parentPath === '/') {
            // Top level folder
            tree.push(node);
        } else {
            // Child folder - add to parent's children
            const parent = folderMap.get(parentPath);
            if (parent) {
                parent.children.push(node);
            } else {
                // Parent not in list yet, add to root
                tree.push(node);
            }
        }
    });
    
    // Sort folders alphabetically at each level
    const sortFolders = (folders) => {
        folders.sort((a, b) => a.name.localeCompare(b.name));
        folders.forEach(f => sortFolders(f.children));
    };
    sortFolders(tree);
    
    return tree;
});

// Toggle folder expand/collapse
const toggleFolderExpanded = (path) => {
    if (expandedFolders.value.has(path)) {
        expandedFolders.value.delete(path);
    } else {
        expandedFolders.value.add(path);
    }
    expandedFolders.value = new Set(expandedFolders.value); // Trigger reactivity
};

// Check if folder is expanded
const isFolderExpanded = (path) => expandedFolders.value.has(path);

// Check if file is an archive (zip, tar, tar.gz, tgz)
const isArchive = (file) => {
    if (!file || !file.extension) return false;
    const ext = file.extension.toLowerCase();
    return ['zip', 'tar', 'gz', 'tgz'].includes(ext);
};

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

const isVideo = (file) => {
    const videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'];
    return videoExtensions.includes(file.extension?.toLowerCase());
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
        const response = await api.get('/admin/ja/file-manager', {
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
        const response = await api.get('/admin/ja/file-manager', {
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
    if (isImage(file) || isVideo(file)) {
        // Show image/video preview modal
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

const contextMenuRef = ref(null);

const setContextMenuPosition = async (event, item, type) => {
    event.preventDefault();
    event.stopPropagation();
    
    // Initial position (hidden for measurement)
    contextMenu.value = {
        show: true,
        x: event.clientX,
        y: event.clientY,
        item: item,
        type: type,
        invisible: true // Add this flag
    };

    await nextTick();

    if (contextMenuRef.value) {
        const menuEl = contextMenuRef.value;
        const rect = menuEl.getBoundingClientRect();
        const winWidth = document.documentElement.clientWidth;
        const winHeight = document.documentElement.clientHeight;
        const buffer = 10;

        let x = event.clientX;
        let y = event.clientY;

        // Check right edge
        if (x + rect.width + buffer > winWidth) {
            // Flip to left of cursor
            x -= rect.width;
        }

        // Check bottom edge
        if (y + rect.height + buffer > winHeight) {
            // Flip to above cursor
            y -= rect.height;
        }

        // Ensure not going off top-left
        if (x < buffer) x = buffer;
        if (y < buffer) y = buffer;

        contextMenu.value.x = x;
        contextMenu.value.y = y;
        contextMenu.value.invisible = false;
    }
};

const showFolderContextMenu = (event, folder) => {
    setContextMenuPosition(event, folder, 'folder');
};

const showFileContextMenu = (event, file) => {
    setContextMenuPosition(event, file, 'file');
};

const closeContextMenu = () => {
    contextMenu.value.show = false;
};

// Toggle sidebar collapse state
const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('fileManagerSidebarCollapsed', sidebarCollapsed.value.toString());
};

// Show context menu at position
const showContextMenu = (event, item, type) => {
    setContextMenuPosition(event, item, type);
};

// Handle context menu action
const contextMenuAction = async (action) => {
    const { item, type } = contextMenu.value;
    
    switch (action) {
        case 'open':
            if (type === 'file') {
                viewFile(item);
            }
            break;
        case 'openFolder':
            if (type === 'folder') {
                navigateToFolder(item.path);
            }
            break;
        case 'download':
            if (type === 'file' && item.url) {
                const link = document.createElement('a');
                link.href = item.url;
                link.download = item.name;
                link.target = '_blank';
                link.click();
            }
            break;
        case 'copyPath':
            try {
                await navigator.clipboard.writeText(item.path);
                // Could show a toast notification here
            } catch (err) {
                console.error('Failed to copy path:', err);
            }
            break;
        case 'copyUrl':
            if (type === 'file' && item.url) {
                try {
                    await navigator.clipboard.writeText(item.url);
                } catch (err) {
                    console.error('Failed to copy URL:', err);
                }
            }
            break;
        case 'extract':
            if (type === 'file') {
                await extractFile(item);
            }
            break;
        case 'compress':
            await compressItems([item.path]);
            break;
        case 'copy':
            copyToClipboard([item], 'copy');
            break;
        case 'paste':
            // Paste into the target folder
            if (type === 'folder') {
                await pasteFromClipboard(item.path);
            } else if (type === 'background') {
                await pasteFromClipboard(currentPath.value);
            }
            break;
        case 'delete':
            if (showTrashView.value) {
                deleteFromTrash(item);
            } else if (type === 'folder') {
                deleteFolderAction(item);
            } else {
                deleteFileAction(item);
            }
            break;
        case 'restore':
            restoreItem(item);
            break;
    }
    
    closeContextMenu();
};

const showBackgroundContextMenu = (event) => {
    setContextMenuPosition(event, null, 'background');
};

const extractFile = async (file) => {
    loading.value = true;
    try {
        await api.post('/admin/ja/file-manager/extract', {
            path: file.path
        });
        
        // Refresh to show extracted contents
        filesCache.value.clear();
        allFolders.value = [];
        await fetchFiles();
        await fetchCurrentPath();
        
        // Optional: show success message
    } catch (error) {
        console.error('Failed to extract file:', error);
        toast.error('Error', 'Failed to extract archive');
    } finally {
        loading.value = false;
    }
};

const compressItems = async (paths) => {
    loading.value = true;
    try {
        await api.post('/admin/ja/file-manager/compress', {
            paths: paths
        });
        
        // Refresh to show new archive
        filesCache.value.clear();
        await fetchFiles();
        await fetchCurrentPath();
        
    } catch (error) {
        console.error('Failed to compress items:', error);
        toast.error('Error', 'Failed to compress items');
    } finally {
        loading.value = false;
    }
};

// Copy to clipboard
const copyToClipboard = (items, action = 'copy') => {
    clipboard.value = {
        items: items.map(item => ({ path: item.path, type: item.type })),
        action: action
    };
    // Optional: Toast notification
};

// Paste from clipboard to destination
const pasteFromClipboard = async (destinationPath) => {
    if (clipboard.value.items.length === 0) return;

    loading.value = true;
    try {
        const promises = clipboard.value.items.map(async (item) => {
            const endpoint = clipboard.value.action === 'move' ? '/admin/ja/file-manager/move' : '/admin/ja/file-manager/copy';
            
            // For move/copy ops
            return api.post(endpoint, {
                source: item.path,
                destination: destinationPath,
                type: item.type
            });
        });

        await Promise.all(promises);

        // Clear clipboard if move action
        if (clipboard.value.action === 'move') {
            clipboard.value.items = [];
        }

        // Refresh
        filesCache.value.clear();
        allFolders.value = [];
        await fetchFiles();
        await fetchCurrentPath();

    } catch (error) {
        console.error('Failed to paste items:', error);
        toast.error('Error', 'Failed to paste items');
    } finally {
        loading.value = false;
    }
};

// Legacy handler (keeping for compatibility)
const handleContextMenuAction = (action) => {
    contextMenuAction(action);
};

// Drag & Drop handlers
const onDragStart = (event, item, type) => {
    draggedItem.value = item;
    draggedType.value = type;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', JSON.stringify({ path: item.path, type }));
    // Add dragging class
    event.target.classList.add('opacity-50');
};

const onDragEnd = (event) => {
    draggedItem.value = null;
    draggedType.value = null;
    dropTarget.value = null;
    event.target.classList.remove('opacity-50');
};

const onDragOver = (event, folder) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
    dropTarget.value = folder?.path || '/';
};

const onDragLeave = () => {
    dropTarget.value = null;
};

const onDrop = async (event, targetFolder) => {
    event.preventDefault();
    dropTarget.value = null;
    
    if (!draggedItem.value) return;
    
    const source = draggedItem.value;
    const sourceType = draggedType.value;
    const destinationPath = targetFolder?.path || '/';
    
    // Don't drop on itself or its children
    if (source.path === destinationPath || destinationPath.startsWith(source.path + '/')) {
        return;
    }
    
    // Don't move to the same parent folder
    const sourceParent = source.path.substring(0, source.path.lastIndexOf('/')) || '/';
    if (sourceParent === destinationPath) {
        return;
    }
    
    try {
        await moveItem(source.path, destinationPath, sourceType);
    } catch (error) {
        console.error('Failed to move item:', error);
        toast.error('Error', t('features.file_manager.messages.moveFailed') || 'Failed to move item');
    }
    
    draggedItem.value = null;
    draggedType.value = null;
};

const moveItem = async (sourcePath, destinationPath, type) => {
    try {
        await api.post('/admin/ja/file-manager/move', {
            source: sourcePath.replace(/^\//, ''),
            destination: destinationPath === '/' ? '' : destinationPath.replace(/^\//, ''),
            type: type
        });
        
        // Clear cache to force fresh data
        filesCache.value.clear();
        allFolders.value = [];
        
        // Refresh the file list and current view
        await fetchFiles();
        await fetchCurrentPath();
        
    } catch (error) {
        throw error;
    }
};

const deleteFolderAction = async (folder) => {
    const confirmed = await confirm({
        title: t('features.file_manager.actions.delete_folder'),
        message: t('features.file_manager.messages.deleteFolderConfirm', { name: folder.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;
    
    try {
        await api.delete('/admin/ja/file-manager/folder', {
            params: { path: folder.path.replace(/^\//, '') },
        });
        
        // Remove from allFolders cache
        allFolders.value = allFolders.value.filter(f => f.path !== folder.path);
        folders.value = allFolders.value;
        
        await fetchFiles();
    } catch (error) {
        console.error('Failed to delete folder:', error);
        toast.error('Error', t('features.file_manager.messages.deleteFolderFailed'));
    }
};

const deleteFileAction = async (file) => {
    const confirmed = await confirm({
        title: t('features.file_manager.actions.delete_file'),
        message: t('features.file_manager.messages.deleteFileConfirm', { name: file.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;
    
    try {
        await api.delete('/admin/ja/file-manager', {
            params: { path: file.path.replace(/^\//, '') },
        });
        await fetchFiles();
    } catch (error) {
        console.error('Failed to delete file:', error);
        toast.error('Error', t('features.file_manager.messages.deleteFileFailed'));
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
    
    const confirmed = await confirm({
        title: t('features.file_manager.bulk.delete'),
        message: t('features.file_manager.bulk.confirmDelete', count, { count }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
        confirmTextClass: 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
    });

    if (!confirmed) return;
    
    try {
        // Delete each item
        for (const path of selectedItems.value) {
            // Check if it's a folder or file
            const isFolder = allFolders.value.find(f => f.path === path);
            
            if (isFolder) {
                await api.delete('/admin/ja/file-manager/folder', {
                    params: { path: path.replace(/^\//, '') },
                });
                // Remove from cache
                allFolders.value = allFolders.value.filter(f => f.path !== path);
            } else {
                await api.delete('/admin/ja/file-manager', {
                    params: { path: path.replace(/^\//, '') },
                });
            }
        }
        
        clearSelection();
        folders.value = allFolders.value;
        await fetchCurrentPath();
    } catch (error) {
        console.error('Failed to bulk delete:', error);
        toast.error('Error', 'Failed to delete some items');
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

// ==================== TRASH / RECYCLE BIN FUNCTIONS ====================

// Fetch trash items
const fetchTrash = async () => {
    trashLoading.value = true;
    try {
        const response = await api.get('/admin/ja/file-manager/trash');
        const data = parseSingleResponse(response.data);
        trashItems.value = data.items || [];
    } catch (error) {
        console.error('Failed to fetch trash:', error);
        trashItems.value = [];
    } finally {
        trashLoading.value = false;
    }
};

// Restore item from trash
const restoreItem = async (item) => {
    try {
        await api.post('/admin/ja/file-manager/restore', { id: item.id });
        // Refresh trash and files
        await fetchTrash();
        filesCache.value.clear();
        allFolders.value = [];
        await fetchFiles();
        await fetchCurrentPath();
    } catch (error) {
        console.error('Failed to restore item:', error);
        toast.error('Error', 'Failed to restore item');
    }
};

// Empty entire trash
const emptyTrash = async () => {
    const confirmed = await confirm({
        title: t('features.file_manager.trash.empty'),
        message: 'Are you sure you want to permanently delete all items in trash? This action cannot be undone.',
        variant: 'danger',
        confirmText: t('features.file_manager.trash.empty'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/ja/file-manager/trash/empty');
        toast.success(t('features.file_manager.messages.trashEmptied'));
        fetchTrash();
    } catch (error) {
        console.error('Failed to empty trash:', error);
        toast.error('Error', error.response?.data?.message || t('features.file_manager.messages.emptyTrashFailed'));
    }
};

// Permanently delete item from trash
const deleteFromTrash = async (item) => {
    const confirmed = await confirm({
        title: t('features.file_manager.trash.permanent_delete'),
        message: `Are you sure you want to permanently delete "${item.name}"? This action cannot be undone.`,
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        if (item.type === 'folder') {
            await api.delete(`/admin/ja/file-manager/trash/folder/${item.id}`); // This might need POST since we added alias, but delete is standard
            toast.success(t('features.file_manager.messages.folderDeleted'));
        } else {
            await api.post('/admin/ja/file-manager/trash/permanent', { id: item.id }); // Use the POST alias we created
            toast.success(t('features.file_manager.messages.fileDeleted'));
        }
        fetchTrash();
    } catch (error) {
        console.error('Failed to permanently delete item:', error);
        toast.error('Error', error.response?.data?.message || t('features.file_manager.messages.deleteFailed'));
    }
};

// Watch for trash view toggle
watch(showTrashView, (newVal) => {
    if (newVal) {
        fetchTrash();
    }
});

// Close context menu when clicking outside
const handleClickOutside = () => {
    if (contextMenu.value.show) {
        closeContextMenu();
    }
};

onMounted(() => {
    fetchFiles(); // Initial load - recursively fetch all
    document.addEventListener('click', handleClickOutside);
    setTimeout(() => { isReady.value = true; }, 100);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

