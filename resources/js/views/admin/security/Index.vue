<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Security Management</h1>
        </div>

        <!-- Statistics -->
        <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Security Events</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ statistics.total_events || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Blocked IPs</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ statistics.blocked_ips || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Failed Logins</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ statistics.failed_logins || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Last 24h</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ statistics.last_24h || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- IP Management -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">IP Management</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Block IP Address
                    </label>
                    <div class="flex space-x-2">
                        <input
                            v-model="ipToBlock"
                            type="text"
                            placeholder="192.168.1.1"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        />
                        <button
                            @click="blockIP"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                        >
                            Block
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Check IP Status
                    </label>
                    <div class="flex space-x-2">
                        <input
                            v-model="ipToCheck"
                            type="text"
                            placeholder="192.168.1.1"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        />
                        <button
                            @click="checkIPStatus"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                        >
                            Check
                        </button>
                    </div>
                    <div v-if="ipStatus" class="mt-2 p-3 rounded-md" :class="ipStatus.blocked ? 'bg-red-50 text-red-800' : 'bg-green-50 text-green-800'">
                        <p class="text-sm font-medium">
                            IP Status: {{ ipStatus.blocked ? 'Blocked' : 'Allowed' }}
                        </p>
                        <p v-if="ipStatus.reason" class="text-xs mt-1">{{ ipStatus.reason }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Logs -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Security Logs</h2>
                    <div class="flex items-center space-x-4">
                        <select
                            v-model="logFilter"
                            class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">All Events</option>
                            <option value="failed_login">Failed Login</option>
                            <option value="blocked_ip">Blocked IP</option>
                            <option value="suspicious_activity">Suspicious Activity</option>
                        </select>
                        <input
                            v-model="logSearch"
                            type="text"
                            placeholder="Search logs..."
                            class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-gray-500">Loading...</p>
            </div>

            <div v-else-if="filteredLogs.length === 0" class="p-6 text-center">
                <p class="text-gray-500">No security logs found</p>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Event
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            IP Address
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Details
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="log in filteredLogs" :key="log.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="{
                                    'bg-red-100 text-red-800': log.event_type === 'failed_login' || log.event_type === 'blocked_ip',
                                    'bg-yellow-100 text-yellow-800': log.event_type === 'suspicious_activity',
                                    'bg-blue-100 text-blue-800': log.event_type === 'security_check',
                                }"
                            >
                                {{ log.event_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ log.ip_address }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ log.user?.name || '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ log.details || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatDate(log.created_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button
                                v-if="log.ip_address"
                                @click="blockIPFromLog(log.ip_address)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Block IP
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../../../services/api';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';

const logs = ref([]);
const statistics = ref(null);
const loading = ref(false);
const logFilter = ref('');
const logSearch = ref('');
const ipToBlock = ref('');
const ipToCheck = ref('');
const ipStatus = ref(null);

const filteredLogs = computed(() => {
    let filtered = logs.value;
    
    if (logFilter.value) {
        filtered = filtered.filter(log => log.event_type === logFilter.value);
    }
    
    if (logSearch.value) {
        const searchLower = logSearch.value.toLowerCase();
        filtered = filtered.filter(log => 
            log.ip_address?.toLowerCase().includes(searchLower) ||
            log.details?.toLowerCase().includes(searchLower) ||
            log.user?.name?.toLowerCase().includes(searchLower)
        );
    }
    
    return filtered;
});

const fetchLogs = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/security/logs');
        const { data } = parseResponse(response);
        logs.value = ensureArray(data);
        
        // Fetch statistics
        try {
            const statsResponse = await api.get('/admin/cms/security/statistics');
            statistics.value = parseSingleResponse(statsResponse) || {};
        } catch (error) {
            // Calculate from logs if endpoint doesn't exist
            statistics.value = {
                total_events: logs.value.length,
                blocked_ips: new Set(logs.value.filter(l => l.event_type === 'blocked_ip').map(l => l.ip_address)).size,
                failed_logins: logs.value.filter(l => l.event_type === 'failed_login').length,
                last_24h: logs.value.filter(l => {
                    const logDate = new Date(l.created_at);
                    const now = new Date();
                    return (now - logDate) < 24 * 60 * 60 * 1000;
                }).length,
            };
        }
    } catch (error) {
        console.error('Failed to fetch security logs:', error);
    } finally {
        loading.value = false;
    }
};

const blockIP = async () => {
    if (!ipToBlock.value) {
        alert('Please enter an IP address');
        return;
    }

    try {
        await api.post('/admin/cms/security/block-ip', { ip: ipToBlock.value });
        alert('IP address blocked successfully');
        ipToBlock.value = '';
        await fetchLogs();
    } catch (error) {
        console.error('Failed to block IP:', error);
        alert(error.response?.data?.message || 'Failed to block IP address');
    }
};

const checkIPStatus = async () => {
    if (!ipToCheck.value) {
        alert('Please enter an IP address');
        return;
    }

    try {
        const response = await api.get(`/admin/cms/security/check-ip/${ipToCheck.value}`);
        ipStatus.value = parseSingleResponse(response) || {};
    } catch (error) {
        console.error('Failed to check IP status:', error);
        alert('Failed to check IP status');
    }
};

const blockIPFromLog = async (ip) => {
    if (!confirm(`Are you sure you want to block IP address ${ip}?`)) {
        return;
    }

    try {
        await api.post('/admin/cms/security/block-ip', { ip });
        alert('IP address blocked successfully');
        await fetchLogs();
    } catch (error) {
        console.error('Failed to block IP:', error);
        alert('Failed to block IP address');
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
};

onMounted(() => {
    fetchLogs();
});
</script>

