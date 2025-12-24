<template>
    <div class="relative" v-click-outside="closeDropdown">
        <button
            @click="toggleDropdown"
            class="flex items-center justify-center p-2 rounded-md transition-colors hover:bg-muted"
            :class="{ 'bg-indigo-500/10 text-indigo-500': isOpen, 'text-muted-foreground hover:text-foreground': !isOpen }"
            :title="currentLanguage?.native_name || 'Select Language'"
        >
            <span class="text-xl">{{ currentLanguage ? getLanguageFlag(currentLanguage) : '🌐' }}</span>
        </button>

        <!-- Dropdown Menu -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 mt-2 w-56 rounded-md bg-card ring-1 ring-black ring-opacity-5 z-50 transform origin-top-right"
            >
                <div class="py-1 max-h-64 overflow-y-auto">
                    <button
                        v-for="language in languages"
                        :key="language.id"
                        @click="selectLanguage(language)"
                        class="w-full text-left px-4 py-2 text-sm flex items-center space-x-3 hover:bg-accent transition-colors"
                        :class="{
                            'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400': currentLanguage?.id === language.id
                        }"
                    >
                        <span class="text-xl">{{ getLanguageFlag(language) }}</span>
                        <div class="flex-1">
                            <div class="font-medium text-foreground">
                                {{ language.native_name || language.name }}
                            </div>
                            <div v-if="language.native_name && language.native_name !== language.name" class="text-xs text-muted-foreground">
                                {{ language.name }}
                            </div>
                        </div>
                        <svg
                            v-if="currentLanguage?.id === language.id"
                            class="w-5 h-5 text-indigo-600 dark:text-indigo-400"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useLanguage } from '../composables/useLanguage';

const {
    currentLanguage,
    languages,
    loading,
    setLanguage,
    getLanguageFlag,
    initializeLanguage,
} = useLanguage();

const isOpen = ref(false);

const closeDropdown = () => {
    isOpen.value = false;
};

// Toggle dropdown and dispatch event to close other dropdowns
const toggleDropdown = () => {
    const wasOpen = isOpen.value;
    if (!wasOpen) {
        // Close other dropdowns before opening this one
        window.dispatchEvent(new CustomEvent('close-navbar-dropdowns'));
    }
    isOpen.value = !wasOpen;
};

// Listen for close events from other dropdowns
const handleCloseDropdowns = () => {
    isOpen.value = false;
};

const selectLanguage = async (language) => {
    await setLanguage(language.code);
    closeDropdown();
    
    // Emit event for parent components
    // Optionally reload page or update content
    if (typeof window !== 'undefined') {
        // You can emit an event or trigger a page reload
        // For now, we'll just update the language
        window.dispatchEvent(new CustomEvent('language-changed', { detail: language }));
    }
};

// Close dropdown on escape key
const handleEscape = (e) => {
    if (e.key === 'Escape' && isOpen.value) {
        closeDropdown();
    }
};

// Click outside directive
const vClickOutside = {
    mounted(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value();
            }
        };
        document.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent);
    },
};

onMounted(async () => {
    await initializeLanguage();
    document.addEventListener('keydown', handleEscape);
    window.addEventListener('close-navbar-dropdowns', handleCloseDropdowns);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape);
    window.removeEventListener('close-navbar-dropdowns', handleCloseDropdowns);
});
</script>

<style scoped>
/* RTL Support */
.rtl .absolute {
    right: auto;
    left: 0;
}
</style>

