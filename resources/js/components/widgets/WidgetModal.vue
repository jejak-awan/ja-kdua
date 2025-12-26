<template>
    <Dialog :open="true" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>
                    {{ widget ? $t('features.widgets.modals.widget.titleEdit') : $t('features.widgets.modals.widget.titleCreate') }}
                </DialogTitle>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-4 max-h-[70vh] overflow-y-auto pr-2">
                <div class="space-y-2">
                    <Label>{{ $t('features.widgets.modals.widget.name') }} <span class="text-red-500">*</span></Label>
                    <Input v-model="form.name" type="text" required />
                </div>
                <div class="space-y-2">
                    <Label>{{ $t('features.widgets.modals.widget.type') }} <span class="text-red-500">*</span></Label>
                    <Select v-model="form.type">
                        <SelectTrigger>
                            <SelectValue :placeholder="$t('features.widgets.modals.widget.type')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="text">Text</SelectItem>
                            <SelectItem value="html">HTML</SelectItem>
                            <SelectItem value="menu">Menu</SelectItem>
                            <SelectItem value="custom">Custom</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="space-y-2">
                    <Label>{{ $t('features.widgets.modals.widget.position') }}</Label>
                    <Input v-model="form.position" type="text" :placeholder="$t('features.widgets.modals.widget.positionPlaceholder')" />
                </div>
                <div class="space-y-2">
                    <Label>{{ $t('features.widgets.modals.widget.content') }}</Label>
                    <Textarea v-model="form.content" :rows="6" />
                </div>
                <div class="flex items-center space-x-2 pt-2">
                    <Checkbox id="is_active" v-model:checked="form.is_active" />
                    <Label for="is_active" class="text-sm font-normal cursor-pointer">
                        {{ $t('features.widgets.modals.widget.active') }}
                    </Label>
                </div>
            </form>

            <DialogFooter>
                <Button variant="outline" @click="$emit('close')">
                    {{ $t('common.cancel') }}
                </Button>
                <Button @click="handleSubmit" :disabled="saving">
                    <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                    {{ saving ? $t('features.widgets.modals.widget.saving') : (widget ? $t('common.update') : $t('common.create')) }}
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
import Textarea from '../ui/textarea.vue';
import Checkbox from '../ui/checkbox.vue';
import Select from '../ui/select.vue';
import SelectTrigger from '../ui/select-trigger.vue';
import SelectValue from '../ui/select-value.vue';
import SelectContent from '../ui/select-content.vue';
import SelectItem from '../ui/select-item.vue';
import { Loader2 } from 'lucide-vue-next';

const props = defineProps({ widget: { type: Object, default: null } });
const emit = defineEmits(['close', 'saved']);

const { t } = useI18n();
const saving = ref(false);
const form = ref({ name: '', type: 'text', position: '', content: '', is_active: true });

const loadWidget = () => {
    if (props.widget) {
        form.value = { ...props.widget };
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        if (props.widget) {
            await api.put(`/admin/cms/widgets/${props.widget.id}`, form.value);
        } else {
            await api.post('/admin/cms/widgets', form.value);
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save widget:', error);
        alert(t('features.widgets.messages.saveFailed'));
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadWidget();
});
</script>

