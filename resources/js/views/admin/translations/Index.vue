<template>
    <div>
        <div class="mb-6 flex items-center gap-4">
            <Button
                variant="ghost"
                size="icon"
                @click="router.push({ name: 'languages.index' })"
            >
                <ChevronLeft class="w-5 h-5" />
            </Button>
            <div>
                <h1 class="text-2xl font-bold text-foreground">Translations</h1>
                <p class="text-sm text-muted-foreground">Language: {{ languageCode }}</p>
            </div>
        </div>

        <Card>
            <CardHeader class="pb-3">
                <div class="flex items-center gap-4">
                    <div class="relative flex-1">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Search translations..."
                            class="pl-9"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="loading" class="flex flex-col items-center justify-center py-12">
                    <Loader2 class="w-8 h-8 animate-spin text-muted-foreground mb-2" />
                    <p class="text-muted-foreground">Loading...</p>
                </div>
                <div v-else-if="filteredTranslations.length === 0" class="flex flex-col items-center justify-center py-12">
                    <Search class="w-12 h-12 text-muted-foreground/20 mb-4" />
                    <p class="text-muted-foreground">No translations found</p>
                </div>
                <Table v-else>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[30%]">Key</TableHead>
                            <TableHead>Translation</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="translation in filteredTranslations" :key="translation.id">
                            <TableCell class="font-medium font-mono text-xs">{{ translation.key }}</TableCell>
                            <TableCell class="text-sm">{{ translation.value || '-' }}</TableCell>
                            <TableCell class="text-right">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="openEditModal(translation)"
                                >
                                    <Pencil class="w-4 h-4 mr-2" />
                                    Edit
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <!-- Edit Translation Modal -->
        <Dialog :open="showEditModal" @update:open="showEditModal = $event">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>Edit Translation</DialogTitle>
                    <DialogDescription>
                        Key: <code class="text-xs">{{ editingTranslation?.key }}</code>
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label>Value</Label>
                        <Textarea 
                            v-model="editValue" 
                            rows="4"
                            placeholder="Enter translation..."
                        />
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showEditModal = false">
                        Cancel
                    </Button>
                    <Button 
                        @click="handleUpdate" 
                        :disabled="updating"
                    >
                        <Loader2 v-if="updating" class="w-4 h-4 mr-2 animate-spin" />
                        Save Changes
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n'; 
import toast from '../../../services/toast';
import { useConfirm } from '../../../composables/useConfirm';
import api from '../../../services/api';
import Card from '../../../components/ui/card.vue';
import CardHeader from '../../../components/ui/card-header.vue';
import CardTitle from '../../../components/ui/card-title.vue';
import CardContent from '../../../components/ui/card-content.vue';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Textarea from '../../../components/ui/textarea.vue';
import Label from '../../../components/ui/label.vue';
import Dialog from '../../../components/ui/dialog.vue';
import DialogContent from '../../../components/ui/dialog-content.vue';
import DialogHeader from '../../../components/ui/dialog-header.vue';
import DialogTitle from '../../../components/ui/dialog-title.vue';
import DialogDescription from '../../../components/ui/dialog-description.vue';
import DialogFooter from '../../../components/ui/dialog-footer.vue';
import Table from '../../../components/ui/table.vue';
import TableHeader from '../../../components/ui/table-header.vue';
import TableRow from '../../../components/ui/table-row.vue';
import TableHead from '../../../components/ui/table-head.vue';
import TableBody from '../../../components/ui/table-body.vue';
import TableCell from '../../../components/ui/table-cell.vue';
import { 
    ChevronLeft, Search, Pencil, 
    Loader2 
} from 'lucide-vue-next';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const route = useRoute();
const router = useRouter();
const { t, locale } = useI18n();
const { confirm } = useConfirm();

const languageCode = route.params.lang || 'en';

const translations = ref([]);
const loading = ref(false);
const search = ref('');

const showEditModal = ref(false);
const editingTranslation = ref(null);
const editValue = ref('');
const updating = ref(false);

const openEditModal = (translation) => {
    editingTranslation.value = translation;
    editValue.value = translation.value || '';
    showEditModal.value = true;
};

const handleUpdate = async () => {
    if (!editingTranslation.value) return;
    
    updating.value = true;
    try {
        await api.put(`/admin/cms/translations/${editingTranslation.value.id}`, { 
            value: editValue.value 
        });
        showEditModal.value = false;
        await fetchTranslations();
        toast.success(t('features.translations.messages.update_success')); // Assuming a success message
    } catch (error) {
        console.error('Failed to update translation:', error);
        toast.error('Failed to update translation');
    } finally {
        updating.value = false;
    }
};

const filteredTranslations = computed(() => {
    if (!search.value) return translations.value;
    const searchLower = search.value.toLowerCase();
    return translations.value.filter(t => 
        t.key.toLowerCase().includes(searchLower) || 
        (t.value && t.value.toLowerCase().includes(searchLower))
    );
});

const fetchTranslations = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/translations/${languageCode}`);
        const { data } = parseResponse(response);
        translations.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch translations:', error);
        translations.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchTranslations();
});
</script>

