# JA-CMS Developer & AI Agent Guidelines

This document outlines the mandatory standards and architecture that must be followed when creating or updating any part of the JA-CMS project. **Consistency is key.**

## 1. Core Principles
- **Tuntas (Finished)**: Never leave a task half-done or with "to-do" comments.
- **Consistency**: Use existing patterns. Look at `BaseApiController` before writing a controller.
- **Security-First**: Never trust user input. Always use validation and standard helpers for IP/Auth.

## 2. Coding Standards
- **PHPStan (Level 9)**: All code MUST pass PHPStan Level 9. 
    - No exceptions for `mixed` - always cast or type-hint using `@var`.
    - Relations MUST specify generics: `/** @return HasMany<TargetModel, $this> */`.
    - Mixed IDs from `getAttribute('id')` MUST be narrowed before usage: `is_numeric($id) ? (int) $id : 0`.
- **Laravel Pint**: Run `./vendor/bin/pint` before every commit. This ensures code style consistency across the team.
- **Strict Typing**: Use PHP 8.x/9.x strict typing. Declare return types and property types wherever possible. Always use `declare(strict_types=1);` in new files.

## 3. API Guidelines
- **Inheritance**: All `Api/V1` controllers MUST extend `App\Http\Controllers\Api\V1\BaseApiController`.
- **Responses**: Use `$this->success($data, $message)` or `$this->error($message, $status)`. Do NOT return raw `response()->json()` in controllers.
- **Error Logging**: Use the built-in `handleException` or `error` methods in `BaseApiController` to ensure errors are logged with Trace IDs.

## 4. Architecture
- **Services**: Business logic belongs in `app/Services`. Controllers should be thin.
- **Models**: Use traits for shared functionality (e.g., `HasShortUuid`, `Loggable`).
- **Helpers**: Use `App\Helpers\IpHelper::getClientIp($request)` instead of `$request->ip()` for Cloudflare compatibility.

## 5. Security Protocols
- **CSP**: Content Security Policy is managed in `App\Http\Middleware\SecurityHeaders.php`. If you add a new external script source, add it there, not in Nginx.
- **Validation**: Always use `$request->validate([...])` or FormRequests.
- **Storage**: Never store files in `public/` directly. Use the relevant `disk` and symbolic links.

## 6. Frontend Standards (Vue/TS)
- **Component Style**: Use `<script setup lang="ts">`. Avoid Option API or class-based components.
- **TypeScript**: 
    - Define interfaces for all API responses in `resources/js/types/`.
    - Minimize `as any`. Use proper casting or generics if a library type is missing.
    - Export shared types from `resources/js/types/index.ts`.
- **Imports**:
    - Use `@/` alias for all internal imports.
    - **Lucide Icons**: Import icons specifically from the ESM path for tree-shaking:
      `import House from 'lucide-vue-next/dist/esm/icons/house.js';`
- **i18n**: Never hardcode user-facing strings. Use `$t()` or `t()` from `vue-i18n`.
- **State**: Use Pinia for global state. Use `pinia-plugin-persistedstate` only for Auth or critical UI preferences (not for large datasets).
- **API**: Use the standard `@/services/api` instance for all requests to ensure circuit breaking and auth headers are applied.

## 7. Pre-Commit Checklist (Mandatory)
Before pushing or marking a task as complete:
1. **Backend**:
    - `./vendor/bin/phpstan` (0 errors)
    - `./vendor/bin/pint` (Clean)
    - `php artisan test` (Pass)
2. **Frontend**:
    - `npm run type-check` (0 errors)
    - `npm run lint` (0 errors)
3. **Logs**: Check `storage/logs/` for any new errors or worker crashes.

---
*Follow these rules and the [K2NET Style Guide](file:///var/www/ja-kdua/docs/guidelines/K2NET_STYLE_GUIDE.md) to ensure high-quality, maintainable, and secure contributions to K2NET.*
