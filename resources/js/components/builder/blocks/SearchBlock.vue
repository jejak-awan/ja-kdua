<template>
    <div :class="containerClasses">
        <form @submit.prevent="handleSearch" class="flex gap-2">
            <div class="relative flex-1">
                <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                <input 
                    type="text"
                    :placeholder="placeholder || 'Search...'"
                    :class="inputClasses"
                />
            </div>
            <button 
                v-if="show_button"
                type="submit"
                :class="buttonClasses"
            >
                {{ button_text || 'Search' }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Search as SearchIcon } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    placeholder: { type: String, default: 'Search...' },
    button_text: { type: String, default: 'Search' },
    show_button: { type: Boolean, default: true },
    style: { type: String, default: 'border-input bg-background' },
    radius: { type: String, default: 'rounded-md' },
    padding: { type: String, default: '' },
    width: { type: String, default: 'max-w-xl' },
    alignment: { type: String, default: 'text-left' }
});

const containerClasses = computed(() => {
    return ['transition-all duration-300 w-full', props.padding, props.width, props.alignment, 'mx-auto'].filter(Boolean);
});

const inputClasses = computed(() => {
    return ['w-full h-11 pl-10 pr-4 text-sm ring-offset-background border transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2', props.style, props.radius].filter(Boolean);
});

const buttonClasses = computed(() => {
    return ['h-11 px-6 bg-primary text-primary-foreground font-semibold text-sm hover:opacity-90 active:scale-95 transition-all shadow-sm', props.radius].filter(Boolean);
});

const handleSearch = () => {
    // Simulated
};
</script>
