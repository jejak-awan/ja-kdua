<template>
    <Popover :open="open" @update:open="$emit('update:open', $event)">
        <!-- Anchor the popover to the actual media node -->
        <PopoverAnchor :element="anchor" v-if="anchor" />
        <PopoverTrigger asChild v-else>
            <div class="sr-only">Properties Trigger</div>
        </PopoverTrigger>
        
        <PopoverContent 
            class="w-80 p-0 z-[100] outline-none bg-transparent border-none shadow-none" 
            align="center" 
            side="right" 
            :sideOffset="10"
        >
            <div 
                class="flex flex-col h-full w-full bg-card border border-border shadow-2xl rounded-lg overflow-hidden"
                :style="{ transform: `translate(${dragOffset.x}px, ${dragOffset.y}px)` }"
            >
                <div 
                    class="flex items-center justify-between p-3 border-b bg-muted/30 cursor-move select-none"
                    @pointerdown="startDrag"
                >
                    <div class="flex items-center gap-2">
                        <GripHorizontal class="w-3.5 h-3.5 text-muted-foreground" />
                        <h3 class="text-sm font-semibold text-foreground">Media Properties</h3>
                    </div>
                    <Button variant="ghost" size="icon" class="h-6 w-6 rounded-full hover:bg-destructive/10 hover:text-destructive" @pointerdown.stop @click="$emit('update:open', false)">
                        <X class="w-3.5 h-3.5" />
                    </Button>
                </div>

            <div class="p-0">
                <Accordion type="single" collapsible default-value="general">
                    <!-- General Settings -->
                    <AccordionItem value="general" class="border-b px-3">
                        <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">General Settings</AccordionTrigger>
                        <AccordionContent class="pt-1 pb-4 space-y-3">
                            <!-- Source URL -->
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-medium text-muted-foreground">Source URL</label>
                                <div class="flex gap-1.5">
                                    <Input v-model="form.src" placeholder="https://..." class="h-8 text-xs" />
                                </div>
                            </div>

                            <!-- Display Mode -->
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-medium text-muted-foreground">Display mode</label>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <Button 
                                        v-for="mode in [
                                            { val: 'block', lab: 'Block' },
                                            { val: 'inline', lab: 'Inline' },
                                            { val: 'float-left', lab: 'Wrap Left' },
                                            { val: 'float-right', lab: 'Wrap Right' }
                                        ]" 
                                        :key="mode.val"
                                        variant="outline"
                                        size="sm"
                                        class="text-[10px] h-7 px-1 font-normal"
                                        :class="{ 'bg-primary text-primary-foreground border-primary font-medium': form.displayMode === mode.val }"
                                        @click="form.displayMode = mode.val"
                                    >
                                        {{ mode.lab }}
                                    </Button>
                                </div>
                            </div>

                            <!-- Alignment -->
                            <div class="space-y-1.5" v-if="form.displayMode === 'block'">
                                <label class="text-[11px] font-medium text-muted-foreground">Alignment</label>
                                <div class="flex gap-1.5">
                                    <Button 
                                        v-for="align in ['left', 'center', 'right']" 
                                        :key="align"
                                        variant="outline"
                                        size="sm"
                                        class="flex-1 capitalize h-7 text-[10px] font-normal"
                                        :class="{ 'bg-primary text-primary-foreground border-primary font-medium': form.align === align }"
                                        @click="form.align = align"
                                    >
                                        {{ align }}
                                    </Button>
                                </div>
                            </div>

                            <!-- Alt Text -->
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-medium text-muted-foreground">Alt text</label>
                                <Input v-model="form.alt" placeholder="Description..." class="h-8 text-xs" />
                            </div>
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Dimensions -->
                    <AccordionItem value="dimensions" class="border-b px-3">
                        <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">Dimensions</AccordionTrigger>
                        <AccordionContent class="pt-1 pb-4">
                            <div class="flex items-end gap-2">
                                <div class="space-y-1.5 flex-1">
                                    <label class="text-[11px] font-medium text-muted-foreground">Width</label>
                                    <Input v-model="form.width" placeholder="auto" class="h-8 text-xs" @input="onDimensionChange('width')" />
                                </div>
                                
                                <div class="pb-1">
                                    <Button 
                                        variant="ghost" 
                                        size="icon" 
                                        class="h-6 w-6 text-muted-foreground"
                                        :class="{ 'bg-muted text-primary': constrainProportions }"
                                        @click="constrainProportions = !constrainProportions"
                                        title="Constrain proportions"
                                    >
                                        <LinkIcon v-if="constrainProportions" class="w-3.5 h-3.5" />
                                        <UnlinkIcon v-else class="w-3.5 h-3.5" />
                                    </Button>
                                </div>

                                <div class="space-y-1.5 flex-1">
                                    <label class="text-[11px] font-medium text-muted-foreground">Height</label>
                                    <Input v-model="form.height" placeholder="auto" class="h-8 text-xs" @input="onDimensionChange('height')" />
                                </div>
                            </div>
                        </AccordionContent>
                    </AccordionItem>
                    
                    <!-- Video Settings -->
                    <AccordionItem value="video" class="border-b px-3" v-if="isVideoNode">
                         <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">Video Playback</AccordionTrigger>
                         <AccordionContent class="pt-1 pb-4 space-y-3">
                            <div class="flex items-center justify-between group cursor-pointer" @click="form.autoplay = !form.autoplay">
                                <label class="text-[11px] font-medium text-muted-foreground group-hover:text-foreground cursor-pointer">Autoplay</label>
                                <input type="checkbox" v-model="form.autoplay" class="accent-primary h-4 w-4 rounded border-border cursor-pointer" />
                            </div>
                            <div class="flex items-center justify-between group cursor-pointer" @click="form.controls = !form.controls">
                                <label class="text-[11px] font-medium text-muted-foreground group-hover:text-foreground cursor-pointer">Controls</label>
                                <input type="checkbox" v-model="form.controls" class="accent-primary h-4 w-4 rounded border-border cursor-pointer" />
                            </div>
                            <div class="flex items-center justify-between group cursor-pointer" @click="form.loop = !form.loop">
                                <label class="text-[11px] font-medium text-muted-foreground group-hover:text-foreground cursor-pointer">Loop</label>
                                <input type="checkbox" v-model="form.loop" class="accent-primary h-4 w-4 rounded border-border cursor-pointer" />
                            </div>
                         </AccordionContent>
                    </AccordionItem>

                    <!-- Appearance -->
                    <AccordionItem value="appearance" class="px-3 border-b-0">
                        <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">Appearance</AccordionTrigger>
                        <AccordionContent class="pt-1 pb-4 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-[11px] font-medium text-muted-foreground">Radius (px)</label>
                                    <Input v-model="form.borderRadius" type="number" placeholder="0" class="h-8 text-xs" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[11px] font-medium text-muted-foreground">Border (px)</label>
                                    <Input v-model="form.borderWidth" type="number" placeholder="0" class="h-8 text-xs" />
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-medium text-muted-foreground">Border color</label>
                                <div class="flex gap-2 items-center">
                                    <ColorPicker v-model="form.borderColor" title="Border Color">
                                        <Button variant="outline" size="icon" class="h-8 w-8 p-0 shrink-0 relative overflow-hidden">
                                             <Palette class="w-4 h-4 text-muted-foreground" v-if="!form.borderColor" />
                                             <div v-else class="absolute inset-0" :style="{ backgroundColor: form.borderColor }"></div>
                                        </Button>
                                    </ColorPicker>
                                    <Input v-model="form.borderColor" placeholder="None" class="h-8 text-xs flex-1 uppercase font-mono" />
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-medium text-muted-foreground">Wrap spacing (px)</label>
                                <Input v-model="form.margin" type="number" placeholder="16" class="h-8 text-xs" />
                            </div>
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>

            <div class="flex justify-end gap-2 p-3 border-t bg-muted/10">
                <Button variant="outline" size="sm" class="h-7 text-xs px-3" @click="$emit('update:open', false)">Cancel</Button>
                <Button size="sm" class="h-7 text-xs px-3" @click="save">Save changes</Button>
            </div>
            </div>
        </PopoverContent>
    </Popover>
</template>

<script setup>
import { ref, watch, reactive, computed } from 'vue'
import { X, GripHorizontal, Link as LinkIcon, Unlink as UnlinkIcon, Palette } from 'lucide-vue-next'
import Button from '@/components/ui/button.vue'
import Input from '@/components/ui/input.vue'
import Popover from '@/components/ui/popover.vue'
import PopoverTrigger from '@/components/ui/popover-trigger.vue'
import PopoverContent from '@/components/ui/popover-content.vue'
import { PopoverAnchor } from 'radix-vue'
import Accordion from '@/components/ui/accordion.vue'
import AccordionItem from '@/components/ui/accordion-item.vue'
import AccordionTrigger from '@/components/ui/accordion-trigger.vue'
import AccordionContent from '@/components/ui/accordion-content.vue'
import ColorPicker from '@/components/ui/color-picker.vue'

const props = defineProps({
    open: Boolean,
    node: Object,
    anchor: [Object, HTMLElement]
})

const emit = defineEmits(['update:open', 'save'])

const form = ref({
    src: '',
    width: '',
    height: '',
    borderRadius: '0',
    borderWidth: '0',
    borderColor: '',
    align: 'center',
    alt: '',
    displayMode: 'block',
    margin: '16',
    // Video specific
    autoplay: false,
    controls: true,
    loop: false
})

const constrainProportions = ref(true)
const isVideoNode = computed(() => props.node?.type.name === 'video')

// Dragging Logic (unchanged)
const dragOffset = reactive({ x: 0, y: 0 })
let isDragging = false
let startX = 0
let startY = 0

const startDrag = (e) => {
    isDragging = true
    startX = e.clientX - dragOffset.x
    startY = e.clientY - dragOffset.y
    document.addEventListener('pointermove', onDrag)
    document.addEventListener('pointerup', stopDrag)
    document.body.style.userSelect = 'none'
}

const onDrag = (e) => {
    if (!isDragging) return
    dragOffset.x = e.clientX - startX
    dragOffset.y = e.clientY - startY
}

const stopDrag = () => {
    isDragging = false
    document.removeEventListener('pointermove', onDrag)
    document.removeEventListener('pointerup', stopDrag)
    document.body.style.userSelect = ''
}

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        dragOffset.x = 0
        dragOffset.y = 0
        
        if (props.node) {
            const attrs = props.node.attrs
            form.value = {
                src: attrs.src || '',
                width: attrs.width || '',
                height: attrs.height || '',
                borderRadius: parseInt(attrs.borderRadius) || 0,
                borderWidth: parseInt(attrs.borderWidth) || 0,
                borderColor: attrs.borderColor || '',
                align: attrs.align || 'center',
                alt: attrs.alt || '',
                displayMode: attrs.displayMode || 'block',
                margin: attrs.margin ? parseInt(attrs.margin) : 16,
                // Video attrs (with fallbacks)
                autoplay: attrs.autoplay !== undefined ? attrs.autoplay : false,
                controls: attrs.controls !== undefined ? attrs.controls : true,
                loop: attrs.loop !== undefined ? attrs.loop : false
            }
        }
    }
})

function onDimensionChange(changedField) {
    if (!constrainProportions.value) return
    
    // If one field changes to a value (not empty), set the other to 'auto' to maintain aspect ratio
    // If setting to empty, leave other as is.
    if (changedField === 'width' && form.value.width) {
        form.value.height = 'auto'
    } else if (changedField === 'height' && form.value.height) {
        form.value.width = 'auto'
    }
}

const save = () => {
    // Only include video attrs if it is a video node
    const baseAttrs = {
        ...form.value,
        borderRadius: form.value.borderRadius ? `${form.value.borderRadius}px` : null,
        borderWidth: form.value.borderWidth ? `${form.value.borderWidth}px` : '0px',
        margin: form.value.margin ? `${form.value.margin}px` : '0px'
    }
    
    if (isVideoNode.value) {
        baseAttrs.autoplay = form.value.autoplay
        baseAttrs.controls = form.value.controls
        baseAttrs.loop = form.value.loop
    } else {
        // Clean up video attrs if not video
        delete baseAttrs.autoplay
        delete baseAttrs.controls
        delete baseAttrs.loop
    }

    emit('save', baseAttrs)
    emit('update:open', false)
}
</script>
