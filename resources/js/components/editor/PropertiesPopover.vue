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
                class="flex flex-col h-full w-full bg-card border border-border shadow-2xl rounded-md overflow-hidden"
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
                    <Button variant="ghost" size="icon" class="h-6 w-6" @pointerdown.stop @click="$emit('update:open', false)">
                        <X class="w-3.5 h-3.5 text-foreground" />
                    </Button>
                </div>

            <div class="p-0">
                <Accordion type="single" collapsible default-value="general">
                    <!-- General Settings -->
                    <AccordionItem value="general" class="border-b px-3">
                        <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">General Settings</AccordionTrigger>
                        <AccordionContent class="pt-1 pb-4 space-y-3">
                            <!-- Source URL -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-tight">Source URL</label>
                                <div class="flex gap-1.5">
                                    <Input v-model="form.src" placeholder="https://..." class="h-7 text-xs" />
                                </div>
                            </div>

                            <!-- Display Mode -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-tight">Display Mode</label>
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
                                        class="text-[10px] h-7 px-1"
                                        :class="{ 'bg-primary text-primary-foreground border-primary': form.displayMode === mode.val }"
                                        @click="form.displayMode = mode.val"
                                    >
                                        {{ mode.lab }}
                                    </Button>
                                </div>
                            </div>

                            <!-- Alignment -->
                            <div class="space-y-1" v-if="form.displayMode === 'block'">
                                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-tight">Alignment</label>
                                <div class="flex gap-1.5">
                                    <Button 
                                        v-for="align in ['left', 'center', 'right']" 
                                        :key="align"
                                        variant="outline"
                                        size="sm"
                                        class="flex-1 capitalize h-7 text-[10px]"
                                        :class="{ 'bg-primary text-primary-foreground border-primary': form.align === align }"
                                        @click="form.align = align"
                                    >
                                        {{ align }}
                                    </Button>
                                </div>
                            </div>

                            <!-- Alt Text -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-tight">Alt Text</label>
                                <Input v-model="form.alt" placeholder="Description..." class="h-7 text-xs" />
                            </div>
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Dimensions -->
                    <AccordionItem value="dimensions" class="border-b px-3">
                        <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">Dimensions</AccordionTrigger>
                        <AccordionContent class="pt-1 pb-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-tight">Width</label>
                                    <Input v-model="form.width" placeholder="100% / 500px" class="h-7 text-xs" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-tight">Height</label>
                                    <Input v-model="form.height" placeholder="auto" class="h-7 text-xs" />
                                </div>
                            </div>
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Appearance -->
                    <AccordionItem value="appearance" class="px-3 border-b-0">
                        <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">Appearance</AccordionTrigger>
                        <AccordionContent class="pt-1 pb-4 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-tight">Radius (px)</label>
                                    <Input v-model="form.borderRadius" type="number" placeholder="0" class="h-7 text-xs" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-tight">Border (px)</label>
                                    <Input v-model="form.borderWidth" type="number" placeholder="0" class="h-7 text-xs" />
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-tight">Border Color</label>
                                <div class="flex gap-1.5 items-center">
                                    <Input v-model="form.borderColor" type="color" class="h-7 w-8 p-0.5 border-none bg-transparent cursor-pointer" />
                                    <Input v-model="form.borderColor" placeholder="#000000" class="h-7 text-xs flex-1 uppercase" />
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-tight">Wrap Spacing (px)</label>
                                <Input v-model="form.margin" type="number" placeholder="16" class="h-7 text-xs" />
                            </div>
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>

            <div class="flex justify-end gap-2 p-3 border-t bg-muted/10">
                <Button variant="outline" size="sm" class="h-7 text-xs px-3" @click="$emit('update:open', false)">Cancel</Button>
                <Button size="sm" class="h-7 text-xs px-3" @click="save">Save</Button>
            </div>
            </div>
        </PopoverContent>
    </Popover>
</template>

<script setup>
import { ref, watch, reactive } from 'vue'
import { X, GripHorizontal } from 'lucide-vue-next'
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
    borderColor: '#000000',
    align: 'center',
    alt: '',
    displayMode: 'block',
    margin: '16'
})

// Dragging Logic
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
    
    // Prevent text selection while dragging
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
        // Reset drag offset when opening
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
                borderColor: attrs.borderColor || '#000000',
                align: attrs.align || 'center',
                alt: attrs.alt || '',
                displayMode: attrs.displayMode || 'block',
                margin: attrs.margin ? parseInt(attrs.margin) : 16
            }
        }
    }
})

const save = () => {
    emit('save', {
        ...form.value,
        borderRadius: form.value.borderRadius ? `${form.value.borderRadius}px` : null,
        borderWidth: form.value.borderWidth ? `${form.value.borderWidth}px` : '0px',
        margin: form.value.margin ? `${form.value.margin}px` : '0px'
    })
    emit('update:open', false)
}
</script>
