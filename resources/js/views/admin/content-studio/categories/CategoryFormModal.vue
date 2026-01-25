<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-[600px]" @pointer-down-outside="handlePointerDownOutside">
            <DialogHeader>
                <DialogTitle>{{ isEdit ? $t('features.categories.form.editTitle') : $t('features.categories.form.createTitle') }}</DialogTitle>
                <DialogDescription>
                    {{ isEdit ? $t('features.categories.form.editDescription') : $t('features.categories.form.createDescription') }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="space-y-2">
                        <Label>
                            {{ $t('features.categories.form.name') }} <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            v-model="form.name"
                            required
                            @input="generateSlug"
                            :class="errors.name ? 'border-destructive focus-visible:ring-destructive' : ''"
                            :placeholder="$t('features.categories.form.namePlaceholder')"
                        />
                        <p v-if="errors.name" class="text-sm text-destructive">
                            {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
                        </p>
                    </div>

                    <!-- Slug -->
                    <div class="space-y-2">
                        <Label>
                            {{ $t('features.categories.form.slug') }} <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            v-model="form.slug"
                            required
                            :class="errors.slug ? 'border-destructive focus-visible:ring-destructive' : ''"
                            :placeholder="$t('features.categories.form.slugPlaceholder')"
                        />
                        <p class="text-xs text-muted-foreground">{{ $t('features.categories.form.slugHelp') }}</p>
                        <p v-if="errors.slug" class="text-sm text-destructive">
                            {{ Array.isArray(errors.slug) ? errors.slug[0] : errors.slug }}
                        </p>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <Label>
                        {{ $t('features.categories.form.description') }}
                    </Label>
                    <Textarea
                        v-model="form.description"
                        rows="3"
                        :placeholder="$t('features.categories.form.descriptionPlaceholder')"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Parent Category -->
                    <div class="space-y-2">
                        <Label>
                            {{ $t('features.categories.form.parent') }}
                        </Label>
                        <Select v-model="form.parent_id">
                            <SelectTrigger>
                                <SelectValue :placeholder="$t('features.categories.form.noParent')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="null_value">
                                    {{ $t('features.categories.form.noParent') }}
                                </SelectItem>
                                <SelectItem
                                    v-for="cat in availableParents"
                                    :key="cat.id"
                                    :value="cat.id.toString()"
                                >
                                    {{ cat.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Sort Order -->
                    <div class="space-y-2">
                        <Label>
                            {{ $t('features.categories.form.sortOrder') }}
                        </Label>
                        <Input
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            placeholder="0"
                        />
                    </div>
                </div>

                <!-- Image -->
                <div class="space-y-2">
                    <Label>
                        {{ $t('features.categories.form.image') }}
                    </Label>
                    <div v-if="form.image" class="mb-2 relative w-32 h-32 group">
                        <img
                            :src="form.image"
                            alt="Category image"
                            class="w-full h-full object-cover rounded-lg border border-border"
                        >
                        <Button
                            type="button"
                            variant="destructive"
                            size="icon"
                            class="absolute -top-2 -right-2 h-6 w-6 opacity-0 group-hover:opacity-100 transition-opacity"
                            @click="form.image = null"
                        >
                            <X class="w-3 h-3" />
                        </Button>
                    </div>
                    <MediaPicker
                        v-else
                        v-model:open="isMediaPickerOpen"
                        @selected="(media) => form.image = media.url"
                        :label="$t('features.categories.form.selectImage')"
                    />
                </div>

                <!-- Active Status -->
                <div class="flex items-center space-x-2">
                    <Checkbox 
                        id="is_active" 
                        :checked="form.is_active"
                        @update:checked="(val) => form.is_active = val"
                    />
                    <Label for="is_active" class="cursor-pointer">
                        {{ $t('features.categories.form.active') }}
                    </Label>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="$emit('update:open', false)">
                        {{ $t('common.actions.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="saving || !isValid">
                        <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                        {{ saving ? (isEdit ? $t('common.messages.loading.updating') : $t('common.messages.loading.creating')) : (isEdit ? $t('common.actions.update') : $t('common.actions.create')) }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useFormValidation } from '@/composables/useFormValidation';
import { categorySchema } from '@/schemas';
import MediaPicker from '@/components/MediaPicker.vue';
import { Loader2, X } from 'lucide-vue-next';
import type { Category } from '@/types/cms';

// Shadcn UI
// @ts-ignore
import Button from '@/components/ui/button.vue';
// @ts-ignore
import Input from '@/components/ui/input.vue';
// @ts-ignore
import Label from '@/components/ui/label.vue';
// @ts-ignore
import Textarea from '@/components/ui/textarea.vue';
// @ts-ignore
import Checkbox from '@/components/ui/checkbox.vue';
// @ts-ignore
import Select from '@/components/ui/select.vue';
// @ts-ignore
import SelectContent from '@/components/ui/select-content.vue';
// @ts-ignore
import SelectItem from '@/components/ui/select-item.vue';
// @ts-ignore
import SelectTrigger from '@/components/ui/select-trigger.vue';
// @ts-ignore
import SelectValue from '@/components/ui/select-value.vue';
// @ts-ignore
import Dialog from '@/components/ui/dialog.vue';
import DialogContent from '@/components/ui/dialog-content.vue';
import DialogDescription from '@/components/ui/dialog-description.vue';
import DialogFooter from '@/components/ui/dialog-footer.vue';
import DialogHeader from '@/components/ui/dialog-header.vue';
import DialogTitle from '@/components/ui/dialog-title.vue';

const props = defineProps<{
    open: boolean;
    category?: Category | null;
    categories?: Category[]; // For parent selection
}>();

const emit = defineEmits(['update:open', 'success']);

const { t } = useI18n();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(categorySchema);

const saving = ref(false);
const isEdit = computed(() => !!props.category);
const isMediaPickerOpen = ref(false);

const form = ref<any>({
    name: '',
    slug: '',
    description: '',
    image: null,
    parent_id: null,
    is_active: true,
    sort_order: 0
});

// Initialize form when opening
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        clearErrors();
        if (props.category) {
            form.value = {
                ...props.category,
                parent_id: props.category.parent_id ? props.category.parent_id.toString() : null, // Convert to string for Select
                image: props.category.image || null,
            };
        } else {
            // Reset
            form.value = {
                name: '',
                slug: '',
                description: '',
                image: null,
                parent_id: null,
                is_active: true,
                sort_order: 0
            };
        }
    }
});

const isValid = computed(() => {
    return !!form.value.name?.trim();
});

// Prevent closing modal when clicking inside MediaPicker (which is effectively outside the dialog content)
const handlePointerDownOutside = (e: any) => {
    if (isMediaPickerOpen.value) {
        e.preventDefault();
    }
};

// Flatten categories for parent selection
const flattenTree = (nodes: Category[], depth = 0): any[] => {
    if (!nodes) return [];
    let result: any[] = [];
    nodes.forEach(node => {
        // Prevent selecting self or children as parent (circular)
        if (isEdit.value && (node.id === props.category?.id)) return;
        
        result.push({
            id: node.id,
            label: '— '.repeat(depth) + node.name,
            raw: node
        });
        
        const children = node.all_children || node.children;
        if (children && children.length > 0) {
            // If editing, filter out children of current node to prevent cycles
            // Ideally we check if node is a descendant, but simple containment check of ID in parent set is complex here without full tree traversal.
            // For now, simple client side recursion:
            result = result.concat(flattenTree(children, depth + 1));
        }
    });
    return result;
};

// Filtered available parents
const availableParents = computed(() => {
    return flattenTree(props.categories || []);
});

const generateSlug = () => {
    if (!isEdit.value || !form.value.slug || form.value.slug === slugify(form.value.name)) {
         form.value.slug = slugify(form.value.name);
    }
};

const slugify = (text: string) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    saving.value = true;
    clearErrors();
    try {
        const payload = { ...form.value };
        // Handle select returning string 'null_value'
        if (payload.parent_id === 'null_value' || !payload.parent_id) {
            payload.parent_id = null;
        }

        if (isEdit.value) {
            await api.put(`/admin/ja/categories/${props.category?.id}`, payload);
            toast.success.update('Category');
        } else {
            await api.post('/admin/ja/categories', payload);
            toast.success.create('Category');
        }
        
        emit('success');
        emit('update:open', false);
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
</script>
