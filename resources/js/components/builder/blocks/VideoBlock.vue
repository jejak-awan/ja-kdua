<template>
    <section 
        :class="['px-6 transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto', width]">
            <h2 v-if="title" class="text-3xl font-bold mb-8 text-center tracking-tight">{{ title }}</h2>
            <div :class="['aspect-video w-full overflow-hidden shadow-2xl bg-black transition-all duration-500', radius]">
                <iframe 
                    v-if="videoUrl"
                    :src="embedUrl" 
                    class="w-full h-full"
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen
                ></iframe>
                <div v-else class="w-full h-full flex flex-col items-center justify-center text-muted-foreground gap-4">
                    <VideoOff class="w-12 h-12 opacity-20" />
                    <p class="text-xs font-bold uppercase tracking-widest opacity-40">No Video Provided</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';
import { VideoOff } from 'lucide-vue-next';

const props = defineProps({
    title: String,
    videoUrl: String,
    autoplay: Boolean,
    padding: { type: String, default: 'py-16' },
    width: { type: String, default: 'max-w-5xl' },
    bgColor: String,
    radius: { type: String, default: 'rounded-2xl' },
    animation: { type: String, default: '' }
});

const embedUrl = computed(() => {
    if (!props.videoUrl) return '';
    
    let url = props.videoUrl;
    
    // Simple YouTube conversion
    if (url.includes('youtube.com/watch?v=')) {
        url = url.replace('watch?v=', 'embed/');
    } else if (url.includes('youtu.be/')) {
        url = url.replace('youtu.be/', 'youtube.com/embed/');
    }
    
    // Add autoplay if needed
    if (props.autoplay) {
        url += (url.includes('?') ? '&' : '?') + 'autoplay=1&mute=1';
    }
    
    return url;
});
</script>
