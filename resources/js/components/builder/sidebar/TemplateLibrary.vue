<template>
    <Dialog :open="builder.showTemplateLibrary.value" @update:open="builder.showTemplateLibrary.value = false">
        <DialogContent class="sm:max-w-4xl h-[80vh] flex flex-col p-0 z-[9999]">
            <DialogHeader class="p-6 border-b">
                <DialogTitle>Template Library</DialogTitle>
                <DialogDescription>Choose a professionally designed block or full layout template.</DialogDescription>
            </DialogHeader>
            <div class="flex-1 p-8 overflow-y-auto grid grid-cols-2 md:grid-cols-3 gap-6 bg-muted/10">
                <div v-for="i in 6" :key="i" class="group relative aspect-[4/3] rounded-xl overflow-hidden border bg-background hover:border-primary transition-all cursor-pointer">
                    <div class="absolute inset-0 bg-dot-pattern opacity-40"></div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center gap-2">
                        <LayoutTemplate class="w-8 h-8 opacity-10 group-hover:opacity-100 transition-opacity text-primary" />
                        <span class="text-xs font-bold uppercase tracking-widest text-muted-foreground group-hover:text-primary">Featured Layout {{ i }}</span>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                        <Button size="xs" class="w-full h-8 shadow-lg" @click="insertTemplate(i)">Insert Template</Button>
                    </div>
                </div>
            </div>
            <DialogFooter class="p-4 border-t bg-muted/30">
                <Button variant="ghost" @click="builder.showTemplateLibrary.value = false">Close</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { inject } from 'vue';
import { LayoutTemplate } from 'lucide-vue-next';
import Button from '@/components/ui/button.vue'; // Ensure relative path or alias works
import Dialog from '@/components/ui/dialog.vue';
import DialogContent from '@/components/ui/dialog-content.vue';
import DialogHeader from '@/components/ui/dialog-header.vue';
import DialogTitle from '@/components/ui/dialog-title.vue';
import DialogDescription from '@/components/ui/dialog-description.vue';
import DialogFooter from '@/components/ui/dialog-footer.vue';
import { toast } from '@/services/toast'; // Using alias if available, else relative

const builder = inject('builder');

// We need access to modelValue to push new blocks.
// 'builder' object should probably expose a method for this, OR we access modelValue directly if provided.
// In our useBuilder, we didn't include addBlock yet because it depends on props.modelValue or a Ref passed to it.
// Strategy: The parent (Builder.vue) provides 'blocks' array purely? Or useBuilder manages it?
// Let's assume useBuilder is enhanced to include operations, OR we rely on a method 'insertBlock' provided by builder context.

// Wait, I need to update useBuilder to handle `insertBlock`.
// OR builder context has `blocks` ref which IS the modelValue (synced).

// Let's implement insertTemplate using a method from builder context.
const insertTemplate = (index) => {
    // Mock template insertion
    const newBlock = {
        id: crypto.randomUUID(),
        type: 'hero',
        settings: {
            title: `Featured Template ${index}`,
            subtitle: 'This is a professionally designed template block.',
            bgImage: '',
            primaryButtonText: 'Get Started',
            primaryButtonUrl: '#',
            secondaryButtonText: 'Learn More',
            secondaryButtonUrl: '#',
            visibility: { desktop: true, tablet: true, mobile: true },
            paddingTop: '6rem',
            paddingBottom: '6rem',
            textAlign: 'center'
        }
    };
    
    // We call a method on the builder context
    if (builder.addBlock) {
        builder.addBlock(newBlock);
        toast.success('Template inserted successfully');
        builder.showTemplateLibrary.value = false;
    } else {
        console.error("Link to builder logic missing");
    }
};
</script>
