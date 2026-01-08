<template>
    <Card class="border-t-4 border-t-primary/20">
        <CardHeader class="pb-3">
            <div class="flex items-center justify-between">
                <CardTitle class="text-base">{{ t('features.menus.form.addItems') }}</CardTitle>
                <Button 
                    variant="ghost" 
                    size="icon" 
                    class="h-8 w-8"
                    @click="$emit('collapse')"
                >
                    <PanelLeftClose class="w-4 h-4" />
                </Button>
            </div>
        </CardHeader>
        <CardContent class="p-0">
            <Accordion type="multiple" class="w-full" :default-value="['pages', 'custom']">
                <!-- Pages -->
                <AccordionItem value="pages">
                    <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
                        <div class="flex items-center gap-2 flex-1">
                            <FileText class="w-4 h-4 text-blue-500" />
                            <span>{{ t('features.menus.form.types.page') }}</span>
                            <Badge variant="secondary" class="ml-2">{{ pages.length }}</Badge>
                            <Button 
                                size="icon" 
                                variant="ghost" 
                                class="ml-auto h-6 w-6" 
                                title="Add All" 
                                @click.stop="addAll('page', pages)"
                            >
                                <Plus class="w-3 h-3" />
                            </Button>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent class="px-4 pb-4">
                        <div class="max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                            <div v-if="loadingPages" class="flex justify-center py-4">
                                <Loader2 class="w-5 h-5 animate-spin text-muted-foreground" />
                            </div>
                            <draggable
                                v-else
                                :list="pages"
                                :group="{ name: 'menu', pull: 'clone', put: false }"
                                :clone="(item) => createItem('page', item)"
                                item-key="id"
                                class="space-y-2"
                            >
                                <template #item="{ element }">
                                    <SourceItem 
                                        :item="element" 
                                        :type="'page'"
                                        @add="addItem('page', element)"
                                    />
                                </template>
                            </draggable>
                            <div v-if="!loadingPages && pages.length === 0" class="text-center py-4 text-muted-foreground text-xs">
                                {{ t('features.menus.form.noPages') }}
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionItem>

                <!-- Posts -->
                <AccordionItem value="posts">
                    <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
                        <div class="flex items-center gap-2 flex-1">
                            <File class="w-4 h-4 text-orange-500" />
                            <span>{{ t('features.menus.form.types.post') }}</span>
                            <Badge variant="secondary" class="ml-2">{{ posts.length }}</Badge>
                            <Button 
                                size="icon" 
                                variant="ghost" 
                                class="ml-auto h-6 w-6" 
                                title="Add All" 
                                @click.stop="addAll('post', posts)"
                            >
                                <Plus class="w-3 h-3" />
                            </Button>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent class="px-4 pb-4">
                        <div class="max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                            <div v-if="loadingPosts" class="flex justify-center py-4">
                                <Loader2 class="w-5 h-5 animate-spin text-muted-foreground" />
                            </div>
                            <draggable
                                v-else
                                :list="posts"
                                :group="{ name: 'menu', pull: 'clone', put: false }"
                                :clone="(item) => createItem('post', item)"
                                item-key="id"
                                class="space-y-2"
                            >
                                <template #item="{ element }">
                                    <SourceItem 
                                        :item="element" 
                                        :type="'post'"
                                        @add="addItem('post', element)"
                                    />
                                </template>
                            </draggable>
                            <div v-if="!loadingPosts && posts.length === 0" class="text-center py-4 text-muted-foreground text-xs">
                                {{ t('features.menus.form.noPosts') }}
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionItem>

                <!-- Categories -->
                <AccordionItem value="categories">
                    <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
                        <div class="flex items-center gap-2 flex-1">
                            <Tag class="w-4 h-4 text-purple-500" />
                            <span>{{ t('features.menus.form.types.category') }}</span>
                            <Badge variant="secondary" class="ml-2">{{ categories.length }}</Badge>
                            <Button 
                                size="icon" 
                                variant="ghost" 
                                class="ml-auto h-6 w-6" 
                                title="Add All" 
                                @click.stop="addAll('category', categories)"
                            >
                                <Plus class="w-3 h-3" />
                            </Button>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent class="px-4 pb-4">
                        <div class="max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                            <div v-if="loadingCategories" class="flex justify-center py-4">
                                <Loader2 class="w-5 h-5 animate-spin text-muted-foreground" />
                            </div>
                            <draggable
                                v-else
                                :list="categories"
                                :group="{ name: 'menu', pull: 'clone', put: false }"
                                :clone="(item) => createItem('category', item)"
                                item-key="id"
                                class="space-y-2"
                            >
                                <template #item="{ element }">
                                    <SourceItem 
                                        :item="element" 
                                        :type="'category'"
                                        @add="addItem('category', element)"
                                    />
                                </template>
                            </draggable>
                            <div v-if="!loadingCategories && categories.length === 0" class="text-center py-4 text-muted-foreground text-xs">
                                {{ t('features.menus.form.noCategories') }}
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionItem>

                <!-- Custom Link -->
                <AccordionItem value="custom">
                    <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
                        <div class="flex items-center gap-2">
                            <LinkIcon class="w-4 h-4 text-emerald-500" />
                            <span>{{ t('features.menus.form.customLink') }}</span>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent class="px-4 pb-4 pt-2">
                        <div class="space-y-3">
                            <div class="space-y-1.5">
                                <Label class="text-xs">{{ t('features.menus.form.linkText') }}</Label>
                                <Input v-model="customLink.title" class="h-8" :placeholder="t('features.menus.form.labelPlaceholder')" />
                            </div>
                            <div class="space-y-1.5">
                                <Label class="text-xs">{{ t('features.menus.form.url') }}</Label>
                                <Input v-model="customLink.url" class="h-8" placeholder="https://" />
                            </div>
                            <Button size="sm" class="w-full mt-2" @click="addCustomLink" :disabled="!customLink.title">
                                <PlusCircle class="w-3.5 h-3.5 mr-2" />
                                {{ t('features.menus.actions.addToMenu') }}
                            </Button>
                        </div>
                    </AccordionContent>
                </AccordionItem>

                <!-- Column Group -->
                <AccordionItem value="structure">
                    <AccordionTrigger class="px-4 py-3 hover:no-underline hover:bg-muted/50">
                        <div class="flex items-center gap-2">
                            <Columns class="w-4 h-4 text-indigo-500" />
                            <span>{{ t('features.menus.form.structure') }}</span>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent class="px-4 pb-4 pt-2">
                        <Button 
                            size="sm" 
                            variant="outline" 
                            class="w-full border-dashed"
                            @click.prevent="addColumnGroup"
                        >
                            <Columns class="w-3.5 h-3.5 mr-2" />
                            {{ t('features.menus.form.addColumnGroup') }}
                        </Button>
                    </AccordionContent>
                </AccordionItem>
            </Accordion>
        </CardContent>
    </Card>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';
import api from '../../../services/api';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import { useMenuContext } from '../../../composables/useMenu';
import { menuItemRegistry } from '../registry';

// UI Components
import Card from '../../ui/card.vue';
import CardHeader from '../../ui/card-header.vue';
import CardTitle from '../../ui/card-title.vue';
import CardContent from '../../ui/card-content.vue';
import Accordion from '../../ui/accordion.vue';
import AccordionContent from '../../ui/accordion-content.vue';
import AccordionItem from '../../ui/accordion-item.vue';
import AccordionTrigger from '../../ui/accordion-trigger.vue';
import Badge from '../../ui/badge.vue';
import Button from '../../ui/button.vue';
import Input from '../../ui/input.vue';
import Label from '../../ui/label.vue';
import SourceItem from './SourceItem.vue';

import { 
    FileText, File, Tag, Link as LinkIcon, Columns,
    Plus, PlusCircle, Loader2, PanelLeftClose
} from 'lucide-vue-next';

const { t } = useI18n();
const menuContext = useMenuContext();

defineEmits(['collapse']);

// Data sources
const pages = ref([]);
const posts = ref([]);
const categories = ref([]);
const loadingPages = ref(false);
const loadingPosts = ref(false);
const loadingCategories = ref(false);

// Custom link form
const customLink = ref({ title: '', url: 'https://' });

// Fetch data sources
const fetchPages = async () => {
    loadingPages.value = true;
    try {
        const response = await api.get('/admin/ja/contents?type=page&status=published');
        const { data } = parseResponse(response);
        pages.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch pages:', error);
    } finally {
        loadingPages.value = false;
    }
};

const fetchPosts = async () => {
    loadingPosts.value = true;
    try {
        const response = await api.get('/admin/ja/contents?type=post&status=published');
        const { data } = parseResponse(response);
        posts.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch posts:', error);
    } finally {
        loadingPosts.value = false;
    }
};

const fetchCategories = async () => {
    loadingCategories.value = true;
    try {
        const response = await api.get('/admin/ja/categories');
        const { data } = parseResponse(response);
        categories.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch categories:', error);
    } finally {
        loadingCategories.value = false;
    }
};

// Create menu item from source
const createItem = (type, sourceItem) => {
    const item = menuItemRegistry.createInstance(type, {
        title: sourceItem.title || sourceItem.name,
        target_id: sourceItem.id,
        url: sourceItem.url || null
    });
    return item;
};

// Add single item
const addItem = (type, sourceItem) => {
    const item = createItem(type, sourceItem);
    menuContext.addItem(item);
};

// Add all items of a type
const addAll = (type, items) => {
    items.forEach(sourceItem => {
        addItem(type, sourceItem);
    });
};

// Add custom link
const addCustomLink = () => {
    if (!customLink.value.title) return;
    
    const item = menuItemRegistry.createInstance('custom', {
        title: customLink.value.title,
        url: customLink.value.url || '#'
    });
    
    menuContext.addItem(item);
    customLink.value = { title: '', url: 'https://' };
};

// Add column group
const addColumnGroup = () => {
    const item = menuItemRegistry.createInstance('column_group', {
        title: t('features.menus.form.newColumnGroup')
    });
    menuContext.addItem(item);
};

onMounted(() => {
    fetchPages();
    fetchPosts();
    fetchCategories();
});
</script>

<style scoped>
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: hsl(var(--border)) transparent;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: hsl(var(--border));
    border-radius: 4px;
}
</style>
