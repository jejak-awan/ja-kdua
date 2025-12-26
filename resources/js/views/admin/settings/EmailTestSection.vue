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
                        <Input
                            :model-value="testEmail.to"
                            @update:model-value="$emit('update:test-email', { ...testEmail, to: $event })"
                            type="email"
                            :placeholder="$t('features.settings.emailTest.recipientPlaceholder')"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-foreground mb-1">{{ $t('features.settings.emailTest.subject') }}</label>
                        <Input
                            :model-value="testEmail.subject"
                            @update:model-value="$emit('update:test-email', { ...testEmail, subject: $event })"
                            type="text"
                            :placeholder="$t('features.settings.emailTest.subjectPlaceholder')"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-foreground mb-1">{{ $t('features.settings.emailTest.message') }}</label>
                        <Textarea
                            :model-value="testEmail.message"
                            @update:model-value="$emit('update:test-email', { ...testEmail, message: $event })"
                            :rows="3"
                            :placeholder="$t('features.settings.emailTest.messagePlaceholder')"
                        />
                    </div>
                    <Button
                        type="button"
                        @click="$emit('send-test-email')"
                        :disabled="sendingTestEmail || !testEmail.to"
                        class="w-full"
                    >
                        {{ sendingTestEmail ? $t('features.settings.emailTest.sending') : $t('features.settings.emailTest.sendTest') }}
                    </Button>
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
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="$emit('refresh-queue')"
                            :disabled="loadingQueueStatus"
                        >
                            {{ loadingQueueStatus ? $t('features.settings.loading') : $t('features.settings.emailTest.refresh') }}
                        </Button>
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
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="$emit('refresh-logs')"
                            :disabled="loadingLogs"
                        >
                            {{ loadingLogs ? $t('features.settings.loading') : $t('features.settings.emailTest.refresh') }}
                        </Button>
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

import Button from '../../../components/ui/button.vue'
import Input from '../../../components/ui/input.vue'
import Textarea from '../../../components/ui/textarea.vue'
</script>
