<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.menus.title') }}</h1>
            <Button
                @click="showCreateModal = true"
            >
                <Plus class="w-4 h-4 mr-2" />
                {{ t('features.menus.actions.create') }}
            </Button>
        </div>

        <Card>
            <div v-if="loading" class="p-12 text-center">
                <Loader2 class="w-8 h-8 animate-spin mx-auto text-muted-foreground mb-2" />
                <p class="text-muted-foreground">{{ t('features.menus.messages.loading') }}</p>
            </div>

            <div v-else-if="menus.length === 0" class="p-12 text-center">
                <MenuSquare class="w-12 h-12 mx-auto text-muted-foreground/20 mb-4" />
                <p class="text-muted-foreground">{{ t('features.menus.messages.empty') }}</p>
            </div>

            <Table v-else>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ t('features.menus.headers.name') }}</TableHead>
                        <TableHead>{{ t('features.menus.headers.location') }}</TableHead>
                        <TableHead>{{ t('features.menus.headers.items') }}</TableHead>
                        <TableHead class="text-right">{{ t('features.menus.headers.actions') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="menu in menus" :key="menu.id">
                        <TableCell class="font-medium">{{ menu.name }}</TableCell>
                        <TableCell>
                            <Badge variant="outline" v-if="menu.location">
                                {{ menu.location }}
                            </Badge>
                            <span v-else class="text-muted-foreground">-</span>
                        </TableCell>
                        <TableCell>
                            <Badge variant="secondary">
                                {{ menu.items_count || 0 }} {{ t('features.menus.headers.items') }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-2">
                                <Button
                                    as="router-link"
                                    :to="{ name: 'menus.edit', params: { id: menu.id } }"
                                    variant="ghost"
                                    size="sm"
                                >
                                    <Pencil class="w-4 h-4 mr-2" />
                                    {{ t('features.menus.actions.edit') }}
                                </Button>
                                <Button
                                    @click="deleteMenu(menu)"
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive hover:text-destructive hover:bg-destructive/10"
                                >
                                    <Trash2 class="w-4 h-4 mr-2" />
                                    {{ t('features.menus.actions.delete') }}
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </Card>

        <!-- Create Menu Modal -->
        <MenuModal
            v-if="showCreateModal"
            @close="showCreateModal = false"
            @saved="handleMenuSaved"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '../../../services/api';
import toast from '../../../services/toast';
import { useConfirm } from '../../../composables/useConfirm';
import MenuModal from '../../../components/menus/MenuModal.vue';
import Button from '../../../components/ui/button.vue';
import Card from '../../../components/ui/card.vue';
import Badge from '../../../components/ui/badge.vue';
import Table from '../../../components/ui/table.vue';
import TableHeader from '../../../components/ui/table-header.vue';
import TableRow from '../../../components/ui/table-row.vue';
import TableHead from '../../../components/ui/table-head.vue';
import TableBody from '../../../components/ui/table-body.vue';
import TableCell from '../../../components/ui/table-cell.vue';
import { 
    Plus, Pencil, Trash2, 
    Loader2, MenuSquare 
} from 'lucide-vue-next';

const { t } = useI18n();
const router = useRouter();
const { confirm } = useConfirm();
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const menus = ref([]);
const loading = ref(false);
const showCreateModal = ref(false);

const fetchMenus = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/menus');
        const { data } = parseResponse(response);
        menus.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch menus:', error);
    } finally {
        loading.value = false;
    }
};

const deleteMenu = async (menu) => {
    const confirmed = await confirm({
        title: t('features.menus.actions.delete'),
        message: t('features.menus.messages.deleteConfirm', { name: menu.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/admin/cms/menus/${menu.id}`);
        toast.success(t('features.menus.messages.deleteSuccess'));
        fetchMenus();
    } catch (error) {
        console.error('Error deleting menu:', error);
        toast.error('Error', error.response?.data?.message || t('features.menus.messages.deleteFailed'));
    }
};

const handleMenuSaved = () => {
    fetchMenus();
    showCreateModal.value = false;
};

onMounted(() => {
    fetchMenus();
});
</script>

