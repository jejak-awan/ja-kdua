<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import { Card, CardContent, CardDescription, CardHeader, CardTitle, Button, Input, Label, Textarea, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, Switch } from '@/components/ui'
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js'
import Save from 'lucide-vue-next/dist/esm/icons/save.js'
import { useToast } from '@/composables/useToast'

interface ServiceNode {
  id: number
  name: string
}

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const toast = useToast()

const isEdit = computed(() => !!route.params.id)
const loading = ref(false)
const saving = ref(false)
const routers = ref<ServiceNode[]>([])

const form = ref({
  name: '',
  network: '',
  gateway: '',
  dns_primary: '',
  dns_secondary: '',
  vlan_id: '',
  router_id: '',
  status: 'active',
  description: '',
  generate_addresses: true,
})

const fetchRouters = async () => {
  try {
    const { data } = await api.get('/admin/janet/isp/infra', { params: { type: 'Router' } })
    routers.value = data.data || []
  } catch (error) {
    console.error('Failed to fetch routers:', error)
  }
}

const fetchPool = async () => {
  if (!isEdit.value) return
  loading.value = true
  try {
    const { data } = await api.get(`/admin/janet/isp/ip-pools/${route.params.id}`)
    const pool = data.data.pool
    form.value = {
      name: pool.name,
      network: pool.network,
      gateway: pool.gateway || '',
      dns_primary: pool.dns_primary || '',
      dns_secondary: pool.dns_secondary || '',
      vlan_id: pool.vlan_id?.toString() || '',
      router_id: pool.router_id?.toString() || '',
      status: pool.status,
      description: pool.description || '',
      generate_addresses: false,
    }
  } catch {
    toast.error.default(t('isp.network.ip_pool.messages.pool_error'))
    router.push('/admin/isp/network/ip-pools')
  } finally {
    loading.value = false
  }
}

const submit = async () => {
  saving.value = true
  try {
    const payload = {
      ...form.value,
      vlan_id: form.value.vlan_id ? parseInt(form.value.vlan_id) : null,
      router_id: form.value.router_id ? parseInt(form.value.router_id) : null,
    }

    if (isEdit.value) {
      await api.put(`/admin/janet/isp/ip-pools/${route.params.id}`, payload)
      toast.success.default(t('isp.network.ip_pool.messages.update_success'))
    } else {
      await api.post('/admin/janet/isp/ip-pools', payload)
      toast.success.default(t('isp.network.ip_pool.messages.create_success'))
    }
    router.push('/admin/isp/network/ip-pools')
  } catch (error: unknown) {
    const message = (error as { response?: { data?: { message?: string } } })?.response?.data?.message || t('isp.network.ip_pool.messages.save_error')
    toast.error.default(message)
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchRouters()
  fetchPool()
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="icon" class="rounded-xl" @click="router.push('/admin/isp/network/ip-pools')" :aria-label="t('common.actions.back')">
        <ArrowLeft class="h-5 w-5" />
      </Button>
      <div>
        <h1 class="text-2xl font-bold">{{ isEdit ? t('isp.network.ip_pool.edit') : t('isp.network.ip_pool.create') }}</h1>
        <p class="text-muted-foreground">{{ isEdit ? t('isp.network.ip_pool.edit') : t('isp.network.ip_pool.create_desc') }}</p>
      </div>
    </div>

    <Card class="border border-border/40 shadow-sm rounded-xl overflow-hidden">
      <CardHeader>
        <CardTitle>{{ t('isp.network.ip_pool.info') }}</CardTitle>
        <CardDescription>{{ t('isp.network.ip_pool.create_desc') }}</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <Label for="name">{{ t('isp.network.ip_pool.fields.name') }} *</Label>
              <Input id="name" v-model="form.name" placeholder="contoh: Pool Perumahan A" class="rounded-xl" required />
            </div>

            <div class="space-y-2">
              <Label for="network">{{ t('isp.network.ip_pool.fields.network') }} *</Label>
              <Input id="network" v-model="form.network" :placeholder="t('isp.network.ip_pool.fields.network_placeholder')" class="rounded-xl" required :disabled="isEdit" />
              <p class="text-xs text-muted-foreground">{{ t('isp.network.ip_pool.fields.network_hint') }}</p>
            </div>

            <div class="space-y-2">
              <Label for="gateway">{{ t('isp.network.ip_pool.fields.gateway') }}</Label>
              <Input id="gateway" v-model="form.gateway" placeholder="contoh: 10.10.10.1" class="rounded-xl" />
            </div>

            <div class="space-y-2">
              <Label for="vlan_id">{{ t('isp.network.ip_pool.fields.vlan_id') }}</Label>
              <Input id="vlan_id" v-model="form.vlan_id" type="number" min="1" max="4094" placeholder="1-4094" class="rounded-xl" />
            </div>

            <div class="space-y-2">
              <Label for="dns_primary">{{ t('isp.network.ip_pool.fields.dns_primary') }}</Label>
              <Input id="dns_primary" v-model="form.dns_primary" placeholder="contoh: 8.8.8.8" class="rounded-xl" />
            </div>

            <div class="space-y-2">
              <Label for="dns_secondary">{{ t('isp.network.ip_pool.fields.dns_secondary') }}</Label>
              <Input id="dns_secondary" v-model="form.dns_secondary" placeholder="contoh: 8.8.4.4" class="rounded-xl" />
            </div>

            <div class="space-y-2">
              <Label for="router_id">{{ t('isp.network.ip_pool.fields.router') }}</Label>
              <Select v-model="form.router_id">
                <SelectTrigger class="rounded-xl">
                  <SelectValue :placeholder="t('isp.network.ip_pool.select_router')" />
                </SelectTrigger>
                <SelectContent class="rounded-xl">
                  <SelectItem value="">{{ t('isp.network.ip_pool.no_router') }}</SelectItem>
                  <SelectItem v-for="r in routers" :key="r.id" :value="r.id.toString()">
                    {{ r.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label for="status">{{ t('isp.network.ip_pool.fields.status') }}</Label>
              <Select v-model="form.status">
                <SelectTrigger class="rounded-xl">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent class="rounded-xl">
                  <SelectItem value="active">{{ t('isp.network.ip_pool.status.active') }}</SelectItem>
                  <SelectItem value="inactive">{{ t('isp.network.ip_pool.status.inactive') }}</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="md:col-span-2 space-y-2">
              <Label for="description">{{ t('isp.network.ip_pool.fields.description') }}</Label>
              <Textarea id="description" v-model="form.description" :placeholder="t('isp.network.ip_pool.fields.description_placeholder')" rows="3" class="rounded-xl" />
            </div>

            <div v-if="!isEdit" class="flex items-center gap-3">
              <Switch id="generate" v-model:checked="form.generate_addresses" />
              <Label for="generate" class="cursor-pointer">{{ t('isp.network.ip_pool.fields.generate_auto') }}</Label>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t">
            <Button type="button" variant="outline" class="rounded-xl" @click="router.push('/admin/isp/network/ip-pools')">
              {{ t('common.actions.cancel') }}
            </Button>
            <Button type="submit" class="rounded-xl" :disabled="saving">
              <Save class="mr-2 h-4 w-4" />
              {{ saving ? t('common.actions.saving') : t('common.actions.save') }}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
