<template>
    <div>
        <div class="px-6 py-4 hover:bg-gray-50 flex items-center justify-between group">
            <div class="flex items-center flex-1">
                <div class="flex-shrink-0 mr-4">
                    <button
                        v-if="hasChildren"
                        @click="expanded = !expanded"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg
                            class="w-5 h-5 transition-transform"
                            :class="{ 'rotate-90': expanded }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div v-else class="w-5"></div>
                </div>
                <div
                    v-if="category.image"
                    class="flex-shrink-0 h-10 w-10 mr-3"
                >
                    <img
                        :src="category.image"
                        :alt="category.name"
                        class="h-10 w-10 rounded-full object-cover"
                    />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center">
                        <p class="text-sm font-medium text-gray-900">{{ category.name }}</p>
                        <span
                            class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                        >
                            {{ category.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <p v-if="category.description" class="text-sm text-gray-500 truncate">
                        {{ category.description }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">{{ category.slug }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <button
                    @click="$emit('edit', category)"
                    class="text-indigo-600 hover:text-indigo-900 text-sm"
                >
                    Edit
                </button>
                <button
                    @click="$emit('delete', category)"
                    class="text-red-600 hover:text-red-900 text-sm"
                >
                    Delete
                </button>
            </div>
        </div>
        <div v-if="expanded && hasChildren" class="pl-12 bg-gray-50">
            <CategoryTreeItem
                v-for="child in category.children"
                :key="child.id"
                :category="child"
                :all-categories="allCategories"
                @edit="$emit('edit', $event)"
                @delete="$emit('delete', $event)"
                @move="$emit('move', $event)"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
    allCategories: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['edit', 'delete', 'move']);

const expanded = ref(true);

const hasChildren = computed(() => {
    return props.category.children && props.category.children.length > 0;
});
</script>

