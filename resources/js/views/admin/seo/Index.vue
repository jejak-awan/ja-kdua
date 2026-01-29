<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.seo.title') }}</h1>
        </div>

        <!-- Shadcn Tabs -->
        <Card>
            <Tabs v-model="activeTab" class="w-full">
                <div class="mb-10 px-6 pt-6">
                    <TabsList class="bg-transparent p-0 h-auto gap-0">
                        <TabsTrigger value="sitemap" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                            <Globe class="w-4 h-4 mr-2" />
                            {{ $t('features.seo.tabs.sitemap') }}
                        </TabsTrigger>
                        <TabsTrigger value="robots" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                            <FileText class="w-4 h-4 mr-2" />
                            {{ $t('features.seo.tabs.robots') }}
                        </TabsTrigger>
                        <TabsTrigger value="analysis" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                            <Search class="w-4 h-4 mr-2" />
                            {{ $t('features.seo.tabs.analysis') }}
                        </TabsTrigger>
                        <TabsTrigger value="schema" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                            <FileJson class="w-4 h-4 mr-2" />
                            {{ $t('features.seo.tabs.schema') }}
                        </TabsTrigger>
                    </TabsList>
                </div>

                <!-- Sitemap Tab -->
                <TabsContent value="sitemap" class="p-6 space-y-6">
                    <!-- ... rest of content ... -->
                        <div>
                            <h3 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.seo.sitemap.title') }}</h3>
                            <Card class="bg-muted/50 border-none">
                                <CardContent class="p-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[10px] uppercase tracking-wider text-muted-foreground mb-1 font-bold">
                                                {{ $t('features.seo.sitemap.urlLabel') }}
                                            </p>
                                            <code class="text-sm font-mono text-indigo-500 truncate block">
                                                {{ windowLocationOrigin }}{{ sitemapUrl }}
                                            </code>
                                        </div>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="copySitemapUrl"
                                        >
                                            <Copy class="w-4 h-4 mr-2" />
                                            {{ $t('features.seo.sitemap.copyUrl') }}
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                        <Button
                            @click="generateSitemap"
                            :disabled="generatingSitemap"
                            size="lg"
                        >
                            <Loader2 v-if="generatingSitemap" class="w-4 h-4 mr-2 animate-spin" />
                            <RefreshCw v-else class="w-4 h-4 mr-2" />
                            {{ generatingSitemap ? $t('features.seo.sitemap.generating') : $t('features.seo.sitemap.generate') }}
                        </Button>
                    </TabsContent>

                    <!-- Robots.txt Tab -->
                    <TabsContent value="robots" class="p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.seo.robots.title') }}</h3>
                            <div class="space-y-2 mb-4">
                                <Label class="text-sm font-medium">
                                    {{ $t('features.seo.robots.contentLabel') }}
                                </Label>
                                <Textarea
                                    v-model="robotsContent"
                                    :rows="15"
                                    class="font-mono text-sm resize-none"
                                    :placeholder="$t('features.seo.robots.placeholder')"
                                />
                            </div>
                            <div class="flex gap-3">
                                <Button
                                    @click="saveRobotsTxt"
                                    :disabled="savingRobots || !isDirty"
                                >
                                    <Loader2 v-if="savingRobots" class="w-4 h-4 mr-2 animate-spin" />
                                    <Save v-else class="w-4 h-4 mr-2" />
                                    {{ savingRobots ? $t('features.seo.robots.saving') : $t('features.seo.robots.save') }}
                                </Button>
                                <Button
                                    variant="outline"
                                    @click="fetchRobotsTxt"
                                    :disabled="!isDirty && !!robotsContent"
                                >
                                    <RotateCcw class="w-4 h-4 mr-2" />
                                    {{ $t('features.seo.robots.reload') }}
                                </Button>
                            </div>
                        </div>
                    </TabsContent>

                    <!-- SEO Analysis Tab -->
                    <TabsContent value="analysis" class="p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.seo.analysis.title') }}</h3>
                            <div class="space-y-2 mb-4">
                                <Label>{{ $t('features.seo.analysis.selectContent') }}</Label>
                                <Select v-model="selectedContentId">
                                    <SelectTrigger>
                                        <SelectValue :placeholder="$t('features.seo.analysis.placeholder')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem 
                                            v-for="content in contents" 
                                            :key="content.id" 
                                            :value="content.id"
                                        >
                                            {{ content.title }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button
                                @click="runAnalysis"
                                :disabled="analyzing || !selectedContentId"
                            >
                                <Loader2 v-if="analyzing" class="w-4 h-4 mr-2 animate-spin" />
                                <Activity v-else class="w-4 h-4 mr-2" />
                                {{ analyzing ? $t('features.seo.analysis.analyzing') : $t('features.seo.analysis.run') }}
                            </Button>
                        </div>

                        <!-- Analysis Results -->
                        <div v-if="analysisResults" class="space-y-4">
                            <h4 class="text-md font-semibold text-foreground">{{ $t('features.seo.analysis.results') }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <Card
                                    v-for="(result, key) in analysisResults"
                                    :key="key"
                                    class="bg-muted/30"
                                >
                                    <CardContent class="p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-semibold text-foreground capitalize">{{ (key as string).replace(/_/g, ' ') }}</span>
                                            <Badge
                                                :variant="result.score >= 80 ? 'default' : result.score >= 60 ? 'secondary' : 'destructive'"
                                                class="font-bold"
                                                :class="[
                                                    result.score >= 80 ? 'bg-green-500/10 text-green-500 border-green-500/20' :
                                                    result.score >= 60 ? 'bg-yellow-500/10 text-yellow-600 border-yellow-500/20' :
                                                    'bg-destructive/10 text-destructive border-destructive/20'
                                                ]"
                                            >
                                                {{ $t('features.seo.analysis.score', { score: result.score }) }}
                                            </Badge>
                                        </div>
                                        <p class="text-sm text-muted-foreground mb-3">{{ result.message }}</p>
                                        <ul v-if="result.suggestions && result.suggestions.length > 0" class="space-y-1">
                                            <li
                                                v-for="(suggestion, idx) in result.suggestions"
                                                :key="idx"
                                                class="text-xs text-muted-foreground flex items-start"
                                            >
                                                <span class="mr-2 mt-0.5 text-indigo-500">•</span>
                                                {{ suggestion }}
                                            </li>
                                        </ul>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </TabsContent>

                    <!-- Schema Generation Tab -->
                    <TabsContent value="schema" class="p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.seo.schema.title') }}</h3>
                            <div class="space-y-2 mb-4">
                                <Label>{{ $t('features.seo.schema.selectContent') }}</Label>
                                <Select v-model="selectedContentForSchema">
                                    <SelectTrigger>
                                        <SelectValue :placeholder="$t('features.seo.analysis.placeholder')" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem 
                                            v-for="content in contents" 
                                            :key="content.id" 
                                            :value="content.id"
                                        >
                                            {{ content.title }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button
                                @click="generateSchema"
                                :disabled="generatingSchema || !selectedContentForSchema"
                            >
                                <Loader2 v-if="generatingSchema" class="w-4 h-4 mr-2 animate-spin" />
                                <Code2 v-else class="w-4 h-4 mr-2" />
                                {{ generatingSchema ? $t('features.seo.schema.generating') : $t('features.seo.schema.generate') }}
                            </Button>
                        </div>

                        <!-- Schema JSON -->
                        <Card v-if="schemaJson" class="bg-muted/30 overflow-hidden">
                            <CardHeader class="flex flex-row items-center justify-between py-3 px-6 bg-muted/50 border-b">
                                <CardTitle class="text-sm font-medium">{{ $t('features.seo.schema.jsonTitle') }}</CardTitle>
                                <Button
                                    size="sm"
                                    variant="secondary"
                                    @click="copySchema"
                                >
                                    <Copy class="w-3.5 h-3.5 mr-2" />
                                    {{ $t('features.seo.schema.copy') }}
                                </Button>
                            </CardHeader>
                            <CardContent class="p-0">
                                <pre class="p-6 overflow-x-auto text-[13px] font-mono leading-relaxed bg-card text-foreground">{{ schemaJson }}</pre>
                            </CardContent>
                        </Card>
                    </TabsContent>
            </Tabs>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';

import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { useToast } from '../../../composables/useToast';
import Card from '../../../components/ui/card.vue';
import CardHeader from '../../../components/ui/card-header.vue';
import CardTitle from '../../../components/ui/card-title.vue';
import CardContent from '../../../components/ui/card-content.vue';
import Button from '../../../components/ui/button.vue';
import Textarea from '../../../components/ui/textarea.vue';
import Label from '../../../components/ui/label.vue';
import Badge from '../../../components/ui/badge.vue';
import Select from '../../../components/ui/select.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import Tabs from '../../../components/ui/tabs.vue';
import TabsList from '../../../components/ui/tabs-list.vue';
import TabsTrigger from '../../../components/ui/tabs-trigger.vue';
import TabsContent from '../../../components/ui/tabs-content.vue';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import FileJson from 'lucide-vue-next/dist/esm/icons/file-braces.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import Save from 'lucide-vue-next/dist/esm/icons/save.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import Code2 from 'lucide-vue-next/dist/esm/icons/code-xml.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';

const { t } = useI18n();
const toast = useToast();
const activeTab = ref('sitemap');
const tabs = computed(() => [
    { id: 'sitemap', label: t('features.seo.tabs.sitemap') },
    { id: 'robots', label: t('features.seo.tabs.robots') },
    { id: 'analysis', label: t('features.seo.tabs.analysis') },
    { id: 'schema', label: t('features.seo.tabs.schema') },
]);

const windowLocationOrigin = ref(window.location.origin);
const sitemapUrl = ref('/sitemap.xml');
const generatingSitemap = ref(false);
const robotsContent = ref('');
const savingRobots = ref(false);
const contents = ref<any[]>([]);
const selectedContentId = ref<any>(null);
const analyzing = ref(false);
const analysisResults = ref<any>(null);
const selectedContentForSchema = ref<any>(null);
const generatingSchema = ref(false);
const schemaJson = ref<any>(null);
const initialRobotsContent = ref('');

const isDirty = computed(() => {
    return robotsContent.value !== initialRobotsContent.value;
});

const fetchRobotsTxt = async () => {
    try {
        const response = await api.get('/admin/ja/seo/robots-txt');
        const data = parseSingleResponse<any>(response) || {};
        robotsContent.value = data.content || '';
        initialRobotsContent.value = robotsContent.value;
    } catch (error: any) {
        console.error('Failed to fetch robots.txt:', error);
    }
};

const saveRobotsTxt = async () => {
    savingRobots.value = true;
    try {
        await api.put('/admin/ja/seo/robots-txt', { content: robotsContent.value });
        initialRobotsContent.value = robotsContent.value;
        toast.success.save();
    } catch (error: any) {
        console.error('Failed to save robots.txt:', error);
        toast.error.fromResponse(error);
    } finally {
        savingRobots.value = false;
    }
};

const generateSitemap = async () => {
    generatingSitemap.value = true;
    try {
        await api.get('/admin/ja/seo/sitemap');
        toast.success.action(t('features.seo.sitemap.generated'));
    } catch (error: any) {
        console.error('Failed to generate sitemap:', error);
        toast.error.fromResponse(error);
    } finally {
        generatingSitemap.value = false;
    }
};

const copySitemapUrl = () => {
    navigator.clipboard.writeText(window.location.origin + sitemapUrl.value);
    toast.success.default(t('features.seo.sitemap.copySuccess'));
};

const fetchContents = async () => {
    try {
        const response = await api.get('/admin/ja/contents');
        const { data } = parseResponse(response);
        contents.value = ensureArray(data);
    } catch (error: any) {
        console.error('Failed to fetch contents:', error);
        contents.value = [];
    }
};

const runAnalysis = async () => {
    if (!selectedContentId.value) return;
    
    analyzing.value = true;
    try {
        const response = await api.get(`/admin/ja/contents/${selectedContentId.value}/seo-analysis`);
        analysisResults.value = parseSingleResponse<any>(response) || {};
    } catch (error: any) {
        console.error('Failed to run SEO analysis:', error);
        toast.error.fromResponse(error);
    } finally {
        analyzing.value = false;
    }
};

const generateSchema = async () => {
    if (!selectedContentForSchema.value) return;
    
    generatingSchema.value = true;
    try {
        const response = await api.get(`/admin/ja/contents/${selectedContentForSchema.value}/schema`);
        const schema = parseSingleResponse<any>(response) || {};
        schemaJson.value = JSON.stringify(schema, null, 2);
    } catch (error: any) {
        console.error('Failed to generate schema:', error);
        toast.error.fromResponse(error);
    } finally {
        generatingSchema.value = false;
    }
};

const copySchema = () => {
    if (schemaJson.value) {
        navigator.clipboard.writeText(schemaJson.value);
        toast.success.default(t('features.seo.schema.copySuccess'));
    }
};

onMounted(() => {
    fetchRobotsTxt();
    fetchContents();
});
</script>

