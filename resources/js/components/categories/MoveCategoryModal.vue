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
                        <X class="w-6 h-6" />
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

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { DialogFooter } from '@/components/ui';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import { useToast } from '@/composables/useToast';
import { useFormValidation } from '@/composables/useFormValidation';
import { moveCategorySchema } from '@/schemas';
import api from '@/services/api';

interface Category {
    id: number | string;
    name: string;
    parent_id?: number | string | null;
    [key: string]: any;
}

const { t } = useI18n();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(moveCategorySchema);

const props = defineProps<{
    category: Category;
    categories?: Category[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'moved'): void;
}>();

const saving = ref(false);
const selectedParentId = ref<number | string | null>(null);

const availableParents = computed(() => {
    const cats = props.categories || [];
    // Exclude self and descendants from parent options
    return cats.filter(cat => {
        if (cat.id === props.category.id) return false;
        // Check if cat is a descendant of current category
        let check: Category | undefined = cat;
        while (check?.parent_id) {
            if (check.parent_id === props.category.id) return false;
            check = cats.find(c => c.id === check!.parent_id);
            if (!check) break;
        }
        return true;
    });
});

const getCategoryPath = (category: Category) => {
    let path = category.name;
    let current = category;
    const cats = props.categories || [];
    
    while (current.parent_id) {
        const parent = cats.find(c => c.id === current.parent_id);
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
    if (!validateWithZod({ parent_id: selectedParentId.value } as any)) return;

    saving.value = true;
    clearErrors();
    try {
        await api.post(`/admin/ja/categories/${props.category.id}/move`, {
            parent_id: selectedParentId.value,
        });
        toast.success.action(t('features.categories.messages.moveSuccess'));
        emit('moved');
    } catch (error: any) {
        if (error.response?.status === 422) {
            setErrors(error.response.data.errors || {});
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    selectedParentId.value = props.category.parent_id || null;
});
</script>

