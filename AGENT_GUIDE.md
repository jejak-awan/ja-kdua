# JA-CMS Developer & AI Agent Guidelines

This document outlines the mandatory standards and architecture that must be followed when creating or updating any part of the JA-CMS project. **Consistency is key.**

## 1. Core Principles
- **Tuntas (Finished)**: Never leave a task half-done or with "to-do" comments.
- **Consistency**: Use existing patterns. Look at `BaseApiController` before writing a controller.
- **Security-First**: Never trust user input. Always use validation and standard helpers for IP/Auth.

## 2. Coding Standards
- **PHPStan**: All code MUST pass PHPStan Level 9. Always provide generic types for relations (e.g., `/** @return HasMany<TargetModel, $this> */`).
- **Styling**: Run `./vendor/bin/pint` before every commit. Follow the project's enforced style.
- **Strict Typing**: Use PHP 8.x/9.x strict typing. Declare return types and property types wherever possible.

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

## 6. Pre-Commit Checklist (Mandatory)
Before pushing or marking a task as complete:
1. `./vendor/bin/phpstan` (Must be 0 errors)
2. `./vendor/bin/pint` (Must be formatted)
3. `php artisan test` (Must pass all relevant tests)
4. Check `storage/logs/worker.log` to ensure background workers didn't crash.

---
*Follow these rules to ensure high-quality, maintainable, and secure contributions to JA-CMS.*
