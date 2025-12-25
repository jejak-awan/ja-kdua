<template>
    <div class="mt-8 pt-8 border-t border-border">
        <h3 class="text-lg font-medium text-foreground mb-6">{{ $t('features.settings.emailTest.title') }}</h3>
        
        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- LEFT COLUMN: Send Test Email Form -->
            <div class="p-4 bg-muted rounded-lg">
                <h4 class="text-sm font-medium text-foreground mb-3">{{ $t('features.settings.emailTest.sendTest') }}</h4>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-foreground mb-1">{{ $t('features.settings.emailTest.recipient') }}</label>
                        <input
                            :value="testEmail.to"
                            @input="$emit('update:test-email', { ...testEmail, to: $event.target.value })"
                            type="email"
                            :placeholder="$t('features.settings.emailTest.recipientPlaceholder')"
                            class="w-full px-3 py-2 text-sm border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-foreground mb-1">{{ $t('features.settings.emailTest.subject') }}</label>
                        <input
                            :value="testEmail.subject"
                            @input="$emit('update:test-email', { ...testEmail, subject: $event.target.value })"
                            type="text"
                            :placeholder="$t('features.settings.emailTest.subjectPlaceholder')"
                            class="w-full px-3 py-2 text-sm border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-foreground mb-1">{{ $t('features.settings.emailTest.message') }}</label>
                        <textarea
                            :value="testEmail.message"
                            @input="$emit('update:test-email', { ...testEmail, message: $event.target.value })"
                            rows="3"
                            :placeholder="$t('features.settings.emailTest.messagePlaceholder')"
                            class="w-full px-3 py-2 text-sm border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                        />
                    </div>
                    <button
                        type="button"
                        @click="$emit('send-test-email')"
                        :disabled="sendingTestEmail || !testEmail.to"
                        class="w-full px-4 py-2 text-sm bg-primary text-primary-foreground rounded-md hover:bg-primary/80 disabled:opacity-50"
                    >
                        {{ sendingTestEmail ? $t('features.settings.emailTest.sending') : $t('features.settings.emailTest.sendTest') }}
                    </button>
                    <div v-if="testEmailResult" class="text-sm" :class="testEmailResult.success ? 'text-green-600' : 'text-red-600'">
                        {{ testEmailResult.message }}
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Queue Status & Email Logs -->
            <div class="space-y-6">
                <!-- Queue Status -->
                <div class="p-4 bg-muted rounded-lg">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-medium text-foreground">{{ $t('features.settings.emailTest.queueStatus') }}</h4>
                        <button
                            type="button"
                            @click="$emit('refresh-queue')"
                            :disabled="loadingQueueStatus"
                            class="px-3 py-1 text-xs border border-input bg-card text-foreground rounded-md hover:bg-muted disabled:opacity-50"
                        >
                            {{ loadingQueueStatus ? $t('features.settings.loading') : $t('features.settings.emailTest.refresh') }}
                        </button>
                    </div>
                    <div v-if="queueStatus" class="text-sm text-muted-foreground space-y-1">
                        <div class="flex justify-between">
                            <span>{{ $t('features.settings.emailTest.driver') }}:</span>
                            <span class="font-medium text-foreground">{{ queueStatus.driver }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{ $t('features.settings.emailTest.pending') }}:</span>
                            <span class="font-medium text-foreground">{{ queueStatus.pending_jobs }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{ $t('features.settings.emailTest.failedJobs') }}:</span>
                            <span class="font-medium" :class="queueStatus.failed_jobs > 0 ? 'text-red-500' : 'text-foreground'">{{ queueStatus.failed_jobs }}</span>
                        </div>
                    </div>
                    <div v-else class="text-sm text-muted-foreground">
                        {{ $t('features.settings.loading') }}
                    </div>
                </div>

                <!-- Recent Email Logs -->
                <div class="p-4 bg-muted rounded-lg">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-medium text-foreground">{{ $t('features.settings.emailTest.recentLogs') }}</h4>
                        <button
                            type="button"
                            @click="$emit('refresh-logs')"
                            :disabled="loadingLogs"
                            class="px-3 py-1 text-xs border border-input bg-card text-foreground rounded-md hover:bg-muted disabled:opacity-50"
                        >
                            {{ loadingLogs ? $t('features.settings.loading') : $t('features.settings.emailTest.refresh') }}
                        </button>
                    </div>
                    <div v-if="emailLogs && emailLogs.length > 0" class="space-y-2 max-h-48 overflow-y-auto">
                        <div
                            v-for="log in emailLogs"
                            :key="log.sent_at"
                            class="p-2 bg-card rounded border border-border text-xs"
                        >
                            <div class="flex justify-between items-start gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-foreground truncate">{{ log.to }}</p>
                                    <p class="text-muted-foreground truncate">{{ log.subject }}</p>
                                </div>
                                <span class="text-muted-foreground text-xs whitespace-nowrap">{{ formatDate(log.sent_at) }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="emailLogs && emailLogs.length === 0" class="text-sm text-muted-foreground">
                        {{ $t('features.settings.emailTest.noLogs') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    sendingTestEmail: Boolean,
    testEmailResult: Object,
    testEmail: {
        type: Object,
        default: () => ({ to: '', subject: '', message: '' })
    },
    queueStatus: Object,
    loadingQueueStatus: Boolean,
    emailLogs: Array,
    loadingLogs: Boolean
})

defineEmits([
    'send-test-email', 
    'refresh-queue', 
    'refresh-logs',
    'update:test-email'
])

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleString()
}
</script>
