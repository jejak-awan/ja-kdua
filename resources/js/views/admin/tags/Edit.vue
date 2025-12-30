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
                                :class="{ 'border-destructive focus-visible:ring-destructive': errors.name }"
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
                                :class="{ 'border-destructive focus-visible:ring-destructive': errors.slug }"
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
                    <Button type="submit" :disabled="saving">
                        <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                        {{ saving ? $t('common.messages.loading.saving') : $t('common.actions.update') }}
                    </Button>
                </CardFooter>
            </form>
        </Card>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import api from '../../../services/api';
import { useToast } from '../../../composables/useToast';
import { parseSingleResponse } from '../../../utils/responseParser';
import { Loader2 } from 'lucide-vue-next';

import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import Label from '@/components/ui/label.vue';
import Textarea from '@/components/ui/textarea.vue';
import Card from '@/components/ui/card.vue';
import CardHeader from '@/components/ui/card-header.vue';
import CardTitle from '@/components/ui/card-title.vue';
import CardDescription from '@/components/ui/card-description.vue';
import CardContent from '@/components/ui/card-content.vue';
import CardFooter from '@/components/ui/card-footer.vue';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const toast = useToast();

const loading = ref(true);
const saving = ref(false);
const tagId = route.params.id;
const errors = ref({});

const form = ref({
    name: '',
    slug: '',
    description: '',
});

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

const fetchTag = async () => {
    try {
        const response = await api.get(`/admin/cms/tags/${tagId}`);
        const data = response.data?.data || response.data;
        form.value = {
            name: data.name || '',
            slug: data.slug || '',
            description: data.description || '',
        };
    } catch (error) {
        console.error('Failed to fetch tag:', error);
        toast.error.load(error);
        router.push({ name: 'tags.index' });
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    saving.value = true;
    errors.value = {};
    try {
        await api.put(`/admin/cms/tags/${tagId}`, form.value);
        toast.success.update('Tag');
        router.push({ name: 'tags.index' });
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
    fetchTag();
});
</script>
