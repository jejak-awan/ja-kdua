<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import { 
    Dialog, 
    DialogContent, 
    DialogHeader, 
    DialogTitle, 
    Input,
    Button
} from '@/components/ui';
import { useToast } from '@/composables/useToast';

defineProps<{
    isOpen: boolean;
}>();

const emit = defineEmits(['close']);

const { t } = useI18n();
const { success } = useToast();

const gateway = ref('192.168.1.1');
const isCopied = ref(false);

const vpnServerIp = '172.31.195.54'; // Hardcoded based on user screenshot/portal context

function generateScript() {
    return `/ip route add dst-address=${vpnServerIp} gateway=${gateway.value} comment="Static Routing VPN"`;
}

async function handleGetScript() {
    const script = generateScript();
    try {
        await navigator.clipboard.writeText(script);
        isCopied.value = true;
        success.action(t('isp.network.routing.messages.copied'));
        setTimeout(() => {
            isCopied.value = false;
        }, 2000);
    } catch (_err) {
        // Fallback for non-secure contexts if needed
        const textArea = document.createElement("textarea");
        textArea.value = script;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        isCopied.value = true;
        success.action(t('isp.network.routing.messages.copied'));
        setTimeout(() => {
            isCopied.value = false;
        }, 2000);
    }
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="emit('close')">
        <DialogContent class="max-w-md rounded-2xl p-0 overflow-hidden border-none shadow-2xl">
            <div class="bg-white p-6">
                <DialogHeader class="flex flex-row items-center justify-between space-y-0 pb-6">
                    <DialogTitle class="text-xl font-bold text-slate-800 uppercase tracking-tight">
                        {{ t('isp.network.routing.title') }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-6">
                    <!-- Instructional Box -->
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
                        <ol class="space-y-2 text-[13px] text-blue-800 list-decimal pl-4 leading-relaxed font-medium">
                            <li>{{ t('isp.network.routing.instructions.item1') }}</li>
                            <li>{{ t('isp.network.routing.instructions.item2') }}</li>
                            <li>{{ t('isp.network.routing.instructions.item3') }}</li>
                            <li>{{ t('isp.network.routing.instructions.item4') }}</li>
                        </ol>
                    </div>

                    <div class="space-y-3">
                        <label class="text-[13px] font-bold text-slate-600 uppercase">
                            {{ t('isp.network.routing.fields.gateway') }}
                            <span class="block text-slate-400 font-normal normal-case mt-0.5">
                                {{ t('isp.network.routing.fields.gateway_hint') }}
                            </span>
                        </label>
                        <Input 
                            v-model="gateway" 
                            placeholder="e.g. 192.168.1.1 or ether1"
                            class="h-11 rounded-xl bg-slate-50 border-slate-200 focus:ring-red-400 transition-all font-mono"
                        />
                    </div>

                    <div class="pt-2">
                        <Button 
                            @click="handleGetScript" 
                            class="w-full h-12 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold shadow-md shadow-red-100 transition-all active:scale-[0.98]"
                        >
                            <Check v-if="isCopied" class="h-5 w-5 mr-2" />
                            <Copy v-else class="h-4 w-4 mr-2" />
                            {{ t('isp.network.routing.actions.get_script') }}
                        </Button>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
