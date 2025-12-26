<template>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ t('features.content_templates.form.editTitle') }}</h1>
            <Button variant="ghost" @click="router.push({ name: 'content-templates' })">
                <ChevronLeft class="w-4 h-4 mr-2" />
                {{ t('features.content_templates.form.back') }}
            </Button>
        </div>

        <div v-if="loading && !form.name" class="text-center py-12">
            <Loader2 class="w-8 h-8 animate-spin mx-auto text-muted-foreground mb-4" />
            <p class="text-muted-foreground">{{ t('features.content_templates.loading') }}</p>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">{{ t('features.content_templates.form.details') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">
                            {{ t('features.content_templates.form.name') }} <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            required
                            :placeholder="t('features.content_templates.form.namePlaceholder')"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="description">
                            {{ t('features.content_templates.form.description') }}
                        </Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            rows="2"
                            :placeholder="t('features.content_templates.form.descriptionPlaceholder')"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="type">
                            {{ t('features.content_templates.form.type') }} <span class="text-destructive">*</span>
                        </Label>
                        <Select v-model="form.type" required>
                            <SelectTrigger id="type">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="post">Post</SelectItem>
                                <SelectItem value="page">Page</SelectItem>
                                <SelectItem value="custom">Custom</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">{{ t('features.content_templates.form.content') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="title">
                            {{ t('features.content_templates.form.titleLabel') }}
                        </Label>
                        <Input
                            id="title"
                            v-model="form.title"
                            :placeholder="t('features.content_templates.form.titlePlaceholder')"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label>
                            {{ t('features.content_templates.form.body') }}
                        </Label>
                        <RichTextEditor
                            v-model="form.body"
                            :placeholder="t('features.content_templates.form.bodyPlaceholder')"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="excerpt">
                            {{ t('features.content_templates.form.excerpt') }}
                        </Label>
                        <Textarea
                            id="excerpt"
                            v-model="form.excerpt"
                            rows="3"
                            :placeholder="t('features.content_templates.form.excerptPlaceholder')"
                        />
                    </div>
                </CardContent>
            </Card>

            <div class="flex justify-end space-x-3">
                <Button variant="outline" as-child>
                    <router-link :to="{ name: 'content-templates' }">
                        {{ t('features.content_templates.form.cancel') }}
                    </router-link>
                </Button>
                <Button type="submit" :disabled="saving">
                    <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                    <Save v-else class="w-4 h-4 mr-2" />
                    {{ saving ? t('features.content_templates.form.updating') : t('features.content_templates.form.update') }}
                </Button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseSingleResponse } from '../../../utils/responseParser';
import Card from '../../../components/ui/card.vue';
import CardHeader from '../../../components/ui/card-header.vue';
import CardTitle from '../../../components/ui/card-title.vue';
import CardContent from '../../../components/ui/card-content.vue';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Label from '../../../components/ui/label.vue';
import Textarea from '../../../components/ui/textarea.vue';
import Select from '../../../components/ui/select.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import RichTextEditor from '../../../components/RichTextEditor.vue';
import { 
    ChevronLeft, 
    Save, 
    Loader2 
} from 'lucide-vue-next';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const templateId = route.params.id;

const loading = ref(false);
const saving = ref(false);

const form = ref({
    name: '',
    description: '',
    type: 'post',
    title: '',
    body: '',
    excerpt: '',
});

const fetchTemplate = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/content-templates/${templateId}`);
        const template = parseSingleResponse(response) || {};
        
        form.value = {
            name: template.name || '',
            description: template.description || '',
            type: template.type || 'post',
            title: template.title || '',
            body: template.body || '',
            excerpt: template.excerpt || '',
        };
    } catch (error) {
        console.error('Failed to fetch template:', error);
        alert(t('features.content_templates.messages.loadError'));
        router.push({ name: 'content-templates' });
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        await api.put(`/admin/cms/content-templates/${templateId}`, form.value);
        router.push({ name: 'content-templates' });
    } catch (error) {
        console.error('Failed to update template:', error);
        alert(error.response?.data?.message || t('features.content_templates.messages.updateError'));
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchTemplate();
});
</script>

