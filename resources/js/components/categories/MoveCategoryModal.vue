<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg max-w-md w-full">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ t('features.categories.move.title') }}
                    </h3>
                    <button
                        @click="$emit('close')"
                        class="text-muted-foreground hover:text-muted-foreground"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div>
                        <p class="text-sm text-muted-foreground mb-4">
                            {{ t('features.categories.move.description', { name: category.name }) }}
                        </p>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.categories.move.newParent') }}
                        </label>
                        <select
                            v-model="selectedParentId"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option :value="null">{{ t('features.categories.form.noParent') }}</option>
                            <option
                                v-for="cat in availableParents"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ getCategoryPath(cat) }}
                            </option>
                        </select>
                    </div>
                </form>

                <!-- Footer -->
                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                    >
                        {{ t('features.categories.move.cancel') }}
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 disabled:opacity-50"
                    >
                        {{ saving ? t('features.categories.move.moving') : t('features.categories.move.submit') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';

const { t } = useI18n();
const toast = useToast();
const errors = ref({});

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close', 'moved']);

const saving = ref(false);
const selectedParentId = ref(null);

const availableParents = computed(() => {
    // Exclude self and descendants from parent options
    return props.categories.filter(cat => {
        if (cat.id === props.category.id) return false;
        // Check if cat is a descendant of current category
        let check = cat;
        while (check.parent_id) {
            if (check.parent_id === props.category.id) return false;
            check = props.categories.find(c => c.id === check.parent_id);
            if (!check) break;
        }
        return true;
    });
});

const getCategoryPath = (category) => {
    let path = category.name;
    let current = category;
    while (current.parent_id) {
        const parent = props.categories.find(c => c.id === current.parent_id);
        if (parent) {
            path = parent.name + ' > ' + path;
            current = parent;
        } else {
            break;
        }
    }
    return path;
};

const handleSubmit = async () => {
    saving.value = true;
    errors.value = {};
    try {
        await api.post(`/admin/cms/categories/${props.category.id}/move`, {
            parent_id: selectedParentId.value,
        });
        toast.success('Category moved successfully');
        emit('moved');
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    selectedParentId.value = props.category.parent_id;
});
</script>

