<script setup>
import { computed } from 'vue';
import { Search as SearchIcon } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    placeholder: { type: String, default: 'Search...' },
    showButton: { type: Boolean, default: true },
    buttonStyle: { type: String, default: 'icon' },
    buttonText: { type: String, default: 'Search' },
    buttonBackgroundColor: { type: String, default: '#2059ea' },
    buttonTextColor: { type: String, default: '#ffffff' },
    padding: { type: String, default: '' },
    alignment: { type: String, default: 'text-left' }
});

const containerClasses = computed(() => {
    return ['transition-all duration-300 w-full', props.padding, props.alignment, 'mx-auto'].filter(Boolean);
});

const buttonStyles = computed(() => ({
    backgroundColor: props.buttonBackgroundColor,
    color: props.buttonTextColor
}));

const handleSearch = () => {
    // Simulated
};
</script>

<template>
    <div :class="containerClasses">
        <form @submit.prevent="handleSearch" class="search-form flex gap-0 overflow-hidden border rounded-lg bg-background shadow-sm focus-within:ring-2 focus-within:ring-primary/20 transition-all">
            <div class="relative flex-1 flex items-center">
                <SearchIcon class="absolute left-4 w-4 h-4 text-muted-foreground" />
                <input 
                    type="text"
                    :placeholder="placeholder"
                    class="w-full h-12 pl-11 pr-4 text-sm bg-transparent border-none outline-none focus:ring-0"
                />
            </div>
            
            <button 
                v-if="showButton"
                type="submit"
                class="search-button h-12 px-6 flex items-center justify-center gap-2 font-semibold text-sm transition-all hover:opacity-90 active:scale-95"
                :style="buttonStyles"
            >
                <SearchIcon v-if="buttonStyle !== 'text'" class="w-4 h-4" />
                <span v-if="buttonStyle !== 'icon'">{{ buttonText }}</span>
            </button>
        </form>
    </div>
</template>

<style scoped>
.search-form {
    border-color: #e0e0e0;
}
.search-button {
    border-radius: 0;
}
</style>
