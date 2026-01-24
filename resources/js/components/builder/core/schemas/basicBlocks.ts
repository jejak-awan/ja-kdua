import { z } from 'zod'

// Shared schemas
const colorSchema = z.string().regex(/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/).optional()

const responsiveStringSchema = z.union([
    z.string(),
    z.object({
        desktop: z.string().optional(),
        tablet: z.string().optional(),
        mobile: z.string().optional()
    })
]).optional()

export const headingSchema = z.object({
    text: z.string().min(1, "Heading cannot be empty"),
    level: z.enum(['h1', 'h2', 'h3', 'h4', 'h5', 'h6']).default('h2'),
    alignment: z.enum(['left', 'center', 'right', 'justify']).optional(),
    color: colorSchema
})

export const buttonSchema = z.object({
    text: z.string().min(1, "Button text cannot be empty"),
    url: z.string().url("Invalid URL").or(z.string().startsWith('/')).or(z.string().startsWith('#')).optional(),
    size: z.enum(['sm', 'md', 'lg']).default('md'),
    variant: z.enum(['primary', 'secondary', 'outline', 'ghost']).default('primary'),
    color: colorSchema
})

export const imageSchema = z.object({
    url: z.string().url("Invalid image URL").optional(),
    alt: z.string().optional(),
    width: z.string().optional(),
    height: z.string().optional(),
    fit: z.enum(['cover', 'contain', 'fill', 'none']).optional()
})

export const textSchema = z.object({
    content: z.string().optional(),
    color: colorSchema,
    fontSize: responsiveStringSchema
})

// Export Zod inferred types for TypeScript usage
export type HeadingSchema = z.infer<typeof headingSchema>
export type ButtonSchema = z.infer<typeof buttonSchema>
export type ImageSchema = z.infer<typeof imageSchema>
export type TextSchema = z.infer<typeof textSchema>
