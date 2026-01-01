<template>
    <section 
        :class="['transition-all duration-500', padding, radius, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <h2 v-if="title" class="text-3xl font-bold text-center mb-12 tracking-tight">{{ title }}</h2>
            
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div 
                    v-for="(item, index) in items" 
                    :key="index"
                    class="p-8 rounded-3xl border bg-card/50 backdrop-blur-md shadow-xl flex flex-col transition-all duration-300 hover:shadow-primary/5 hover:border-primary/20 group hover:-translate-y-2"
                >
                    <!-- Quote Icon -->
                    <div class="mb-6 text-primary/20 group-hover:text-primary/40 transition-colors">
                        <svg class="w-10 h-10 fill-current" viewBox="0 0 24 24">
                            <path d="M14.017 21L14.017 18C14.017 16.8954 14.9124 16 16.017 16H19.017C19.5693 16 20.017 15.5523 20.017 15V9C20.017 8.44772 19.5693 8 19.017 8H16.017C15.4647 8 15.017 8.44772 15.017 9V12C15.017 12.5523 14.5693 13 14.017 13H12.017V21H14.017ZM5.017 21L5.017 18C5.017 16.8954 5.91243 16 7.017 16H10.017C10.5693 16 11.017 15.5523 11.017 15V9C11.017 8.44772 10.5693 8 10.017 8H7.017C6.46472 8 6.017 8.44772 6.017 9V12C6.017 12.5523 5.56929 13 5.017 13H3.017V21H5.017Z" />
                        </svg>
                    </div>

                    <p class="text-lg leading-relaxed mb-8 flex-1 italic text-muted-foreground group-hover:text-foreground transition-colors">
                        "{{ item.quote }}"
                    </p>

                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full border-2 border-primary/10 overflow-hidden bg-muted">
                            <img 
                                :src="item.avatar || '/images/placeholder.svg'" 
                                :alt="item.author"
                                class="w-full h-full object-cover"
                            >
                        </div>
                        <div>
                            <p class="font-bold text-sm">{{ item.author }}</p>
                            <p class="text-[10px] text-muted-foreground uppercase tracking-widest font-semibold">{{ item.role }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
const props = defineProps({
    title: String,
    items: { type: Array, default: () => [] },
    width: { type: String, default: 'max-w-7xl' },
    padding: { type: String, default: 'py-20' },
    bgColor: String,
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' }
});
</script>
