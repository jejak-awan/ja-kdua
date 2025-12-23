<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.seo.title') }}</h1>
        </div>

        <!-- Tabs -->
        <div class="bg-card shadow rounded-lg">
            <div class="border-b border-border">
                <nav class="flex -mb-px">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                            activeTab === tab.id
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-muted-foreground hover:text-foreground hover:border-input'
                        ]"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <!-- Sitemap Tab -->
            <div v-if="activeTab === 'sitemap'" class="p-6">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.seo.sitemap.title') }}</h3>
                        <div class="bg-muted rounded-lg p-4 mb-4">
                            <p class="text-sm text-muted-foreground mb-2">
                                {{ $t('features.seo.sitemap.urlLabel') }}: <code class="px-2 py-1 bg-card rounded text-indigo-600">{{ sitemapUrl }}</code>
                            </p>
                            <button
                                @click="copySitemapUrl"
                                class="text-sm text-indigo-600 hover:text-indigo-800"
                            >
                                {{ $t('features.seo.sitemap.copyUrl') }}
                            </button>
                        </div>
                        <button
                            @click="generateSitemap"
                            :disabled="generatingSitemap"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ generatingSitemap ? $t('features.seo.sitemap.generating') : $t('features.seo.sitemap.generate') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Robots.txt Tab -->
            <div v-if="activeTab === 'robots'" class="p-6">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.seo.robots.title') }}</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-foreground mb-2">
                                {{ $t('features.seo.robots.contentLabel') }}
                            </label>
                            <textarea
                                v-model="robotsContent"
                                rows="15"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                                :placeholder="$t('features.seo.robots.placeholder')"
                            />
                        </div>
                        <div class="flex space-x-3">
                            <button
                                @click="saveRobotsTxt"
                                :disabled="savingRobots"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                            >
                                {{ savingRobots ? $t('features.seo.robots.saving') : $t('features.seo.robots.save') }}
                            </button>
                            <button
                                @click="fetchRobotsTxt"
                                class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                            >
                                {{ $t('features.seo.robots.reload') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Analysis Tab -->
            <div v-if="activeTab === 'analysis'" class="p-6">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.seo.analysis.title') }}</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-foreground mb-2">
                                {{ $t('features.seo.analysis.selectContent') }}
                            </label>
                            <select
                                v-model="selectedContentId"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option :value="null">{{ $t('features.seo.analysis.placeholder') }}</option>
                                <option
                                    v-for="content in contents"
                                    :key="content.id"
                                    :value="content.id"
                                >
                                    {{ content.title }}
                                </option>
                            </select>
                        </div>
                        <button
                            @click="runAnalysis"
                            :disabled="analyzing || !selectedContentId"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ analyzing ? $t('features.seo.analysis.analyzing') : $t('features.seo.analysis.run') }}
                        </button>
                    </div>

                    <!-- Analysis Results -->
                    <div v-if="analysisResults" class="mt-6 bg-muted rounded-lg p-6">
                        <h4 class="text-md font-semibold text-foreground mb-4">{{ $t('features.seo.analysis.results') }}</h4>
                        <div class="space-y-4">
                            <div
                                v-for="(result, key) in analysisResults"
                                :key="key"
                                class="bg-card rounded-lg p-4"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-foreground capitalize">{{ key.replace(/_/g, ' ') }}</span>
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs font-semibold rounded-full',
                                            result.score >= 80 ? 'bg-green-500/20 text-green-400' :
                                            result.score >= 60 ? 'bg-yellow-500/20 text-yellow-400' :
                                            'bg-red-500/20 text-red-400'
                                        ]"
                                    >
                                        {{ $t('features.seo.analysis.score', { score: result.score }) }}
                                    </span>
                                </div>
                                <p class="text-sm text-muted-foreground">{{ result.message }}</p>
                                <ul v-if="result.suggestions && result.suggestions.length > 0" class="mt-2 space-y-1">
                                    <li
                                        v-for="(suggestion, idx) in result.suggestions"
                                        :key="idx"
                                        class="text-xs text-muted-foreground"
                                    >
                                        • {{ suggestion }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schema Generation Tab -->
            <div v-if="activeTab === 'schema'" class="p-6">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.seo.schema.title') }}</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-foreground mb-2">
                                {{ $t('features.seo.schema.selectContent') }}
                            </label>
                            <select
                                v-model="selectedContentForSchema"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option :value="null">{{ $t('features.seo.analysis.placeholder') }}</option>
                                <option
                                    v-for="content in contents"
                                    :key="content.id"
                                    :value="content.id"
                                >
                                    {{ content.title }}
                                </option>
                            </select>
                        </div>
                        <button
                            @click="generateSchema"
                            :disabled="generatingSchema || !selectedContentForSchema"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ generatingSchema ? $t('features.seo.schema.generating') : $t('features.seo.schema.generate') }}
                        </button>
                    </div>

                    <!-- Schema JSON -->
                    <div v-if="schemaJson" class="mt-6 bg-muted rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-md font-semibold text-foreground">{{ $t('features.seo.schema.jsonTitle') }}</h4>
                            <button
                                @click="copySchema"
                                class="px-3 py-1 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                {{ $t('features.seo.schema.copy') }}
                            </button>
                        </div>
                        <pre class="bg-card rounded-lg p-4 overflow-x-auto text-xs font-mono">{{ schemaJson }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';

const { t } = useI18n();
const activeTab = ref('sitemap');
const tabs = computed(() => [
    { id: 'sitemap', label: t('features.seo.tabs.sitemap') },
    { id: 'robots', label: t('features.seo.tabs.robots') },
    { id: 'analysis', label: t('features.seo.tabs.analysis') },
    { id: 'schema', label: t('features.seo.tabs.schema') },
]);

const sitemapUrl = ref('/sitemap.xml');
const generatingSitemap = ref(false);
const robotsContent = ref('');
const savingRobots = ref(false);
const contents = ref([]);
const selectedContentId = ref(null);
const analyzing = ref(false);
const analysisResults = ref(null);
const selectedContentForSchema = ref(null);
const generatingSchema = ref(false);
const schemaJson = ref(null);

const fetchRobotsTxt = async () => {
    try {
        const response = await api.get('/admin/cms/seo/robots-txt');
        const data = parseSingleResponse(response) || {};
        robotsContent.value = data.content || '';
    } catch (error) {
        console.error('Failed to fetch robots.txt:', error);
    }
};

const saveRobotsTxt = async () => {
    savingRobots.value = true;
    try {
        await api.put('/admin/cms/seo/robots-txt', { content: robotsContent.value });
        alert(t('features.seo.robots.saved'));
    } catch (error) {
        console.error('Failed to save robots.txt:', error);
        alert(t('features.seo.robots.failed'));
    } finally {
        savingRobots.value = false;
    }
};

const generateSitemap = async () => {
    generatingSitemap.value = true;
    try {
        await api.get('/admin/cms/seo/sitemap');
        alert(t('features.seo.sitemap.generated'));
    } catch (error) {
        console.error('Failed to generate sitemap:', error);
        alert(t('features.seo.sitemap.failed'));
    } finally {
        generatingSitemap.value = false;
    }
};

const copySitemapUrl = () => {
    navigator.clipboard.writeText(window.location.origin + sitemapUrl.value);
    alert(t('features.seo.sitemap.copySuccess'));
};

const fetchContents = async () => {
    try {
        const response = await api.get('/admin/cms/contents');
        const { data } = parseResponse(response);
        contents.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch contents:', error);
        contents.value = [];
    }
};

const runAnalysis = async () => {
    if (!selectedContentId.value) return;
    
    analyzing.value = true;
    try {
        const response = await api.get(`/admin/cms/contents/${selectedContentId.value}/seo-analysis`);
        analysisResults.value = parseSingleResponse(response) || {};
    } catch (error) {
        console.error('Failed to run SEO analysis:', error);
        alert(t('features.seo.analysis.failed'));
    } finally {
        analyzing.value = false;
    }
};

const generateSchema = async () => {
    if (!selectedContentForSchema.value) return;
    
    generatingSchema.value = true;
    try {
        const response = await api.get(`/admin/cms/contents/${selectedContentForSchema.value}/schema`);
        const schema = parseSingleResponse(response) || {};
        schemaJson.value = JSON.stringify(schema, null, 2);
    } catch (error) {
        console.error('Failed to generate schema:', error);
        alert(t('features.seo.schema.failed'));
    } finally {
        generatingSchema.value = false;
    }
};

const copySchema = () => {
    if (schemaJson.value) {
        navigator.clipboard.writeText(schemaJson.value);
        alert(t('features.seo.schema.copySuccess'));
    }
};

onMounted(() => {
    fetchRobotsTxt();
    fetchContents();
});
</script>

