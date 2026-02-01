# Rencana Standarisasi Import & Code Quality - JA-CMS

**Tanggal**: 2 Februari 2026  
**Status**: Draft - Menunggu Review  
**Konteks**: Belajar dari kegagalan migrasi bulk script sebelumnya, dokumen ini berisi pendekatan bertahap dan aman.

---

## 🚀 QUICK REFERENCE - BACA INI DULU!

> [!IMPORTANT]
> **Untuk Agent/Developer**: Section ini berisi ringkasan lengkap. Baca section detail hanya jika perlu informasi spesifik.

### Apa yang Harus Dikerjakan (Per-Phase)

| Phase | Minggu | Task | Command/Action |
|-------|--------|------|----------------|
| 1 | 1-2 | **ESLint Config Update** | Update `eslint.config.js` dengan rules di section "ESLint Optimization" |
| 1 | 1-2 | **Baseline Audit** | `npm run lint`, `npm run type-check`, catat hasil |
| 2 | 3-4 | **Import Fixes** | Fix per-directory, mulai dari `@/components/admin/` |
| 3 | 5-6 | **TypeScript - Core Types** | Fix `any` di `types/builder.ts`, `types/menu.ts` |
| 4 | 7 | **TypeScript - Components** | Fix `any` di Vue components |
| 5 | 8 | **Checkpoint** | `npm run build && npm run type-check && npm run lint` |
| 6 | 9 | **Naming Rename** | `git mv button.vue Button.vue` untuk ~40 UI files |
| 7 | 10 | **Console Cleanup** | Replace `console.log` → `logger.debug()` |
| 7 | 10 | **Error Handling** | Replace `catch (e: any)` → `catch (e: unknown)` |
| 8 | 10 | **TODO Resolution** | Fix 7 TODOs di Profile.vue, FieldActions.vue |
| 9 | 11-12 | **Add Tests** | Target: 40+ new tests untuk composables & UI |
| 10 | 12 | **A11y Audit** | Add ARIA to Button, Dialog, Select, Toast |
| 11 | 12 | **Final Verify** | Full regression test + report |

### ⚠️ RULES WAJIB

1. **JANGAN bulk script** - Kerjakan per-file atau per-directory
2. **Selalu verify** - `npm run build` setelah setiap batch perubahan
3. **Commit granular** - `refactor(imports): standardize [filename]`
4. **Jangan fix `any` di third-party** - Biarkan dengan comment

### 🔧 Key Standards (Quick Ref)

**Imports:**
```typescript
// Icons - WAJIB per-file import
import Home from 'lucide-vue-next/dist/esm/icons/home.js';

// UI Components - dari barrel
import { Button, Dialog } from '@/components/ui';
```

**Naming:**
- Components: `PascalCase.vue` (Button.vue, NOT button.vue)
- Composables: `useCamelCase.ts` (useToast.ts)
- Utils: `camelCase.ts` (debounce.ts)

**Error Handling:**
```typescript
catch (error: unknown) {  // WAJIB unknown, bukan any
    if (error instanceof Error) { ... }
}
```

**TypeScript:**
```typescript
// Ganti [key: string]: any dengan:
Record<string, unknown>
// atau interface spesifik
```

### 📊 Current Metrics (2 Feb 2026)

| Metric | Current | Target |
|--------|---------|--------|
| `any` usages | ~1000+ | <100 |
| Test coverage | ~2% (34 tests) | 40%+ (200+ tests) |
| console.log | 6 | 0 |
| TODOs | 7 | 0 |
| Naming issues | ~50 files | 0 |
| Vulnerabilities | 0 ✅ | 0 |

### 📁 Files Paling Penting untuk Di-fix

1. `types/builder.ts` - ~30 `any` usages
2. `stores/auth.ts` - mixed `any`/`unknown` in catch
3. `components/ui/*.vue` - ~40 files perlu rename ke PascalCase
4. `Profile.vue` - 3 TODOs

---

## Latar Belakang

Pada percobaan sebelumnya, migrasi import menggunakan bulk script gagal dan harus di-rollback. Rencana ini mengusulkan pendekatan **inkremental** dan **manual verification** untuk menghindari kegagalan yang sama.

---

## Target yang Ingin Dicapai

| # | Target | Deskripsi |
|---|--------|-----------|
| 1 | **Bundle Size** | Mengurangi ukuran bundle melalui optimasi import |
| 2 | **Performa & Keamanan** | Lazy loading strategis, code splitting optimal |
| 3 | **Sanitasi Code** | Pola import yang konsisten dan mudah di-maintain |
| 4 | **Cleanup Unused** | Menghapus import dan file yang tidak digunakan |
| 5 | **TypeScript Consistency** | Mengurangi penggunaan `any`, type safety yang lebih baik |
| 6 | **ESLint Optimization** | Rules yang lebih ketat untuk enforce standar |

---

## Dependency Audit (Updated: 2 Feb 2026)

### ✅ Security Status
```
npm audit → 0 vulnerabilities found
```

### ✅ Updated Packages (npm update applied)
91 packages updated to latest minor/patch versions including:
- `@tiptap/*` 3.14.0 → 3.18.0
- `@fullcalendar/*` 6.1.19 → 6.1.20
- `@playwright/test` 1.58.0 → 1.58.1
- `axios` 1.13.2 → 1.13.4
- `vite` 7.3.0 → 7.3.1
- `vue` 3.5.26 → 3.5.27
- `zod` 4.2.1 → 4.3.6
- And 84 more packages

### ✅ Major Upgrades Applied (2 Feb 2026)

| Package | Before | After | Breaking Changes Fixed |
|---------|--------|-------|------------------------|
| `vue-router` | 4.6.4 | **5.0.1** | ✅ None needed |
| `vue-i18n` | 9.14.5 | **11.2.8** | ✅ Fixed `$t()` signature in 2 files |
| `lucide-vue-next` | 0.562.0 | **0.563.0** | ✅ None needed |
| `globals` | 16.5.0 | **17.3.0** | ✅ None needed |

**Files Fixed for vue-i18n 11:**
- [`CSSField.vue`](file:///var/www/ja-cms/resources/js/components/builder/fields/CSSField.vue) - Removed inline fallback strings from `$t()`
- [`ScrollEffectsField.vue`](file:///var/www/ja-cms/resources/js/components/builder/fields/ScrollEffectsField.vue) - Removed inline fallback strings from `$t()`

### ⚠️ Remaining Major Upgrades (Manual Review Required)

| Package | Current | Latest | Breaking Changes | Recommendation |
|---------|---------|--------|------------------|----------------|
| `cropperjs` | 1.6.2 | 2.1.0 | ❌ Major API changes | Evaluate later |
| `vuedraggable` | 4.1.0 | 2.24.3 | ⚠️ Latest is older version | **Keep 4.1.0** (for Vue 3) |

### Build Verification After All Updates
```bash
npm run build     # ✅ Success (45.75s)
npm run type-check # ✅ Success (0 errors)
```

**Bundle Size Improvement:**
- `vendor-vue-core`: 271KB → 261KB (**-10KB**)
- `vendor-builder-fields`: 400KB → 399.8KB (minor reduction)

---

## Analisis Kondisi Saat Ini

### ✅ Pola Import yang Sudah Baik

1. **UI Components Barrel Export** (`@/components/ui/index.ts`)
   - Mix strategis antara sync dan async exports
   - Heavy components (`ConfirmModal`, `ColorPicker`, `IconPicker`, `Pagination`) sudah menggunakan `defineAsyncComponent()`
   - Contoh penggunaan yang baik:
   ```typescript
   // Sync - untuk komponen yang sering digunakan
   export { default as Button } from './button.vue';
   
   // Async - untuk komponen yang jarang/heavy
   export const ColorPicker = defineAsyncComponent(() => import('./color-picker.vue'));
   ```

2. **Per-File Lucide Icon Imports**
   - Sudah menggunakan import dari file individual untuk tree-shaking
   - Contoh di `AdminDashboard.vue`:
   ```typescript
   import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
   ```

3. **Dynamic Icon Component** (`LucideIcon.vue`)
   - Untuk icon yang ditentukan dinamis (dari database/config)
   - Menggunakan caching untuk mencegah re-import

4. **Content Renderer Definitions**
   - Semua block definition sudah menggunakan `defineAsyncComponent()`
   - Code splitting yang baik untuk page builder

### ✅ ESLint - Kondisi Saat Ini

**File**: [eslint.config.js](file:///var/www/ja-cms/eslint.config.js)

Sudah tersedia dengan konfigurasi:
- `@eslint/js` - Base ESLint rules
- `typescript-eslint` - TypeScript support
- `eslint-plugin-vue` - Vue.js linting

**Rules yang Aktif:**
| Rule | Setting | Catatan |
|------|---------|---------|
| `@typescript-eslint/no-explicit-any` | `warn` | ⚠️ Hanya warning, perlu diketatkan |
| `@typescript-eslint/no-unused-vars` | `warn` | ✅ Sudah aktif |
| `no-console` | `warn` | ✅ Allow `console.warn/error` |
| `vue/no-v-html` | `off` | ✅ Diperlukan untuk CMS |

### ✅ TypeScript - Kondisi Saat Ini

**File**: [tsconfig.json](file:///var/www/ja-cms/tsconfig.json)

| Setting | Value | Status |
|---------|-------|--------|
| `strict` | `true` | ✅ Strict mode aktif |
| `noEmit` | `true` | ✅ Type-check only |
| Path alias `@/*` | `resources/js/*` | ✅ Sudah dikonfigurasi |

**Type Definition Files**: 17 files di `/resources/js/types/`
- `builder.ts` (11.7KB) - ⚠️ Paling banyak `any`
- `menu.ts` (2.8KB)
- `cms.ts` (2.2KB)
- Dan 14 file lainnya

### ⚠️ Area yang Perlu Perhatian

1. **Inkonsistensi pola import** antar file
2. **Potensi unused imports** yang belum dibersihkan
3. **ESLint rules perlu optimasi** untuk enforce standar import

### ❌ TypeScript Issues - Ditemukan ~1000+ Penggunaan `any`

**Distribusi utama:**

| File/Area | Perkiraan `any` Count | Prioritas |
|-----------|----------------------|-----------|
| `types/builder.ts` | ~30+ | 🔴 High - core types |
| `types/*.ts` (lainnya) | ~50+ | 🟡 Medium |
| `components/**/*.vue` | ~500+ | 🟡 Medium |
| `utils/*.ts` | ~20+ | 🟢 Low |
| `services/*.ts` | ~50+ | 🟡 Medium |

**Pattern `any` yang Paling Sering:**
```typescript
// 1. Index signature - sering untuk dynamic objects
[key: string]: any;

// 2. Function parameters
function process(data: any) { ... }

// 3. Callback types
condition?: (settings: any) => boolean;

// 4. Generic fallback
const result: any = response.data;
```

---

## Standar TypeScript yang Direkomendasikan

### 1. Hindari `any` - Gunakan Type yang Tepat

```typescript
// ❌ SALAH
function process(data: any) { ... }

// ✅ BENAR - gunakan generic
function process<T>(data: T): T { ... }

// ✅ BENAR - gunakan unknown untuk data yang belum diketahui
function process(data: unknown) {
    if (typeof data === 'object' && data !== null) {
        // narrow the type
    }
}
```

### 2. Index Signatures - Gunakan Record<K, V>

```typescript
// ❌ SALAH
interface Settings {
    [key: string]: any;
}

// ✅ BENAR
interface Settings {
    [key: string]: string | number | boolean | null;
}

// ✅ ATAU gunakan Record
type Settings = Record<string, string | number | boolean | null>;
```

### 3. API Response - Gunakan Generic Types

```typescript
// ❌ SALAH
const response: any = await api.get('/users');

// ✅ BENAR
interface User { id: number; name: string; }
const response = await api.get<ApiResponse<User[]>>('/users');
```

### 4. Vue Component Props & Emits

```typescript
// ❌ SALAH
const props = defineProps<{ data: any }>();

// ✅ BENAR
interface BlockData {
    id: string;
    type: string;
    settings: Record<string, unknown>;
}
const props = defineProps<{ data: BlockData }>();
```

---

## ESLint Optimization Plan

### Current Config vs Recommended

```diff
// eslint.config.js - Perubahan yang direkomendasikan

rules: {
    // TypeScript Rules - KETATKAN
-   '@typescript-eslint/no-explicit-any': 'warn',
+   '@typescript-eslint/no-explicit-any': 'error',  // Phase 2+
    
    // TAMBAHKAN: Prevent barrel import dari lucide
+   'no-restricted-imports': ['error', {
+       paths: [{
+           name: 'lucide-vue-next',
+           message: 'Import dari lucide-vue-next/dist/esm/icons/[icon-name].js'
+       }]
+   }],
    
    // TAMBAHKAN: Enforce consistent type imports
+   '@typescript-eslint/consistent-type-imports': ['warn', {
+       prefer: 'type-imports',
+       disallowTypeAnnotations: false
+   }],
    
    // TAMBAHKAN: No unused imports (auto-fixable)
+   '@typescript-eslint/no-unused-vars': ['warn', {
+       argsIgnorePattern: '^_',
+       varsIgnorePattern: '^_',
+       ignoreRestSiblings: true
+   }],
}
```

### ESLint Optimization Phases

| Phase | Rule Change | Severity | Auto-fix |
|-------|-------------|----------|----------|
| 1 | Add `no-restricted-imports` untuk lucide | `error` | ❌ |
| 1 | Add `consistent-type-imports` | `warn` | ✅ |
| 2 | Change `no-explicit-any` → `error` | `error` | ❌ |
| 3 | Add import ordering rules | `warn` | ✅ |

---

## Naming Standardization

### 📊 Kondisi Saat Ini (Audit 2 Feb 2026)

| Area | Pola Saat Ini | Konsisten? | Contoh |
|------|---------------|------------|--------|
| **UI Components** | Mixed PascalCase + kebab-case | ❌ | `Spinner.vue`, `button.vue`, `ConfirmModal.vue`, `accordion-content.vue` |
| **Builder Fields** | PascalCase | ✅ | `ColorField.vue`, `CSSField.vue`, `AnimationField.vue` |
| **Composables** | use* prefix (camelCase) | ✅ | `useToast.ts`, `useBuilder.ts`, `useMediaManager.ts` |
| **Services** | Mixed PascalCase + camelCase | ❌ | `BlockPresetService.ts`, `api.ts`, `toast.ts`, `SystemMonitor.ts` |
| **Utils** | camelCase | ✅ | `debounce.ts`, `responseParser.ts`, `icons.ts` |
| **Types** | camelCase | ✅ | `builder.ts`, `menu.ts`, `dashboard.ts` |

### ✅ Standar Naming yang Direkomendasikan

#### 1. Vue Components (`.vue` files)

**Rule: Gunakan PascalCase untuk semua component files**

```bash
# ✅ BENAR - PascalCase
Button.vue
CardHeader.vue
ConfirmModal.vue
ColorPicker.vue

# ❌ SALAH - kebab-case  
button.vue
card-header.vue
confirm-modal.vue
```

> [!NOTE]
> Vue Style Guide merekomendasikan PascalCase untuk Single-File Components (SFC).
> Referensi: https://vuejs.org/style-guide/rules-strongly-recommended.html#single-file-component-filename-casing

#### 2. Composables (`.ts` files)

**Rule: Gunakan `use` prefix + camelCase**

```bash
# ✅ BENAR
useToast.ts
useMediaManager.ts
useFormValidation.ts

# ❌ SALAH
toast.ts          # Missing 'use' prefix
UseToast.ts       # PascalCase
use-toast.ts      # kebab-case
```

#### 3. Services (`.ts` files)

**Rule: Gunakan camelCase atau PascalCase + 'Service' suffix**

```bash
# ✅ BENAR
api.ts                    # Simple utility
ApiService.ts             # If class-based
templateService.ts        # camelCase utility
BlockPresetService.ts     # PascalCase for class

# ❌ SALAH - Inconsistent
toast.ts          # Should be toastService.ts or useToast.ts
```

#### 4. Utilities (`.ts` files)

**Rule: Gunakan camelCase**

```bash
# ✅ BENAR
debounce.ts
responseParser.ts
stringUtils.ts

# ❌ SALAH
Debounce.ts       # PascalCase
string-utils.ts   # kebab-case
```

#### 5. Type Definitions (`.ts` files)

**Rule: Gunakan camelCase untuk file, PascalCase untuk interface/type**

```typescript
// File: types/builder.ts
export interface BlockInstance { ... }  // ✅ PascalCase for types
export type BlockSettings = { ... }     // ✅ PascalCase for types
```

#### 6. Constants & Enums

**Rule: SCREAMING_SNAKE_CASE untuk constants, PascalCase untuk enums**

```typescript
// ✅ BENAR
export const MAX_UPLOAD_SIZE = 10485760;
export const DEFAULT_LOCALE = 'en';

export enum BlockType {
    Section = 'section',
    Row = 'row',
    Column = 'column',
}

// ❌ SALAH
export const maxUploadSize = 10485760;  // camelCase for constant
export enum blockType { ... }           // camelCase for enum
```

#### 7. Event Names & Props

**Rule: kebab-case untuk events, camelCase untuk props**

```vue
<!-- ✅ BENAR -->
<MyComponent 
    :someData="data" 
    @update:modelValue="handleUpdate"
    @custom-event="handleEvent"
/>

<!-- ❌ SALAH -->
<MyComponent 
    :SomeData="data"           <!-- PascalCase prop -->
    @updateModelValue="..."    <!-- camelCase event -->
/>
```

### 🔧 File Rename Priority (Phase 6 in Timeline)

**High Priority - UI Components (banyak digunakan):**
| Current | Rename To |
|---------|-----------|
| `button.vue` | `Button.vue` |
| `accordion-content.vue` | `AccordionContent.vue` |
| `accordion-item.vue` | `AccordionItem.vue` |
| `accordion-trigger.vue` | `AccordionTrigger.vue` |
| `alert-description.vue` | `AlertDescription.vue` |
| `alert-title.vue` | `AlertTitle.vue` |
| ... (semua kebab-case → PascalCase) |

**Medium Priority - Services:**
| Current | Rename To | Reason |
|---------|-----------|--------|
| `api.ts` | Keep as-is | Simple utility, OK |
| `toast.ts` | `toastService.ts` | Clarity |
| `templateService.ts` | `TemplateService.ts` | Consistency if class |

### ⚠️ Breaking Changes Warning

> [!CAUTION]
> Rename file akan mempengaruhi semua import statements!
> Gunakan `git mv` untuk tracking dan update imports secara bertahap.

```bash
# Safe rename with git
git mv resources/js/components/ui/button.vue resources/js/components/ui/Button.vue

# Then update barrel export
# index.ts: export { default as Button } from './Button.vue';
```

---

## Console.log & Debug Cleanup

### 📊 Kondisi Saat Ini
```
console.log statements found: 6
```

### ✅ Standar yang Direkomendasikan

**Rule: Gunakan logger utility, bukan console.log langsung**

```typescript
// ❌ SALAH - console.log langsung
console.log('User data:', userData);
console.error('Failed to fetch:', error);

// ✅ BENAR - Gunakan logger utility
import { logger } from '@/utils/logger';

logger.debug('User data:', userData);  // Stripped in production
logger.error('Failed to fetch:', error);  // Kept + sent to monitoring
```

### 🔧 Action Items
1. Audit semua `console.log` statements
2. Replace dengan `logger.debug()` atau `logger.info()`
3. Replace `console.error` dengan `logger.error()`
4. ESLint rule untuk prevent `console.log`:
   ```javascript
   'no-console': ['warn', { allow: ['warn', 'error'] }]
   ```

---

## Error Handling Standardization

### 📊 Kondisi Saat Ini
| Pattern | Count | Status |
|---------|-------|--------|
| `catch (error: unknown)` | ✅ | Correct |
| `catch (error: any)` | ❌ | Found in `stores/auth.ts` |
| `catch (e)` | ❌ | No type, found in services |

### ✅ Standar yang Direkomendasikan

**Rule: Selalu gunakan `error: unknown` dan type guard**

```typescript
// ❌ SALAH - Using 'any'
try {
    await api.post('/users');
} catch (error: any) {
    toast.error(error.message);  // Unsafe
}

// ✅ BENAR - Using 'unknown' with type guard
try {
    await api.post('/users');
} catch (error: unknown) {
    if (error instanceof Error) {
        toast.error(error.message);
    } else if (isAxiosError(error)) {
        toast.error(error.response?.data?.message || 'Request failed');
    } else {
        toast.error('An unexpected error occurred');
    }
}
```

### Helper Function untuk Error Handling

```typescript
// utils/errorHandler.ts
export function getErrorMessage(error: unknown): string {
    if (error instanceof Error) return error.message;
    if (typeof error === 'string') return error;
    if (isAxiosError(error)) {
        return error.response?.data?.message || error.message;
    }
    return 'An unexpected error occurred';
}

// Usage
catch (error: unknown) {
    toast.error(getErrorMessage(error));
}
```

### 🔧 Files to Fix
| File | Issue | Action |
|------|-------|--------|
| `stores/auth.ts` | Mixed `any`/`unknown` | Standardize to `unknown` |
| `services/api.ts` | `catch (error)` | Add `: unknown` |
| `services/DynamicContentService.ts` | `catch (e)` | Add `: unknown` |
| `services/BlockPresetService.ts` | `catch (e)` | Add `: unknown` |

---

## TODO/FIXME Resolution

### 📊 Kondisi Saat Ini
```
Total TODO/FIXME comments: 7
```

### 📍 Locations Found

| File | TODO | Priority | Action |
|------|------|----------|--------|
| `Profile.vue` (3x) | "Migrate components" | High | Complete migration or remove |
| `ChildrenManagerField.vue` | "Implement copy styles" | Medium | Implement or document as future |
| `FieldActions.vue` | "Implement copy attributes" | Medium | Implement or document |
| `FieldActions.vue` | "Implement extend attributes" | Medium | Implement or document |
| `FieldActions.vue` | "Implement find & replace" | Low | Document as future feature |

### ✅ Standar untuk TODO Comments

**Rule: Gunakan format yang trackable**

```typescript
// ❌ SALAH - Vague TODO
// TODO: Fix this later

// ✅ BENAR - Trackable TODO dengan context
// TODO(username): [JIRA-123] Implement copy styles functionality
// Expected deadline: Q1 2026
// Context: This feature requires refactoring the style inheritance system
```

---

## Unit Test Coverage Expansion

### 📊 Kondisi Saat Ini

| Metric | Value | Target |
|--------|-------|--------|
| Vue Components | 301 | - |
| Test Files | 6 | 30+ |
| Coverage | ~2% | 40%+ |
| Tests Passing | 34/34 ✅ | 200+ |

### Current Test Structure
```
tests/js/
├── components/
│   ├── Button.spec.ts
│   ├── builder/Builder.spec.ts
│   └── media/MediaPicker.spec.ts
├── shared/ui/
│   ├── Badge.spec.ts
│   └── Checkbox.spec.ts
└── utils/
    └── utils.spec.ts
```

### 🎯 Priority Components to Test

**High Priority (Core UI):**
| Component | Why | Complexity |
|-----------|-----|------------|
| `ConfirmModal.vue` | Critical for user actions | Medium |
| `Toast.vue` | Used everywhere | Low |
| `Dialog.vue` | Complex interactions | High |
| `DataTable.vue` | Complex data handling | High |

**High Priority (Business Logic):**
| Component | Why | Complexity |
|-----------|-----|------------|
| `useBuilder.ts` | Core functionality | High |
| `useToast.ts` | Used everywhere | Low |
| `useFormValidation.ts` | Form handling | Medium |
| `responseParser.ts` | API handling | Medium |

### Test Command
```bash
# Run all tests
npm run test

# Run with coverage
npm run test -- --coverage

# Run specific test
npm run test -- tests/js/components/Button.spec.ts
```

---

## Accessibility (a11y) Improvements

### 📊 Kondisi Saat Ini
| Metric | Value | Target |
|--------|-------|--------|
| ARIA attributes in UI | 11 | 100+ |
| UI Components | 50+ | - |
| Coverage | ~20% | 90%+ |

### ✅ Standar A11y yang Direkomendasikan

**1. Interactive Elements (Buttons, Links)**
```vue
<!-- ❌ SALAH - No accessibility -->
<div @click="handleClick">Click me</div>

<!-- ✅ BENAR - Accessible -->
<button 
    type="button"
    :aria-label="label"
    :aria-disabled="disabled"
    @click="handleClick"
>
    Click me
</button>
```

**2. Form Elements**
```vue
<!-- ❌ SALAH - No label association -->
<input type="text" placeholder="Name">

<!-- ✅ BENAR - Properly labeled -->
<label :for="inputId">Name</label>
<input 
    :id="inputId" 
    type="text"
    aria-describedby="name-help"
>
<span id="name-help">Enter your full name</span>
```

**3. Modal/Dialog**
```vue
<div 
    role="dialog"
    aria-modal="true"
    :aria-labelledby="titleId"
    :aria-describedby="descriptionId"
>
    <h2 :id="titleId">{{ title }}</h2>
    <p :id="descriptionId">{{ description }}</p>
</div>
```

### 🔧 Priority Components for A11y

| Component | Missing | Priority |
|-----------|---------|----------|
| `Button.vue` | `aria-label`, `aria-disabled` | High |
| `Dialog.vue` | `role`, `aria-modal` | High |
| `Select.vue` | `aria-expanded`, `aria-haspopup` | High |
| `Toast.vue` | `role="alert"`, `aria-live` | Medium |
| `Tabs.vue` | `role="tablist"`, `aria-selected` | Medium |

---

## Deprecated Code Cleanup

### 📊 Kondisi Saat Ini
```
Deprecated references found: 2
```

### Action
- Audit dan remove deprecated code
- Update ke versi API yang baru
- Document breaking changes jika ada

### 1. Lucide Icons

#### Rule: Gunakan **Static Import dari Per-File Path**

```typescript
// ✅ BENAR - Static import, tree-shakeable
import Home from 'lucide-vue-next/dist/esm/icons/home.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';

// ❌ SALAH - Named import dari barrel (tidak tree-shakeable)
import { Home, Settings } from 'lucide-vue-next';
```

#### Kapan Gunakan Dynamic `<LucideIcon name="...">`?

Hanya gunakan `LucideIcon.vue` component ketika **nama icon tidak diketahui saat compile time**:
- Icon dari database (content builder blocks)
- Icon dari user configuration
- Icon yang ditentukan secara kondisional berdasarkan data

```vue
<!-- ✅ Untuk icon dinamis dari data -->
<LucideIcon :name="block.iconName" />

<!-- ✅ Untuk icon statis - gunakan static import -->
<script setup>
import AlertCircle from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
</script>
<template>
  <AlertCircle class="w-4 h-4" />
</template>
```

---

### 2. UI Components

#### Rule: Import dari Barrel Export `@/components/ui`

```typescript
// ✅ BENAR - Import dari barrel
import { Button, Card, CardContent, Dialog } from '@/components/ui';

// ❌ SALAH - Import langsung dari file individual
import Button from '@/components/ui/button.vue';
import Card from '@/components/ui/card.vue';
```

#### Alasan:
- Barrel export sudah menangani async/sync split dengan optimal
- Lebih mudah di-maintain
- Konsistensi antar file

---

### 3. Feature Components (Non-UI)

#### Rule: Import Langsung dari File, dengan Async untuk Heavy Components

```typescript
// Komponen ringan - import langsung
import QuickActions from '@/components/admin/QuickActions.vue';

// Komponen berat atau jarang diakses - defineAsyncComponent
import { defineAsyncComponent } from 'vue';
const HeavyChart = defineAsyncComponent(() => import('@/components/charts/HeavyChart.vue'));
```

#### Kapan Gunakan `defineAsyncComponent`?
- Komponen > 10KB
- Komponen yang hanya muncul pada kondisi tertentu (modals, dialogs)
- Komponen yang memiliki dependencies besar (chart libraries, editors)

---

### 4. Content Renderer Blocks

#### Rule: Selalu Gunakan `defineAsyncComponent`

Semua block definitions di `/components/content-renderer/definitions/` **WAJIB** menggunakan async import:

```typescript
import { defineAsyncComponent } from 'vue';

export default {
    name: 'audio',
    component: defineAsyncComponent(() => import('@/shared/blocks/AudioBlock.vue')),
};
```

---

### 5. Utilities & Services

#### Rule: Gunakan Named Exports

```typescript
// @/utils/responseParser.ts
export { parseSingleResponse, parseResponse, ensureArray };

// Usage
import { parseSingleResponse, parseResponse } from '@/utils/responseParser';
```

---

## Strategi Implementasi Bertahap

> [!CAUTION]
> **JANGAN gunakan bulk script otomatis!** Pengalaman sebelumnya menunjukkan ini berisiko tinggi.

### Timeline Overview

```
Minggu 1-2: Setup & Audit
Minggu 3-4: ESLint Rules & Import Fix
Minggu 5-6: TypeScript Cleanup (Core Types)
Minggu 7-8: TypeScript Cleanup (Components) + Verify
```

---

### Phase 1: Setup & Audit (Minggu 1-2)

#### 1.1 Update ESLint Config
```bash
# Tambahkan rules baru ke eslint.config.js
# Lihat section "ESLint Optimization Plan" di atas
```

**Tasks:**
- [ ] Tambah `no-restricted-imports` untuk lucide-vue-next
- [ ] Tambah `consistent-type-imports` rule
- [ ] Run `npm run lint` untuk baseline report
- [ ] Save baseline bundle size: `npm run build && ls -la public/build/assets/`

#### 1.2 Audit Existing Issues
```bash
# Generate report
npm run lint 2>&1 | tee lint-report.txt
npm run type-check 2>&1 | tee type-check-report.txt
```

**Deliverables:**
- `lint-report.txt` - baseline lint issues
- `type-check-report.txt` - baseline type issues
- Bundle size baseline

---

### Phase 2: Fix Import Violations (Minggu 3-4)

Migrasi dilakukan **per-direktori**, bukan bulk:

| Batch | Direktori | Est. Files | Priority |
|-------|-----------|------------|----------|
| 1 | `@/components/admin/` | 8 | 🔴 High |
| 2 | `@/components/dashboard/` | 3 | 🔴 High |
| 3 | `@/components/charts/` | 3 | 🟡 Medium |
| 4 | `@/components/editor/` | 24 | 🟡 Medium |
| 5 | `@/views/` | ~91 | 🟢 Low (largest) |
| 6 | `@/components/builder/` | ~238 | 🟢 Low (largest) |

**Workflow per file:**
1. `git add -p` - stage changes granularly
2. Make import changes
3. Run `npm run build` - verify build
4. Run `npm run dev` - verify runtime
5. Commit with message: `refactor(imports): standardize [filename]`

---

### Phase 3: TypeScript Cleanup - Core Types (Minggu 5-6)

**Target Files (Priority Order):**

| File | Est. `any` | Action |
|------|-----------|--------|
| `types/builder.ts` | ~30 | Replace with proper interfaces |
| `types/menu.ts` | ~10 | Replace with proper interfaces |
| `types/theme.ts` | ~8 | Replace with proper interfaces |
| `types/dashboard.ts` | ~5 | Replace with proper interfaces |

**Approach:**
1. Identify each `any` usage
2. Determine if it can be:
   - Replaced with specific type
   - Replaced with `unknown`
   - Replaced with generic `<T>`
   - Kept as `any` with `// eslint-disable-next-line` + comment

**Example Refactor:**
```typescript
// BEFORE
interface Block {
    settings: any;
    [key: string]: any;
}

// AFTER
interface BlockSettings {
    width?: string;
    height?: string;
    background?: string;
    // ... specific settings
}

interface Block {
    settings: BlockSettings;
    [key: string]: unknown;  // or specific union type
}
```

---

### Phase 4: TypeScript Cleanup - Components (Minggu 7)

**Target: Reduce `any` in Vue components**

Focus areas:
1. API response typing
2. Event handler parameters
3. Prop definitions
4. Ref types

**Don't fix:**
- Third-party library types (e.g., vuedraggable)
- Genuinely dynamic data (leave with comment)

---

### Phase 5: Verify & Measure (Minggu 8)

1. **Build check:**
   ```bash
   npm run build
   npm run type-check
   npm run lint
   ```

2. **Bundle size comparison:**
   ```bash
   ls -la public/build/assets/
   # Compare with baseline
   ```

3. **Manual testing:**
   - Login flow
   - Dashboard load
   - Content builder
   - Media upload

4. **Generate interim report:**
   - Lines of `any` reduced
   - Bundle size change
   - Build time change

---

### Phase 6: Naming Standardization (Minggu 9)

**Rename UI components dari kebab-case ke PascalCase:**

| Priority | Files | Action |
|----------|-------|--------|
| High | ~40 UI components | `git mv button.vue Button.vue` |
| Medium | Services | `toast.ts` → `toastService.ts` |
| Low | Types | Keep as-is (already consistent) |

**Workflow:**
1. Rename file dengan `git mv`
2. Update import di barrel export (`index.ts`)
3. Run build untuk detect broken imports
4. Fix broken imports
5. Commit per-batch (5-10 files)

---

### Phase 7: Console & Error Cleanup (Minggu 10)

**Console.log cleanup:**
```bash
# Find all console.log statements
grep -r "console.log" resources/js --include="*.vue" --include="*.ts"
```

**Error handling standardization:**
- Replace `catch (error: any)` → `catch (error: unknown)`
- Add `getErrorMessage()` helper function
- Update all catch blocks to use type guards

**Files to fix:**
- `stores/auth.ts`
- `services/api.ts`
- `services/DynamicContentService.ts`
- `services/BlockPresetService.ts`

---

### Phase 8: TODO Resolution (Minggu 10)

**Resolve or document all TODOs:**

| File | Action |
|------|--------|
| `Profile.vue` (3x) | Complete migration or remove dead code |
| `ChildrenManagerField.vue` | Implement or create issue |
| `FieldActions.vue` (3x) | Implement or create issues |

---

### Phase 9: Test Coverage Expansion (Minggu 11-12)

**Target: 40%+ coverage dari components kritis**

| Week | Focus | Est. New Tests |
|------|-------|----------------|
| 11 | Core composables (`useToast`, `useBuilder`) | 15 tests |
| 11 | Utility functions | 10 tests |
| 12 | Critical UI (`Dialog`, `Toast`) | 12 tests |
| 12 | Business logic components | 10 tests |

**Commands:**
```bash
# Run tests with coverage
npm run test -- --coverage

# Generate coverage report
npx vitest run --coverage --reporter=html
```

---

### Phase 10: Accessibility Audit (Minggu 12)

**Add ARIA attributes to UI components:**

| Component | Additions |
|-----------|-----------|
| `Button.vue` | `aria-label`, `aria-disabled` |
| `Dialog.vue` | `role="dialog"`, `aria-modal` |
| `Select.vue` | `aria-expanded`, `aria-haspopup` |
| `Toast.vue` | `role="alert"`, `aria-live="polite"` |

---

### Phase 11: Final Verification (Minggu 12)

1. **Full regression testing**
2. **Performance audit:**
   ```bash
   npm run build -- --report
   ```
3. **Generate final report**
4. **Documentation update**

---

## Summary Timeline (12 Weeks)

| Week | Phase | Focus |
|------|-------|-------|
| 1-2 | Phase 1 | Setup & Audit |
| 3-4 | Phase 2 | Import Standardization |
| 5-6 | Phase 3 | TypeScript Core Types |
| 7 | Phase 4 | TypeScript Components |
| 8 | Phase 5 | Verify & Measure (Checkpoint) |
| 9 | Phase 6 | Naming Standardization |
| 10 | Phase 7-8 | Console/Error Cleanup + TODO Resolution |
| 11-12 | Phase 9-10 | Test Coverage + Accessibility |
| 12 | Phase 11 | Final Verification |

---

## ESLint Rules yang Direkomendasikan

Tambahkan ke `eslint.config.js`:

```javascript
{
    rules: {
        // Auto-fix unused imports
        'no-unused-vars': 'warn',
        
        // Enforce import order
        'import/order': ['warn', {
            'groups': ['builtin', 'external', 'internal', 'parent', 'sibling'],
            'newlines-between': 'always'
        }],
        
        // Warn on barrel import dari lucide (tidak tree-shakeable)
        'no-restricted-imports': ['error', {
            paths: [{
                name: 'lucide-vue-next',
                message: 'Import dari lucide-vue-next/dist/esm/icons/[icon-name].js untuk tree-shaking'
            }]
        }]
    }
}
```

---

## Script Bantuan (Opsional - Gunakan dengan Hati-hati)

### Audit Icon Usage

```bash
# Cari semua penggunaan icon import
grep -r "from 'lucide-vue-next'" resources/js --include="*.vue" --include="*.ts" -l

# Cari penggunaan yang benar (per-file import)
grep -r "lucide-vue-next/dist/esm/icons" resources/js --include="*.vue" --include="*.ts" -l
```

### Check Unused Imports

```bash
npx eslint resources/js --ext .vue,.ts --no-error-on-unmatched-pattern
```

---

## Checklist Verifikasi

Setelah setiap perubahan:

- [ ] `npm run build` berhasil tanpa error
- [ ] `npm run dev` berjalan normal
- [ ] Tidak ada console error di browser
- [ ] Halaman utama load dengan benar
- [ ] Feature yang diubah berfungsi normal

---

## Risiko dan Mitigasi

| Risiko | Mitigasi |
|--------|----------|
| Build gagal setelah perubahan | Commit per-file, rollback mudah |
| Runtime error | Test setiap perubahan di dev mode |
| Perubahan besar terlalu kompleks | Batch kecil per direktori |
| Team tidak konsisten | ESLint rules + code review |

---

## Rekomendasi Prioritas

1. **Minggu ini**: Setup ESLint rules dulu, agar otomatis mencegah pola buruk baru
2. **Bertahap**: Mulai dari file dengan icon imports yang paling banyak
3. **Jangan Buru-buru**: Lebih baik lambat tapi aman daripada cepat tapi rollback

---

## Referensi

- [AdminDashboard.vue](file:///var/www/ja-cms/resources/js/components/dashboard/AdminDashboard.vue) - Contoh pola import yang baik
- [UI Barrel Export](file:///var/www/ja-cms/resources/js/components/ui/index.ts) - Contoh async/sync split
- [LucideIcon.vue](file:///var/www/ja-cms/resources/js/components/ui/LucideIcon.vue) - Dynamic icon component
- [Vite Config](file:///var/www/ja-cms/vite.config.js) - Manual chunks configuration

---

**Dibuat oleh**: AI Assistant  
**Review by**: [Nama Reviewer]  
**Approved**: [ ] Ya / [ ] Tidak  
**Catatan Review**: _____
