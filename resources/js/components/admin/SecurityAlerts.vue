<template>
    <div v-if="alerts.length > 0" class="bg-card border border-border rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-border flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-destructive" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h3 class="font-semibold text-foreground">{{ t('features.security_alerts.title') }}</h3>
                <span class="bg-destructive/20 text-destructive text-xs px-2 py-0.5 rounded-full">
                    {{ alerts.length }}
                </span>
            </div>
            <button 
                @click="toggleExpanded" 
                class="text-sm text-primary hover:underline"
            >
                {{ expanded ? t('features.security_alerts.hide') : t('features.security_alerts.show') }}
            </button>
        </div>
        
        <div v-if="expanded" class="divide-y divide-border max-h-80 overflow-y-auto">
            <div
                v-for="alert in alerts"
                :key="alert.id"
                class="px-6 py-3 flex items-center justify-between hover:bg-muted/50"
            >
                <div class="flex items-center gap-3">
                    <span
                        :class="[
                            'w-2 h-2 rounded-full flex-shrink-0',
                            alert.severity === 'critical' ? 'bg-destructive' :
                            alert.severity === 'warning' ? 'bg-warning' : 'bg-info'
                        ]"
                    ></span>
                    <div>
                        <p class="text-sm font-medium text-foreground">{{ alert.title }}</p>
                        <p class="text-xs text-muted-foreground">{{ alert.message }}</p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <span
                        :class="[
                            'text-xs px-2 py-1 rounded-full',
                            alert.severity === 'critical' ? 'bg-destructive/20 text-destructive' :
                            alert.severity === 'warning' ? 'bg-warning/20 text-warning' : 'bg-info/20 text-info'
                        ]"
                    >
                        {{ getSeverityLabel(alert.severity) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';

const { t } = useI18n();

const alerts = ref([]);
const expanded = ref(true);
let pollInterval = null;

const fetchAlerts = async () => {
    try {
        const response = await api.get('/admin/ja/security/alerts');
        alerts.value = response.data?.data?.alerts || [];
    } catch (error) {
        // Silently fail
        console.debug('Failed to fetch security alerts:', error);
    }
};

const toggleExpanded = () => {
    expanded.value = !expanded.value;
};

const getSeverityLabel = (severity) => {
    const labels = {
        critical: 'Kritis',
        warning: 'Peringatan',
        info: 'Info',
    };
    return labels[severity] || severity;
};

onMounted(() => {
    fetchAlerts();
    // Poll every 60 seconds
    pollInterval = setInterval(fetchAlerts, 60000);
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});
</script>
