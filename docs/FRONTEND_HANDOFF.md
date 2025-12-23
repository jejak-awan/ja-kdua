# Frontend UI Development Handoff

> **Purpose:** Ensure consistency when updating JA-CMS frontend UI across sessions and developers.

---

## ⚠️ CRITICAL: The 3 Design Pillars

Every frontend UI change MUST follow these 3 design documents:

| Document | Purpose | Key Requirement |
|----------|---------|-----------------|
| [design-i18n.md](file:///var/www/ja-cms/docs/design-i18n.md) | Internationalization | All text must use translation keys |
| [design-shadcn.md](file:///var/www/ja-cms/docs/design-shadcn.md) | Component System | Use Shadcn design tokens |
| [design-theme.md](file:///var/www/ja-cms/docs/design-theme.md) | Dark/Light Mode | Use CSS variables, no hardcoded colors |

---

## Quick Reference Checklist

### ✅ Before Writing Code
- [ ] Check if translation keys exist for the text you need
- [ ] Check if Shadcn component exists for the UI element
- [ ] Verify both light and dark mode will work

### ✅ During Development
```vue
<!-- ✅ CORRECT -->
<template>
  <div class="bg-card text-foreground border-border">
    <h1>{{ $t('features.module.title') }}</h1>
    <Button variant="default">{{ $t('common.actions.save') }}</Button>
  </div>
</template>

<!-- ❌ WRONG -->
<template>
  <div class="bg-white text-gray-900 border-gray-200">
    <h1>Page Title</h1>
    <button class="btn-primary">Save</button>
  </div>
</template>
```

### ✅ After Development
- [ ] Run `npm run build` - no errors
- [ ] Test in dark mode
- [ ] Test with Indonesian language
- [ ] Add missing translations to EN/ID JSON files

---

## 1. Internationalization (i18n)

### File Structure
```
resources/lang/
├── en/
│   ├── common/          # Reusable (actions, labels, validation, messages, pagination)
│   ├── features/        # Feature-specific (auth, content, security, etc.)
│   └── index.js         # Exports all translations
└── id/                  # Same structure for Indonesian
```

### Translation Key Format
```javascript
// Pattern: {namespace}.{module}.{section}.{key}

// Common
$t('common.actions.save')
$t('common.labels.email')
$t('common.validation.required')

// Features
$t('features.security.logs.title')
$t('features.security.logs.eventTypes.login_failed')
```

### Adding New Translations
1. Add key to `resources/lang/en/features/{module}.json`
2. Add key to `resources/lang/id/features/{module}.json`
3. If new file, register in `resources/lang/{locale}/index.js`
4. Use in Vue: `{{ $t('features.module.key') }}`

---

## 2. Shadcn Design Tokens

### Color Classes (Always Use These)
| Purpose | Class | NOT |
|---------|-------|-----|
| Card background | `bg-card` | `bg-white` |
| Main background | `bg-background` | `bg-gray-100` |
| Muted background | `bg-muted` | `bg-gray-50` |
| Primary text | `text-foreground` | `text-gray-900` |
| Secondary text | `text-muted-foreground` | `text-gray-500` |
| Borders | `border-border` | `border-gray-200` |
| Table dividers | `divide-border` | `divide-gray-200` |
| Hover states | `hover:bg-accent` | `hover:bg-gray-100` |

### Status Badges (Transparent Style)
```html
<!-- Pattern: bg-{color}-500/20 text-{color}-500 border border-{color}-500/30 -->
<span class="bg-green-500/20 text-green-500 border border-green-500/30">Success</span>
<span class="bg-red-500/20 text-red-500 border border-red-500/30">Error</span>
<span class="bg-yellow-500/20 text-yellow-500 border border-yellow-500/30">Warning</span>
```

### Component Imports
```javascript
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
```

---

## 3. Dark/Light Mode

### How It Works
- Theme class (`dark`) applied to `<html>` element
- CSS variables defined in `resources/css/app.css`
- Toggle component: `DarkModeToggle.vue`

### Testing
1. Click theme toggle in navbar
2. Verify all elements visible in both modes
3. Check contrast ratios

### Common Issues & Fixes
| Issue | Fix |
|-------|-----|
| White background in dark mode | Replace `bg-white` with `bg-card` |
| Black text invisible in dark mode | Replace `text-gray-900` with `text-foreground` |
| Border not visible | Replace `border-gray-*` with `border-border` |
| Solid color badges | Use transparent: `bg-*-500/20 text-*-500` |

---

## Module-Specific Notes

### Security Module
- Event types use `getEventLabel()` for translation
- Protected IPs: localhost, private ranges (10.x, 172.16.x, 192.168.x)
- TrustProxies middleware handles Cloudflare, Nginx, AWS headers

### API Response Parsing
```javascript
// For paginated responses
const result = parseResponse(response);
logs.value = result.data || [];

// For single object
const data = parseSingleResponse(response);
```

---

## File Locations

| Type | Location |
|------|----------|
| Vue Components | `resources/js/views/admin/{module}/` |
| Shadcn UI | `resources/js/components/ui/` |
| Translations EN | `resources/lang/en/features/` |
| Translations ID | `resources/lang/id/features/` |
| CSS Variables | `resources/css/app.css` |
| Composables | `resources/js/composables/` |

---

## Build & Verify

```bash
# Build and check for errors
npm run build

# Watch for development
npm run dev
```

**Expected build time:** ~17 seconds  
**Known warnings:** Large chunk sizes (RichTextEditor) - acceptable

---

## Questions?

Refer to the full design documents:
- [design-i18n.md](file:///var/www/ja-cms/docs/design-i18n.md) - Complete i18n strategy
- [design-shadcn.md](file:///var/www/ja-cms/docs/design-shadcn.md) - Component library details
- [design-theme.md](file:///var/www/ja-cms/docs/design-theme.md) - Theme system implementation
