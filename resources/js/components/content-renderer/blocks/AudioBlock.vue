<script setup>
import { ref, computed, onMounted } from 'vue';
import { Play, Pause, Volume2, VolumeX } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    src: { type: String, default: '' },
    title: { type: String, default: '' },
    artist: { type: String, default: '' },
    cover_image: { type: String, default: '' },
    autoplay: { type: Boolean, default: false },
    loop: { type: Boolean, default: false },
    style: { type: String, default: 'card' },
    padding: { type: String, default: 'py-8' },
    alignment: { type: String, default: 'center' }
});

const audioRef = ref(null);
const isPlaying = ref(false);
const isMuted = ref(false);
const currentTime = ref(0);
const duration = ref(0);
const progress = ref(0);

const containerClasses = computed(() => {
    return ['transition-all duration-300', props.padding].filter(Boolean);
});

const alignmentClass = computed(() => {
    if (props.alignment === 'left') return 'mr-auto';
    if (props.alignment === 'right') return 'ml-auto';
    return 'mx-auto';
});

const styleClass = computed(() => {
    const styles = {
        card: 'bg-card border rounded-2xl p-6 shadow-sm',
        minimal: 'bg-transparent',
        dark: 'bg-zinc-900 text-white rounded-2xl p-6'
    };
    return styles[props.style] || styles.card;
});

const togglePlay = () => {
    if (!audioRef.value) return;
    if (isPlaying.value) {
        audioRef.value.pause();
    } else {
        audioRef.value.play();
    }
    isPlaying.value = !isPlaying.value;
};

const toggleMute = () => {
    if (!audioRef.value) return;
    audioRef.value.muted = !audioRef.value.muted;
    isMuted.value = !isMuted.value;
};

const updateProgress = () => {
    if (!audioRef.value) return;
    currentTime.value = audioRef.value.currentTime;
    duration.value = audioRef.value.duration || 0;
    progress.value = duration.value ? (currentTime.value / duration.value) * 100 : 0;
};

const seek = (e) => {
    if (!audioRef.value || !duration.value) return;
    const rect = e.target.getBoundingClientRect();
    const percent = (e.clientX - rect.left) / rect.width;
    audioRef.value.currentTime = percent * duration.value;
};

const formatTime = (seconds) => {
    if (!seconds || isNaN(seconds)) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

onMounted(() => {
    if (audioRef.value) {
        audioRef.value.addEventListener('timeupdate', updateProgress);
        audioRef.value.addEventListener('loadedmetadata', updateProgress);
        audioRef.value.addEventListener('ended', () => { isPlaying.value = false; });
    }
});
</script>

<template>
    <div :class="containerClasses">
        <div :class="['max-w-md', alignmentClass, styleClass]">
            <audio 
                ref="audioRef" 
                :src="src || ''" 
                :autoplay="autoplay" 
                :loop="loop"
                preload="metadata"
            ></audio>
            
            <div class="flex items-center gap-4">
                <!-- Cover Image -->
                <div 
                    v-if="cover_image || style !== 'minimal'"
                    class="w-16 h-16 rounded-xl overflow-hidden bg-muted shrink-0"
                >
                    <img v-if="cover_image" :src="cover_image" :alt="title" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground">
                        <Volume2 class="w-6 h-6" />
                    </div>
                </div>
                
                <!-- Info & Controls -->
                <div class="flex-1 min-w-0">
                    <h4 v-if="title" class="font-bold truncate">{{ title }}</h4>
                    <p v-if="artist" class="text-sm text-muted-foreground truncate">{{ artist }}</p>
                    
                    <!-- Progress Bar -->
                    <div 
                        class="h-1.5 bg-muted rounded-full mt-3 cursor-pointer group"
                        @click="seek"
                    >
                        <div 
                            class="h-full bg-primary rounded-full relative transition-all"
                            :style="{ width: progress + '%' }"
                        >
                            <div class="absolute right-0 top-1/2 -translate-y-1/2 w-3 h-3 bg-primary rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </div>
                    </div>
                    
                    <!-- Time -->
                    <div class="flex justify-between text-[10px] text-muted-foreground mt-1">
                        <span>{{ formatTime(currentTime) }}</span>
                        <span>{{ formatTime(duration) }}</span>
                    </div>
                </div>
                
                <!-- Play/Pause -->
                <button 
                    @click="togglePlay"
                    class="w-12 h-12 rounded-full bg-primary text-primary-foreground flex items-center justify-center hover:opacity-90 transition-all shrink-0"
                >
                    <Pause v-if="isPlaying" class="w-5 h-5" />
                    <Play v-else class="w-5 h-5 ml-0.5" />
                </button>
                
                <!-- Mute -->
                <button 
                    @click="toggleMute"
                    class="w-8 h-8 rounded-full text-muted-foreground hover:text-foreground transition-colors shrink-0"
                >
                    <VolumeX v-if="isMuted" class="w-4 h-4" />
                    <Volume2 v-else class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>
