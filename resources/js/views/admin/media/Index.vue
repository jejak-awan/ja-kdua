<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ $t('features.media.title') }}</h1>
                <p class="text-muted-foreground">{{ $t('features.media.description') }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <Button 
                    variant="outline"
                    @click="showFolderModal = true"
                >
                    <FolderPlus class="w-4 h-4 mr-2" />
                    {{ $t('features.media.newFolder') }}
                </Button>
                <Button @click="showUploadModal = true">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('features.media.upload') }}
                </Button>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <p class="text-sm font-medium">{{ $t('features.media.stats.total') }}</p>
                        <ImageIcon class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ statistics?.total_count || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ $t('features.media.allMedia') }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <p class="text-sm font-medium">{{ $t('features.media.stats.storage') }}</p>
                        <HardDrive class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ formatFileSize(statistics?.total_size || 0) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ $t('features.media.stats.storage') }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <p class="text-sm font-medium">{{ $t('features.media.stats.images') }}</p>
                        <ImageIcon class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ statistics?.types?.find(t => t.type === 'image')?.count || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ $t('features.media.stats.images') }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <p class="text-sm font-medium">{{ $t('features.media.stats.videos') }}</p>
                        <VideoIcon class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ statistics?.types?.find(t => t.type === 'video')?.count || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ $t('features.media.stats.videos') }}
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="flex gap-6">
            <!-- Sidebar: Folders (Collapsible) -->
            <div
                :class="[
                    'bg-card border border-border rounded-lg h-fit',
                    isReady ? 'transition-all duration-300' : '',
                    sidebarCollapsed ? 'w-12 p-2' : 'w-64 p-4'
                ]"
            >
                <div class="flex items-center justify-between mb-3">
                    <h2 v-if="!sidebarCollapsed" class="text-sm font-semibold text-foreground flex items-center">
                        <Folder class="w-4 h-4 mr-2" />
                        {{ $t('features.media.folders') }}
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
                <div v-if="!sidebarCollapsed" class="flex flex-col h-[calc(100vh-380px)]">
                    <div class="flex-1 overflow-y-auto pr-2 space-y-0.5 custom-scrollbar">
                        <button
                            @click="selectFolder(null)"
                            :class="[
                                'w-full flex items-center gap-2 text-sm h-9 px-3 rounded-md transition-all',
                                (selectedFolder === null && !isTrashMode) ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-accent'
                            ]"
                        >
                            <FolderOpen v-if="selectedFolder === null && !isTrashMode" class="w-4 h-4 flex-shrink-0 text-primary" />
                            <Folder v-else class="w-4 h-4 flex-shrink-0" />
                            <span class="truncate font-medium">{{ $t('features.media.allMedia') }}</span>
                        </button>

                        <!-- Recursive Folder Tree -->
                        <template v-for="folder in treeFolders" :key="folder.id">
                            <div class="flex flex-col">
                                <div 
                                    v-if="!folder.is_trashed"
                                    :class="[
                                        'group w-full flex items-center gap-1 text-sm h-9 px-2 rounded-md transition-all cursor-pointer',
                                        selectedFolder === folder.id ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-accent'
                                    ]"
                                    @click="selectFolder(folder.id)"
                                >
                                    <button 
                                        v-if="folder.children?.length > 0"
                                        @click.stop="toggleFolder(folder.id)"
                                        class="h-6 w-6 flex items-center justify-center rounded-sm hover:bg-accent/50 text-muted-foreground/50 hover:text-foreground transition-colors"
                                    >
                                        <ChevronDown v-if="expandedFolders.has(folder.id)" class="w-3 h-3" />
                                        <ChevronRight v-else class="w-3 h-3" />
                                    </button>
                                    <div v-else class="w-6"></div>
                                    
                                    <FolderOpen v-if="selectedFolder === folder.id" class="w-4 h-4 flex-shrink-0 text-primary -ml-1" />
                                    <Folder v-else class="w-4 h-4 flex-shrink-0 -ml-1" />
                                    <span class="truncate ml-1 flex-1" :class="{ 'line-through text-muted-foreground/50': folder.is_trashed }">{{ folder.name }}</span>
                                    
                                    <!-- Folder Actions -->
                                    <div class="opacity-0 group-hover:opacity-100 flex items-center gap-1 z-20 relative ml-auto">
                                        <button 
                                            @click.stop.prevent="deleteFolder(folder)"
                                            class="p-1.5 hover:bg-destructive/10 text-muted-foreground hover:text-destructive rounded-sm transition-colors pointer-events-auto"
                                            :title="$t('features.media.actions.delete')"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Sub folders -->
                                <div v-if="expandedFolders.has(folder.id) && folder.children?.length > 0" class="ml-4 border-l border-border pl-1 mt-0.5 space-y-0.5">
                                    <template v-for="child in folder.children" :key="child.id">
                                        <div 
                                            v-if="!child.is_trashed"
                                            :class="[
                                                'group w-full flex items-center gap-1 text-sm h-9 px-2 rounded-md transition-all cursor-pointer',
                                                selectedFolder === child.id ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-accent'
                                            ]"
                                            @click="selectFolder(child.id)"
                                        >
                                            <button 
                                                v-if="child.children?.length > 0"
                                                @click.stop="toggleFolder(child.id)"
                                                class="h-6 w-6 flex items-center justify-center rounded-sm hover:bg-accent/50 text-muted-foreground/50 hover:text-foreground transition-colors"
                                            >
                                                <ChevronDown v-if="expandedFolders.has(child.id)" class="w-3 h-3" />
                                                <ChevronRight v-else class="w-3 h-3" />
                                            </button>
                                            <div v-else class="w-6"></div>
                                            
                                            <FolderOpen v-if="selectedFolder === child.id" class="w-4 h-4 flex-shrink-0 text-primary -ml-1" />
                                            <Folder v-else class="w-4 h-4 flex-shrink-0 -ml-1" />
                                            <span class="truncate ml-1 flex-1" :class="{ 'line-through text-muted-foreground/50': child.is_trashed }">{{ child.name }}</span>

                                            <!-- Folder Actions -->
                                            <div class="opacity-0 group-hover:opacity-100 flex items-center gap-1 z-20 relative ml-auto">
                                                <button 
                                                    @click.stop.prevent="deleteFolder(child)"
                                                    class="p-1.5 hover:bg-destructive/10 text-muted-foreground hover:text-destructive rounded-sm transition-colors pointer-events-auto"
                                                    :title="$t('features.media.actions.delete')"
                                                >
                                                    <Trash2 class="w-3.5 h-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Level 3 -->
                                        <div v-if="expandedFolders.has(child.id) && child.children?.length > 0" class="ml-4 border-l border-border pl-1 mt-0.5 space-y-0.5">
                                            <div 
                                                v-for="subChild in child.children" 
                                                :key="subChild.id"
                                                v-show="!subChild.is_trashed"
                                                :class="[
                                                    'group w-full flex items-center gap-1 text-sm h-9 px-2 rounded-md transition-all cursor-pointer',
                                                    selectedFolder === subChild.id ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-accent'
                                                ]"
                                                @click="selectFolder(subChild.id)"
                                            >
                                                <div class="w-6"></div>
                                                <FolderOpen v-if="selectedFolder === subChild.id" class="w-4 h-4 flex-shrink-0 text-primary -ml-1" />
                                                <Folder v-else class="w-4 h-4 flex-shrink-0 -ml-1" />
                                                <span class="truncate ml-1 flex-1">{{ subChild.name }}</span>

                                                <!-- Folder Actions -->
                                                <div class="opacity-0 group-hover:opacity-100 flex items-center gap-1 z-20 relative ml-auto">
                                                    <button 
                                                        @click.stop.prevent="deleteFolder(subChild)"
                                                        class="p-1.5 hover:bg-destructive/10 text-muted-foreground hover:text-destructive rounded-sm transition-colors pointer-events-auto"
                                                        :title="$t('features.media.actions.delete')"
                                                    >
                                                        <Trash2 class="w-3.5 h-3.5" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Sidebar Bottom: Trash -->
                    <div class="pt-4 mt-2 border-t border-border">
                        <button
                            @click="toggleTrashMode"
                            :class="[
                                'w-full flex items-center gap-2 text-sm h-9 px-3 rounded-md transition-all',
                                isTrashMode ? 'bg-destructive/10 text-destructive' : 'text-muted-foreground hover:bg-accent'
                            ]"
                        >
                            <Trash2 class="w-4 h-4 flex-shrink-0" :class="isTrashMode ? 'text-destructive' : ''" />
                            <span class="truncate font-medium">{{ $t('features.media.trash') }}</span>
                            <Badge v-if="statistics?.trash_count > 0" variant="secondary" class="ml-auto text-[10px] h-4 px-1 min-w-[16px] justify-center">
                                {{ statistics.trash_count }}
                            </Badge>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Breadcrumbs -->
                <div v-if="selectedFolder !== null || isTrashMode" class="flex items-center gap-1 text-sm mb-4 bg-card border border-border rounded-lg p-2 px-4 shadow-sm">
                    <Button variant="ghost" size="sm" class="h-6 px-1.5 text-muted-foreground hover:text-foreground" @click="selectFolder(null)">
                        <Folder class="w-3.5 h-3.5" />
                    </Button>
                    <span class="text-muted-foreground">/</span>
                    <template v-if="isTrashMode">
                        <span class="text-destructive font-semibold h-6 flex items-center px-1.5">{{ $t('features.media.trash') }}</span>
                    </template>
                    <div v-for="(folder, index) in breadcrumbs" :key="folder.id" class="flex items-center gap-1">
                        <Button 
                            variant="link" 
                            class="h-6 px-1 text-muted-foreground hover:text-foreground font-medium" 
                            @click="selectFolder(folder.id)"
                        >
                            {{ folder.name }}
                        </Button>
                        <span v-if="index < breadcrumbs.length - 1" class="text-muted-foreground">/</span>
                    </div>
                </div>

                <!-- Filters & Toolbar -->
                <div class="bg-card border border-border rounded-lg p-4 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <!-- Search (Left) -->
                        <div class="relative flex-1 max-w-xs">
                            <SearchIcon class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                type="text"
                                :placeholder="$t('features.media.search')"
                                class="pl-8 bg-background"
                            />
                        </div>
                        
                        <!-- Filters, View Toggle, Empty Trash (Right) -->
                        <div class="flex items-center gap-2 ml-auto flex-wrap">
                            <Select v-model="mimeFilter">
                                <SelectTrigger class="w-[130px] bg-background">
                                    <SelectValue :placeholder="$t('features.media.filter.allTypes')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{{ $t('features.media.filter.allTypes') }}</SelectItem>
                                    <SelectItem value="image">{{ $t('features.media.filter.images') }}</SelectItem>
                                    <SelectItem value="video">{{ $t('features.media.filter.videos') }}</SelectItem>
                                    <SelectItem value="application">{{ $t('features.media.filter.documents') }}</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select v-model="usageFilter">
                                <SelectTrigger class="w-[130px] bg-background">
                                    <SelectValue :placeholder="$t('features.media.filter.allStatus')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{{ $t('features.media.filter.allStatus') }}</SelectItem>
                                    <SelectItem value="used">{{ $t('features.media.filter.used') }}</SelectItem>
                                    <SelectItem value="unused">{{ $t('features.media.filter.unused') }}</SelectItem>
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

                            <!-- Empty Trash Button -->
                            <Button v-if="isTrashMode && statistics?.trash_count > 0" variant="destructive" size="sm" @click="emptyTrash">
                                <Trash2 class="w-4 h-4 mr-2" />
                                {{ $t('features.media.emptyTrash') }}
                            </Button>
                        </div>

                        <!-- Bulk Actions -->
                        <div v-if="selectedMedia.length > 0" class="flex items-center gap-2 animate-in fade-in slide-in-from-right-2">
                            <div class="flex items-center px-3 py-1.5 bg-secondary rounded-md text-sm font-medium text-secondary-foreground">
                                {{ t('features.media.selected', { count: selectedMedia.length }) }}
                            </div>
                            <Select v-model="bulkAction" @update:modelValue="handleBulkAction">
                                <SelectTrigger class="w-[150px] bg-background">
                                    <SelectValue :placeholder="$t('features.media.actions.bulk')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <template v-if="isTrashMode">
                                        <SelectItem value="restore">
                                            <div class="flex items-center">
                                                <RefreshCw class="w-4 h-4 mr-2" />
                                                {{ $t('features.media.actions.restore') }}
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="delete_permanent" class="text-destructive focus:text-destructive">
                                            <div class="flex items-center">
                                                <Trash2 class="w-4 h-4 mr-2" />
                                                {{ $t('features.media.actions.deletePermanent') }}
                                            </div>
                                        </SelectItem>
                                    </template>
                                    <template v-else>
                                        <SelectItem value="move">
                                            <div class="flex items-center">
                                                <Move class="w-4 h-4 mr-2" />
                                                {{ $t('features.media.actions.move') }}
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="update_alt">
                                            <div class="flex items-center">
                                                <Type class="w-4 h-4 mr-2" />
                                                {{ $t('features.media.actions.updateAlt') }}
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="download">
                                            <div class="flex items-center">
                                                <Download class="w-4 h-4 mr-2" />
                                                {{ $t('features.media.actions.downloadZip') }}
                                            </div>
                                        </SelectItem>
                                        <SelectItem value="delete" class="text-destructive focus:text-destructive">
                                            <div class="flex items-center">
                                                <Trash2 class="w-4 h-4 mr-2" />
                                                {{ $t('features.media.actions.delete') }}
                                            </div>
                                        </SelectItem>
                                    </template>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="bg-card border border-border rounded-lg min-h-[400px]">
                    <div v-if="loading" class="p-12 text-center h-full flex flex-col items-center justify-center">
                        <Loader2 class="w-8 h-8 animate-spin text-primary mb-4" />
                        <p class="text-muted-foreground">{{ $t('features.media.loading') }}</p>
                    </div>

                    <div v-else-if="mediaList.length === 0" class="p-12 text-center h-full flex flex-col items-center justify-center">
                        <ImageIcon class="mx-auto h-12 w-12 text-muted-foreground opacity-20" />
                        <p class="mt-4 text-muted-foreground">{{ $t('features.media.empty') }}</p>
                    </div>

                    <div v-else>
                        <!-- Grid View -->
                        <div v-if="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 p-4">
                            <!-- Folders -->
                            <div
                                v-for="folder in currentFolders"
                                :key="'folder-' + folder.id"
                                @click="selectFolder(folder.id)"
                                class="group relative bg-background border border-border rounded-lg overflow-hidden cursor-pointer transition-all hover:border-primary/50 shadow-sm"
                            >
                                <div class="aspect-square bg-blue-50/30 flex flex-col items-center justify-center p-4">
                                    <div class="relative">
                                        <Folder class="w-16 h-16 text-blue-400 fill-blue-400/10 transition-transform group-hover:scale-110" />
                                        <div v-if="folder.children_count > 0" class="absolute -top-1 -right-1 bg-blue-500 text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full border border-white">
                                            {{ folder.children_count }}
                                        </div>
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-foreground truncate w-full text-center px-2" :title="folder.name">
                                        {{ folder.name }}
                                    </p>
                                    <Badge v-if="folder.is_shared" variant="secondary" class="mt-1 bg-blue-100 text-blue-700 hover:bg-blue-100/80 border-blue-200">
                                        <Users class="w-3 h-3 mr-1" />
                                        {{ $t('features.media.shared') }}
                                    </Badge>
                                </div>
                                <!-- Folder Actions Overlay -->
                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Button variant="ghost" size="icon" @click.stop="deleteFolder(folder)" class="h-8 w-8 text-destructive hover:bg-destructive/10">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Media Items -->
                            <div
                                v-for="media in mediaList"
                                :key="media.id"
                                @click="toggleMediaSelection(media)"
                                class="group relative bg-background border border-border rounded-lg overflow-hidden cursor-pointer transition-all hover:border-primary/50 shadow-sm"
                                :class="isMediaSelected(media.id) ? 'ring-2 ring-indigo-500 border-indigo-500' : ''"
                            >
                                <!-- Checkbox -->
                                <div class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity" :class="{ 'opacity-100': isMediaSelected(media.id) }">
                                    <Checkbox
                                        :checked="isMediaSelected(media.id)"
                                        @update:checked="toggleMediaSelection(media)"
                                        @click.stop
                                    />
                                </div>
                                <div class="aspect-square bg-muted/30 flex items-center justify-center relative group-content" :data-media-id="media.id">
                                    <LazyImage
                                        v-if="media.mime_type?.startsWith('image/')"
                                        :src="media.thumbnail_url || media.url"
                                        :alt="media.alt || media.name"
                                        image-class="w-full h-full object-cover transition-transform group-hover:scale-105"
                                        @error="handleImageError($event)"
                                    />
                                    <div v-else class="text-muted-foreground">
                                        <FileIcon class="w-12 h-12" />
                                    </div>
                                    <!-- Quick Actions Overlay -->
                                    <div class="absolute inset-0 bg-background/40 backdrop-blur-[1px] opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-2">
                                        <template v-if="isTrashMode">
                                            <Button
                                                variant="secondary"
                                                size="icon"
                                                @click.stop="restoreMedia(media)"
                                                class="h-9 w-9 bg-background/90 hover:bg-background text-foreground"
                                                :title="t('features.media.actions.restore')"
                                            >
                                                <RefreshCw class="w-4 h-4" />
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="icon"
                                                @click.stop="deleteMedia(media)"
                                                class="h-9 w-9 opacity-90 hover:opacity-100"
                                                :title="t('features.media.actions.deletePermanent')"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </template>
                                        <template v-else>
                                            <Button
                                                variant="secondary"
                                                size="icon"
                                                @click.stop="viewMedia(media)"
                                                class="h-9 w-9 bg-background/90 hover:bg-background text-foreground"
                                            >
                                                <Eye class="w-4 h-4" />
                                            </Button>
                                            <Button
                                                variant="secondary"
                                                size="icon"
                                                @click.stop="editMedia(media)"
                                                class="h-9 w-9 bg-background/90 hover:bg-background text-foreground"
                                            >
                                                <Edit class="w-4 h-4" />
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="icon"
                                                @click.stop="deleteMedia(media)"
                                                class="h-9 w-9 opacity-90 hover:opacity-100"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </template>
                                    </div>
                                </div>
                                <div class="p-3 border-t border-border bg-card">
                                    <p class="text-sm font-medium text-foreground truncate" :title="media.name">{{ media.name }}</p>
                                    <div class="flex items-center justify-between mt-1">
                                        <p class="text-xs text-muted-foreground">{{ formatFileSize(media.size) }}</p>
                                        <Badge v-if="media.is_shared" variant="secondary" class="text-[10px] h-4 px-1 bg-blue-50 text-blue-600 hover:bg-blue-50/80 border-blue-100">
                                            <Users class="w-2.5 h-2.5 mr-0.5" />
                                            {{ $t('features.media.shared') }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- List View -->
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-border">
                                <thead class="bg-muted">
                                    <tr>
                                        <th class="px-6 py-3 text-left w-12">
                                            <Checkbox
                                                :checked="allMediaSelected"
                                                @update:checked="toggleSelectAll"
                                            />
                                        </th>

                                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                                            {{ $t('features.media.table.media') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                                            {{ $t('features.media.table.name') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                                            {{ $t('features.media.table.type') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                                            {{ $t('features.media.table.size') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                                            {{ $t('features.media.table.folder') }}
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground tracking-wider">
                                            {{ $t('features.media.table.actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-card divide-y divide-border">
                                    <!-- Folders -->
                                    <tr 
                                        v-for="folder in currentFolders" 
                                        :key="'folder-list-' + folder.id" 
                                        class="hover:bg-muted/30 cursor-pointer group"
                                        @click="selectFolder(folder.id)"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap" @click.stop>
                                            <!-- Folders cannot be selected for bulk media actions for now -->
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="w-16 h-16 bg-blue-50/50 rounded flex items-center justify-center p-1">
                                                <Folder class="w-8 h-8 text-blue-400" />
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <div class="text-sm font-medium text-foreground">{{ folder.name }}</div>
                                                <Badge v-if="folder.is_shared" variant="secondary" class="text-[10px] h-4 px-1 bg-blue-50 text-blue-600 border-blue-100">
                                                    {{ $t('features.media.shared') }}
                                                </Badge>
                                            </div>
                                            <div class="text-xs text-muted-foreground">{{ $t('features.media.folder') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Badge variant="outline" class="font-normal border-blue-200 text-blue-600 bg-blue-50/50">
                                                FOLDER
                                            </Badge>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                            -
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                            {{ folder.parent?.name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <Button variant="ghost" size="icon" @click.stop="deleteFolder(folder)" class="h-8 w-8 text-destructive">
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Media -->
                                    <tr 
                                        v-for="media in mediaList" 
                                        :key="media.id" 
                                        class="hover:bg-muted/30 cursor-pointer group"
                                        :class="{ 'bg-primary/5': isMediaSelected(media.id) }"
                                        @click="viewMedia(media)"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap" @click.stop>
                                            <Checkbox
                                                :checked="isMediaSelected(media.id)"
                                                @update:checked="toggleMediaSelection(media)"
                                            />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="w-16 h-16 bg-muted/50 rounded flex items-center justify-center p-1" :data-media-id="media.id">
                                                <LazyImage
                                                    v-if="media.mime_type?.startsWith('image/')"
                                                    :src="media.thumbnail_url || media.url"
                                                    :alt="media.alt || media.name"
                                                    image-class="w-full h-full object-cover rounded shadow-sm"
                                                    @error="handleImageError($event)"
                                                />
                                                <FileIcon v-else class="w-8 h-8 text-muted-foreground/50" />
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <div class="text-sm font-medium text-foreground">{{ media.name }}</div>
                                                <Badge v-if="media.is_shared" variant="secondary" class="text-[10px] h-4 px-1 bg-blue-50 text-blue-600 border-blue-100">
                                                    {{ $t('features.media.shared') }}
                                                </Badge>
                                            </div>
                                            <div v-if="media.alt" class="text-sm text-muted-foreground line-clamp-1">{{ media.alt }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Badge variant="secondary" class="font-normal">
                                                {{ media.mime_type.split('/')[1]?.toUpperCase() || media.mime_type }}
                                            </Badge>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                            {{ formatFileSize(media.size) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                            {{ media.folder?.name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <template v-if="isTrashMode">
                                                    <Button variant="ghost" size="icon" @click.stop="restoreMedia(media)" class="h-8 w-8 text-primary">
                                                        <RefreshCw class="w-4 h-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="icon" @click.stop="deleteMedia(media)" class="h-8 w-8 text-destructive">
                                                        <Trash2 class="w-4 h-4" />
                                                    </Button>
                                                </template>
                                                <template v-else>
                                                    <Button variant="ghost" size="icon" @click.stop="viewMedia(media)" class="h-8 w-8">
                                                        <Eye class="w-4 h-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="icon" @click.stop="editMedia(media)" class="h-8 w-8">
                                                        <Edit class="w-4 h-4" />
                                                    </Button>
                                                    <Button variant="ghost" size="icon" @click.stop="deleteMedia(media)" class="h-8 w-8 text-destructive">
                                                        <Trash2 class="w-4 h-4" />
                                                    </Button>
                                                </template>
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
                    v-if="pagination && pagination.total > 0"
                    :current-page="pagination.current_page"
                    :total-items="pagination.total"
                    :per-page="Number(pagination.per_page || 10)"
                    :show-page-numbers="true"
                    @page-change="changePage"
                    @update:per-page="(val) => { if(pagination) { pagination.per_page = val; pagination.current_page = 1; fetchMedia(); } }"
                    class="border-none shadow-none mt-4"
                />
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
            class="fixed inset-0 bg-background/80 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
            @click.self="showUpdateAltModal = false"
        >
            <div class="relative w-full max-w-md p-6 border rounded-md bg-card">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-foreground mb-4">{{ t('features.media.modals.updateAlt.title') }}</h3>
                    <p class="text-sm text-muted-foreground mb-4">
                        {{ t('features.media.modals.updateAlt.description', { count: selectedMedia.length }) }}
                    </p>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-foreground mb-2">
                            {{ t('features.media.modals.updateAlt.label') }}
                        </label>
                        <Input
                            v-model="bulkAltText"
                            type="text"
                            :placeholder="t('features.media.modals.updateAlt.placeholder')"
                        />
                    </div>
                    <div class="flex justify-end space-x-3">
                        <Button
                            variant="outline"
                            @click="showUpdateAltModal = false; bulkAltText = ''"
                        >
                            {{ t('features.media.modals.updateAlt.cancel') }}
                        </Button>
                        <Button
                            @click="handleUpdateAltText"
                            :disabled="bulkProcessing"
                        >
                            {{ bulkProcessing ? t('features.media.modals.updateAlt.updating') : t('features.media.modals.updateAlt.update') }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Processing Progress -->
        <div
            v-if="bulkProcessing"
            class="fixed bottom-4 right-4 bg-card rounded-lg p-4 w-80 z-50"
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
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useMediaToast } from '@/composables/useMediaToast.js';
import { useConfirm } from '@/composables/useConfirm.js';
import { 
    LayoutGrid, 
    List, 
    Plus, 
    Folder, 
    FolderPlus, 
    Search as SearchIcon, 
    Filter, 
    MoreVertical, 
    Eye, 
    Edit, 
    Trash2, 
    Download, 
    Move, 
    Type,
    Users,
    Image as ImageIcon,
    Video as VideoIcon,
    FileText as FileIcon,
    HardDrive,
    Loader2,
    PanelLeft,
    PanelLeftClose,
    ChevronDown,
    ChevronRight,
    FolderOpen,
    CircleHelp,
    RefreshCw
} from 'lucide-vue-next';
import Pagination from '@/components/ui/pagination.vue';
import api from '../../../services/api';
import MediaUploadModal from '../../../components/media/MediaUploadModal.vue';
import MediaEditModal from '../../../components/media/MediaEditModal.vue';
import MediaViewModal from '../../../components/media/MediaViewModal.vue';
import FolderModal from '../../../components/media/FolderModal.vue';
import MoveToFolderModal from '../../../components/media/MoveToFolderModal.vue';
import LazyImage from '../../../components/LazyImage.vue';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Select from '@/components/ui/select.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';
import Checkbox from '@/components/ui/checkbox.vue';
import Badge from '@/components/ui/badge.vue';
import Card from '@/components/ui/card.vue';
import CardContent from '@/components/ui/card-content.vue';
import CardHeader from '@/components/ui/card-header.vue';
import CardTitle from '@/components/ui/card-title.vue';
import CardDescription from '@/components/ui/card-description.vue';

const { t } = useI18n();
const mediaToast = useMediaToast();
const { confirm } = useConfirm();
const viewMode = ref('grid');
const loading = ref(false);
const isReady = ref(false);
const mediaList = ref([]);
const folders = ref([]); // Flattened list of folders for lookups and modals
const treeFolders = ref([]); // Hierarchical list of folders for sidebar and current view
const currentFolders = computed(() => {
    // If in trash mode, show ALL folders that are trashed
    if (isTrashMode.value) {
        return folders.value.filter(f => f.is_trashed);
    }
    
    // Non-trash mode: show children of the selected folder
    // If no folder is selected, show root folders
    if (selectedFolder.value === null) {
        // Root folders are those where parent_id is null and NOT trashed
        return treeFolders.value.filter(f => !f.is_trashed);
    }
    
    // Find the selected folder and return its children (non-trashed)
    const current = folders.value.find(f => f.id === selectedFolder.value);
    if (!current || current.is_trashed) return [];
    
    return (current.children || []).filter(f => !f.is_trashed);
});
const selectedFolder = ref(null);
const selectedMedia = ref([]);
const pagination = ref(null);
const statistics = ref(null);
const search = ref('');
const mimeFilter = ref('all');
const usageFilter = ref('all');
const bulkAction = ref('');
const breadcrumbs = computed(() => {
    if (selectedFolder.value === null) return [];
    
    const crumbs = [];
    let currentId = selectedFolder.value;
    
    while (currentId) {
        const folder = folders.value.find(f => f.id === currentId);
        if (folder) {
            crumbs.unshift({ id: folder.id, name: folder.name });
            currentId = folder.parent_id;
        } else {
            currentId = null;
        }
    }
    
    return crumbs;
});

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
const sidebarCollapsed = ref(false);
const isTrashMode = ref(false);
const expandedFolders = ref(new Set());

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
};

const toggleFolder = (folderId) => {
    if (expandedFolders.value.has(folderId)) {
        expandedFolders.value.delete(folderId);
    } else {
        expandedFolders.value.add(folderId);
    }
};

const selectFolder = (id) => {
    isTrashMode.value = false;
    selectedFolder.value = id;
};

const toggleTrashMode = () => {
    isTrashMode.value = !isTrashMode.value;
    selectedFolder.value = null;
    fetchMedia();
};

const fetchMedia = async () => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value?.current_page || 1,
            view: viewMode.value,
            trashed: isTrashMode.value ? 'only' : undefined,
        };

        if (selectedFolder.value !== null) {
            params.folder_id = selectedFolder.value;
        }

        if (search.value) {
            params.search = search.value;
        }

        if (mimeFilter.value && mimeFilter.value !== 'all') {
            params.mime_type = mimeFilter.value === 'image' ? 'image/' : 
                              mimeFilter.value === 'video' ? 'video/' : 
                              mimeFilter.value === 'application' ? 'application/' : undefined;
        }

        if (usageFilter.value && usageFilter.value !== 'all') {
            params.usage = usageFilter.value;
        }

        const response = await api.get('/admin/cms/media', { params });
        const { data, pagination: paginationData } = parseResponse(response);
        mediaList.value = ensureArray(data);
        if (paginationData) {
            pagination.value = paginationData;
        }
    } catch (error) {
        // console.error('Failed to fetch media:', error);
    } finally {
        loading.value = false;
    }
};

const fetchStatistics = async () => {
    try {
        const response = await api.get('/admin/cms/media/statistics');
        statistics.value = response.data?.data || response.data;
    } catch (error) {
        // console.error('Failed to fetch media statistics:', error);
    }
};

const fetchFolders = async () => {
    try {
        // For sidebar: only fetch active folders (not trashed)
        // Trashed folders will be handled separately with media in trash mode
        const response = await api.get('/admin/cms/media-folders', { 
            params: { 
                tree: true
                // Don't include trashed: 'with' - we want clean sidebar
            } 
        });
        const { data } = parseResponse(response);
        treeFolders.value = ensureArray(data);
        
        // Flatten folders for breadcrumbs and lookup if needed
        const flatten = (items) => {
            let result = [];
            items.forEach(item => {
                result.push(item);
                if (item.children && item.children.length > 0) {
                    result = result.concat(flatten(item.children));
                }
            });
            return result;
        };
        folders.value = flatten(treeFolders.value);
    } catch (error) {
        // console.error('Failed to fetch folders:', error);
    }
};

const restoreMedia = async (media) => {
    try {
        await api.post(`/admin/cms/media/${media.id}/restore`);
        await fetchMedia();
        fetchStatistics();
    } catch (error) {
        // console.error('Failed to restore media:', error);
    }
};

const emptyTrash = async () => {
    const confirmed = await confirm({
        title: t('features.media.confirm.emptyTrashTitle'),
        message: t('features.media.confirm.emptyTrashMessage'),
        variant: 'danger',
        confirmText: t('features.media.confirm.delete'),
        cancelText: t('features.media.confirm.cancel')
    });
    
    if (!confirmed) {
        return;
    }
    
    try {
        await api.delete('/admin/cms/media/empty-trash');
        await fetchMedia();
        await fetchFolders();
        fetchStatistics();
    } catch (error) {
        // console.error('Failed to empty trash:', error);
        alert(error.response?.data?.message || t('features.media.messages.emptyTrashFailed') || 'Failed to empty trash');
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
            fetchStatistics();
            selectedMedia.value = [];
            bulkAction.value = '';
        } catch (error) {
            // console.error('Failed to delete media:', error);
            mediaToast.error.fromResponse(error);
            bulkAction.value = '';
        } finally {
            bulkProcessing.value = false;
        }
    } else if (action === 'restore') {
        bulkProcessing.value = true;
        try {
            await api.post('/admin/cms/media/bulk-action', {
                action: 'restore',
                ids: selectedMedia.value,
            });
            await fetchMedia();
            fetchStatistics();
            selectedMedia.value = [];
            bulkAction.value = '';
        } catch (error) {
            // console.error('Failed to restore media:', error);
            alert(error.response?.data?.message || t('features.media.messages.restoreFailed'));
        } finally {
            bulkProcessing.value = false;
        }
    } else if (action === 'delete_permanent') {
        if (!confirm(t('features.media.messages.deletePermanentConfirm', { count }))) {
            bulkAction.value = '';
            return;
        }
        bulkProcessing.value = true;
        try {
            await api.post('/admin/cms/media/bulk-action', {
                action: 'delete_permanent',
                ids: selectedMedia.value,
            });
            await fetchMedia();
            fetchStatistics();
            selectedMedia.value = [];
            bulkAction.value = '';
        } catch (error) {
            // console.error('Failed to delete permanent:', error);
            alert(error.response?.data?.message);
        } finally {
            bulkProcessing.value = false;
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
            // console.error('Failed to download media:', error);
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
        // console.error('Failed to move media:', error);
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
        // console.error('Failed to update alt text:', error);
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
    const isPermanent = isTrashMode.value;
    
    const confirmed = await confirm({
        title: isPermanent 
            ? t('features.media.confirm.deletePermanentTitle')
            : t('features.media.confirm.deleteTitle'),
        message: isPermanent
            ? t('features.media.confirm.deletePermanentMessage')
            : t('features.media.confirm.deleteMessage'),
        variant: 'danger',
        confirmText: t('features.media.confirm.delete'),
        cancelText: t('features.media.confirm.cancel')
    });

    if (!confirmed) {
        return;
    }

    try {
        if (isPermanent) {
            // Use force-delete endpoint for permanent deletion
            await api.delete(`/admin/cms/media/${media.id}/force-delete`);
        } else {
            // Regular soft delete
            await api.delete(`/admin/cms/media/${media.id}`);
        }
        await fetchMedia();
        fetchStatistics();
    } catch (error) {
        // console.error('Failed to delete media:', error);
        alert(t('features.media.messages.deleteFailed'));
    }
};

const deleteFolder = async (folder) => {
    const isPermanent = isTrashMode.value;
    
    const confirmed = await confirm({
        title: isPermanent 
            ? t('features.media.confirm.deletePermanentTitle')
            : t('features.media.confirm.deleteTitle'),
        message: isPermanent
            ? t('features.media.confirm.deletePermanentMessage')
            : t('features.media.confirm.deleteMessage'),
        variant: 'danger',
        confirmText: t('features.media.confirm.delete'),
        cancelText: t('features.media.confirm.cancel')
    });

    if (!confirmed) {
        return;
    }

    try {
        await api.delete(`/admin/cms/media-folders/${folder.id}`, {
            params: { permanent: isPermanent ? 1 : 0 }
        });
        await fetchFolders(); // Refresh tree
        if (selectedFolder.value === folder.id) {
            selectedFolder.value = folder.parent_id || null;
        }
        fetchStatistics();
    } catch (error) {
        // console.error('Failed to delete folder:', error);
        mediaToast.error.fromResponse(error);
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

onMounted(() => {
    fetchMedia();
    fetchFolders();
    fetchStatistics();
    setTimeout(() => { isReady.value = true; }, 100);
});

// Watch for changes
watch([selectedFolder, search, mimeFilter, usageFilter, viewMode, isTrashMode], () => {
    if (pagination.value) {
        pagination.value.current_page = 1;
    }
    fetchMedia();
    if (selectedFolder.value === null && !isTrashMode.value) {
        fetchFolders(); // Refresh tree when going back to root
    }
});
</script>
