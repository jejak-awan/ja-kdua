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
                        <h3 class="text-sm font-semibold text-foreground">{{ t('features.editor.properties.title') }}</h3>
                    </div>
                    <Button variant="ghost" size="icon" class="h-6 w-6 rounded-full hover:bg-destructive/10 hover:text-destructive" @pointerdown.stop @click="$emit('update:open', false)">
                        <X class="w-3.5 h-3.5" />
                    </Button>
                </div>

            <div class="p-0">
                <Accordion type="single" collapsible default-value="general">
                    <!-- HTML Content (Embed Specific) -->
                    <AccordionItem value="html-content" class="border-b px-3" v-if="isHtmlEmbedNode">
                        <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">{{ t('features.editor.properties.html') }}</AccordionTrigger>
                        <AccordionContent class="pt-1 pb-4">
                            <textarea 
                                v-model="form.html" 
                                class="flex min-h-[120px] w-full rounded-md border border-input bg-background px-3 py-2 text-xs shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 font-mono"
                                :placeholder="t('features.editor.placeholders.html')"
                            ></textarea>
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Icon Settings -->
                    <AccordionItem value="icon-settings" class="border-b px-3" v-if="isIconNode">
                        <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">Icon Style</AccordionTrigger>
                        <AccordionContent class="pt-1 pb-4 space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <!-- Size -->
                                <div class="space-y-1.5">
                                    <label class="text-[11px] font-medium text-muted-foreground">Size</label>
                                    <Input v-model="form.size" placeholder="1em" class="h-8 text-xs" />
                                </div>
                                <!-- Color -->
                                <div class="space-y-1.5">
                                    <label class="text-[11px] font-medium text-muted-foreground">Color</label>
                                    <div class="flex gap-2 items-center">
                                        <ColorPicker v-model="form.color">
                                            <Button variant="outline" size="icon" class="h-8 w-8 p-0 shrink-0 relative overflow-hidden">
                                                <div class="absolute inset-0" :style="{ backgroundColor: form.color === 'currentColor' ? '#000' : form.color }"></div>
                                            </Button>
                                        </ColorPicker>
                                        <Input v-model="form.color" class="h-8 text-xs flex-1 uppercase font-mono" />
                                    </div>
                                </div>
                            </div>

                            <!-- Stroke & Rotate -->
                            <div class="grid grid-cols-2 gap-3">
                                 <div class="space-y-1.5">
                                    <label class="text-[11px] font-medium text-muted-foreground flex justify-between">
                                        Stroke Width <span>{{ form.strokeWidth }}px</span>
                                    </label>
                                    <input type="range" v-model.number="form.strokeWidth" min="0.5" max="4" step="0.5" class="w-full h-1.5 bg-secondary rounded-lg appearance-none cursor-pointer" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[11px] font-medium text-muted-foreground flex justify-between">
                                        Rotate <span>{{ form.rotate }}°</span>
                                    </label>
                                     <input type="range" v-model.number="form.rotate" min="0" max="360" step="15" class="w-full h-1.5 bg-secondary rounded-lg appearance-none cursor-pointer" />
                                </div>
                            </div>
                            
                            <!-- Opacity -->
                             <div class="space-y-1.5">
                                <label class="text-[11px] font-medium text-muted-foreground flex justify-between">
                                    Opacity <span>{{ form.opacity * 100 }}%</span>
                                </label>
                                <input type="range" v-model.number="form.opacity" min="0" max="1" step="0.1" class="w-full h-1.5 bg-secondary rounded-lg appearance-none cursor-pointer" />
                            </div>

                             <!-- Background & Padding -->
                             <Accordion type="single" collapsible>
                                 <AccordionItem value="icon-appearance" class="border-t pt-2 -mx-0 border-b-0">
                                    <AccordionTrigger class="text-[11px] font-medium py-1.5 hover:no-underline text-muted-foreground">Background & Spacing</AccordionTrigger>
                                    <AccordionContent class="pt-2 pb-0 space-y-3">
                                        <div class="space-y-1.5">
                                            <label class="text-[11px] font-medium text-muted-foreground">Background Color</label>
                                            <div class="flex gap-2 items-center">
                                                <ColorPicker v-model="form.backgroundColor">
                                                    <Button variant="outline" size="icon" class="h-8 w-8 p-0 shrink-0 relative overflow-hidden">
                                                        <Palette class="w-3.5 h-3.5 text-muted-foreground" v-if="!form.backgroundColor" />
                                                        <div v-else class="absolute inset-0" :style="{ backgroundColor: form.backgroundColor }"></div>
                                                    </Button>
                                                </ColorPicker>
                                                <Input v-model="form.backgroundColor" placeholder="None" class="h-8 text-xs flex-1 uppercase font-mono" />
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="space-y-1.5">
                                                <label class="text-[11px] font-medium text-muted-foreground">Padding</label>
                                                <Input v-model="form.padding" placeholder="0px" class="h-8 text-xs" />
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-[11px] font-medium text-muted-foreground">Radius</label>
                                                <Input v-model="form.borderRadius" placeholder="0px" class="h-8 text-xs" />
                                            </div>
                                        </div>
                                    </AccordionContent>
                                 </AccordionItem>
                             </Accordion>
                        </AccordionContent>
                    </AccordionItem>

                    <!-- General Settings (Not for Embeds or Shapes) -->
                    <AccordionItem value="general" class="border-b px-3" v-if="!isHtmlEmbedNode && !isIconNode">
                        <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">{{ t('features.editor.properties.general') }}</AccordionTrigger>
                        <AccordionContent class="pt-1 pb-4 space-y-3">
                            <!-- Source URL -->
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-medium text-muted-foreground">{{ t('features.editor.fields.source') }}</label>
                                <div class="flex gap-1.5">
                                    <Input v-model="form.src" placeholder="https://..." class="h-8 text-xs" />
                                </div>
                            </div>
                            
                            <!-- Display Mode -->
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-medium text-muted-foreground">{{ t('features.editor.fields.displayMode') }}</label>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <Button 
                                        v-for="mode in [
                                            { val: 'block', lab: t('features.editor.values.block') },
                                            { val: 'inline', lab: t('features.editor.values.inline') },
                                            { val: 'float-left', lab: t('features.editor.values.floatLeft') },
                                            { val: 'float-right', lab: t('features.editor.values.floatRight') }
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
                                <label class="text-[11px] font-medium text-muted-foreground">{{ t('features.editor.fields.alignment') }}</label>
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
                                <label class="text-[11px] font-medium text-muted-foreground">{{ t('features.editor.fields.altText') }}</label>
                                <Input v-model="form.alt" :placeholder="t('features.editor.placeholders.alt')" class="h-8 text-xs" />
                            </div>
                        </AccordionContent>
                    </AccordionItem>

                    <!-- Dimensions -->
                    <AccordionItem value="dimensions" class="border-b px-3" v-if="!isIconNode">
                        <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">{{ t('features.editor.properties.dimensions') }}</AccordionTrigger>
                        <AccordionContent class="pt-1 pb-4">
                            <div class="flex items-end gap-2">
                                <div class="space-y-1.5 flex-1">
                                    <label class="text-[11px] font-medium text-muted-foreground">{{ t('features.editor.fields.width') }}</label>
                                    <Input v-model="form.width" placeholder="auto" class="h-8 text-xs" @input="onDimensionChange('width')" />
                                </div>
                                
                                <div class="pb-1">
                                    <Button 
                                        variant="ghost" 
                                        size="icon" 
                                        class="h-6 w-6 text-muted-foreground"
                                        :class="{ 'bg-muted text-primary': constrainProportions }"
                                        @click="constrainProportions = !constrainProportions"
                                        :title="t('features.editor.actions.constrainProportions')"
                                    >
                                        <LinkIcon v-if="constrainProportions" class="w-3.5 h-3.5" />
                                        <UnlinkIcon v-else class="w-3.5 h-3.5" />
                                    </Button>
                                </div>

                                <div class="space-y-1.5 flex-1">
                                    <label class="text-[11px] font-medium text-muted-foreground">{{ t('features.editor.fields.height') }}</label>
                                    <Input v-model="form.height" placeholder="auto" class="h-8 text-xs" @input="onDimensionChange('height')" />
                                </div>
                            </div>
                        </AccordionContent>
                    </AccordionItem>
                    
                    <!-- Video Settings -->
                    <AccordionItem value="video" class="border-b px-3" v-if="isVideoNode || isYoutubeEmbed">
                         <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">{{ t('features.editor.properties.video') }}</AccordionTrigger>
                         <AccordionContent class="pt-1 pb-4 space-y-3">
                            <div class="flex items-center justify-between group">
                                <label for="video-autoplay" class="text-[11px] font-medium text-muted-foreground group-hover:text-foreground cursor-pointer select-none">{{ t('features.editor.fields.autoplay') }}</label>
                                <Switch id="video-autoplay" v-model:checked="form.autoplay" class="scale-75 origin-right" />
                            </div>
                            <div class="flex items-center justify-between group">
                                <label for="video-controls" class="text-[11px] font-medium text-muted-foreground group-hover:text-foreground cursor-pointer select-none">{{ t('features.editor.fields.controls') }}</label>
                                <Switch id="video-controls" v-model:checked="form.controls" class="scale-75 origin-right" />
                            </div>
                            <div class="flex items-center justify-between group">
                                <label for="video-loop" class="text-[11px] font-medium text-muted-foreground group-hover:text-foreground cursor-pointer select-none">{{ t('features.editor.fields.loop') }}</label>
                                <Switch id="video-loop" v-model:checked="form.loop" class="scale-75 origin-right" />
                            </div>
                         </AccordionContent>
                    </AccordionItem>

                    <!-- Appearance -->
                    <AccordionItem value="appearance" class="px-3 border-b-0" v-if="!isIconNode">
                        <AccordionTrigger class="text-xs font-semibold py-2.5 hover:no-underline">{{ t('features.editor.properties.appearance') }}</AccordionTrigger>
                        <AccordionContent class="pt-1 pb-4 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-[11px] font-medium text-muted-foreground">{{ t('features.editor.fields.borderRadius') }}</label>
                                    <Input v-model="form.borderRadius" type="number" placeholder="0" class="h-8 text-xs" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[11px] font-medium text-muted-foreground">{{ t('features.editor.fields.borderWidth') }}</label>
                                    <Input v-model="form.borderWidth" type="number" placeholder="0" class="h-8 text-xs" />
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-medium text-muted-foreground">{{ t('features.editor.fields.borderColor') }}</label>
                                <div class="flex gap-2 items-center">
                                    <ColorPicker v-model="form.borderColor" :title="t('features.editor.fields.borderColor')">
                                        <Button variant="outline" size="icon" class="h-8 w-8 p-0 shrink-0 relative overflow-hidden">
                                             <Palette class="w-4 h-4 text-muted-foreground" v-if="!form.borderColor" />
                                             <div v-else class="absolute inset-0" :style="{ backgroundColor: form.borderColor }"></div>
                                        </Button>
                                    </ColorPicker>
                                    <Input v-model="form.borderColor" :placeholder="t('features.editor.placeholders.none')" class="h-8 text-xs flex-1 uppercase font-mono" />
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-medium text-muted-foreground">{{ t('features.editor.fields.margin') }}</label>
                                <Input v-model="form.margin" type="number" placeholder="16" class="h-8 text-xs" />
                            </div>
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>

            <div class="flex justify-end gap-2 p-3 border-t bg-muted/10">
                <Button variant="outline" size="sm" class="h-7 text-xs px-3" @click="$emit('update:open', false)">{{ t('features.editor.actions.cancel') }}</Button>
                <Button size="sm" class="h-7 text-xs px-3" @click="save">{{ t('features.editor.actions.save') }}</Button>
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
import Switch from '@/components/ui/switch.vue'
import Select from '@/components/ui/select.vue'
import SelectContent from '@/components/ui/select-content.vue'
import SelectItem from '@/components/ui/select-item.vue'
import SelectTrigger from '@/components/ui/select-trigger.vue'
import SelectValue from '@/components/ui/select-value.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    open: Boolean,
    node: Object,
    anchor: [Object, HTMLElement]
})

const emit = defineEmits(['update:open', 'save'])



const constrainProportions = ref(true)


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

const isVideoNode = computed(() => props.node?.type === 'video')
const isHtmlEmbedNode = computed(() => props.node?.type === 'htmlEmbed')
const isIconNode = computed(() => props.node?.type === 'icon')

// Initialize form
const form = ref({
    // Image/Video props
    width: '',
    height: '',
    alt: '',
    title: '',
    className: '',
    // Icon specific
    size: '1em',
    size: '1em',
    color: 'currentColor',
    strokeWidth: 2,
    rotate: 0,
    backgroundColor: '',
    opacity: 1,
    // Video specific
    autoplay: false,
    controls: true,
    loop: false,
    muted: false,
    // HTML Embed specific
    htmlContent: '',
    isInteractMode: false,
    // Common
    borderRadius: '4px',
    borderWidth: '0',
    borderColor: null,
    borderStyle: 'none',
    margin: '0',
    padding: '0'
})

// Helper to detect YouTube
const isYoutubeEmbed = computed(() => {
    return isHtmlEmbedNode.value && form.value.html && (form.value.html.includes('youtube.com/embed') || form.value.html.includes('youtu.be'))
})

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
                title: attrs.title || '',
                className: attrs.class || '',

                // Icon attrs
                // Icon attrs
                size: attrs.size || '1em',
                color: attrs.color || 'currentColor',
                strokeWidth: attrs.strokeWidth || 2,
                rotate: attrs.rotate || 0,
                backgroundColor: attrs.backgroundColor || '',
                opacity: attrs.opacity !== undefined ? attrs.opacity : 1, // Default 1
                
                borderRadius: attrs.borderRadius || '0', // Can be string for icons '50%'
                borderWidth: parseInt(attrs.borderWidth) || 0,
                borderColor: attrs.borderColor || '',
                padding: attrs.padding || '0',
                align: attrs.align || 'center',
                alt: attrs.alt || '',
                displayMode: attrs.displayMode || 'block',
                margin: attrs.margin ? parseInt(attrs.margin) : 16,
                // Video attrs (with fallbacks)
                autoplay: attrs.autoplay !== undefined ? attrs.autoplay : false,
                controls: attrs.controls !== undefined ? attrs.controls : true,
                loop: attrs.loop !== undefined ? attrs.loop : false,
                // Embed specific
                html: attrs.html || ''
            }

            // Sync controls state from YouTube URL if applicable
            if (isYoutubeEmbed.value) {
                const srcMatch = form.value.html.match(/src=["']([^"']+)["']/)
                if (srcMatch && srcMatch[1]) {
                    const url = srcMatch[1]
                    form.value.autoplay = url.includes('autoplay=1')
                    form.value.controls = !url.includes('controls=0') // YouTube default is 1 (show)
                    form.value.loop = url.includes('loop=1')
                }
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
        borderRadius: form.value.borderRadius ? `${form.value.borderRadius.toString().replace('px','')}px` : null,
        borderWidth: form.value.borderWidth ? `${form.value.borderWidth}px` : '0px',
        borderColor: form.value.borderColor || null,
        borderStyle: form.value.borderStyle || 'none',
        margin: form.value.margin ? `${form.value.margin}px` : '0px',
        padding: form.value.padding ? `${form.value.padding}px` : '0px'
    }
    
    if (isVideoNode.value) {
        baseAttrs.autoplay = form.value.autoplay
        baseAttrs.controls = form.value.controls
        baseAttrs.loop = form.value.loop
    } else if (isHtmlEmbedNode.value) {
        let html = form.value.html

        // Intelligent YouTube Param Injection
        if (isYoutubeEmbed.value) {
           const srcMatch = html.match(/src=["']([^"']+)["']/)
           if (srcMatch && srcMatch[1]) {
                let url = srcMatch[1]
                let params = []

                // Autoplay (Note: often requires mute=1 for browser policies)
                if (form.value.autoplay) {
                    if (!url.includes('autoplay=1')) params.push('autoplay=1')
                    if (!url.includes('mute=1')) params.push('mute=1') 
                } else {
                     url = url.replace(/autoplay=1/g, '').replace(/mute=1/g, '')
                }

                // Controls
                if (!form.value.controls) {
                    if (!url.includes('controls=0')) params.push('controls=0')
                } else {
                    url = url.replace(/controls=0/g, '')
                }

                // Loop (Requires playlist=VIDEO_ID for single video loop)
                if (form.value.loop) {
                    if (!url.includes('loop=1')) params.push('loop=1')
                    
                    // Extract Video ID for playlist param
                    // Supports: embed/ID, v=ID, youtu.be/ID
                    const idMatch = url.match(/(?:embed\/|v=|youtu\.be\/)([^?&"']{11})/)
                    if (idMatch && idMatch[1]) {
                        const videoId = idMatch[1]
                        if (!url.includes(`playlist=${videoId}`)) {
                            params.push(`playlist=${videoId}`)
                        }
                    }
                } else {
                    url = url.replace(/loop=1/g, '').replace(/playlist=[^&]*/g, '')
                }
                
                // Clean up any double && or ?? from naive replace
                url = url.replace(/&+/g, '&').replace(/\?&/g, '?')
                if (url.endsWith('&') || url.endsWith('?')) url = url.slice(0, -1)

                // Append new params
                if (params.length > 0) {
                    const separator = url.includes('?') ? '&' : '?'
                    url = `${url}${separator}${params.join('&')}`
                }
                
                // Replace in HTML
               html = html.replace(srcMatch[1], url)
           }
        }

        baseAttrs.html = html
        
        // Auto-extract dimensions from HTML if it contains iframe/embed
        // Only if width/height are currently empty or auto
        if (baseAttrs.html && (!baseAttrs.width || baseAttrs.width === 'auto' || !baseAttrs.height || baseAttrs.height === 'auto')) {
            const widthMatch = baseAttrs.html.match(/(?:width|WIDTH)=["']?(\d+)(?:px)?["']?/)
            const heightMatch = baseAttrs.html.match(/(?:height|HEIGHT)=["']?(\d+)(?:px)?["']?/)
            
            if (widthMatch && widthMatch[1]) {
                baseAttrs.width = `${widthMatch[1]}px`
            }
            if (heightMatch && heightMatch[1]) {
                baseAttrs.height = `${heightMatch[1]}px`
            }
        }
    } else if (isIconNode.value) {
        baseAttrs.size = form.value.size
        baseAttrs.color = form.value.color
        baseAttrs.strokeWidth = form.value.strokeWidth
        baseAttrs.rotate = form.value.rotate
        baseAttrs.backgroundColor = form.value.backgroundColor
        
        // Ensure units for CSS properties
        const ensureUnit = (val) => (val && !isNaN(val)) ? `${val}px` : val
        baseAttrs.borderRadius = ensureUnit(form.value.borderRadius)
        baseAttrs.padding = ensureUnit(form.value.padding)
        baseAttrs.opacity = form.value.opacity
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
