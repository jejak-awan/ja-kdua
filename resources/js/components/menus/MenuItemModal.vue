<template>
    <Dialog :open="true" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>
                    {{ item ? t('features.menus.form.editItemTitle') : t('features.menus.form.createItemTitle') }}
                </DialogTitle>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-4 max-h-[70vh] overflow-y-auto pr-2">
                <div class="space-y-2">
                    <Label>{{ t('features.menus.form.label') }} <span class="text-red-500">*</span></Label>
                    <Input v-model="form.label" type="text" required />
                </div>

                <div class="space-y-2">
                    <Label>{{ t('features.menus.form.type') }} <span class="text-red-500">*</span></Label>
                    <Select v-model="form.type" @update:modelValue="handleTypeChange">
                        <SelectTrigger>
                            <SelectValue :placeholder="t('features.menus.form.type')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="link">{{ t('features.menus.form.types.link') }}</SelectItem>
                            <SelectItem value="page">{{ t('features.menus.form.types.page') }}</SelectItem>
                            <SelectItem value="category">{{ t('features.menus.form.types.category') }}</SelectItem>
                            <SelectItem value="content">{{ t('features.menus.form.types.content') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div v-if="form.type === 'link'" class="space-y-2">
                    <Label>{{ t('features.menus.form.url') }} <span class="text-red-500">*</span></Label>
                    <Input v-model="form.url" type="url" required />
                </div>

                <div v-else-if="form.type === 'page'" class="space-y-2">
                    <Label>{{ t('features.menus.form.page') }}</Label>
                    <Select v-model="form.target_id">
                        <SelectTrigger>
                            <SelectValue :placeholder="t('features.menus.form.placeholders.selectPage')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="null">{{ t('features.menus.form.placeholders.selectPage') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div v-else-if="form.type === 'category'" class="space-y-2">
                    <Label>{{ t('features.menus.form.category') }}</Label>
                    <Select v-model="form.target_id">
                        <SelectTrigger>
                            <SelectValue :placeholder="t('features.menus.form.placeholders.selectCategory')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="null">{{ t('features.menus.form.placeholders.selectCategory') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div v-else-if="form.type === 'content'" class="space-y-2">
                    <Label>{{ t('features.menus.form.content') }}</Label>
                    <Select v-model="form.target_id">
                        <SelectTrigger>
                            <SelectValue :placeholder="t('features.menus.form.placeholders.selectContent')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="null">{{ t('features.menus.form.placeholders.selectContent') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="space-y-2">
                    <Label>{{ t('features.menus.form.cssClasses') }}</Label>
                    <Input v-model="form.css_classes" type="text" />
                </div>

                <div class="flex items-center space-x-2 pt-2">
                    <Checkbox id="new_tab" v-model:checked="form.open_in_new_tab" />
                    <Label for="new_tab" class="text-sm font-normal cursor-pointer">
                        {{ t('features.menus.form.openInNewTab') }}
                    </Label>
                </div>
            </form>

            <DialogFooter>
                <Button variant="outline" @click="$emit('close')">
                    {{ t('features.menus.actions.cancel') }}
                </Button>
                <Button @click="handleSubmit" :disabled="saving">
                    <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                    {{ saving ? t('features.menus.actions.saving') : (item ? t('features.menus.actions.update') : t('features.menus.actions.createAction')) }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import Dialog from '../ui/dialog.vue';
import DialogContent from '../ui/dialog-content.vue';
import DialogHeader from '../ui/dialog-header.vue';
import DialogTitle from '../ui/dialog-title.vue';
import DialogFooter from '../ui/dialog-footer.vue';
import Button from '../ui/button.vue';
import Input from '../ui/input.vue';
import Label from '../ui/label.vue';
import Checkbox from '../ui/checkbox.vue';
import Select from '../ui/select.vue';
import SelectTrigger from '../ui/select-trigger.vue';
import SelectValue from '../ui/select-value.vue';
import SelectContent from '../ui/select-content.vue';
import SelectItem from '../ui/select-item.vue';
import { Loader2 } from 'lucide-vue-next';

const { t } = useI18n();

const props = defineProps({
    item: { type: Object, default: null },
    menuId: { type: [String, Number], required: true },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);
const form = ref({
    label: '',
    type: 'link',
    url: '',
    target_id: null,
    css_classes: '',
    open_in_new_tab: false,
    parent_id: null,
});

const handleTypeChange = () => {
    form.value.url = '';
    form.value.target_id = null;
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        const payload = { ...form.value, menu_id: props.menuId };
        if (props.item) {
            await api.put(`/admin/cms/menu-items/${props.item.id}`, payload);
        } else {
            await api.post('/admin/cms/menu-items', payload);
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save menu item:', error);
        alert(t('features.menus.messages.saveItemFailed'));
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    if (props.item) {
        form.value = { ...props.item };
    }
});
</script>

