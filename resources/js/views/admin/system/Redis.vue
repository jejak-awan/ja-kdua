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
      <TabsContent value="settings" class="settings-tab">
        <div class="card">
          <div class="card-header">
            <h3>{{ $t('features.redis.settings.connection') }}</h3>
            <button @click="testConnection" :disabled="testing" class="btn btn-primary">
              <span v-if="!testing">{{ $t('features.redis.settings.test') }}</span>
              <span v-else>{{ $t('features.redis.settings.testing') }}</span>
            </button>
          </div>

          <div v-if="connectionStatus" :class="['alert', connectionStatus.type]">
            {{ connectionStatus.message }}
          </div>

          <form @submit.prevent="saveSettings" class="settings-form">
            <div v-for="group in groupedSettings" :key="group.name" class="settings-group">
              <h4>{{ group.name }}</h4>
              
              <div v-for="setting in group.items" :key="setting.key" class="form-group">
                <label :for="setting.key">
                  {{ formatLabel(setting.key) }}
                  <span v-if="setting.description" class="description">{{ setting.description }}</span>
                </label>

                <input
                  v-if="setting.type === 'string' || setting.type === 'integer'"
                  :id="setting.key"
                  :type="setting.type === 'integer' ? 'number' : (setting.is_encrypted ? 'password' : 'text')"
                  v-model="settingsForm[setting.key]"
                  class="form-control"
                />

                <select
                  v-else-if="setting.type === 'boolean'"
                  :id="setting.key"
                  v-model="settingsForm[setting.key]"
                  class="form-control"
                >
                  <option value="true">{{ $t('features.redis.settings.enabled') }}</option>
                  <option value="false">{{ $t('features.redis.settings.disabled') }}</option>
                </select>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" :disabled="saving" class="btn btn-success">
                <span v-if="!saving">{{ $t('features.redis.settings.save') }}</span>
                <span v-else>{{ $t('features.redis.settings.saving') }}</span>
              </button>
            </div>
          </form>
        </div>
      </TabsContent>

      <!-- Statistics Tab -->
      <TabsContent value="statistics" class="statistics-tab">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon">🚀</div>
            <div class="stat-content">
              <div class="stat-label">{{ $t('features.redis.statistics.grid.version') }}</div>
              <div class="stat-value">{{ stats.version || 'N/A' }}</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">💾</div>
            <div class="stat-content">
              <div class="stat-label">{{ $t('features.redis.statistics.grid.memory') }}</div>
              <div class="stat-value">{{ stats.used_memory || 'N/A' }}</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">🔑</div>
            <div class="stat-content">
              <div class="stat-label">{{ $t('features.redis.statistics.grid.keys') }}</div>
              <div class="stat-value">{{ stats.total_keys || 0 }}</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">⏱️</div>
            <div class="stat-content">
              <div class="stat-label">{{ $t('features.redis.statistics.grid.uptime') }}</div>
              <div class="stat-value">{{ stats.uptime_days || 'N/A' }}</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
              <div class="stat-label">{{ $t('features.redis.statistics.grid.clients') }}</div>
              <div class="stat-value">{{ stats.connected_clients || 0 }}</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">🎯</div>
            <div class="stat-content">
              <div class="stat-label">{{ $t('features.redis.statistics.grid.hitRate') }}</div>
              <div class="stat-value">{{ stats.hit_rate || '0%' }}</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
              <div class="stat-label">{{ $t('features.redis.statistics.grid.ops') }}</div>
              <div class="stat-value">{{ stats.operations_per_sec || 0 }}</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">💥</div>
            <div class="stat-content">
              <div class="stat-label">{{ $t('features.redis.statistics.grid.commands') }}</div>
              <div class="stat-value">{{ formatNumber(stats.total_commands) || 0 }}</div>
            </div>
          </div>
        </div>

        <div class="card mt-4">
          <div class="card-header">
            <h3>{{ $t('features.redis.statistics.hitMiss.title') }}</h3>
          </div>
          <div class="card-body">
            <div class="hit-miss-stats">
              <div class="stat-row">
                <span class="label">{{ $t('features.redis.statistics.hitMiss.hits') }}:</span>
                <span class="value success">{{ formatNumber(stats.hits) || 0 }}</span>
              </div>
              <div class="stat-row">
                <span class="label">{{ $t('features.redis.statistics.hitMiss.misses') }}:</span>
                <span class="value danger">{{ formatNumber(stats.misses) || 0 }}</span>
              </div>
              <div class="stat-row">
                <span class="label">{{ $t('features.redis.statistics.hitMiss.hitRate') }}:</span>
                <span class="value primary">{{ stats.hit_rate || '0%' }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="refresh-info">
          <button @click="loadStats" :disabled="loadingStats" class="btn btn-sm btn-outline">
            <span v-if="!loadingStats">{{ $t('features.redis.statistics.refresh') }}</span>
            <span v-else>{{ $t('features.redis.messages.loading') }}</span>
          </button>
          <span class="text-muted">{{ $t('features.redis.statistics.autoRefresh') }}</span>
        </div>
      </TabsContent>

      <!-- Cache Tab -->
      <TabsContent value="cache" class="cache-tab">
        <div class="card">
          <div class="card-header">
            <h3>{{ $t('features.redis.cache.title') }}</h3>
          </div>

          <div class="cache-actions">
            <div class="cache-action-card">
              <div class="icon">🧹</div>
              <h4>{{ $t('features.redis.cache.actions.all.title') }}</h4>
              <p>{{ $t('features.redis.cache.actions.all.desc') }}</p>
              <button @click="flushCache('all')" :disabled="flushing" class="btn btn-danger">
                {{ $t('features.redis.cache.actions.all.button') }}
              </button>
            </div>

            <div class="cache-action-card">
              <div class="icon">💾</div>
              <h4>{{ $t('features.redis.cache.actions.cache.title') }}</h4>
              <p>{{ $t('features.redis.cache.actions.cache.desc') }}</p>
              <button @click="flushCache('cache')" :disabled="flushing" class="btn btn-warning">
                {{ $t('features.redis.cache.actions.cache.button') }}
              </button>
            </div>

            <div class="cache-action-card">
              <div class="icon">⚙️</div>
              <h4>{{ $t('features.redis.cache.actions.config.title') }}</h4>
              <p>{{ $t('features.redis.cache.actions.config.desc') }}</p>
              <button @click="flushCache('config')" :disabled="flushing" class="btn btn-warning">
                {{ $t('features.redis.cache.actions.config.button') }}
              </button>
            </div>

            <div class="cache-action-card">
              <div class="icon">🛣️</div>
              <h4>{{ $t('features.redis.cache.actions.route.title') }}</h4>
              <p>{{ $t('features.redis.cache.actions.route.desc') }}</p>
              <button @click="flushCache('route')" :disabled="flushing" class="btn btn-warning">
                {{ $t('features.redis.cache.actions.route.button') }}
              </button>
            </div>

            <div class="cache-action-card">
              <div class="icon">👁️</div>
              <h4>{{ $t('features.redis.cache.actions.view.title') }}</h4>
              <p>{{ $t('features.redis.cache.actions.view.desc') }}</p>
              <button @click="flushCache('view')" :disabled="flushing" class="btn btn-warning">
                {{ $t('features.redis.cache.actions.view.button') }}
              </button>
            </div>
          </div>

          <div v-if="cacheStats" class="card mt-4">
            <div class="card-header">
              <h3>{{ $t('features.redis.cache.stats.title') }}</h3>
            </div>
            <div class="card-body">
              <div class="cache-stats-grid">
                <div class="stat">
                  <span class="label">{{ $t('features.redis.cache.stats.keys') }}:</span>
                  <span class="value">{{ cacheStats.total_keys || 0 }}</span>
                </div>
                <div class="stat">
                  <span class="label">{{ $t('features.redis.cache.stats.size') }}:</span>
                  <span class="value">{{ cacheStats.cache_size || 'N/A' }}</span>
                </div>
                <div class="stat">
                  <span class="label">{{ $t('features.redis.cache.stats.expired') }}:</span>
                  <span class="value">{{ cacheStats.expired_keys || 0 }}</span>
                </div>
              </div>

              <div v-if="cacheStats.top_keys && cacheStats.top_keys.length" class="top-keys mt-4">
                <h4>{{ $t('features.redis.cache.stats.topKeys') }}</h4>
                <table class="table">
                  <thead>
                    <tr>
                      <th>{{ $t('features.redis.cache.stats.table.key') }}</th>
                      <th>{{ $t('features.redis.cache.stats.table.size') }}</th>
                      <th>{{ $t('features.redis.cache.stats.table.ttl') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(key, index) in cacheStats.top_keys" :key="index">
                      <td><code>{{ key.key }}</code></td>
                      <td>{{ key.size }}</td>
                      <td>{{ key.ttl }}</td>
                    </tr>
                  </tbody>
                </table>
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
const groupedSettings = computed(() => {
  const groups = {}
  
  Object.entries(settings.value).forEach(([groupName, items]) => {
    groups[groupName] = {
      name: formatGroupName(groupName),
      items: items
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

/* Tabs */
.tabs {
  display: flex;
  gap: 0.5rem;
  border-bottom: 2px solid hsl(var(--border));
  margin-bottom: 2rem;
}

.tab {
  padding: 0.75rem 1.5rem;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  color: hsl(var(--muted-foreground));
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: -2px;
}

.tab:hover {
  color: #2563eb;
}

.tab.active {
  color: #2563eb;
  border-bottom-color: #2563eb;
}

/* Card */
.card {
  background: hsl(var(--card));
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid hsl(var(--border));
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: hsl(var(--foreground));
  margin: 0;
}

.card-body {
  padding: 1.5rem;
}

/* Alert */
.alert {
  padding: 1rem;
  border-radius: 0.375rem;
  margin-bottom: 1rem;
}

.alert.success {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #6ee7b7;
}

.alert.error {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fca5a5;
}

/* Forms */
.settings-form {
  padding: 1.5rem;
}

.settings-group {
  margin-bottom: 2rem;
}

.settings-group h4 {
  font-size: 1.125rem;
  font-weight: 600;
  color: hsl(var(--foreground));
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid hsl(var(--border));
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 500;
  color: hsl(var(--foreground));
  margin-bottom: 0.5rem;
}

.form-group .description {
  display: block;
  font-size: 0.875rem;
  color: hsl(var(--muted-foreground));
  font-weight: 400;
  margin-top: 0.25rem;
}

.form-control {
  width: 100%;
  padding: 0.625rem 0.875rem;
  border: 1px solid hsl(var(--border));
  border-radius: 0.375rem;
  font-size: 0.875rem;
  transition: border-color 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 1px solid hsl(var(--border));
}

/* Buttons */
.btn {
  padding: 0.625rem 1.25rem;
  border: none;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: #2563eb;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #1d4ed8;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover:not(:disabled) {
  background: #059669;
}

.btn-warning {
  background: #f59e0b;
  color: white;
}

.btn-warning:hover:not(:disabled) {
  background: #d97706;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover:not(:disabled) {
  background: #dc2626;
}

.btn-outline {
  background: hsl(var(--card));
  color: hsl(var(--foreground));
  border: 1px solid hsl(var(--border));
}

.btn-outline:hover:not(:disabled) {
  background: hsl(var(--muted));
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

/* Statistics */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: hsl(var(--card));
  border-radius: 0.5rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  font-size: 2.5rem;
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: hsl(var(--muted-foreground));
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: hsl(var(--foreground));
}

.hit-miss-stats {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.stat-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: hsl(var(--muted));
  border-radius: 0.375rem;
}

.stat-row .label {
  font-weight: 500;
  color: hsl(var(--foreground));
}

.stat-row .value {
  font-size: 1.25rem;
  font-weight: 700;
}

.stat-row .value.success {
  color: #10b981;
}

.stat-row .value.danger {
  color: #ef4444;
}

.stat-row .value.primary {
  color: #2563eb;
}

.refresh-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid hsl(var(--border));
}

.text-muted {
  color: hsl(var(--muted-foreground));
  font-size: 0.875rem;
}

/* Cache Actions */
.cache-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  padding: 1.5rem;
}

.cache-action-card {
  background: hsl(var(--muted));
  border-radius: 0.5rem;
  padding: 1.5rem;
  text-align: center;
  border: 2px solid hsl(var(--border));
  transition: all 0.2s;
}

.cache-action-card:hover {
  border-color: #2563eb;
  box-shadow: 0 4px 6px rgba(37, 99, 235, 0.1);
}

.cache-action-card .icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.cache-action-card h4 {
  font-size: 1.125rem;
  font-weight: 600;
  color: hsl(var(--foreground));
  margin-bottom: 0.5rem;
}

.cache-action-card p {
  color: hsl(var(--muted-foreground));
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

.cache-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.cache-stats-grid .stat {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem;
  background: hsl(var(--muted));
  border-radius: 0.375rem;
}

.cache-stats-grid .label {
  font-weight: 500;
  color: hsl(var(--foreground));
}

.cache-stats-grid .value {
  font-weight: 700;
  color: #2563eb;
}

/* Table */
.table {
  width: 100%;
  border-collapse: collapse;
}

.table th,
.table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid hsl(var(--border));
}

.table th {
  font-weight: 600;
  color: hsl(var(--foreground));
  background: hsl(var(--muted));
}

.table code {
  background: #f3f4f6;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.875rem;
  font-family: 'Courier New', monospace;
}

.mt-4 {
  margin-top: 1.5rem;
}

.top-keys h4 {
  font-size: 1.125rem;
  font-weight: 600;
  color: hsl(var(--foreground));
  margin-bottom: 1rem;
}
</style>

