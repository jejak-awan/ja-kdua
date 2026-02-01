import fs from 'fs';
import path from 'path';

const VUE_NAMES = [
    'ref', 'computed', 'watch', 'watchEffect', 'inject', 'provide',
    'reactive', 'onMounted', 'onUnmounted', 'onBeforeUnmount',
    'onUpdated', 'nextTick', 'defineProps', 'defineEmits', 'propType', 'PropType',
    'toRef', 'toRefs', 'unref', 'shallowRef', 'shallowReactive',
    'markRaw', 'toRaw', 'Component', 'FunctionalComponent', 'SVGAttributes',
    'useSlots', 'useAttrs', 'h', 'defineComponent', 'withDefaults', 'type Ref',
    'getCurrentInstance', 'onBeforeMount', 'onActivated', 'onDeactivated', 'onErrorCaptured'
];

const ROUTER_NAMES = ['useRouter', 'useRoute', 'RouterLink', 'RouterView'];
const I18N_NAMES = ['useI18n'];
const TOAST_NAMES = ['useToast'];

const RADIX_NAMES = [
    'SelectItem', 'SelectItemIndicator', 'SelectItemText', 'SelectItemProps', 'SelectIcon',
    'SelectTrigger', 'SelectTriggerProps', 'SelectValue', 'SelectContent', 'SelectGroup',
    'SelectLabel', 'SelectSeparator', 'SelectItemEmits',
    'Accordion', 'AccordionItem', 'AccordionHeader', 'AccordionTrigger', 'AccordionTriggerProps', 'AccordionContent',
    'Tabs', 'TabsList', 'TabsTrigger', 'TabsContent', 'TabsProps',
    'Dialog', 'DialogTrigger', 'DialogContent', 'DialogHeader', 'DialogFooter', 'DialogTitle', 'DialogDescription',
    'DialogContentEmits', 'DialogOverlay', 'DialogPortal', 'DialogClose',
    'useForwardProps', 'useForwardPropsEmits',
    'Popover', 'PopoverTrigger', 'PopoverContent', 'PopoverAnchor',
    'Tooltip', 'TooltipProvider', 'TooltipTrigger', 'TooltipContent',
    'Label', 'Separator', 'Switch', 'Checkbox', 'RadioGroup', 'RadioGroupItem'
];

const UI_COMPONENTS = [
    'Button', 'Checkbox', 'Input', 'Label', 'Badge', 'Alert', 'Card', 'CardHeader', 'CardTitle', 'CardDescription', 'CardContent', 'CardFooter',
    'ContextMenu', 'ContextMenuTrigger', 'ContextMenuItem', 'ContextMenuLabel', 'ContextMenuSeparator', 'ContextMenuContent'
];

function isRadix(name) {
    if (RADIX_NAMES.includes(name)) return true;
    if (name.endsWith('Props') || name.endsWith('Emits')) return true;
    if (name.startsWith('useForward')) return true;
    return false;
}

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    const lucideRegex = /import \{ ([\s\S]*?) \} from 'lucide-vue-next';/g;

    content = content.replace(lucideRegex, (match, iconsPart) => {
        const items = iconsPart.split(',').map(i => i.trim()).filter(i => i.length > 0);

        const lucideItems = [];
        const vueItems = [];
        const radixItems = [];
        const routerItems = [];
        const i18nItems = [];
        const toastItems = [];
        const uiItems = [];
        const otherItems = [];

        items.forEach(item => {
            const baseName = item.startsWith('type ') ? item.substring(5).trim() : item.trim();
            const actualNameInside = baseName.includes(' as ') ? baseName.split(' as ')[0].trim() : baseName;

            if (VUE_NAMES.includes(actualNameInside)) {
                vueItems.push(item);
            } else if (ROUTER_NAMES.includes(actualNameInside)) {
                routerItems.push(item);
            } else if (I18N_NAMES.includes(actualNameInside)) {
                i18nItems.push(item);
            } else if (TOAST_NAMES.includes(actualNameInside)) {
                toastItems.push(item);
            } else if (isRadix(actualNameInside)) {
                radixItems.push(item);
            } else if (UI_COMPONENTS.includes(actualNameInside)) {
                uiItems.push(item);
            } else if (actualNameInside === 'nodeViewProps' || actualNameInside === 'NodeViewWrapper' || actualNameInside === 'cn') {
                otherItems.push(item);
            } else {
                lucideItems.push(item);
            }
        });

        if (vueItems.length === 0 && radixItems.length === 0 && uiItems.length === 0 &&
            routerItems.length === 0 && i18nItems.length === 0 && toastItems.length === 0 &&
            otherItems.length === 0) {
            return match;
        }

        let newImports = [];
        if (lucideItems.length > 0) {
            newImports.push(`import { ${lucideItems.sort().join(', ')} } from 'lucide-vue-next';`);
        }
        if (vueItems.length > 0) {
            newImports.push(`import { ${vueItems.sort().join(', ')} } from 'vue';`);
        }
        if (routerItems.length > 0) {
            newImports.push(`import { ${routerItems.sort().join(', ')} } from 'vue-router';`);
        }
        if (i18nItems.length > 0) {
            newImports.push(`import { ${i18nItems.sort().join(', ')} } from 'vue-i18n';`);
        }
        if (toastItems.length > 0) {
            newImports.push(`import { useToast } from '@/composables/useToast';`);
        }
        if (radixItems.length > 0) {
            newImports.push(`import { ${radixItems.sort().join(', ')} } from 'radix-vue';`);
        }
        if (uiItems.length > 0) {
            const uiPath = filePath.includes('/shared/') ? '../../components/ui' : '@/components/ui';
            newImports.push(`import { ${uiItems.sort().join(', ')} } from '${uiPath}';`);
        }

        if (otherItems.length > 0) {
            otherItems.forEach(oi => {
                const name = oi.includes(' as ') ? oi.split(' as ')[0].trim() : (oi.startsWith('type ') ? oi.substring(5).trim() : oi.trim());
                if (name === 'cn') {
                    newImports.push(`import { cn } from '@/lib/utils';`);
                } else if (name === 'nodeViewProps' || name === 'NodeViewWrapper') {
                    newImports.push(`import { ${oi} } from '@tiptap/vue-3';`);
                } else {
                    newImports.push(`import { ${oi} } from 'lucide-vue-next';`);
                }
            });
        }

        return newImports.join('\n');
    });

    if (content !== original) {
        fs.writeFileSync(filePath, content);
        console.log(`Deep Cleaned: ${filePath}`);
    }
}

const targetDir = 'resources/js';
const files = [];

function walk(dir) {
    if (!fs.existsSync(dir)) return;
    fs.readdirSync(dir).forEach(f => {
        let p = path.join(dir, f);
        if (fs.statSync(p).isDirectory()) walk(p);
        else if (f.endsWith('.vue') || f.endsWith('.ts')) files.push(p);
    });
}
walk(targetDir);
files.forEach(processFile);
console.log('Deep Cleanup completed.');
