<script setup>
import { computed } from 'vue';
import { Copy, Check } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    code: { type: String, default: '' },
    language: { type: String, default: 'javascript' },
    show_line_numbers: { type: Boolean, default: true },
    show_copy_button: { type: Boolean, default: true },
    theme: { type: String, default: 'dark' },
    max_height: { type: String, default: '' },
    padding: { type: String, default: 'py-6' }
});

const copied = ref(false);

const containerClasses = computed(() => {
    return ['transition-all duration-300', props.padding].filter(Boolean);
});

const themeClasses = computed(() => {
    const themes = {
        dark: 'bg-zinc-900 text-zinc-100',
        light: 'bg-zinc-100 text-zinc-900',
        github: 'bg-white text-zinc-800 border'
    };
    return themes[props.theme] || themes.dark;
});

const lines = computed(() => {
    return (props.code || '// Your code here').split('\n');
});

const copyCode = async () => {
    try {
        await navigator.clipboard.writeText(props.code || '');
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch (e) {
        console.warn('Failed to copy code');
    }
};
</script>

<template>
    <div :class="containerClasses">
        <div 
            :class="['relative rounded-xl overflow-hidden font-mono text-sm', themeClasses]"
            :style="{ maxHeight: max_height || 'auto' }"
        >
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-2 border-b border-white/10">
                <span class="text-xs opacity-60">{{ language }}</span>
                <button 
                    v-if="show_copy_button"
                    @click="copyCode"
                    class="flex items-center gap-1.5 text-xs opacity-60 hover:opacity-100 transition-opacity"
                >
                    <Check v-if="copied" class="w-3.5 h-3.5 text-emerald-400" />
                    <Copy v-else class="w-3.5 h-3.5" />
                    <span>{{ copied ? 'Copied!' : 'Copy' }}</span>
                </button>
            </div>
            
            <!-- Code -->
            <div 
                class="overflow-auto p-4"
                :style="{ maxHeight: max_height ? `calc(${max_height} - 40px)` : 'auto' }"
            >
                <table class="w-full">
                    <tbody>
                        <tr v-for="(line, index) in lines" :key="index" class="leading-relaxed">
                            <td 
                                v-if="show_line_numbers" 
                                class="pr-4 text-right select-none opacity-40 w-8"
                            >
                                {{ index + 1 }}
                            </td>
                            <td class="whitespace-pre">{{ line }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
