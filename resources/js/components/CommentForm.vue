<template>
    <Card class="comment-form">
        <CardHeader>
            <CardTitle>{{ $t('features.comments.leaveComment') }}</CardTitle>
        </CardHeader>
        <CardContent>
            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div v-if="!authStore.isAuthenticated" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="name">{{ $t('features.comments.name') }}</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="email">{{ $t('features.comments.email') }}</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                        />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="body">{{ $t('features.comments.comment') }}</Label>
                    <Textarea
                        id="body"
                        v-model="form.body"
                        rows="4"
                        required
                    />
                </div>

                <Button
                    type="submit"
                    :disabled="loading"
                    class="w-full sm:w-auto"
                >
                    <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                    {{ loading ? $t('features.comments.submitting') : $t('features.comments.submit') }}
                </Button>
            </form>
        </CardContent>
    </Card>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';
import { Loader2 } from 'lucide-vue-next';
import Card from './ui/card.vue';
import CardHeader from './ui/card-header.vue';
import CardTitle from './ui/card-title.vue';
import CardContent from './ui/card-content.vue';
import Input from './ui/input.vue';
import Label from './ui/label.vue';
import Textarea from './ui/textarea.vue';
import Button from './ui/button.vue';
import Button from './ui/button.vue';
import toast from '../services/toast';

const props = defineProps({
    contentId: {
        type: [Number, String],
        required: true,
    },
    parentId: {
        type: [Number, String],
        default: null,
    },
});

const emit = defineEmits(['submitted']);
const { t } = useI18n();
const authStore = useAuthStore();

const form = ref({
    name: '',
    email: '',
    body: '',
    parent_id: props.parentId,
});

const loading = ref(false);

const handleSubmit = async () => {
    loading.value = true;
    try {
        await api.post(`/cms/contents/${props.contentId}/comments`, form.value);
        
        // Reset form
        form.value = {
            name: '',
            email: '',
            body: '',
            parent_id: props.parentId,
        };

        emit('submitted');
        toast.success(t('common.messages.success.created', { item: 'Comment' }));
    } catch (error) {
        console.error('Error posting comment:', error);
        toast.error('Error', t('features.comments.error'));
    } finally {
        loading.value = false;
    }
};
</script>


