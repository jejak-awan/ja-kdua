<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">SEO Tools</h1>
        </div>

        <!-- Tabs -->
        <div class="bg-white shadow rounded-lg">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                            activeTab === tab.id
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
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
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Sitemap Management</h3>
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <p class="text-sm text-gray-600 mb-2">
                                Sitemap URL: <code class="px-2 py-1 bg-white rounded text-indigo-600">{{ sitemapUrl }}</code>
                            </p>
                            <button
                                @click="copySitemapUrl"
                                class="text-sm text-indigo-600 hover:text-indigo-800"
                            >
                                Copy URL
                            </button>
                        </div>
                        <button
                            @click="generateSitemap"
                            :disabled="generatingSitemap"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ generatingSitemap ? 'Generating...' : 'Generate Sitemap' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Robots.txt Tab -->
            <div v-if="activeTab === 'robots'" class="p-6">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Robots.txt Editor</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Robots.txt Content
                            </label>
                            <textarea
                                v-model="robotsContent"
                                rows="15"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                                placeholder="User-agent: *&#10;Disallow: /admin&#10;Allow: /"
                            ></textarea>
                        </div>
                        <div class="flex space-x-3">
                            <button
                                @click="saveRobotsTxt"
                                :disabled="savingRobots"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                            >
                                {{ savingRobots ? 'Saving...' : 'Save Robots.txt' }}
                            </button>
                            <button
                                @click="fetchRobotsTxt"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                            >
                                Reload
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Analysis Tab -->
            <div v-if="activeTab === 'analysis'" class="p-6">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO Analysis</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select Content to Analyze
                            </label>
                            <select
                                v-model="selectedContentId"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option :value="null">Select content...</option>
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
                            {{ analyzing ? 'Analyzing...' : 'Run Analysis' }}
                        </button>
                    </div>

                    <!-- Analysis Results -->
                    <div v-if="analysisResults" class="mt-6 bg-gray-50 rounded-lg p-6">
                        <h4 class="text-md font-semibold text-gray-900 mb-4">Analysis Results</h4>
                        <div class="space-y-4">
                            <div
                                v-for="(result, key) in analysisResults"
                                :key="key"
                                class="bg-white rounded-lg p-4"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700 capitalize">{{ key.replace(/_/g, ' ') }}</span>
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs font-semibold rounded-full',
                                            result.score >= 80 ? 'bg-green-100 text-green-800' :
                                            result.score >= 60 ? 'bg-yellow-100 text-yellow-800' :
                                            'bg-red-100 text-red-800'
                                        ]"
                                    >
                                        Score: {{ result.score }}/100
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600">{{ result.message }}</p>
                                <ul v-if="result.suggestions && result.suggestions.length > 0" class="mt-2 space-y-1">
                                    <li
                                        v-for="(suggestion, idx) in result.suggestions"
                                        :key="idx"
                                        class="text-xs text-gray-500"
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
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Schema Markup Generator</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select Content
                            </label>
                            <select
                                v-model="selectedContentForSchema"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option :value="null">Select content...</option>
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
                            {{ generatingSchema ? 'Generating...' : 'Generate Schema' }}
                        </button>
                    </div>

                    <!-- Schema JSON -->
                    <div v-if="schemaJson" class="mt-6 bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-md font-semibold text-gray-900">Generated Schema JSON</h4>
                            <button
                                @click="copySchema"
                                class="px-3 py-1 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Copy to Clipboard
                            </button>
                        </div>
                        <pre class="bg-white rounded-lg p-4 overflow-x-auto text-xs font-mono">{{ schemaJson }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../../services/api';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';

const activeTab = ref('sitemap');
const tabs = [
    { id: 'sitemap', label: 'Sitemap' },
    { id: 'robots', label: 'Robots.txt' },
    { id: 'analysis', label: 'SEO Analysis' },
    { id: 'schema', label: 'Schema Generation' },
];

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
        alert('Robots.txt saved successfully');
    } catch (error) {
        console.error('Failed to save robots.txt:', error);
        alert('Failed to save robots.txt');
    } finally {
        savingRobots.value = false;
    }
};

const generateSitemap = async () => {
    generatingSitemap.value = true;
    try {
        await api.get('/admin/cms/seo/sitemap');
        alert('Sitemap generated successfully');
    } catch (error) {
        console.error('Failed to generate sitemap:', error);
        alert('Failed to generate sitemap');
    } finally {
        generatingSitemap.value = false;
    }
};

const copySitemapUrl = () => {
    navigator.clipboard.writeText(window.location.origin + sitemapUrl.value);
    alert('Sitemap URL copied to clipboard!');
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
        alert('Failed to run SEO analysis');
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
        alert('Failed to generate schema');
    } finally {
        generatingSchema.value = false;
    }
};

const copySchema = () => {
    if (schemaJson.value) {
        navigator.clipboard.writeText(schemaJson.value);
        alert('Schema JSON copied to clipboard!');
    }
};

onMounted(() => {
    fetchRobotsTxt();
    fetchContents();
});
</script>

