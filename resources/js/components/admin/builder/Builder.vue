<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <Layout class="w-5 h-5" />
                Page Builder
            </h2>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="showBlockPicker = true">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Block
                </Button>
            </div>
        </div>

        <!-- Draggable Block List -->
        <draggable 
            v-if="modelValue.length > 0" 
            :list="modelValue" 
            item-key="id"
            handle=".drag-handle"
            @change="emitUpdate"
            class="space-y-4"
        >
            <template #item="{ element: block, index }">
                <div 
                    class="group relative border rounded-lg bg-card overflow-hidden transition-all hover:border-primary/50"
                >
                    <!-- Block Header/Actions -->
                    <div class="flex items-center justify-between px-4 py-2 bg-muted/50 border-b">
                        <div class="flex items-center gap-2">
                            <GripVertical class="w-4 h-4 text-muted-foreground cursor-move drag-handle" />
                            <span class="text-xs font-semibold capitalize text-muted-foreground/80">
                                {{ block.type }} block
                            </span>
                        </div>
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground" @click.stop="moveUp(index)" :disabled="index === 0">
                                <ChevronUp class="w-3.5 h-3.5" />
                            </Button>
                            <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground" @click.stop="moveDown(index)" :disabled="index === modelValue.length - 1">
                                <ChevronDown class="w-3.5 h-3.5" />
                            </Button>
                            <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground" @click="editBlock(index)">
                                <Settings class="w-3.5 h-3.5" />
                            </Button>
                            <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="removeBlock(index)">
                                <Trash2 class="w-3.5 h-3.5" />
                            </Button>
                        </div>
                    </div>

                    <!-- Block Preview Content -->
                    <div @click="editBlock(index)" class="p-6 min-h-[100px] flex items-center justify-center bg-dot-pattern cursor-pointer hover:bg-muted/30 transition-colors">
                        <div class="text-center">
                            <component 
                                :is="getBlockPreviewIcon(block.type)" 
                                class="w-8 h-8 mx-auto text-muted-foreground/30 mb-2" 
                            />
                            <p class="text-sm text-muted-foreground font-medium">
                                {{ block.settings.title || getBlockLabel(block.type) }}
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </draggable>

        <!-- Empty State -->
        <div 
            v-else 
            @click="showBlockPicker = true"
            class="group border-2 border-dashed rounded-xl p-12 flex flex-col items-center justify-center gap-4 cursor-pointer hover:border-primary/50 hover:bg-primary/5 transition-all"
        >
            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                <Plus class="w-6 h-6 text-primary" />
            </div>
            <div class="text-center">
                <h3 class="font-medium">No blocks added yet</h3>
                <p class="text-sm text-muted-foreground">Click to add your first block and start building</p>
            </div>
        </div>

        <!-- Block Picker Modal (Simple for now) -->
        <div v-if="showBlockPicker" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-background/40 backdrop-blur-md">
            <div class="bg-background/80 backdrop-blur-xl border-2 border-primary/10 shadow-2xl rounded-2xl w-full max-w-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-bold">Select a Block</h3>
                    <Button variant="ghost" size="icon" @click="showBlockPicker = false">
                        <X class="w-4 h-4" />
                    </Button>
                </div>
                <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-4 h-[400px] overflow-y-auto">
                    <div 
                        v-for="type in availableBlocks" 
                        :key="type.name"
                        @click="addBlock(type.name)"
                        class="p-4 border rounded-lg hover:border-primary hover:bg-primary/5 cursor-pointer transition-all flex flex-col items-center gap-3 text-center group"
                    >
                        <div class="w-12 h-12 rounded-lg bg-muted flex items-center justify-center group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                            <component :is="type.icon" class="w-6 h-6" />
                        </div>
                        <div class="space-y-1">
                            <div class="font-medium text-sm">{{ type.label }}</div>
                            <div class="text-[10px] text-muted-foreground">{{ type.description }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Block Settings Drawer -->
        <div v-if="editingIndex !== null" class="fixed inset-y-0 right-0 z-[100] w-[480px] bg-background/90 backdrop-blur-2xl border-l border-primary/10 shadow-[0_0_50px_-12px_rgba(0,0,0,0.25)] animate-in slide-in-from-right duration-300">
            <div class="flex flex-col h-full">
                <!-- Header -->
                <div class="p-6 border-b border-primary/5">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="font-bold text-xl tracking-tight">Block Editor</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-bold px-1.5 py-0.5 bg-primary/10 text-primary rounded uppercase tracking-tighter">
                                    {{ modelValue[editingIndex].type }}
                                </span>
                                <span class="text-[10px] text-muted-foreground font-medium">#{{ modelValue[editingIndex].id.slice(0, 8) }}</span>
                            </div>
                        </div>
                        <Button variant="ghost" size="icon" class="rounded-full h-8 w-8 hover:bg-primary/5" @click="editingIndex = null">
                            <X class="w-4 h-4" />
                        </Button>
                    </div>

                    <!-- Tabs Menu -->
                    <div class="flex gap-1 p-1 bg-muted/30 rounded-xl border border-primary/5">
                        <Button 
                            v-for="tab in ['Content', 'Style', 'Advanced']" 
                            :key="tab"
                            variant="ghost" 
                            size="sm"
                            class="flex-1 rounded-lg text-xs font-semibold transition-all duration-200"
                            :class="activeTab === tab.toLowerCase() ? 'bg-background shadow-md text-primary' : 'text-muted-foreground hover:text-foreground'"
                            @click="activeTab = tab.toLowerCase()"
                        >
                            {{ tab }}
                        </Button>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- CONTENT TAB -->
                    <div v-if="activeTab === 'content'" class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <!-- Basic Info -->
                        <div v-if="'title' in modelValue[editingIndex].settings" class="space-y-3">
                            <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Basic Content</label>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Title</label>
                                    <Input v-model="modelValue[editingIndex].settings.title" placeholder="Enter block title" />
                                </div>
                                <div v-if="'subtitle' in modelValue[editingIndex].settings" class="space-y-2">
                                    <label class="text-sm font-medium">Subtitle</label>
                                    <Input v-model="modelValue[editingIndex].settings.subtitle" placeholder="Enter subtitle" />
                                </div>
                            </div>
                        </div>

                        <!-- Block Specifics -->
                        <div class="space-y-4 pt-4 border-t border-primary/5">
                            <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider">{{ modelValue[editingIndex].type }} Settings</label>
                            
                            <!-- CTA Specific -->
                            <template v-if="modelValue[editingIndex].type === 'cta'">
                                <div class="space-y-3">
                                    <label class="text-sm font-medium">Button Text</label>
                                    <Input v-model="modelValue[editingIndex].settings.buttonText" />
                                </div>
                                <div class="space-y-3">
                                    <label class="text-sm font-medium">Button URL</label>
                                    <Input v-model="modelValue[editingIndex].settings.buttonUrl" />
                                </div>
                            </template>

                            <!-- Image / Hero Background -->
                            <div v-if="'background' in modelValue[editingIndex].settings" class="space-y-3">
                                <label class="text-sm font-medium">Background Image URL</label>
                                <Input v-model="modelValue[editingIndex].settings.background" placeholder="/images/bg.jpg" />
                            </div>

                            <!-- Columns Specific -->
                            <div v-if="modelValue[editingIndex].type === 'columns'" class="space-y-4">
                                <label class="text-sm font-medium">Layout Preset</label>
                                <div class="grid grid-cols-5 gap-2">
                                    <Button 
                                        v-for="l in ['1-1', '1-2', '2-1', '1-1-1', '1-1-1-1']" 
                                        :key="l"
                                        variant="outline" 
                                        size="xs"
                                        class="h-10 text-[10px] uppercase font-bold"
                                        :class="modelValue[editingIndex].settings.layout === l ? 'border-primary bg-primary/5 text-primary' : ''"
                                        @click="modelValue[editingIndex].settings.layout = l"
                                    >
                                        {{ l }}
                                    </Button>
                                </div>
                                <p class="text-[10px] text-muted-foreground italic">Nested block management in columns is coming in the next update. Currently, this sets the column structure for frontend rendering.</p>
                            </div>

                            <!-- Pricing Specific -->
                            <div v-if="modelValue[editingIndex].type === 'pricing'" class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium">Plans</label>
                                    <Button size="xs" variant="outline" @click="addPricingPlan(editingIndex)">
                                        <Plus class="w-3 h-3 mr-1" /> Add Plan
                                    </Button>
                                </div>
                                <div class="space-y-4">
                                    <div 
                                        v-for="(plan, i) in modelValue[editingIndex].settings.items" 
                                        :key="i"
                                        class="p-4 border rounded-xl bg-muted/20 relative group/item space-y-3"
                                    >
                                        <Button 
                                            size="icon" 
                                            variant="ghost" 
                                            class="absolute top-2 right-2 h-6 w-6 opacity-0 group-hover/item:opacity-100 transition-opacity"
                                            @click="removePricingPlan(editingIndex, i)"
                                        >
                                            <Trash2 class="w-3 h-3 text-destructive" />
                                        </Button>
                                        <Input v-model="plan.name" placeholder="Plan Name" class="h-8 font-bold" />
                                        <Input v-model="plan.price" placeholder="Price (e.g. $49)" class="h-8" />
                                        <Textarea v-model="plan.buttonText" placeholder="Button Text" rows="1" class="h-8 py-1" />
                                    </div>
                                </div>
                            </div>

                            <!-- Accordion Specific -->
                            <div v-if="modelValue[editingIndex].type === 'accordion'" class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium">FAQ Items</label>
                                    <Button size="xs" variant="outline" @click="addAccordionItem(editingIndex)">
                                        <Plus class="w-3 h-3 mr-1" /> Add FAQ
                                    </Button>
                                </div>
                                <div class="space-y-4">
                                    <div 
                                        v-for="(faq, i) in modelValue[editingIndex].settings.items" 
                                        :key="i"
                                        class="p-4 border rounded-xl bg-muted/20 relative group/item space-y-3"
                                    >
                                        <Button 
                                            size="icon" 
                                            variant="ghost" 
                                            class="absolute top-2 right-2 h-6 w-6 opacity-0 group-hover/item:opacity-100 transition-opacity"
                                            @click="removeAccordionItem(editingIndex, i)"
                                        >
                                            <Trash2 class="w-3 h-3 text-destructive" />
                                        </Button>
                                        <Input v-model="faq.question" placeholder="Question" class="h-8 font-semibold" />
                                        <Textarea v-model="faq.answer" placeholder="Answer text..." rows="3" class="text-xs" />
                                    </div>
                                </div>
                            </div>

                                <!-- Video Specific -->
                                <template v-if="modelValue[editingIndex].type === 'video'">
                                    <div class="space-y-3">
                                        <label class="text-sm font-medium">Video URL (YouTube)</label>
                                        <Input v-model="modelValue[editingIndex].settings.videoUrl" />
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" v-model="modelValue[editingIndex].settings.autoplay" />
                                            <label class="text-xs">Autoplay & Mute</label>
                                        </div>
                                    </div>
                                </template>

                            <!-- Content / Text -->
                            <div v-if="'content' in modelValue[editingIndex].settings" class="space-y-3">
                                <Textarea v-model="modelValue[editingIndex].settings.content" placeholder="Enter text content..." rows="12" class="font-serif leading-relaxed" />
                            </div>

                            <!-- Spacer Specific -->
                            <template v-if="modelValue[editingIndex].type === 'spacer'">
                                <div class="space-y-3">
                                    <label class="text-sm font-medium">Height</label>
                                    <select v-model="modelValue[editingIndex].settings.height" class="w-full h-10 px-3 bg-muted/50 border-none rounded-xl text-sm ring-offset-background focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                                        <option value="h-4">Extra Small (16px)</option>
                                        <option value="h-8">Small (32px)</option>
                                        <option value="h-12">Medium (48px)</option>
                                        <option value="h-24">Large (96px)</option>
                                        <option value="h-48">Extra Large (192px)</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-2 pt-2">
                                     <input type="checkbox" v-model="modelValue[editingIndex].settings.showLine" class="rounded border-primary/20" />
                                     <label class="text-xs font-medium">Show Divider Line</label>
                                </div>
                            </template>

                            <!-- Feature Grid Items -->
                            <div v-if="modelValue[editingIndex].type === 'features'" class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-semibold">Grid Items</label>
                                    <Button size="xs" variant="outline" @click="addBlockItem(editingIndex)">
                                        <Plus class="w-3 h-3 mr-1" /> Add Item
                                    </Button>
                                </div>
                                <div class="space-y-3">
                                    <div 
                                        v-for="(item, i) in modelValue[editingIndex].settings.items" 
                                        :key="i"
                                        class="p-4 border rounded-lg bg-muted/20 relative group/item"
                                    >
                                        <Button 
                                            size="icon" 
                                            variant="ghost" 
                                            class="absolute top-2 right-2 h-6 w-6 opacity-0 group-hover/item:opacity-100 transition-opacity"
                                            @click="removeBlockItem(editingIndex, i)"
                                        >
                                            <Trash2 class="w-3 h-3 text-destructive" />
                                        </Button>
                                        <div class="space-y-2 pr-6">
                                            <Input v-model="item.title" placeholder="Item title" class="h-8 text-sm" />
                                            <Textarea v-model="item.description" placeholder="Item description" class="text-xs" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gallery Images -->
                            <div v-if="modelValue[editingIndex].type === 'gallery'" class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-semibold">Gallery Images</label>
                                    <Button size="xs" variant="outline" @click="addGalleryImage(editingIndex)">
                                        <Plus class="w-3 h-3 mr-1" /> Add Image
                                    </Button>
                                </div>
                                <div class="space-y-3">
                                    <div 
                                        v-for="(image, i) in modelValue[editingIndex].settings.images" 
                                        :key="i"
                                        class="p-4 border rounded-xl bg-muted/20 space-y-3 relative group/image"
                                    >
                                        <Button 
                                            size="icon" 
                                            variant="ghost" 
                                            class="absolute top-2 right-2 h-6 w-6 opacity-0 group-hover/image:opacity-100 transition-opacity"
                                            @click="removeGalleryImage(editingIndex, i)"
                                        >
                                            <Trash2 class="w-3 h-3 text-destructive" />
                                        </Button>
                                        <Input v-model="image.url" placeholder="Image URL" class="h-8 text-sm" />
                                        <Input v-model="image.caption" placeholder="Caption (optional)" class="h-8 text-xs" />
                                    </div>
                                </div>
                                <div class="space-y-3 pt-4 border-t border-primary/5">
                                    <label class="text-sm font-semibold">Grid Columns</label>
                                    <select v-model="modelValue[editingIndex].settings.columns" class="w-full rounded-md border p-2 text-sm bg-background">
                                        <option value="2">2 Columns</option>
                                        <option value="3">3 Columns (Standard)</option>
                                        <option value="4">4 Columns (Dense)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Testimonials Specific -->
                        <div v-if="modelValue[editingIndex].type === 'testimonials'" class="space-y-6">
                            <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Testimonials</label>
                            <div class="space-y-4">
                                <label class="text-sm font-medium">Section Title</label>
                                <Input v-model="modelValue[editingIndex].settings.title" placeholder="What our customers say" class="h-10 px-4 bg-muted/50 border-none rounded-xl" />
                                
                                <div class="space-y-4">
                                    <div v-for="(item, index) in modelValue[editingIndex].settings.items" :key="index" class="p-4 bg-muted/30 rounded-2xl border border-primary/5 space-y-3 relative group">
                                        <button @click="removeTestimonial(editingIndex, index)" class="absolute top-2 right-2 p-1 text-muted-foreground hover:text-destructive opacity-0 group-hover:opacity-100 transition-opacity">
                                            <X class="w-4 h-4" />
                                        </button>
                                        <textarea v-model="item.quote" placeholder="Testimonial quote" class="w-full bg-background border-none rounded-lg p-2 text-sm italic h-20 outline-none"></textarea>
                                        <div class="grid grid-cols-2 gap-2">
                                            <Input v-model="item.author" placeholder="Author Name" class="h-8 text-xs" />
                                            <Input v-model="item.role" placeholder="Role/Title" class="h-8 text-xs" />
                                        </div>
                                        <Input v-model="item.avatar" placeholder="Avatar URL" class="h-8 text-[10px]" />
                                    </div>
                                    <Button @click="addTestimonial(editingIndex)" variant="outline" size="sm" class="w-full h-10 border-dashed rounded-xl border-primary/20 text-primary hover:bg-primary/5">
                                        <Plus class="w-4 h-4 mr-2" /> Add Testimonial
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STYLE TAB -->
                    <div v-if="activeTab === 'style'" class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <!-- Colors -->
                        <div class="space-y-6">
                            <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Colors & Background</label>
                            <div class="space-y-4">
                                <label class="text-sm font-medium">Background Color</label>
                                <div class="flex flex-wrap gap-2">
                                    <button 
                                        v-for="color in ['transparent', '#ffffff', '#f8fafc', '#0f172a', '#3b82f6', '#10b981', '#ef4444', '#f59e0b']" 
                                        :key="color"
                                        class="w-6 h-6 rounded-full border border-primary/10 transition-transform active:scale-90"
                                        :style="{ backgroundColor: color }"
                                        @click="modelValue[editingIndex].settings.bgColor = color"
                                    ></button>
                                </div>
                                <Input v-model="modelValue[editingIndex].settings.bgColor" placeholder="#hex-color" class="h-8 text-xs" />
                            </div>
                        </div>

                        <!-- Alignment -->
                        <div v-if="'alignment' in modelValue[editingIndex].settings" class="space-y-4 pt-6 border-t border-primary/5">
                            <label class="text-sm font-medium">Text Alignment</label>
                            <div class="flex gap-1 p-1 bg-muted rounded-xl w-fit">
                                <Button 
                                    v-for="align in [{id: 'left', icon: Rows}, {id: 'center', icon: Layers}, {id: 'right', icon: Rows}]" 
                                    :key="align.id"
                                    variant="ghost" 
                                    size="sm"
                                    class="h-8 px-4 rounded-lg"
                                    :class="modelValue[editingIndex].settings.alignment === 'text-' + align.id ? 'bg-background shadow-md text-primary' : ''"
                                    @click="modelValue[editingIndex].settings.alignment = 'text-' + align.id"
                                >
                                    <component :is="align.icon" class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>

                        <!-- Borders/Radius -->
                        <div class="space-y-4 pt-6 border-t border-primary/5">
                            <label class="text-sm font-medium">Corner Rounding</label>
                            <select v-model="modelValue[editingIndex].settings.radius" class="w-full h-10 px-3 bg-muted/50 border-none rounded-xl text-sm outline-none">
                                <option value="rounded-none">Square (0px)</option>
                                <option value="rounded-lg">Small (8px)</option>
                                <option value="rounded-2xl">Large (16px)</option>
                                <option value="rounded-[40px]">Organic (40px)</option>
                                <option value="rounded-full">Full (Round)</option>
                            </select>
                        </div>
                    </div>

                    <!-- ADVANCED TAB -->
                    <div v-if="activeTab === 'advanced'" class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <!-- Spacing Controls -->
                        <div class="space-y-6">
                            <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Layout & Spacing</label>
                            
                            <div class="space-y-4">
                                <label class="text-sm font-medium">Vertical Padding</label>
                                <select v-model="modelValue[editingIndex].settings.padding" class="w-full h-10 px-3 bg-muted/50 border-none rounded-xl text-sm ring-offset-background focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                                    <option value="py-0">None (0px)</option>
                                    <option value="py-8">Small (32px)</option>
                                    <option value="py-16">Medium (64px)</option>
                                    <option value="py-24">Large (96px)</option>
                                    <option value="py-32">Extra Large (128px)</option>
                                </select>
                            </div>

                            <div v-if="'width' in modelValue[editingIndex].settings" class="space-y-4">
                                <label class="text-sm font-medium">Max Width</label>
                                <select v-model="modelValue[editingIndex].settings.width" class="w-full h-10 px-3 bg-muted/50 border-none rounded-xl text-sm outline-none transition-all focus:ring-2 focus:ring-primary/20">
                                    <option value="max-w-3xl">Narrow (3xl)</option>
                                    <option value="max-w-5xl">Standard (5xl)</option>
                                    <option value="max-w-7xl">Wide (7xl)</option>
                                    <option value="max-w-none">Full Width</option>
                                </select>
                            </div>
                        </div>

                        <!-- Animations -->
                        <div class="space-y-6 pt-6 border-t border-primary/5">
                            <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Animations</label>
                            <div class="space-y-4">
                                <label class="text-sm font-medium">Entrance Effect</label>
                                <select v-model="modelValue[editingIndex].settings.animation" class="w-full h-10 px-3 bg-muted/50 border-none rounded-xl text-sm outline-none">
                                    <option value="">None</option>
                                    <option value="animate-in fade-in duration-700">Fade In</option>
                                    <option value="animate-in slide-in-from-bottom-8 duration-700">Slide Up</option>
                                    <option value="animate-in zoom-in-95 duration-700">Zoom In</option>
                                </select>
                            </div>
                        </div>

                        <!-- Responsive Visibility -->
                        <div class="space-y-6 pt-6 border-t border-primary/5">
                            <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Responsive Visibility</label>
                            <div class="grid grid-cols-3 gap-4">
                                <div 
                                    v-for="device in ['mobile', 'tablet', 'desktop']" 
                                    :key="device"
                                    class="flex flex-col items-center gap-2 p-3 rounded-xl border transition-all cursor-pointer"
                                    :class="modelValue[editingIndex].settings.visibility[device] ? 'bg-primary/5 border-primary text-primary' : 'bg-muted/30 text-muted-foreground'"
                                    @click="modelValue[editingIndex].settings.visibility[device] = !modelValue[editingIndex].settings.visibility[device]"
                                >
                                    <component :is="device === 'mobile' ? MousePointer2 : device === 'tablet' ? Rows : Layout" class="w-4 h-4" />
                                    <span class="text-[10px] font-bold uppercase tracking-wider">{{ device }}</span>
                                </div>
                            </div>
                            <p class="text-[10px] text-muted-foreground italic text-center">Uncheck to hide this block on the corresponding device.</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t bg-muted/30">
                    <Button class="w-full" @click="editingIndex = null">Close Settings</Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import draggable from 'vuedraggable';
import { 
    Layout, Plus, GripVertical, Trash2, Settings, X, 
    Type, Image as ImageIcon, Grid, LayoutTemplate, Square, Rows,
    ChevronUp, ChevronDown, MousePointer2, Clapperboard, Layers,
    SquareSplitVertical, Maximize2, Columns, CreditCard, ListCollapse,
    Heart
} from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Textarea from '@/components/ui/textarea.vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue']);

const showBlockPicker = ref(false);
const editingIndex = ref(null);
const activeTab = ref('content');

const availableBlocks = [
    { 
        name: 'hero', 
        label: 'Hero Section', 
        icon: LayoutTemplate, 
        description: 'Large hero banner with title and background.',
        defaultSettings: { title: 'New Hero', subtitle: 'Hero subtitle here', background: '', padding: 'py-24', bgColor: 'transparent', radius: 'rounded-none', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    { 
        name: 'text', 
        label: 'Text Content', 
        icon: Type, 
        description: 'Rich text area for your page body.',
        defaultSettings: { title: '', content: 'Enter text here...', alignment: 'text-left', padding: 'py-16', bgColor: 'transparent', radius: 'rounded-none', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    { 
        name: 'image', 
        label: 'Single Image', 
        icon: ImageIcon, 
        description: 'Upload and display a single image.',
        defaultSettings: { title: '', url: '', width: 'max-w-5xl', padding: 'py-16', bgColor: 'transparent', radius: 'rounded-2xl', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    { 
        name: 'features', 
        label: 'Feature Grid', 
        icon: Grid, 
        description: 'Display features in a 3-column grid.',
        defaultSettings: { title: 'Features', items: [], padding: 'py-16', bgColor: 'transparent', radius: 'rounded-none', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    { 
        name: 'cta', 
        label: 'Call to Action', 
        icon: MousePointer2, 
        description: 'Eye-catching section with a primary button.',
        defaultSettings: { title: 'Ready to build?', subtitle: 'Start your journey today.', buttonText: 'Get Started', buttonUrl: '#', padding: 'py-24', bgColor: 'transparent', radius: 'rounded-none', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    { 
        name: 'video', 
        label: 'Video Player', 
        icon: Clapperboard, 
        description: 'Embed a YouTube or Vimeo video.',
        defaultSettings: { title: '', videoUrl: '', autoplay: false, padding: 'py-16', bgColor: 'transparent', radius: 'rounded-2xl', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    { 
        name: 'spacer', 
        label: 'Spacer / Divider', 
        icon: SquareSplitVertical, 
        description: 'Add vertical space or a line between sections.',
        defaultSettings: { height: 'h-12', showLine: false, padding: 'py-0', bgColor: 'transparent', radius: 'rounded-none', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    { 
        name: 'gallery', 
        label: 'Image Gallery', 
        icon: Layers, 
        description: 'Display multiple images in a responsive grid.',
        defaultSettings: { title: 'Recent Work', images: [], columns: 3, width: 'max-w-6xl', padding: 'py-16', bgColor: 'transparent', radius: 'rounded-none', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    { 
        name: 'columns', 
        label: 'Columns / Grid', 
        icon: Columns, 
        description: 'Multi-column layout for side-by-side content.',
        defaultSettings: { layout: '1-1', columns: [{ blocks: [] }, { blocks: [] }], padding: 'py-16', width: 'max-w-7xl', bgColor: 'transparent', radius: 'rounded-none', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    { 
        name: 'pricing', 
        label: 'Pricing Table', 
        icon: CreditCard, 
        description: 'Comparison table for different service plans.',
        defaultSettings: { title: 'Pricing Plans', items: [
            { name: 'Basic', price: '$19', features: ['Feature 1', 'Feature 2'], buttonText: 'Buy Basic' },
            { name: 'Pro', price: '$49', features: ['All Basic', 'Feature 3'], buttonText: 'Go Pro' }
        ], padding: 'py-20', width: 'max-w-6xl', bgColor: 'transparent', radius: 'rounded-none', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    { 
        name: 'accordion', 
        label: 'Accordion / FAQ', 
        icon: ListCollapse, 
        description: 'Expandable sections for questions and answers.',
        defaultSettings: { title: 'Frequently Asked Questions', items: [
            { question: 'What is this?', answer: 'This is a question.' },
            { question: 'How it works?', answer: 'It works great.' }
        ], padding: 'py-20', width: 'max-w-5xl', bgColor: 'transparent', radius: 'rounded-none', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    { 
        name: 'testimonials', 
        label: 'Testimonials', 
        icon: Heart, 
        description: 'Display customer feedback and success stories.',
        defaultSettings: { title: 'What Our Clients Say', items: [
            { quote: 'The best CMS experience we have ever had. Simple, powerful and elegant.', author: 'Alex Rivera', role: 'Founder, TechCo', avatar: '' },
            { quote: 'Antigravity transformed our workflow completely. We build faster now.', author: 'Sarah Chen', role: 'Design Lead', avatar: '' }
        ], padding: 'py-20', width: 'max-w-7xl', bgColor: 'transparent', radius: 'rounded-none', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    }
];

const getBlockLabel = (type) => {
    return availableBlocks.find(b => b.name === type)?.label || 'Unknown';
};

const getBlockPreviewIcon = (type) => {
    return availableBlocks.find(b => b.name === type)?.icon || Square;
};

const emitUpdate = () => {
    emit('update:modelValue', [...props.modelValue]);
};

const addBlock = (typeName) => {
    const blockType = availableBlocks.find(b => b.name === typeName);
    const newBlock = {
        id: crypto.randomUUID(),
        type: typeName,
        settings: JSON.parse(JSON.stringify(blockType.defaultSettings))
    };
    
    emit('update:modelValue', [...props.modelValue, newBlock]);
    showBlockPicker.value = false;
};

const editBlock = (index) => {
    editingIndex.value = index;
};

const moveUp = (index) => {
    if (index === 0) return;
    const newValue = [...props.modelValue];
    [newValue[index - 1], newValue[index]] = [newValue[index], newValue[index - 1]];
    emit('update:modelValue', newValue);
};

const moveDown = (index) => {
    if (index === props.modelValue.length - 1) return;
    const newValue = [...props.modelValue];
    [newValue[index + 1], newValue[index]] = [newValue[index], newValue[index + 1]];
    emit('update:modelValue', newValue);
};

const removeBlock = (index) => {
    if (confirm('Are you sure you want to remove this block?')) {
        const newValue = [...props.modelValue];
        newValue.splice(index, 1);
        emit('update:modelValue', newValue);
    }
};

const removeBlockItem = (blockIndex, itemIndex) => {
    props.modelValue[blockIndex].settings.items.splice(itemIndex, 1);
    emitUpdate();
};

const addBlockItem = (blockIndex) => {
    if (!props.modelValue[blockIndex].settings.items) {
        props.modelValue[blockIndex].settings.items = [];
    }
    props.modelValue[blockIndex].settings.items.push({
        title: 'New Item',
        description: 'Item description'
    });
    emitUpdate();
};

const addGalleryImage = (blockIndex) => {
    if (!props.modelValue[blockIndex].settings.images) {
        props.modelValue[blockIndex].settings.images = [];
    }
    props.modelValue[blockIndex].settings.images.push({
        url: '',
        caption: ''
    });
    emitUpdate();
};

const removeGalleryImage = (blockIndex, imageIndex) => {
    props.modelValue[blockIndex].settings.images.splice(imageIndex, 1);
    emitUpdate();
};

const addPricingPlan = (blockIndex) => {
    if (!props.modelValue[blockIndex].settings.items) {
        props.modelValue[blockIndex].settings.items = [];
    }
    props.modelValue[blockIndex].settings.items.push({
        name: 'New Plan',
        price: '$0',
        features: ['Feature 1'],
        buttonText: 'Select Plan'
    });
    emitUpdate();
};

const removePricingPlan = (blockIndex, planIndex) => {
    props.modelValue[blockIndex].settings.items.splice(planIndex, 1);
    emitUpdate();
};

const addAccordionItem = (blockIndex) => {
    if (!props.modelValue[blockIndex].settings.items) {
        props.modelValue[blockIndex].settings.items = [];
    }
    props.modelValue[blockIndex].settings.items.push({
        question: 'New Question',
        answer: 'Provide the answer here.'
    });
    emitUpdate();
};

const removeAccordionItem = (blockIndex, itemIndex) => {
    props.modelValue[blockIndex].settings.items.splice(itemIndex, 1);
    emitUpdate();
};

const addTestimonial = (blockIndex) => {
    if (!props.modelValue[blockIndex].settings.items) {
        props.modelValue[blockIndex].settings.items = [];
    }
    props.modelValue[blockIndex].settings.items.push({
        quote: 'This is an amazing product!',
        author: 'John Doe',
        role: 'CEO, Company',
        avatar: ''
    });
    emitUpdate();
};

const removeTestimonial = (blockIndex, itemIndex) => {
    props.modelValue[blockIndex].settings.items.splice(itemIndex, 1);
    emitUpdate();
};
</script>

<style scoped>
.bg-dot-pattern {
    background-image: radial-gradient(rgba(0, 0, 0, 0.05) 1px, transparent 1px);
    background-size: 20px 20px;
}
</style>
