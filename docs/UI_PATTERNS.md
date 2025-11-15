# UI Patterns & Consistency Guide

**Last Updated:** November 14, 2025

---

## 📋 Current Implementation Patterns

### Pattern 1: Separate Pages (Contents)
**Location:** `/admin/contents/create` dan `/admin/contents/:id/edit`

**Structure:**
```
views/admin/contents/
├── Index.vue      (List page)
├── Create.vue    (Full page - separate route)
└── Edit.vue      (Full page - separate route)
```

**Pros:**
- ✅ Full screen space untuk complex forms
- ✅ Better for long forms dengan banyak sections
- ✅ URL bookmarkable (bisa bookmark draft)
- ✅ Browser back/forward works naturally
- ✅ Better for rich text editors yang butuh space

**Cons:**
- ❌ Requires routing
- ❌ More navigation steps

---

### Pattern 2: Modal Components (Categories & Media)
**Location:** Modal muncul di atas Index page

**Structure:**
```
views/admin/categories/
└── Index.vue      (List + Modal)

components/categories/
├── CategoryModal.vue
└── CategoryTreeItem.vue
```

**Pros:**
- ✅ Faster workflow (no routing)
- ✅ Stay in context (lihat list sambil edit)
- ✅ Consistent dengan simple CRUD
- ✅ Less code (no separate routes)

**Cons:**
- ❌ Limited screen space
- ❌ Can be cramped untuk complex forms
- ❌ Modal bisa terlalu besar untuk mobile

---

## 🤔 Why Different Patterns?

### Contents (Separate Pages)
**Reason:** Content Editor sangat kompleks dengan:
- Rich Text Editor (butuh space)
- Multiple sections (Content, Featured Image, SEO)
- Long form dengan banyak fields
- Better UX dengan full page

### Categories & Media (Modals)
**Reason:** Simple CRUD dengan:
- Fewer fields
- Quick edits
- Better untuk frequent operations
- Stay in context

---

## 🎯 Recommendation: Hybrid Approach

### Option 1: Keep Current (Recommended)
- **Contents:** Separate pages (complex form)
- **Categories/Media:** Modals (simple CRUD)
- **Users:** Modal (simple form)
- **Settings:** Separate page (many sections)

**Pros:** Optimal UX untuk masing-masing use case

### Option 2: Make Everything Modal
- Extract ContentEditor sebagai reusable component
- Use modal untuk semua CRUD operations
- More consistent tapi mungkin cramped untuk Content Editor

**Pros:** More consistent
**Cons:** Content Editor mungkin terlalu cramped

### Option 3: Make Everything Separate Pages
- All CRUD operations jadi separate pages
- More routing, tapi consistent

**Pros:** Consistent
**Cons:** Slower workflow untuk simple operations

---

## 💡 Best Practice

**Rule of Thumb:**
- **Complex forms (>10 fields, rich editor):** Separate pages
- **Simple CRUD (<10 fields):** Modal components
- **Settings/Configuration:** Separate pages (many sections)

---

## 🔄 Refactoring Options

### Option A: Extract ContentEditor Component
Buat reusable `ContentEditor.vue` component yang bisa digunakan di:
- Separate page (Create.vue, Edit.vue)
- Modal (jika diperlukan)

**Structure:**
```
components/contents/
├── ContentEditor.vue      (Reusable form component)
├── ContentSEOFields.vue   (SEO section)
└── ContentMetaFields.vue (Meta section)

views/admin/contents/
├── Index.vue
├── Create.vue             (Uses ContentEditor)
└── Edit.vue               (Uses ContentEditor)
```

**Benefits:**
- ✅ Reusable component
- ✅ Bisa digunakan di modal jika diperlukan
- ✅ Better code organization
- ✅ Consistent dengan pattern Categories/Media

---

## 📝 Current Status

| Feature | Pattern | Reason |
|---------|---------|--------|
| Contents | Separate Pages | Complex form, rich editor |
| Categories | Modal | Simple CRUD |
| Media | Modal | Simple CRUD |
| Users | TBD | TBD |
| Settings | TBD | TBD |

---

## ✅ Action Items

1. **Decide on pattern** untuk Users & Settings
2. **Consider extracting** ContentEditor component untuk reusability
3. **Document** pattern decision untuk future development

---

**Status:** 📋 **Documented - Awaiting Decision**

