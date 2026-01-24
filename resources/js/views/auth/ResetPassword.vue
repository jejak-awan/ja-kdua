<template>
    <div class="min-h-screen flex items-center justify-center bg-muted/40 px-4 py-12 sm:px-6 lg:px-8">
        <Card class="w-full max-w-md">
            <CardHeader class="space-y-1">
                <CardTitle class="text-2xl font-bold text-center tracking-tight">
                    {{ t('features.auth.resetPassword.title') }}
                </CardTitle>
                <CardDescription class="text-center">
                    Enter your new password below
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form class="space-y-4" @submit.prevent="handleSubmit">
                    <div class="space-y-2">
                        <Label for="email">{{ t('common.labels.email') }}</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            required
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="token">{{ t('features.auth.resetPassword.tokenLabel') }}</Label>
                        <Input
                            id="token"
                            v-model="form.token"
                            name="token"
                            type="text"
                            required
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="password">{{ t('common.labels.password') }}</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            required
                            :class="errors.password ? 'border-destructive focus-visible:ring-destructive' : ''"
                        />
                        <p v-if="errors.password" class="text-sm text-destructive font-medium">{{ errors.password[0] }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="password_confirmation">{{ t('common.labels.confirmPassword') }}</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                        />
                    </div>

                    <div v-if="message" class="rounded-md p-3 text-sm border" :class="messageType === 'error' ? 'bg-destructive/15 text-destructive border-destructive/20' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'">
                        {{ message }}
                    </div>

                    <Button type="submit" class="w-full" :disabled="loading || !isValid">
                        <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                        <span v-if="loading">{{ t('common.messages.loading.processing') }}</span>
                        <span v-else>{{ t('features.auth.resetPassword.submit') }}</span>
                    </Button>

                    <div class="text-center text-sm text-muted-foreground mt-4">
                        <router-link :to="{ name: 'login' }" class="flex items-center justify-center font-medium text-primary hover:underline">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            {{ t('features.auth.forgotPassword.backToLogin') }}
                        </router-link>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../../stores/auth';
import { useFormValidation } from '../../composables/useFormValidation';
import { resetPasswordSchema } from '../../schemas/auth';
import { Loader2, ArrowLeft } from 'lucide-vue-next';

// Shadcn Components
// @ts-ignore
import Card from '../../components/ui/card.vue';
// @ts-ignore
import CardHeader from '../../components/ui/card-header.vue';
// @ts-ignore
import CardTitle from '../../components/ui/card-title.vue';
// @ts-ignore
import CardDescription from '../../components/ui/card-description.vue';
// @ts-ignore
import CardContent from '../../components/ui/card-content.vue';
// @ts-ignore
import Button from '../../components/ui/button.vue';
// @ts-ignore
import Input from '../../components/ui/input.vue';
// @ts-ignore
import Label from '../../components/ui/label.vue';

const router = useRouter();
const route = useRoute();
const { t } = useI18n();
const authStore = useAuthStore();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(resetPasswordSchema);

const form = reactive({
    email: '',
    token: '',
    password: '',
    password_confirmation: '',
});

const isValid = computed(() => {
    return !!form.email && 
           !!form.token && 
           !!form.password && 
           !!form.password_confirmation &&
           form.password === form.password_confirmation;
});

const message = ref('');
const messageType = ref('');
const loading = ref(false);

onMounted(() => {
    if (route.query.token) {
        form.token = route.query.token as string;
    }
    if (route.query.email) {
        form.email = route.query.email as string;
    }
});

const handleSubmit = async () => {
    // Client-side validation first
    if (!validateWithZod(form)) {
        return;
    }

    loading.value = true;
    clearErrors();
    message.value = '';

    const result = await authStore.resetPassword(form);

    if (result.success) {
        message.value = result.message || '';
        messageType.value = 'success';
        setTimeout(() => {
            router.push({ name: 'login' });
        }, 2000);
    } else {
        message.value = result.message || '';
        messageType.value = 'error';
        setErrors(result.errors || {});
    }

    loading.value = false;
};
</script>

