# i18n Implementation Design

## Overview

Comprehensive internationalization (i18n) strategy for JA-CMS with structured, maintainable translation files split by category and feature.

## Implementation Status ✅

**Last Updated:** 2025-12-23

| Category | Status | Files |
|----------|--------|-------|
| **Common Translations** | ✅ Complete | actions, labels, validation, messages, navigation, pagination |
| **Auth Module** | ✅ Complete | login, register, forgot password, email verify |
| **Content Module** | ✅ Complete | list, editor, categories, tags |
| **Users Module** | ✅ Complete | users.json, UserModal.vue |
| **Media Module** | ✅ Complete | media.json, file_manager.json |
| **Settings Module** | ✅ Complete | settings.json (all tabs) |
| **Security Module** | ✅ Complete | security.json (logs, blocklist, whitelist, event types) |
| **Analytics Module** | ✅ Complete | analytics.json |
| **Developer Tools** | ✅ Complete | developer.json (system, plugins, webhooks, logs) |
| **Frontend UI** | ✅ Complete | home, blog, search, post, contact, about |

**Languages Supported:** English (en), Indonesian (id)

---

## Architecture

### Translation File Structure

```
resources/lang/
├── en/
│   ├── common/              # Reusable translations
│   │   ├── actions.json     # Save, Edit, Delete, etc.
│   │   ├── labels.json      # Name, Email, Password, etc.
│   │   ├── validation.json  # Required, Invalid, etc.
│   │   ├── messages.json    # Success, Error messages
│   │   └── navigation.json  # Menu items, breadcrumbs
│   │
│   ├── features/            # Feature-specific translations
│   │   ├── auth.json        # Login, Register, Logout
│   │   ├── content.json     # Content management
│   │   ├── users.json       # User management
│   │   ├── media.json       # Media library
│   │   ├── settings.json    # Settings page
│   │   ├── analytics.json   # Analytics dashboard
│   │   └── comments.json    # Comments module
│   │
│   └── index.js             # Export all translations
│
├── id/                      # Indonesian
│   ├── common/
│   ├── features/
│   └── index.js
│
└── config.js                # i18n configuration
```

---

## Translation Categories

### 1. Common Translations (Reusable)

#### `common/actions.json`
```json
{
  "save": "Save",
  "edit": "Edit",
  "delete": "Delete",
  "cancel": "Cancel",
  "confirm": "Confirm",
  "create": "Create",
  "update": "Update",
  "submit": "Submit",
  "search": "Search",
  "filter": "Filter",
  "export": "Export",
  "import": "Import",
  "download": "Download",
  "upload": "Upload",
  "preview": "Preview",
  "publish": "Publish",
  "draft": "Save as Draft",
  "restore": "Restore",
  "archive": "Archive"
}
```

#### `common/labels.json`
```json
{
  "name": "Name",
  "email": "Email",
  "password": "Password",
  "title": "Title",
  "description": "Description",
  "status": "Status",
  "date": "Date",
  "time": "Time",
  "created_at": "Created At",
  "updated_at": "Updated At",
  "author": "Author",
  "category": "Category",
  "tags": "Tags",
  "image": "Image",
  "file": "File"
}
```

#### `common/validation.json`
```json
{
  "required": "{field} is required",
  "email": "Invalid email format",
  "min": "{field} must be at least {min} characters",
  "max": "{field} must not exceed {max} characters",
  "confirmed": "Passwords do not match",
  "unique": "{field} already exists",
  "invalid": "Invalid {field}"
}
```

#### `common/messages.json`
```json
{
  "success": {
    "created": "{item} created successfully",
    "updated": "{item} updated successfully",
    "deleted": "{item} deleted successfully",
    "saved": "Changes saved successfully"
  },
  "error": {
    "generic": "Something went wrong",
    "not_found": "{item} not found",
    "unauthorized": "You are not authorized to perform this action",
    "network": "Network error. Please try again"
  },
  "confirm": {
    "delete": "Are you sure you want to delete this {item}?",
    "unsaved": "You have unsaved changes. Are you sure you want to leave?"
  }
}
```

#### `common/navigation.json`
```json
{
  "menu": {
    "dashboard": "Dashboard",
    "content": "Content",
    "media": "Media",
    "users": "Users",
    "settings": "Settings",
    "logout": "Logout"
  },
  "breadcrumbs": {
    "home": "Home"
  }
}
```

---

### 2. Feature-Specific Translations

#### `features/auth.json`
```json
{
  "login": {
    "title": "Login to Your Account",
    "email_placeholder": "Enter your email",
    "password_placeholder": "Enter your password",
    "remember_me": "Remember me",
    "forgot_password": "Forgot password?",
    "submit": "Sign In",
    "no_account": "Don't have an account?",
    "register": "Register now"
  },
  "register": {
    "title": "Create an Account",
    "submit": "Create Account",
    "have_account": "Already have an account?",
    "login": "Sign in"
  },
  "logout": {
    "confirm": "Are you sure you want to logout?",
    "success": "Logged out successfully"
  }
}
```

#### `features/content.json`
```json
{
  "list": {
    "title": "All Contents",
    "empty": "No content found",
    "create_new": "Create New Content"
  },
  "form": {
    "title": "Content Title",
    "slug": "URL Slug",
    "content": "Content Body",
    "excerpt": "Excerpt",
    "featured_image": "Featured Image",
    "meta_title": "SEO Title",
    "meta_description": "SEO Description",
    "publish_date": "Publish Date",
    "status": {
      "draft": "Draft",
      "published": "Published",
      "scheduled": "Scheduled"
    }
  },
  "actions": {
    "publish_now": "Publish Now",
    "schedule": "Schedule",
    "save_draft": "Save as Draft"
  }
}
```

---

## Implementation Strategy

### 1. Library Selection

**Recommended: Vue I18n**

```bash
npm install vue-i18n@9
```

**Why Vue I18n?**
- ✅ Official Vue.js i18n solution
- ✅ Composition API support
- ✅ Lazy loading translations
- ✅ Pluralization & formatting
- ✅ TypeScript support

---

### 2. Configuration

**`resources/lang/config.js`**
```javascript
export default {
  legacy: false,
  locale: 'en',
  fallbackLocale: 'en',
  availableLocales: [
    { code: 'en', name: 'English', flag: '🇬🇧' },
    { code: 'id', name: 'Bahasa Indonesia', flag: '🇮🇩' },
  ],
  fallbackWarn: false,
  missingWarn: false,
};
```

**`resources/js/i18n.js`**
```javascript
import { createI18n } from 'vue-i18n';
import config from '../lang/config';
import en from '../lang/en';
import id from '../lang/id';

const i18n = createI18n({
  ...config,
  messages: { en, id },
});

export default i18n;
```

---

### 3. Translation Loading Strategy

#### Option A: Eager Loading (Simple)
```javascript
// lang/en/index.js
import actions from './common/actions.json';
import labels from './common/labels.json';
import auth from './features/auth.json';

export default {
  common: {
    actions,
    labels,
  },
  features: {
    auth,
  },
};
```

#### Option B: Lazy Loading (Optimized)
```javascript
// Dynamically import translations when needed
const loadLocaleMessages = async (locale) => {
  const messages = await import(`../lang/${locale}/index.js`);
  i18n.global.setLocaleMessage(locale, messages.default);
  return messages.default;
};
```

---

### 4. Usage in Components

**Template:**
```vue
<template>
  <button>{{ t('common.actions.save') }}</button>
  <h1>{{ t('features.auth.login.title') }}</h1>
  
  <!-- With parameters -->
  <p>{{ t('common.messages.success.created', { item: 'Post' }) }}</p>
  
  <!-- Pluralization -->
  <p>{{ t('features.content.items_count', { count: 5 }) }}</p>
</template>
```

**Script:**
```javascript
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

// Change language
const switchLanguage = (lang) => {
  locale.value = lang;
  localStorage.setItem('locale', lang);
};
```

---

## File Organization Rules

### Common vs Feature-Specific

**Use `common/` when:**
- Translation is used in 3+ places
- It's a standard action/label
- Part of form validation
- Generic UI messages

**Use `features/` when:**
- Specific to one module
- Business logic terminology
- Feature-unique workflows
- Domain-specific labels

### Naming Conventions

```
✅ Good:
- common/actions.json
- features/user-management.json
- features/content-editor.json

❌ Bad:
- buttons.json (too generic)
- page1.json (not descriptive)
- everything.json (not split)
```

---

## Migration Strategy

### Phase 1: Setup (Week 1)
1. Install Vue I18n
2. Create folder structure
3. Extract common translations
4. Setup configuration

### Phase 2: Common Translations (Week 2)
1. actions.json - All action buttons
2. labels.json - All form labels
3. validation.json - Validation messages
4. messages.json - Success/error messages
5. navigation.json - Menu & breadcrumbs

### Phase 3: Feature Translations (Week 3-4)
1. Auth module
2. Content management
3. User management
4. Media library
5. Settings
6. Analytics

### Phase 4: Testing & Refinement (Week 5)
1. Test all translations
2. Fix missing keys
3. QA review
4. Performance optimization

---

## Backend Integration

### Laravel Translation Files
```
resources/lang/
├── en/
│   ├── auth.php
│   ├── validation.php
│   └── messages.php
├── id/
│   └── ...
```

**Sync Strategy:**
- Frontend: Client-side translations (Vue I18n)
- Backend: Server-side translations (Laravel Lang)
- Share common messages via API if needed

---

## Best Practices

### 1. Keep Keys Descriptive
```javascript
// ✅ Good
"features.auth.login.submit_button"

// ❌ Bad
"btn1"
```

### 2. Use Nested Structure
```json
{
  "features": {
    "auth": {
      "login": {
        "title": "Login"
      }
    }
  }
}
```

### 3. Handle Pluralization
```json
{
  "items": "{count} item | {count} items",
  "items_count": "no items | one item | {count} items"
}
```

### 4. Format Dates/Numbers
```javascript
// Use i18n number & date formatting
{{ n(1000, 'currency') }}  // $1,000.00
{{ d(new Date(), 'short') }} // 12/23/2025
```

---

## Tooling

### VSCode Extensions
- **i18n Ally** - Inline translation management
- **Vue i18n** - Syntax highlighting

### Development Tools
```bash
# Extract missing keys
npm run i18n:extract

# Validate translations
npm run i18n:validate

# Generate types (TypeScript)
npm run i18n:types
```

---

## Performance Considerations

1. **Lazy load feature translations**
2. **Cache loaded translations**
3. **Minimize bundle size** (split by route)
4. **Use compilation** for production
5. **Avoid dynamic keys** (bundle optimization)

---

## Maintenance

### Adding New Translations
1. Identify if common or feature-specific
2. Add to appropriate JSON file
3. Update TypeScript types (if using)
4. Test in both languages
5. Document usage

### Translation Review Process
1. Developer adds key
2. Translation team reviews
3. Native speaker validates
4. QA tests implementation
5. Merge to main

---

## Example: Full Component

```vue
<template>
  <div>
    <h1>{{ t('features.content.list.title') }}</h1>
    
    <button @click="create">
      {{ t('common.actions.create') }}
    </button>
    
    <p v-if="items.length === 0">
      {{ t('features.content.list.empty') }}
    </p>
    
    <div v-for="item in items" :key="item.id">
      <button @click="edit(item)">
        {{ t('common.actions.edit') }}
      </button>
      <button @click="confirmDelete(item)">
        {{ t('common.actions.delete') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const confirmDelete = (item) => {
  if (confirm(t('common.messages.confirm.delete', { item: item.title }))) {
    deleteItem(item);
  }
};
</script>
```

---

## Conclusion

This i18n strategy provides:
- ✅ Clear organization (common vs features)
- ✅ Maintainable structure
- ✅ Scalable for growth
- ✅ Developer-friendly
- ✅ Performance optimized
