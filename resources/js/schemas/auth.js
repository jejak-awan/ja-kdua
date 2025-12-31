/**
 * Authentication form validation schemas using Zod
 * These schemas provide instant client-side validation before API calls
 */
import { z } from 'zod';

/**
 * Login form schema
 */
export const loginSchema = z.object({
    email: z.string()
        .min(1, 'Email wajib diisi')
        .email('Format email tidak valid'),
    password: z.string()
        .min(1, 'Password wajib diisi'),
});

/**
 * Registration form schema
 */
export const registerSchema = z.object({
    name: z.string()
        .min(2, 'Nama minimal 2 karakter')
        .max(255, 'Nama maksimal 255 karakter'),
    email: z.string()
        .min(1, 'Email wajib diisi')
        .email('Format email tidak valid'),
    password: z.string()
        .min(8, 'Password minimal 8 karakter'),
    password_confirmation: z.string()
        .min(1, 'Konfirmasi password wajib diisi'),
}).refine(data => data.password === data.password_confirmation, {
    message: 'Konfirmasi password tidak cocok',
    path: ['password_confirmation'],
});

/**
 * Forgot password form schema
 */
export const forgotPasswordSchema = z.object({
    email: z.string()
        .min(1, 'Email wajib diisi')
        .email('Format email tidak valid'),
});

/**
 * Reset password form schema
 */
export const resetPasswordSchema = z.object({
    email: z.string()
        .min(1, 'Email wajib diisi')
        .email('Format email tidak valid'),
    password: z.string()
        .min(8, 'Password minimal 8 karakter'),
    password_confirmation: z.string()
        .min(1, 'Konfirmasi password wajib diisi'),
    token: z.string().min(1),
}).refine(data => data.password === data.password_confirmation, {
    message: 'Konfirmasi password tidak cocok',
    path: ['password_confirmation'],
});
