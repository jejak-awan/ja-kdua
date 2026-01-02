<template>
    <div 
        v-if="builder.contextMenu.value.visible" 
        class="fixed z-[9999] min-w-[200px] overflow-hidden rounded-md border border-border bg-popover p-1 text-popover-foreground shadow-lg animate-in fade-in-80 zoom-in-95 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 slide-in-from-top-2"
        :style="{
            top: `${builder.contextMenu.value.y}px`,
            left: `${builder.contextMenu.value.x}px`
        }"
        @contextmenu.prevent
    >
        <div class="px-2 py-1.5 text-xs font-semibold text-muted-foreground border-b border-border/50 mb-1">
            {{ builder.getBlockLabel(builder.contextMenu.value.type) }}
        </div>

        <button @click="handleAction('edit')" class="relative flex w-full cursor-default select-none items-center rounded-sm px-2 py-1.5 text-xs outline-none hover:bg-accent hover:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50">
            <Edit class="mr-2 h-3.5 w-3.5" />
            <span>Edit Settings</span>
            <span class="ml-auto text-[10px] tracking-widest opacity-60">Click</span>
        </button>

        <button @click="handleAction('duplicate')" class="relative flex w-full cursor-default select-none items-center rounded-sm px-2 py-1.5 text-xs outline-none hover:bg-accent hover:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50">
            <Copy class="mr-2 h-3.5 w-3.5" />
            <span>Duplicate</span>
            <span class="ml-auto text-[10px] tracking-widest opacity-60">Ctrl+D</span>
        </button>

        <div class="my-1 h-px bg-muted" />

        <button @click="handleAction('copy')" class="relative flex w-full cursor-default select-none items-center rounded-sm px-2 py-1.5 text-xs outline-none hover:bg-accent hover:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50">
            <ClipboardCopy class="mr-2 h-3.5 w-3.5" />
            <span>Copy</span>
            <span class="ml-auto text-[10px] tracking-widest opacity-60">Ctrl+C</span>
        </button>

        <button @click="handleAction('paste')" :disabled="!builder.canPaste.value" class="relative flex w-full cursor-default select-none items-center rounded-sm px-2 py-1.5 text-xs outline-none hover:bg-accent hover:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50 disabled:opacity-30">
            <ClipboardPaste class="mr-2 h-3.5 w-3.5" />
            <span>Paste After</span>
            <span class="ml-auto text-[10px] tracking-widest opacity-60">Ctrl+V</span>
        </button>

        <div class="my-1 h-px bg-muted" />

        <button @click="handleAction('delete')" class="relative flex w-full cursor-default select-none items-center rounded-sm px-2 py-1.5 text-xs outline-none hover:bg-accent hover:text-accent-foreground text-destructive data-[disabled]:pointer-events-none data-[disabled]:opacity-50">
            <Trash2 class="mr-2 h-3.5 w-3.5" />
            <span>Delete</span>
            <span class="ml-auto text-[10px] tracking-widest opacity-60">Del</span>
        </button>
    </div>

    <!-- Backdrop to close -->
    <div 
        v-if="builder.contextMenu.value.visible" 
        class="fixed inset-0 z-[9998] bg-transparent"
        @click="closeMenu"
        @contextmenu.prevent="closeMenu"
    ></div>
</template>

<script setup>
import { inject } from 'vue';
import { 
    Copy, Trash2, Edit, ClipboardCopy, ClipboardPaste 
} from 'lucide-vue-next';

const builder = inject('builder');

const closeMenu = () => {
    builder.contextMenu.value.visible = false;
};

const handleAction = (action) => {
    const { index, blockId } = builder.contextMenu.value;
    
    if (index !== null) {
        switch (action) {
            case 'edit':
                builder.editingIndex.value = index;
                builder.activeBlockId.value = blockId;
                builder.activeTab.value = 'content';
                break;
            case 'duplicate':
                builder.duplicateBlock(index);
                break;
            case 'copy':
                builder.copyBlock(index);
                break;
            case 'paste':
                builder.pasteBlock(index);
                break;
            case 'delete':
                builder.removeBlock(index);
                break;
        }
    }
    
    closeMenu();
};
</script>
