<template>
    <Card class="border-none shadow-sm bg-card/50 overflow-hidden">
        <CardHeader class="pb-4">
            <CardTitle class="text-xl font-bold flex items-center gap-2">
                <Image class="w-5 h-5 text-primary" />
                {{ $t('features.content.form.featuredImage') }}
            </CardTitle>
        </CardHeader>
        <CardContent>
            <div class="space-y-4">
                <div v-if="modelValue" class="relative group aspect-video">
                    <img
                        :src="modelValue"
                        alt="Featured Image"
                        class="w-full h-full object-cover rounded-lg border border-border/40 shadow-sm"
                    />
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-lg">
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="$emit('update:modelValue', null)"
                        >
                            <Trash2 class="w-4 h-4 mr-2" />
                            {{ $t('features.content.form.remove') }}
                        </Button>
                    </div>
                </div>
                <div v-else class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-border/40 rounded-lg bg-background/30 hover:bg-background/50 transition-colors">
                    <MediaPicker
                        @selected="(media) => $emit('update:modelValue', media.url)"
                        :label="$t('features.content.form.selectImage')"
                    >
                        <template #trigger>
                            <Button variant="outline" class="gap-2">
                                <Plus class="w-4 h-4" />
                                {{ $t('features.content.form.selectImage') }}
                            </Button>
                        </template>
                    </MediaPicker>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
import MediaPicker from '../MediaPicker.vue';
import Card from '@/components/ui/card.vue';
import CardHeader from '@/components/ui/card-header.vue';
import CardTitle from '@/components/ui/card-title.vue';
import CardContent from '@/components/ui/card-content.vue';
import Button from '@/components/ui/button.vue';
import { Image, Trash2, Plus } from 'lucide-vue-next';

const { t } = useI18n();

defineProps({
    modelValue: {
        type: [String, null],
        default: null,
    },
});

defineEmits(['update:modelValue']);
</script>
