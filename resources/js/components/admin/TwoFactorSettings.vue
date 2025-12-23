<template>
    <div class="bg-card rounded-lg shadow p-6 space-y-6">
        <!-- Status Header -->
        <div class="flex items-center justify-between pb-4 border-b border-border">
            <div>
                <h3 class="text-lg font-medium text-foreground">
                    Two-Factor Authentication
                </h3>
                <p class="text-sm text-muted-foreground dark:text-gray-400 mt-1">
                    {{ status.enabled ? '2FA is enabled on your account' : 'Add an extra layer of security to your account' }}
                </p>
            </div>
            <span
                :class="[
                    'px-3 py-1 rounded-full text-sm font-medium',
                    status.enabled
                        ? 'bg-green-500/20 text-green-400 dark:bg-green-900 dark:text-green-200'
                        : 'bg-secondary text-secondary-foreground dark:bg-gray-600 dark:text-gray-200'
                ]"
            >
                {{ status.enabled ? 'Enabled' : 'Disabled' }}
            </span>
        </div>

        <!-- Enable 2FA Section -->
        <div v-if="!status.enabled" class="space-y-4">
            <div>
                <h4 class="text-md font-medium text-foreground mb-2">
                    Enable Two-Factor Authentication
                </h4>
                <p class="text-sm text-muted-foreground dark:text-gray-400 mb-4">
                    Scan the QR code with your authenticator app (Google Authenticator, Authy, etc.)
                </p>
            </div>

            <!-- Generate Button -->
            <div v-if="!qrCodeUrl" class="flex justify-center">
                <button
                    @click="generateSecret"
                    :disabled="generating"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2"
                >
                    <svg
                        v-if="generating"
                        class="animate-spin h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ generating ? 'Generating...' : 'Generate QR Code' }}
                </button>
            </div>

            <!-- QR Code Display -->
            <div v-if="qrCodeUrl" class="flex flex-col items-center space-y-4">
                <div class="p-4 bg-card dark:bg-gray-700 rounded-lg border border-border dark:border-gray-600">
                    <img :src="qrCodeUrl" alt="2FA QR Code" class="w-48 h-48" />
                </div>

                <!-- Secret Key -->
                <div v-if="secret" class="w-full bg-yellow-500/10 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">
                        Save this secret key in a safe place:
                    </p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 px-3 py-2 bg-card border border-yellow-300 dark:border-yellow-700 rounded text-sm font-mono text-yellow-900 dark:text-yellow-100 break-all">
                            {{ secret }}
                        </code>
                        <button
                            @click="copySecret"
                            class="px-3 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-sm whitespace-nowrap"
                        >
                            Copy
                        </button>
                    </div>
                    <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-2">
                        Use this code if you cannot access your authenticator app
                    </p>
                </div>

                <!-- Verification Code Input -->
                <div class="w-full">
                    <label class="block text-sm font-medium text-foreground dark:text-gray-300 mb-2">
                        Enter verification code from authenticator app *
                    </label>
                    <input
                        v-model="verificationCode"
                        type="text"
                        maxlength="6"
                        placeholder="000000"
                        class="w-full px-4 py-2 border border-input dark:border-gray-700 rounded-md dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-center text-2xl tracking-widest"
                        @input="verificationCode = verificationCode.replace(/\D/g, '')"
                    />
                </div>

                <!-- Enable Button -->
                <div class="w-full flex justify-end">
                    <button
                        @click="enable2FA"
                        :disabled="!verificationCode || verificationCode.length !== 6 || enabling"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <svg
                            v-if="enabling"
                            class="animate-spin h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ enabling ? 'Enabling...' : 'Enable 2FA' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Disable 2FA Section -->
        <div v-else class="space-y-4">
            <div>
                <h4 class="text-md font-medium text-foreground mb-2">
                    Disable Two-Factor Authentication
                </h4>
                <p class="text-sm text-muted-foreground dark:text-gray-400 mb-4">
                    To disable 2FA, enter your password
                </p>
                <div v-if="status.required" class="mb-4 p-3 bg-yellow-500/10 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        ⚠️ Two-factor authentication is required for admin users and cannot be disabled.
                    </p>
                </div>
            </div>

            <!-- Password Input -->
            <div>
                <label class="block text-sm font-medium text-foreground dark:text-gray-300 mb-2">
                    Password *
                </label>
                <input
                    v-model="disablePassword"
                    type="password"
                    :disabled="status.required"
                    class="w-full px-4 py-2 border border-input dark:border-gray-700 rounded-md dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-50"
                />
            </div>

            <!-- Disable Button -->
            <div class="flex justify-end">
                <button
                    @click="disable2FA"
                    :disabled="!disablePassword || disabling || status.required"
                    class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <svg
                        v-if="disabling"
                        class="animate-spin h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ disabling ? 'Disabling...' : 'Disable 2FA' }}
                </button>
            </div>

            <!-- Backup Codes Section -->
            <div v-if="backupCodes.length > 0" class="bg-blue-500/10 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h4 class="text-md font-medium text-blue-900 dark:text-blue-200 mb-2">
                    Recovery Codes
                </h4>
                <p class="text-sm text-blue-800 dark:text-blue-300 mb-3">
                    Save these codes in a safe place. Use them if you lose access to your authenticator app.
                </p>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <code
                        v-for="(code, index) in backupCodes"
                        :key="index"
                        class="px-3 py-2 bg-card border border-blue-300 dark:border-blue-700 rounded text-sm font-mono text-blue-900 dark:text-blue-100 text-center"
                    >
                        {{ code }}
                    </code>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="downloadBackupCodes"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                    >
                        Download Codes
                    </button>
                    <button
                        @click="regenerateBackupCodes"
                        :disabled="regenerating"
                        class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-sm disabled:opacity-50"
                    >
                        {{ regenerating ? 'Regenerating...' : 'Regenerate Codes' }}
                    </button>
                </div>
            </div>

            <!-- Backup Codes Count -->
            <div v-else-if="status.backup_codes_count > 0" class="bg-muted dark:bg-gray-700 rounded-lg p-4">
                <p class="text-sm text-muted-foreground dark:text-gray-400">
                    You have <strong>{{ status.backup_codes_count }}</strong> backup code(s) remaining.
                </p>
                <button
                    @click="regenerateBackupCodes"
                    :disabled="regenerating"
                    class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm disabled:opacity-50"
                >
                    {{ regenerating ? 'Regenerating...' : 'Regenerate Backup Codes' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import { parseResponse } from '../../utils/responseParser';
import QRCode from 'qrcode';

const status = ref({
    enabled: false,
    required: false,
    backup_codes_count: 0,
    enabled_at: null,
});

const qrCodeUrl = ref(null);
const secret = ref(null);
const verificationCode = ref('');
const disablePassword = ref('');
const backupCodes = ref([]);
const generating = ref(false);
const enabling = ref(false);
const disabling = ref(false);
const regenerating = ref(false);

const fetchStatus = async () => {
    try {
        const response = await api.get('/two-factor/status');
        const { data } = parseResponse(response);
        status.value = data;
    } catch (error) {
        console.error('Error fetching 2FA status:', error);
    }
};

const generateSecret = async () => {
    generating.value = true;
    qrCodeUrl.value = null;
    secret.value = null;
    verificationCode.value = '';

    try {
        const response = await api.post('/two-factor/generate');
        const { data } = parseResponse(response);
        
        secret.value = data.secret;
        
        // Generate QR code from URL
        if (data.qr_code_url) {
            qrCodeUrl.value = await QRCode.toDataURL(data.qr_code_url, {
                width: 256,
                margin: 2,
            });
        }

        // Store backup codes temporarily (only shown once)
        if (data.backup_codes) {
            backupCodes.value = data.backup_codes;
        }
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to generate 2FA secret');
    } finally {
        generating.value = false;
    }
};

const enable2FA = async () => {
    if (!verificationCode.value || verificationCode.value.length !== 6) {
        alert('Please enter a 6-digit verification code');
        return;
    }

    enabling.value = true;
    try {
        const response = await api.post('/two-factor/verify', {
            code: verificationCode.value,
        });
        const { data, message } = parseResponse(response);
        
        alert(message || 'Two-factor authentication enabled successfully');
        verificationCode.value = '';
        qrCodeUrl.value = null;
        secret.value = null;
        await fetchStatus();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to enable 2FA');
    } finally {
        enabling.value = false;
    }
};

const disable2FA = async () => {
    if (!disablePassword.value) {
        alert('Please enter your password');
        return;
    }

    if (!confirm('Are you sure you want to disable Two-Factor Authentication? This will reduce your account security.')) {
        return;
    }

    disabling.value = true;
    try {
        const response = await api.post('/two-factor/disable', {
            password: disablePassword.value,
        });
        const { message } = parseResponse(response);
        
        alert(message || 'Two-factor authentication disabled successfully');
        disablePassword.value = '';
        backupCodes.value = [];
        await fetchStatus();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to disable 2FA');
    } finally {
        disabling.value = false;
    }
};

const regenerateBackupCodes = async () => {
    if (!confirm('This will invalidate your existing backup codes. Continue?')) {
        return;
    }

    const password = prompt('Please enter your password to regenerate backup codes:');
    if (!password) {
        return;
    }

    regenerating.value = true;
    try {
        const response = await api.post('/two-factor/regenerate-backup-codes', {
            password: password,
        });
        const { data, message } = parseResponse(response);
        
        if (data.backup_codes) {
            backupCodes.value = data.backup_codes;
        }
        alert(message || 'Backup codes regenerated successfully');
        await fetchStatus();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to regenerate backup codes');
    } finally {
        regenerating.value = false;
    }
};

const copySecret = async () => {
    if (!secret.value) return;
    
    try {
        await navigator.clipboard.writeText(secret.value);
        alert('Secret key copied to clipboard');
    } catch (error) {
        console.error('Error copying secret:', error);
        alert('Failed to copy secret key');
    }
};

const downloadBackupCodes = () => {
    if (backupCodes.value.length === 0) return;
    
    const content = `Recovery Codes for Two-Factor Authentication\n\n` +
        `Save these codes in a safe place:\n\n` +
        backupCodes.value.join('\n') +
        `\n\nGenerated: ${new Date().toLocaleString()}`;
    
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `recovery-codes-${new Date().toISOString().slice(0, 10)}.txt`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
};

onMounted(() => {
    fetchStatus();
});
</script>

