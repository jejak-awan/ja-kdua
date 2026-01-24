<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ $t('features.tags.form.editTitle') }}</h1>
                <p class="text-muted-foreground">{{ $t('features.tags.description') }}</p>
            </div>
            <div class="flex space-x-3">
                <Button variant="outline" @click="router.push({ name: 'tags' })">
                    {{ $t('common.actions.back') }}
                </Button>
            </div>
        </div>

        <!-- Form -->
        <div v-if="loading" class="flex justify-center py-12">
            <Loader2 class="w-8 h-8 animate-spin text-muted-foreground" />
        </div>

        <Card v-else>
            <form @submit.prevent="handleSubmit">
                <CardHeader>
                    <CardTitle>{{ $t('features.tags.form.editTitle') }}</CardTitle>
                    <CardDescription>{{ $t('features.tags.description') }}</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label>
                                {{ $t('features.tags.form.name') }} <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                v-model="form.name"
                                required
                                @input="generateSlug"
                                :class="errors.name ? 'border-destructive focus-visible:ring-destructive' : ''"
                                :placeholder="$t('features.tags.form.namePlaceholder')"
                            />
                            <p v-if="errors.name" class="text-sm text-destructive">
                                {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
                            </p>
                        </div>

                        <!-- Slug -->
                        <div class="space-y-2">
                            <Label>
                                {{ $t('features.tags.form.slug') }} <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                v-model="form.slug"
                                required
                                :class="errors.slug ? 'border-destructive focus-visible:ring-destructive' : ''"
                                :placeholder="$t('features.tags.form.slugPlaceholder')"
                            />
                            <p class="text-xs text-muted-foreground">{{ $t('features.tags.form.slugHelp') }}</p>
                            <p v-if="errors.slug" class="text-sm text-destructive">
                                {{ Array.isArray(errors.slug) ? errors.slug[0] : errors.slug }}
                            </p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <Label>
                            {{ $t('features.tags.form.description') }}
                        </Label>
                        <Textarea
                            v-model="form.description"
                            rows="3"
                            :placeholder="$t('features.tags.form.descriptionPlaceholder')"
                        />
                    </div>
                </CardContent>
                <CardFooter class="justify-end space-x-2 border-t border-border pt-6">
                    <Button variant="outline" type="button" @click="router.push({ name: 'tags' })">
                        {{ $t('common.actions.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="saving || !isDirty">
                        <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                        {{ saving ? $t('common.messages.loading.saving') : $t('common.actions.update') }}
                    </Button>
                </CardFooter>
            </form>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useFormValidation } from '@/composables/useFormValidation';
import { tagSchema } from '@/schemas';
import { parseSingleResponse } from '@/utils/responseParser';
import { Loader2 } from 'lucide-vue-next';

// @ts-ignore
import Button from '@/components/ui/button.vue';
// @ts-ignore
import Input from '@/components/ui/input.vue';
// @ts-ignore
import Label from '@/components/ui/label.vue';
// @ts-ignore
import Textarea from '@/components/ui/textarea.vue';
// @ts-ignore
import Card from '@/components/ui/card.vue';
// @ts-ignore
import CardHeader from '@/components/ui/card-header.vue';
// @ts-ignore
import CardTitle from '@/components/ui/card-title.vue';
// @ts-ignore
import CardDescription from '@/components/ui/card-description.vue';
// @ts-ignore
import CardContent from '@/components/ui/card-content.vue';
// @ts-ignore
import CardFooter from '@/components/ui/card-footer.vue';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(tagSchema);

const loading = ref(true);
const saving = ref(false);
const tagId = route.params.id as string;
const initialForm = ref<any>(null);

const form = ref<any>({
    name: '',
    slug: '',
    description: '',
});

const isDirty = computed(() => {
    if (!initialForm.value) return false;
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
});

const generateSlug = () => {
    if (!form.value.slug || form.value.slug === slugify(form.value.name)) {
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

const fetchTag = async () => {
    try {
        const response = await api.get(`/admin/ja/tags/${tagId}`);
        const data = response.data?.data || response.data;
        form.value = {
            name: data.name || '',
            slug: data.slug || '',
            description: data.description || '',
        };
        initialForm.value = JSON.parse(JSON.stringify(form.value));
    } catch (error: any) {
        console.error('Failed to fetch tag:', error);
        toast.error.load(error);
        router.push({ name: 'tags.index' });
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    saving.value = true;
    clearErrors();
    try {
        await api.put(`/admin/ja/tags/${tagId}`, form.value);
        
        initialForm.value = JSON.parse(JSON.stringify(form.value));
        toast.success.update('Tag');
        router.push({ name: 'tags.index' });
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
    fetchTag();
});
</script>
