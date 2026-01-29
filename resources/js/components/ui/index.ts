import { defineAsyncComponent } from 'vue';

// Basic UI (Keep synchronous - used immediately on most pages)
export { default as Button } from './button.vue';
export { default as Input } from './input.vue';
export { default as Checkbox } from './checkbox.vue';
export { default as Switch } from './switch.vue';
export { default as Textarea } from './textarea.vue';
export { default as Label } from './label.vue';
export { default as Badge } from './badge.vue';
export { default as Spinner } from './Spinner.vue';
export { default as LazyImage } from './LazyImage.vue';
export { default as SkeletonLoader } from './SkeletonLoader.vue';
export { default as LucideIcon } from './LucideIcon.vue';

// Cards (Keep synchronous - used on many pages)
export { default as Card } from './card.vue';
export { default as CardHeader } from './card-header.vue';
export { default as CardTitle } from './card-title.vue';
export { default as CardDescription } from './card-description.vue';
export { default as CardContent } from './card-content.vue';
export { default as CardFooter } from './card-footer.vue';

// Dialogs & Modals - Core dialog components sync (wrappers), content async
export { default as Dialog } from './dialog.vue';
export { default as DialogTrigger } from './dialog-trigger.vue';
export { default as DialogContent } from './dialog-content.vue';
export { default as DialogHeader } from './dialog-header.vue';
export { default as DialogTitle } from './dialog-title.vue';
export { default as DialogDescription } from './dialog-description.vue';
export { default as DialogFooter } from './dialog-footer.vue';
// Heavy modals - async loaded when needed
export const ConfirmModal = defineAsyncComponent(() => import('./ConfirmModal.vue'));
export const GlobalErrorModal = defineAsyncComponent(() => import('./GlobalErrorModal.vue'));
export const SessionTimeoutModal = defineAsyncComponent(() => import('./SessionTimeoutModal.vue'));

// Context Menu (Async - used on right-click only)
export const ContextMenu = defineAsyncComponent(() => import('./context-menu.vue'));
export const ContextMenuTrigger = defineAsyncComponent(() => import('./context-menu-trigger.vue'));
export const ContextMenuContent = defineAsyncComponent(() => import('./context-menu-content.vue'));
export const ContextMenuItem = defineAsyncComponent(() => import('./context-menu-item.vue'));
export const ContextMenuLabel = defineAsyncComponent(() => import('./context-menu-label.vue'));
export const ContextMenuSeparator = defineAsyncComponent(() => import('./context-menu-separator.vue'));

// Tabs (Keep sync - commonly used)
export { default as Tabs } from './tabs.vue';
export { default as TabsList } from './tabs-list.vue';
export { default as TabsTrigger } from './tabs-trigger.vue';
export { default as TabsContent } from './tabs-content.vue';

// Accordion (Async - not used on every page)
export const Accordion = defineAsyncComponent(() => import('./accordion.vue'));
export const AccordionItem = defineAsyncComponent(() => import('./accordion-item.vue'));
export const AccordionTrigger = defineAsyncComponent(() => import('./accordion-trigger.vue'));
export const AccordionContent = defineAsyncComponent(() => import('./accordion-content.vue'));

// Select (Keep sync - common form element)
export { default as Select } from './select.vue';
export { default as SelectTrigger } from './select-trigger.vue';
export { default as SelectValue } from './select-value.vue';
export { default as SelectContent } from './select-content.vue';
export { default as SelectItem } from './select-item.vue';

// Popover (Keep sync - used in many places)
export { default as Popover } from './popover.vue';
export { default as PopoverTrigger } from './popover-trigger.vue';
export { default as PopoverContent } from './popover-content.vue';

// Tooltip (Keep sync - used everywhere)
export { default as Tooltip } from './tooltip.vue';
export { default as TooltipProvider } from './tooltip-provider.vue';
export { default as TooltipTrigger } from './tooltip-trigger.vue';
export { default as TooltipContent } from './tooltip-content.vue';

// Table (Keep sync - used on list pages)
export { default as Table } from './table.vue';
export { default as TableHeader } from './table-header.vue';
export { default as TableRow } from './table-row.vue';
export { default as TableHead } from './table-head.vue';
export { default as TableBody } from './table-body.vue';
export { default as TableCell } from './table-cell.vue';

// Fields (Keep sync - used in forms)
export { default as Field } from './field.vue';
export { default as FieldGroup } from './field-group.vue';
export { default as FieldTitle } from './field-title.vue';
export { default as FieldLabel } from './field-label.vue';
export { default as FieldDescription } from './field-description.vue';
export { default as FieldContent } from './field-content.vue';

// Misc
export { default as Avatar } from './avatar.vue';
export { default as AvatarImage } from './avatar-image.vue';
export { default as AvatarFallback } from './avatar-fallback.vue';
export { default as Alert } from './alert.vue';
export { default as AlertTitle } from './alert-title.vue';
export { default as AlertDescription } from './alert-description.vue';
export { default as BackToTop } from './back-to-top.vue';
export { default as Toast } from './toast.vue';

// Heavy UI (Always Async)
export const Pagination = defineAsyncComponent(() => import('./pagination.vue'));
export const ColorPicker = defineAsyncComponent(() => import('./color-picker.vue'));
export const IconPicker = defineAsyncComponent(() => import('./icon-picker.vue'));
