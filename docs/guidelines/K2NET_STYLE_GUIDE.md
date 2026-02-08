# K2NET Extended Development & Style Guide

This guide extends the core `AGENT_GUIDE.md` with specific rules for naming, translations, design patterns, and UI/UX consistency.

## 1. Naming Conventions

### Backend (Laravel)
- **Namespaces/Classes**: `PascalCase` (e.g., `MikrotikService`, `InfraController`).
- **Models**: `PascalCase` singular. ISP models MUST reside in `App\Models\Isp`.
- **Methods**: `camelCase`.
- **Database Tables**: `snake_case` plural (e.g., `isp_service_nodes`).
- **DB Columns**: `snake_case`.

### Frontend (Vue/TS)
- **Components**: `PascalCase` (e.g., `StatusBadge.vue`, `NodeMap.vue`).
- **Variables/Functions**: `camelCase`.
- **Stores**: `use[Domain]Store` (e.g., `useIspStore`).
- **CSS Classes**: Tailwind utility-first. Avoid custom CSS classes unless absolutely necessary (use `snake-case` if needed).

---

## 2. Translation (i18n)

### Rules
- **No Hardcoding**: Every string in `resources/js/` MUST use `$t('path.to.key')`.
- **Structure**:
    - **ISP Common**: `resources/lang/[locale]/common/isp.json`
    - **ISP Features**: `resources/lang/[locale]/features/isp/[feature].json`
- **Keys**: Use `snake_case` for keys (e.g., `confirm_delete`).

---

## 3. Design Patterns

### Service Pattern
- Controllers MUST be thin. All complex logic (Mikrotik API, billing calculations) stays in `app/Services/Isp`.
- Controllers handle validation and standardized responses via `BaseApiController`.

### API Pattern
- Every response must follow the K2NET standard:
  ```json
  {
    "success": true,
    "message": "...",
    "data": { ... }
  }
  ```

---

## 4. UI/UX Consistency (Dark/Light & ARIA)

### Dark/Light Mode
- Use Tailwind's `dark:` modifier.
- **Glassmorphism**: Use `bg-white/10 backdrop-blur-md` for overlays in dark mode.
- **Borders**: Prefer `border-border` (Tailwind variable) to ensure consistency across themes.

### Accessibility (ARIA)
- **Interactive Elements**: All buttons must have `aria-label` if they only contain icons.
- **Form Fields**: Ensure `<label>` is correctly linked to `<input>` via `id` and `for`.
- **Radix Vue**: Leverage `Radix Vue` headless components for complex UI (Dropdowns, Dialogs) as they handle ARIA roles automatically.

---

## 5. ISP-Specific Standards

### Infrastructure Icons
- Use specific Lucide icons for network devices:
    - `Router` for gateway/core routers.
    - `Network` for nodes/POP.
    - `Wifi` or `Zap` for active signals.

### Billing
- All currency formatting must be done via a shared utility helper to ensure consistency (Rp / IDR).
