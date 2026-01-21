<template>
    <div class="lottie-block-placeholder border-2 border-dashed border-gray-200 rounded-lg p-8 flex flex-col items-center justify-center text-center bg-gray-50 text-gray-500">
        <Film class="w-12 h-12 mb-4 opacity-50" />
        <h4 class="font-bold text-lg mb-2">Lottie Animation</h4>
        <p class="text-sm mb-4 max-w-xs">Lottie player dependency is currently missing. Animation source:</p>
        
        <div v-if="source === 'url'" class="text-xs bg-gray-100 p-2 rounded break-all font-mono">
            {{ animationUrl || 'No URL provided' }}
        </div>
        <div v-else class="text-xs bg-gray-100 p-2 rounded break-all font-mono">
            JSON Content ({{ animationJson ? animationJson.length + ' chars' : 'Empty' }})
        </div>
    </div>
</template>

<script setup>
import { Film } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

defineProps({
    source: { type: String, default: 'url' },
    animationUrl: String,
    animationJson: String,
    autoplay: { type: Boolean, default: true },
    loop: { type: Boolean, default: true }
});
</script>
