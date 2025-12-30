<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ $t('features.categories.form.editTitle') }}</h1>
                <p class="text-muted-foreground">{{ $t('features.categories.form.editDescription') }}</p>
            </div>
            <div class="flex space-x-3">
                <Button variant="outline" @click="router.push({ name: 'categories' })">
                    {{ $t('common.actions.back') }}
                </Button>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center py-12">
            <Loader2 class="h-8 w-8 animate-spin text-muted-foreground" />
        </div>

        <!-- Form -->
        <Card v-else>
            <form @submit.prevent="handleSubmit">
                <CardHeader>
                    <CardTitle>{{ $t('features.categories.form.details') }}</CardTitle>
                    <CardDescription>{{ $t('features.categories.form.editDescription') }}</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
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
                            />
                        </div>

                        <!-- Slug -->
                        <div class="space-y-2">
                            <Label>
                                {{ $t('features.categories.form.slug') }} <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                v-model="form.slug"
                                required
                            />
                             <p class="text-xs text-muted-foreground">{{ $t('features.categories.form.slugHelp') }}</p>
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
                                        v-for="cat in flattenedCategories"
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
                            />
                            <p class="text-xs text-muted-foreground">{{ $t('features.categories.form.sortOrderHelp') }}</p>
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
                         <Label for="is_active">
                            {{ $t('features.categories.form.active') }}
                        </Label>
                    </div>
                </CardContent>
                <CardFooter class="justify-end space-x-2 border-t border-border pt-6">
                    <Button variant="outline" type="button" @click="router.push({ name: 'categories' })">
                        {{ $t('common.actions.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="saving">
                        <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                        {{ saving ? $t('common.messages.loading.saving') : $t('common.actions.save') }}
                    </Button>
                </CardFooter>
            </form>
        </Card>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import api from '../../../services/api';
import { useToast } from '../../../composables/useToast';
import MediaPicker from '../../../components/MediaPicker.vue';
import { Loader2, X } from 'lucide-vue-next';

import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Label from '@/components/ui/label.vue';
import Textarea from '@/components/ui/textarea.vue';
import Checkbox from '@/components/ui/checkbox.vue';
import Card from '@/components/ui/card.vue';
import CardHeader from '@/components/ui/card-header.vue';
import CardTitle from '@/components/ui/card-title.vue';
import CardDescription from '@/components/ui/card-description.vue';
import CardContent from '@/components/ui/card-content.vue';
import CardFooter from '@/components/ui/card-footer.vue';
import Select from '@/components/ui/select.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const toast = useToast();

const loading = ref(true);
const saving = ref(false);
const categories = ref([]);
const currentCategory = ref(null);

const form = ref({
    name: '',
    slug: '',
    description: '',
    image: null,
    parent_id: null,
    sort_order: 0,
    is_active: true,
});

const flattenedCategories = computed(() => {
    return flattenTree(categories.value);
});

const flattenTree = (nodes, depth = 0) => {
    if (!nodes) return [];
    let result = [];
    nodes.forEach(node => {
        // Exclude current category and its children from parent options (to prevent cycles)
        if (currentCategory.value && node.id === currentCategory.value.id) {
            return;
        }

        result.push({
            id: node.id,
            label: '— '.repeat(depth) + node.name,
            raw: node
        });
        
        const children = node.all_children || node.children;
        if (children && children.length > 0) {
            result = result.concat(flattenTree(children, depth + 1));
        }
    });
    return result;
};

const generateSlug = () => {
    if (!form.value.slug || form.value.slug === slugify(form.value.name)) {
        form.value.slug = slugify(form.value.name);
    }
};

const slugify = (text) => {
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

const fetchCategories = async () => {
    try {
        const response = await api.get('/admin/cms/categories', { params: { tree: true } });
        categories.value = response.data?.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch categories:', error);
    }
};

const fetchCategory = async () => {
    try {
        const response = await api.get(`/admin/cms/categories/${route.params.id}`);
        const data = response.data?.data || response.data;
        currentCategory.value = data;
        form.value = {
            name: data.name || '',
            slug: data.slug || '',
            description: data.description || '',
            image: data.image || null,
            parent_id: data.parent_id ? data.parent_id.toString() : 'null_value', // String for Select
            sort_order: data.sort_order || 0,
            is_active: data.is_active !== undefined ? data.is_active : true,
        };
    } catch (error) {
        console.error('Failed to fetch category:', error);
        toast.error.load(error);
        router.push({ name: 'categories.index' });
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        const payload = { ...form.value };
        if (payload.parent_id === 'null_value' || !payload.parent_id) {
            payload.parent_id = null;
        }
        await api.put(`/admin/cms/categories/${route.params.id}`, payload);
        toast.success.update('Category');
        router.push({ name: 'categories.index' });
    } catch (error) {
        console.error('Failed to update category:', error);
        toast.error.update(error, 'Category');
    } finally {
        saving.value = false;
    }
};

onMounted(async () => {
    loading.value = true;
    await Promise.all([fetchCategories(), fetchCategory()]);
    loading.value = false;
});
</script>
