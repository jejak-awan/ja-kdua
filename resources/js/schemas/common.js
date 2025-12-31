/**
 * Common form validation schemas using Zod with i18n support
 */
import { z } from 'zod';

/**
 * Helper to create translatable error message
 */
const t = (key, params = {}) => JSON.stringify({ key, params });

/**
 * Menu form schema
 */
export const menuSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    location: z.string().optional().or(z.literal('')),
});

/**
 * Menu item form schema
 */
export const menuItemSchema = z.object({
    title: z.string()
        .min(1, t('common.validation.required', { field: 'Title' }))
        .max(255, t('common.validation.max', { field: 'Title', max: 255 })),
    url: z.string().optional().or(z.literal('')),
    target: z.enum(['_self', '_blank']).optional(),
    icon: z.string().optional().or(z.literal('')),
});

/**
 * Role form schema
 */
export const roleSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    permissions: z.array(z.string()).optional(),
});

/**
 * Redirect form schema
 */
export const redirectSchema = z.object({
    from_url: z.string()
        .min(1, t('common.validation.required', { field: 'From URL' })),
    to_url: z.string()
        .min(1, t('common.validation.required', { field: 'To URL' })),
    status_code: z.number().min(300).max(399).optional(),
    is_active: z.boolean().optional(),
});

/**
 * Widget form schema
 */
export const widgetSchema = z.object({
    title: z.string()
        .min(1, t('common.validation.required', { field: 'Title' }))
        .max(255, t('common.validation.max', { field: 'Title', max: 255 })),
    type: z.enum(['text', 'html', 'recent_posts', 'categories', 'custom']),
    location: z.string().optional().or(z.literal('')),
    content: z.string().optional().or(z.literal('')),
    is_active: z.boolean().optional(),
});

/**
 * Comment form schema
 */
export const commentSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    email: z.string()
        .min(1, t('common.validation.required', { field: 'Email' }))
        .email(t('common.validation.email')),
    body: z.string()
        .min(1, t('common.validation.required', { field: 'Comment' }))
        .max(5000, t('common.validation.max', { field: 'Comment', max: 5000 })),
});

/**
 * Contact form schema
 */
export const contactSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    email: z.string()
        .min(1, t('common.validation.required', { field: 'Email' }))
        .email(t('common.validation.email')),
    subject: z.string()
        .max(255, t('common.validation.max', { field: 'Subject', max: 255 }))
        .optional()
        .or(z.literal('')),
    message: z.string()
        .min(10, t('common.validation.min', { field: 'Message', min: 10 }))
        .max(5000, t('common.validation.max', { field: 'Message', max: 5000 })),
});

/**
 * Newsletter subscription schema
 */
export const newsletterSchema = z.object({
    email: z.string()
        .min(1, t('common.validation.required', { field: 'Email' }))
        .email(t('common.validation.email')),
});

/**
 * Scheduled task form schema
 */
export const taskSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    command: z.string()
        .min(1, t('common.validation.required', { field: 'Command' })),
    schedule: z.string()
        .min(1, t('common.validation.required', { field: 'Schedule' })),
    description: z.string().optional().or(z.literal('')),
    is_active: z.boolean().optional(),
});

/**
 * Folder creation schema
 */
export const folderSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 }))
        .regex(/^[a-zA-Z0-9_\-\s]+$/, t('common.validation.alphanumeric', { field: 'Name' })),
});

/**
 * Email template form schema
 */
export const emailTemplateSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    subject: z.string()
        .min(1, t('common.validation.required', { field: 'Subject' }))
        .max(255, t('common.validation.max', { field: 'Subject', max: 255 })),
    content: z.string()
        .min(1, t('common.validation.required', { field: 'Content' })),
    type: z.string().optional().or(z.literal('')),
    is_active: z.boolean().optional(),
});

/**
 * Form builder schema
 */
export const formBuilderSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    slug: z.string()
        .min(1, t('common.validation.required', { field: 'Slug' }))
        .max(255, t('common.validation.max', { field: 'Slug', max: 255 })),
    description: z.string().optional().or(z.literal('')),
    success_message: z.string().optional().or(z.literal('')),
    is_active: z.boolean().optional(),
});

/**
 * Content template schema
 */
export const contentTemplateSchema = z.object({
    name: z.string()
        .min(1, t('common.validation.required', { field: 'Name' }))
        .max(255, t('common.validation.max', { field: 'Name', max: 255 })),
    description: z.string().optional().or(z.literal('')),
    content: z.string().optional().or(z.literal('')),
    is_active: z.boolean().optional(),
});
