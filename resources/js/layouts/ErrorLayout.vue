<template>
    <div class="min-h-screen w-full flex items-center justify-center bg-gray-50/80 dark:bg-zinc-950 p-4 transition-colors duration-300">
        <!-- Main Card -->
        <div class="w-full max-w-[550px] bg-white dark:bg-zinc-900/90 backdrop-blur-sm border border-gray-200 dark:border-zinc-800/60 rounded-3xl shadow relative flex flex-col items-center text-center p-6 sm:p-10">
            
            <!-- Top Gradient Line -->
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-transparent via-primary/30 to-transparent opacity-50"></div>

            <!-- Icon Container -->
             <div class="mb-6 relative group">
                <div class="absolute inset-0 bg-primary/20 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <slot name="icon"></slot>
            </div>

            <!-- Title & Code -->
            <div class="space-y-2 mb-3 w-full">
                 <h1 class="text-4xl sm:text-5xl font-black tracking-tighter text-foreground">
                    <slot name="title"></slot>
                </h1>
            </div>

            <!-- Message -->
            <div class="w-full mb-8">
                 <h2 class="text-lg font-semibold text-foreground mb-2">
                    <slot name="message"></slot>
                 </h2>
                 <p v-if="$slots.description" class="text-muted-foreground text-sm leading-relaxed max-w-xs mx-auto">
                    <slot name="description"></slot>
                 </p>
            </div>

            <!-- Content Slot (Search, Details, Permission list) -->
            <div v-if="$slots.details" class="w-full mb-8 text-left">
                <slot name="details"></slot>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 w-full mb-8">
                <slot name="actions"></slot>
            </div>

            <!-- Footer (Subtle) -->
            <div class="text-[11px] text-muted-foreground/40 font-mono mt-auto w-full pt-4 border-t border-dashed border-border/30">
                <slot name="footer"></slot>
            </div>
        </div>
    </div>
</template>

<script setup>
// Error Layout Component - Compact Design
</script>
