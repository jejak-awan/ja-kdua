<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Menu as MenuIcon, ChevronDown, Loader2 } from 'lucide-vue-next';
import api from '@/services/api';
import { parseResponse, ensureArray, parseSingleResponse } from '@/utils/responseParser';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    items: {
        type: Array,
        default: () => []
    },
    menu_id: { type: [String, Number], default: null },
    style: { type: String, default: 'horizontal' },
    alignment: { type: String, default: 'center' },
    show_mobile_toggle: { type: Boolean, default: true },
    mobile_breakpoint: { type: String, default: 'md' },
    padding: { type: String, default: 'py-4' },
    bgColor: { type: String, default: '' }
});

const systemItems = ref(null);
const loading = ref(false);
const mobileOpen = ref(false);
const openDropdown = ref(null);

const displayItems = computed(() => {
    if (props.menu_id && systemItems.value) {
        return systemItems.value;
    }
    return props.items && props.items.length > 0 ? props.items : [
        { label: 'Home', url: '/', children: [] },
        { label: 'Blog', url: '/blog', children: [] },
        { label: 'Contact', url: '/contact', children: [] }
    ];
});

const fetchSystemMenu = async () => {
    if (!props.menu_id) {
        systemItems.value = null;
        return;
    }

    loading.value = true;
    try {
        const response = await api.get(`/cms/menus/${props.menu_id}`);
        const data = parseSingleResponse(response);
        // Menu item format might need transformation
        systemItems.value = ensureArray(data?.items);
    } catch (error) {
        console.error('Failed to fetch system menu:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchSystemMenu);
watch(() => props.menu_id, fetchSystemMenu);

const containerClasses = computed(() => {
    return ['transition-all duration-300', props.padding].filter(Boolean);
});

const navClasses = computed(() => {
    const alignments = {
        left: 'justify-start',
        center: 'justify-center',
        right: 'justify-end'
    };
    return ['hidden md:flex items-center gap-1', alignments[props.alignment] || 'justify-center'];
});

const toggleDropdown = (index) => {
    openDropdown.value = openDropdown.value === index ? null : index;
};
</script>

<template>
    <nav 
        :class="containerClasses"
        :style="{ backgroundColor: bgColor || 'transparent' }"
        class="relative"
    >
        <div v-if="loading" class="absolute inset-0 bg-background/50 flex items-center justify-center z-10 rounded-lg">
            <Loader2 class="w-5 h-5 animate-spin text-primary" />
        </div>

        <div class="container mx-auto px-6">
            <!-- Desktop Menu -->
            <ul :class="navClasses">
                <li 
                    v-for="(item, index) in displayItems" 
                    :key="index"
                    class="relative group"
                >
                    <a 
                        :href="item.url || '#'"
                        class="flex items-center gap-1 px-4 py-2 font-medium text-foreground hover:text-primary transition-colors rounded-lg hover:bg-muted"
                        @click="item.children?.length ? ($event.preventDefault(), toggleDropdown(index)) : null"
                    >
                        {{ item.title || item.label }}
                        <ChevronDown 
                            v-if="item.children?.length" 
                            class="w-4 h-4 transition-transform"
                            :class="{ 'rotate-180': openDropdown === index }"
                        />
                    </a>
                    
                    <!-- Dropdown -->
                    <ul 
                        v-if="item.children?.length"
                        class="absolute left-0 top-full pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50"
                    >
                        <li class="bg-card border rounded-xl shadow-lg py-2 min-w-[200px]">
                            <a 
                                v-for="(child, childIndex) in item.children" 
                                :key="childIndex"
                                :href="child.url || '#'"
                                class="block px-4 py-2 text-sm text-foreground hover:text-primary hover:bg-muted transition-colors"
                            >
                                {{ child.title || child.label }}
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            
            <!-- Mobile Toggle -->
            <div v-if="show_mobile_toggle" class="flex md:hidden justify-end">
                <button 
                    @click="mobileOpen = !mobileOpen"
                    class="p-2 rounded-lg hover:bg-muted transition-colors"
                >
                    <MenuIcon class="w-6 h-6" />
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div 
                v-if="mobileOpen"
                class="md:hidden mt-4 bg-card border rounded-xl p-4 space-y-2"
            >
                <template v-for="(item, index) in displayItems" :key="index">
                    <a 
                        :href="item.url || '#'"
                        class="block px-4 py-2 font-medium rounded-lg hover:bg-muted transition-colors"
                        @click="!item.children?.length && (mobileOpen = false)"
                    >
                        {{ item.title || item.label }}
                    </a>
                    <div v-if="item.children?.length" class="pl-4 space-y-1">
                        <a 
                            v-for="(child, childIndex) in item.children" 
                            :key="childIndex"
                            :href="child.url || '#'"
                            class="block px-4 py-2 text-sm text-muted-foreground hover:text-foreground rounded-lg hover:bg-muted transition-colors"
                            @click="mobileOpen = false"
                        >
                            {{ child.title || child.label }}
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </nav>
</template>
