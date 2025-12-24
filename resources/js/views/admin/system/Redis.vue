<template>
  <div class="redis-management">
    <div class="page-header">
      <h1 class="page-title">{{ $t('features.redis.title') }}</h1>
      <p class="page-description">{{ $t('features.redis.description') }}</p>
    </div>

    <!-- Shadcn Tabs -->
    <Tabs v-model="activeTab" class="w-full">
      <TabsList class="mb-6">
        <TabsTrigger v-for="tab in tabs" :key="tab.id" :value="tab.id">
          {{ $t(`features.redis.tabs.${tab.id}`) }}
        </TabsTrigger>
      </TabsList>

      <!-- Settings Tab -->
      <TabsContent value="settings" class="settings-tab space-y-6">
        <!-- Global Driver Warning -->
        <div v-if="cacheDriver !== 'redis'" class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-yellow-800 flex items-start gap-3">
             <div class="text-xl">⚠️</div>
             <div>
                <h4 class="font-bold text-sm uppercase tracking-wider mb-1">Redis Disabled</h4>
                <p class="text-sm">
                   Redis is currently disabled in system settings. To enable it, go to 
                   <router-link to="/admin/settings?tab=performance" class="underline font-semibold hover:text-yellow-900">Settings &gt; Performance</router-link>
                   and select "Redis" as the Cache Driver.
                </p>
             </div>
        </div>

        <!-- Connection Test Card -->
        <div class="bg-card border border-border rounded-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-foreground">{{ $t('features.redis.settings.connection') }}</h3>
            <button 
              type="button"
              @click="testConnection" 
              :disabled="testing" 
              class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4 py-2"
            >
              <span v-if="!testing">{{ $t('features.redis.settings.test') }}</span>
              <span v-else>{{ $t('features.redis.settings.testing') }}</span>
            </button>
          </div>
          
          <div v-if="connectionStatus" class="rounded-lg p-3 text-sm" :class="connectionStatus.type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'">
            {{ connectionStatus.message }}
          </div>
        </div>

        <form @submit.prevent="saveSettings" :inert="cacheDriver !== 'redis'" class="space-y-6">
            <!-- Opacity overlay for disabled state -->
            <div :class="{'opacity-50 pointer-events-none': cacheDriver !== 'redis'}" class="space-y-6">
              
              <div v-for="group in groupedSettings" :key="group.name" class="bg-card border border-border rounded-lg p-6">
                <!-- Group Header -->
                <h3 class="text-base font-semibold text-foreground mb-4 pb-3 border-b border-border">
                  {{ group.name }}
                </h3>
              
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div v-for="setting in group.items" :key="setting.key" class="space-y-2">
                    <Label :for="setting.key" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                      {{ formatLabel(setting.key) }}
                      <span v-if="setting.description" class="block text-xs text-muted-foreground font-normal mt-1">{{ setting.description }}</span>
                    </Label>

                    <Input
                      v-if="setting.type === 'string' || setting.type === 'integer'"
                      :id="setting.key"
                      :type="setting.type === 'integer' ? 'number' : (setting.is_encrypted ? 'password' : 'text')"
                      v-model="settingsForm[setting.key]"
                      class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    />

                    <Select
                      v-else-if="setting.type === 'boolean'"
                      :model-value="String(settingsForm[setting.key])"
                      @update:model-value="(val) => settingsForm[setting.key] = val"
                    >
                      <SelectTrigger class="w-full">
                        <SelectValue :placeholder="$t('features.redis.settings.select')" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="true">{{ $t('features.redis.settings.enabled') }}</SelectItem>
                        <SelectItem value="false">{{ $t('features.redis.settings.disabled') }}</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="flex justify-end space-x-4 pt-6 border-t border-border mt-8">
              <button
                  type="button"
                  @click="loadSettings"
                  class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2"
              >
                  {{ $t('features.redis.settings.cancel') || 'Reset' }}
              </button>
              <button 
                type="submit" 
                :disabled="saving" 
                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2"
              >
                <span v-if="!saving">{{ $t('features.redis.settings.save') }}</span>
                <span v-else>{{ $t('features.redis.settings.saving') }}</span>
              </button>
          </div>
        </form>
      </TabsContent>

      <!-- Statistics Tab -->
      <TabsContent value="statistics" class="statistics-tab space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-card border border-border rounded-lg p-6 flex items-center gap-4 shadow-sm">
            <div class="text-4xl">🚀</div>
            <div class="flex-1">
              <div class="text-sm text-muted-foreground font-medium mb-1">{{ $t('features.redis.statistics.grid.version') }}</div>
              <div class="text-2xl font-bold text-foreground">{{ stats.version || 'N/A' }}</div>
            </div>
          </div>

          <div class="bg-card border border-border rounded-lg p-6 flex items-center gap-4 shadow-sm">
            <div class="text-4xl">💾</div>
            <div class="flex-1">
              <div class="text-sm text-muted-foreground font-medium mb-1">{{ $t('features.redis.statistics.grid.memory') }}</div>
              <div class="text-2xl font-bold text-foreground">{{ stats.used_memory || 'N/A' }}</div>
            </div>
          </div>

          <div class="bg-card border border-border rounded-lg p-6 flex items-center gap-4 shadow-sm">
            <div class="text-4xl">🔑</div>
            <div class="flex-1">
              <div class="text-sm text-muted-foreground font-medium mb-1">{{ $t('features.redis.statistics.grid.keys') }}</div>
              <div class="text-2xl font-bold text-foreground">{{ stats.total_keys || 0 }}</div>
            </div>
          </div>

          <div class="bg-card border border-border rounded-lg p-6 flex items-center gap-4 shadow-sm">
            <div class="text-4xl">⏱️</div>
            <div class="flex-1">
              <div class="text-sm text-muted-foreground font-medium mb-1">{{ $t('features.redis.statistics.grid.uptime') }}</div>
              <div class="text-2xl font-bold text-foreground">{{ stats.uptime_days || 'N/A' }}</div>
            </div>
          </div>

          <div class="bg-card border border-border rounded-lg p-6 flex items-center gap-4 shadow-sm">
            <div class="text-4xl">👥</div>
            <div class="flex-1">
              <div class="text-sm text-muted-foreground font-medium mb-1">{{ $t('features.redis.statistics.grid.clients') }}</div>
              <div class="text-2xl font-bold text-foreground">{{ stats.connected_clients || 0 }}</div>
            </div>
          </div>

          <div class="bg-card border border-border rounded-lg p-6 flex items-center gap-4 shadow-sm">
            <div class="text-4xl">🎯</div>
            <div class="flex-1">
              <div class="text-sm text-muted-foreground font-medium mb-1">{{ $t('features.redis.statistics.grid.hitRate') }}</div>
              <div class="text-2xl font-bold text-foreground">{{ stats.hit_rate || '0%' }}</div>
            </div>
          </div>

          <div class="bg-card border border-border rounded-lg p-6 flex items-center gap-4 shadow-sm">
            <div class="text-4xl">📊</div>
            <div class="flex-1">
              <div class="text-sm text-muted-foreground font-medium mb-1">{{ $t('features.redis.statistics.grid.ops') }}</div>
              <div class="text-2xl font-bold text-foreground">{{ stats.operations_per_sec || 0 }}</div>
            </div>
          </div>

          <div class="bg-card border border-border rounded-lg p-6 flex items-center gap-4 shadow-sm">
            <div class="text-4xl">💥</div>
            <div class="flex-1">
              <div class="text-sm text-muted-foreground font-medium mb-1">{{ $t('features.redis.statistics.grid.commands') }}</div>
              <div class="text-2xl font-bold text-foreground">{{ formatNumber(stats.total_commands) || 0 }}</div>
            </div>
          </div>
        </div>

        <div class="bg-card border border-border rounded-lg p-6 mt-6">
          <div class="mb-4 pb-4 border-b border-border">
            <h3 class="text-lg font-semibold text-foreground">{{ $t('features.redis.statistics.hitMiss.title') }}</h3>
          </div>
          <div>
            <div class="flex flex-col gap-4">
              <div class="flex justify-between items-center p-3 bg-muted/50 rounded-md">
                <span class="font-medium text-foreground">{{ $t('features.redis.statistics.hitMiss.hits') }}:</span>
                <span class="text-lg font-bold text-emerald-600">{{ formatNumber(stats.hits) || 0 }}</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-muted/50 rounded-md">
                <span class="font-medium text-foreground">{{ $t('features.redis.statistics.hitMiss.misses') }}:</span>
                <span class="text-lg font-bold text-red-600">{{ formatNumber(stats.misses) || 0 }}</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-muted/50 rounded-md">
                <span class="font-medium text-foreground">{{ $t('features.redis.statistics.hitMiss.hitRate') }}:</span>
                <span class="text-lg font-bold text-blue-600">{{ stats.hit_rate || '0%' }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-border mt-6">
          <button 
            @click="loadStats" 
            :disabled="loadingStats" 
            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2"
          >
            <span v-if="!loadingStats">{{ $t('features.redis.statistics.refresh') }}</span>
            <span v-else>{{ $t('features.redis.messages.loading') }}</span>
          </button>
          <span class="text-sm text-muted-foreground">{{ $t('features.redis.statistics.autoRefresh') }}</span>
        </div>
      </TabsContent>

      <!-- Cache Tab -->
      <TabsContent value="cache" class="cache-tab space-y-6">
        <div class="bg-card border border-border rounded-lg p-6">
          <div class="mb-6 pb-4 border-b border-border">
            <h3 class="text-lg font-semibold text-foreground">{{ $t('features.redis.cache.title') }}</h3>
          </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <!-- Warm Cache -->
              <div class="rounded-lg p-6 text-center border-2 border-primary/20 bg-primary/5 hover:border-primary/50 transition-all">
                <div class="text-4xl mb-4">🔥</div>
                <h4 class="text-lg font-semibold text-foreground mb-2">{{ $t('features.redis.cache.actions.warm.title') }}</h4>
                <p class="text-sm text-muted-foreground mb-4">{{ $t('features.redis.cache.actions.warm.desc') }}</p>
                <button 
                  @click="warmCache" 
                  :disabled="warming" 
                  class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2"
                >
                  <span v-if="!warming">{{ $t('features.redis.cache.actions.warm.button') }}</span>
                  <span v-else>{{ $t('features.redis.cache.actions.warm.warming') }}</span>
                </button>
              </div>

              <div class="rounded-lg p-6 text-center border border-border bg-card hover:border-destructive/50 transition-all">
                <div class="text-4xl mb-4">🧹</div>
                <h4 class="text-lg font-semibold text-foreground mb-2">{{ $t('features.redis.cache.actions.all.title') }}</h4>
                <p class="text-sm text-muted-foreground mb-4">{{ $t('features.redis.cache.actions.all.desc') }}</p>
                <button 
                  @click="flushCache('all')" 
                  :disabled="flushing" 
                  class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-10 px-4 py-2"
                >
                  {{ $t('features.redis.cache.actions.all.button') }}
                </button>
              </div>

              <div class="rounded-lg p-6 text-center border border-border bg-card hover:border-orange-500/50 transition-all">
                <div class="text-4xl mb-4">💾</div>
                <h4 class="text-lg font-semibold text-foreground mb-2">{{ $t('features.redis.cache.actions.cache.title') }}</h4>
                <p class="text-sm text-muted-foreground mb-4">{{ $t('features.redis.cache.actions.cache.desc') }}</p>
                <button 
                  @click="flushCache('cache')" 
                  :disabled="flushing" 
                  class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-orange-500 text-white hover:bg-orange-600 h-10 px-4 py-2"
                >
                  {{ $t('features.redis.cache.actions.cache.button') }}
                </button>
              </div>

              <div class="rounded-lg p-6 text-center border border-border bg-card hover:border-orange-500/50 transition-all">
                <div class="text-4xl mb-4">⚙️</div>
                <h4 class="text-lg font-semibold text-foreground mb-2">{{ $t('features.redis.cache.actions.config.title') }}</h4>
                <p class="text-sm text-muted-foreground mb-4">{{ $t('features.redis.cache.actions.config.desc') }}</p>
                <button 
                  @click="flushCache('config')" 
                  :disabled="flushing" 
                  class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-orange-500 text-white hover:bg-orange-600 h-10 px-4 py-2"
                >
                  {{ $t('features.redis.cache.actions.config.button') }}
                </button>
              </div>

              <div class="rounded-lg p-6 text-center border border-border bg-card hover:border-orange-500/50 transition-all">
                <div class="text-4xl mb-4">🛣️</div>
                <h4 class="text-lg font-semibold text-foreground mb-2">{{ $t('features.redis.cache.actions.route.title') }}</h4>
                <p class="text-sm text-muted-foreground mb-4">{{ $t('features.redis.cache.actions.route.desc') }}</p>
                <button 
                  @click="flushCache('route')" 
                  :disabled="flushing" 
                  class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-orange-500 text-white hover:bg-orange-600 h-10 px-4 py-2"
                >
                  {{ $t('features.redis.cache.actions.route.button') }}
                </button>
              </div>

              <div class="rounded-lg p-6 text-center border border-border bg-card hover:border-orange-500/50 transition-all">
                <div class="text-4xl mb-4">👁️</div>
                <h4 class="text-lg font-semibold text-foreground mb-2">{{ $t('features.redis.cache.actions.view.title') }}</h4>
                <p class="text-sm text-muted-foreground mb-4">{{ $t('features.redis.cache.actions.view.desc') }}</p>
                <button 
                  @click="flushCache('view')" 
                  :disabled="flushing" 
                  class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-orange-500 text-white hover:bg-orange-600 h-10 px-4 py-2"
                >
                  {{ $t('features.redis.cache.actions.view.button') }}
                </button>
              </div>
            </div>

          <div v-if="cacheStats" class="bg-card border border-border rounded-lg p-6 mt-8">
            <div class="mb-4 pb-4 border-b border-border">
              <h3 class="text-lg font-semibold text-foreground">{{ $t('features.redis.cache.stats.title') }}</h3>
            </div>
            <div>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="flex justify-between items-center p-3 bg-muted/50 rounded-md">
                  <span class="font-medium text-foreground">{{ $t('features.redis.cache.stats.keys') }}:</span>
                  <span class="font-bold text-foreground">{{ cacheStats.total_keys || 0 }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-muted/50 rounded-md">
                  <span class="font-medium text-foreground">{{ $t('features.redis.cache.stats.size') }}:</span>
                  <span class="font-bold text-blue-600">{{ cacheStats.cache_size || 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-muted/50 rounded-md">
                  <span class="font-medium text-foreground">{{ $t('features.redis.cache.stats.expired') }}:</span>
                  <span class="font-bold text-foreground">{{ cacheStats.expired_keys || 0 }}</span>
                </div>
              </div>

              <div v-if="cacheStats.top_keys && cacheStats.top_keys.length" class="top-keys space-y-4">
                <h4 class="font-semibold text-foreground">{{ $t('features.redis.cache.stats.topKeys') }}</h4>
                <div class="overflow-x-auto rounded-md border border-border">
                  <table class="table w-full">
                    <thead class="bg-muted">
                      <tr>
                        <th class="p-3 text-left font-medium text-muted-foreground text-sm">{{ $t('features.redis.cache.stats.table.key') }}</th>
                        <th class="p-3 text-left font-medium text-muted-foreground text-sm">{{ $t('features.redis.cache.stats.table.size') }}</th>
                        <th class="p-3 text-left font-medium text-muted-foreground text-sm">{{ $t('features.redis.cache.stats.table.ttl') }}</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                      <tr v-for="(key, index) in cacheStats.top_keys" :key="index" class="hover:bg-muted/50">
                        <td class="p-3"><code>{{ key.key }}</code></td>
                        <td class="p-3 text-sm">{{ key.size }}</td>
                        <td class="p-3 text-sm">{{ key.ttl }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </TabsContent>
    </Tabs>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import Tabs from '@/components/ui/tabs.vue'
import TabsList from '@/components/ui/tabs-list.vue'
import TabsTrigger from '@/components/ui/tabs-trigger.vue'
import TabsContent from '@/components/ui/tabs-content.vue'
import Input from '@/components/ui/input.vue'
import Label from '@/components/ui/label.vue'
import Select from '@/components/ui/select.vue'
import SelectContent from '@/components/ui/select-content.vue'
import SelectItem from '@/components/ui/select-item.vue'
import SelectTrigger from '@/components/ui/select-trigger.vue'
import SelectValue from '@/components/ui/select-value.vue'

const { t } = useI18n()
const activeTab = ref('statistics')
const tabs = [
  { id: 'statistics', label: 'Statistics' },
  { id: 'settings', label: 'Settings' },
  { id: 'cache', label: 'Cache Management' },
]

// Settings
const settings = ref({})
const settingsForm = ref({})
const cacheDriver = ref(null) // Global cache driver status

const groupedSettings = computed(() => {
  const groups = {}
  
  Object.entries(settings.value).forEach(([groupName, items]) => {
    // Filter out only redis_enabled (which was duplicative).
    // Allow cache_enabled, redis_queue_enabled, redis_session_enabled to show.
    const filteredItems = items.filter(item => item.key !== 'redis_enabled')
     if (filteredItems.length === 0) return

    groups[groupName] = {
      name: formatGroupName(groupName),
      items: filteredItems
    }
  })
  
  return Object.values(groups)
})

const saving = ref(false)
const testing = ref(false)
const connectionStatus = ref(null)

// Statistics
const stats = ref({})
const loadingStats = ref(false)
const statsInterval = ref(null)

// Cache
const cacheStats = ref(null)
const flushing = ref(false)
const warming = ref(false)

// Methods
const loadSettings = async () => {
  try {
    const response = await api.get('/admin/cms/redis/settings')
    settings.value = response.data.data
    
    // Flatten settings for form
    Object.values(settings.value).forEach(items => {
      items.forEach(item => {
        settingsForm.value[item.key] = item.value
      })
    })
  } catch (error) {
    console.error('Failed to load Redis settings:', error)
  }
}

const saveSettings = async () => {
  saving.value = true
  try {
    const settingsArray = Object.entries(settingsForm.value).map(([key, value]) => ({
      key,
      value
    }))

    await api.put('/admin/cms/redis/settings', {
      settings: settingsArray
    })

    connectionStatus.value = {
      type: 'success',
      message: t('features.redis.messages.saveSuccess')
    }

    setTimeout(() => {
      connectionStatus.value = null
    }, 3000)
  } catch (error) {
    connectionStatus.value = {
      type: 'error',
      message: error.response?.data?.message || t('features.redis.messages.saveFailed')
    }
  } finally {
    saving.value = false
  }
}

const testConnection = async () => {
  testing.value = true
  connectionStatus.value = null

  try {
    const response = await api.get('/admin/cms/redis/test-connection')
    connectionStatus.value = {
      type: 'success',
      message: `✅ ${response.data.data.message || t('features.redis.messages.testSuccess')} (${response.data.data.response_time})`
    }
  } catch (error) {
    connectionStatus.value = {
      type: 'error',
      message: `❌ ${error.response?.data?.message || t('features.redis.messages.testFailed')}`
    }
  } finally {
    testing.value = false
  }
}

const loadStats = async () => {
  loadingStats.value = true
  try {
    const response = await api.get('/admin/cms/redis/info')
    stats.value = response.data.data
  } catch (error) {
    console.error('Failed to load Redis stats:', error)
  } finally {
    loadingStats.value = false
  }
}

const loadCacheStats = async () => {
  try {
    const response = await api.get('/admin/cms/redis/cache-stats')
    cacheStats.value = response.data.data
  } catch (error) {
    console.error('Failed to load cache stats:', error)
  }
}

const getCacheStatus = async () => {
    try {
        const response = await api.get('/admin/cms/system/cache-status')
        // We know from Index.vue fix that this returns { driver: ... }
        // Depending on parse logic, might need ensuring.
        // Let's assume response.data.data or utilize responseParser if imported
        // But api.get usually returns axios object.
        // Let's check api.js again. It usually returns response.
        // Assuming typical structure: response.data.data contains the payload if standard success.
        // But BaseApiController returns { success: true, data: ... }
        // So response.data.data is correct.
        const data = response.data.data
        cacheDriver.value = data.driver
    } catch (error) {
        console.error('Failed to get global cache status:', error)
    }
}

const flushCache = async (type) => {
  if (!confirm(t('features.redis.messages.flushConfirm', { type }))) {
    return
  }

  flushing.value = true
  try {
    await api.post('/admin/cms/redis/flush-cache', { type })
    alert(t('features.redis.messages.flushSuccess', { type }))
    loadCacheStats()
  } catch (error) {
    alert(error.response?.data?.message || t('features.redis.messages.flushFailed'))
  } finally {
    flushing.value = false
  }
}

const warmCache = async () => {
  if (!confirm(t('features.redis.messages.warmConfirm'))) {
    return
  }

  warming.value = true
  try {
    await api.post('/admin/cms/redis/warm-cache')
    alert(t('features.redis.messages.warmSuccess'))
    loadCacheStats()
    loadStats()
  } catch (error) {
    alert(error.response?.data?.message || t('features.redis.messages.warmFailed'))
  } finally {
    warming.value = false
  }
}

// Helpers
const formatLabel = (key) => {
  return key.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ')
}

const formatGroupName = (group) => {
  return group.charAt(0).toUpperCase() + group.slice(1)
}

const formatNumber = (num) => {
  if (!num) return 0
  return new Intl.NumberFormat().format(num)
}

// Lifecycle
onMounted(() => {
  loadSettings()
  loadStats()
  loadCacheStats()
  getCacheStatus()

  // Auto-refresh stats every 30 seconds
  statsInterval.value = setInterval(() => {
    if (activeTab.value === 'statistics') {
      loadStats()
    }
  }, 30000)
})

onUnmounted(() => {
  if (statsInterval.value) {
    clearInterval(statsInterval.value)
  }
})
</script>

<style scoped>
.redis-management {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: hsl(var(--foreground));
  margin-bottom: 0.5rem;
}

.page-description {
  color: hsl(var(--muted-foreground));
  font-size: 1rem;
}

/* Ensure code blocks in table look good */
code {
  background-color: hsl(var(--muted));
  color: hsl(var(--foreground));
  padding: 0.2rem 0.4rem;
  border-radius: 0.25rem;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size: 0.875rem;
}
</style>

