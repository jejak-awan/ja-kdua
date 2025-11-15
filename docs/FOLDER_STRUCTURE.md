# 📁 Folder Structure & Naming Conventions

**Last Updated:** November 14, 2025  
**Status:** ✅ **Best Practices Defined**

---

## 🎯 Vue.js Best Practices

### Folder Naming
- ✅ **kebab-case** (lowercase dengan dash) untuk semua folder
- ✅ Contoh: `contents`, `categories`, `media-library`, `user-management`

### Component File Naming
- ✅ **PascalCase** untuk component files
- ✅ Contoh: `Index.vue`, `Create.vue`, `Edit.vue`, `AdminLayout.vue`

### Layouts Location
- ✅ **Layouts** di root `js/layouts/` (bukan di `components/`)
- ✅ Layouts adalah wrapper untuk routes, bukan reusable components

---

## 📂 Current Structure (Before Cleanup)

```
resources/js/
├── layouts/                    ✅ Correct location
│   └── AdminLayout.vue
├── components/
│   ├── layouts/               ❌ DUPLICATE - Should be removed
│   │   └── AdminLayout.vue    ❌ OLD VERSION - Should be deleted
│   ├── CommentForm.vue
│   ├── CommentsList.vue
│   ├── MediaUpload.vue
│   └── RichTextEditor.vue
├── views/
│   ├── admin/
│   │   ├── Categories/        ⚠️ Should be: categories/
│   │   ├── Contents/          ⚠️ Should be: contents/
│   │   ├── Media/             ⚠️ Should be: media/
│   │   ├── Settings/          ⚠️ Should be: settings/
│   │   ├── Users/             ⚠️ Should be: users/
│   │   └── Dashboard.vue
│   └── auth/
│       ├── Login.vue
│       ├── Register.vue
│       ├── ForgotPassword.vue
│       └── ResetPassword.vue
```

---

## 📂 Target Structure (After Cleanup)

```
resources/js/
├── layouts/                    ✅ Correct
│   └── AdminLayout.vue
├── components/                 ✅ Correct
│   ├── CommentForm.vue
│   ├── CommentsList.vue
│   ├── MediaUpload.vue
│   └── RichTextEditor.vue
├── views/
│   ├── admin/
│   │   ├── categories/         ✅ kebab-case
│   │   │   └── Index.vue
│   │   ├── contents/           ✅ kebab-case
│   │   │   ├── Index.vue
│   │   │   ├── Create.vue
│   │   │   └── Edit.vue
│   │   ├── media/              ✅ kebab-case
│   │   │   └── Index.vue
│   │   ├── settings/           ✅ kebab-case
│   │   │   └── Index.vue
│   │   ├── users/              ✅ kebab-case
│   │   │   └── Index.vue
│   │   └── Dashboard.vue
│   └── auth/                   ✅ Correct (kebab-case)
│       ├── Login.vue
│       ├── Register.vue
│       ├── ForgotPassword.vue
│       └── ResetPassword.vue
```

---

## 🔧 Refactoring Steps

### Step 1: Remove Duplicate Layouts ✅
- [x] Delete `components/layouts/AdminLayout.vue` (old version)
- [x] Keep `layouts/AdminLayout.vue` (current version)
- [x] Remove empty `components/layouts/` folder

### Step 2: Rename Admin View Folders ✅
- [x] Rename `views/admin/Categories/` → `views/admin/categories/`
- [x] Rename `views/admin/Contents/` → `views/admin/contents/`
- [x] Rename `views/admin/Media/` → `views/admin/media/`
- [x] Rename `views/admin/Settings/` → `views/admin/settings/`
- [x] Rename `views/admin/Users/` → `views/admin/users/`

### Step 3: Update Router Imports ✅
- [x] Update all imports in `router/index.js` to use kebab-case paths
- [x] All routes updated to use lowercase folder names

### Step 4: Verify All Imports ✅
- [x] Search for any other imports that reference old PascalCase paths
- [x] No other references found

---

## 📝 Rules Summary

1. **Folders**: Always use **kebab-case** (lowercase with dashes)
   - ✅ `contents`, `categories`, `media-library`
   - ❌ `Contents`, `Categories`, `MediaLibrary`

2. **Component Files**: Always use **PascalCase**
   - ✅ `Index.vue`, `Create.vue`, `AdminLayout.vue`
   - ❌ `index.vue`, `create.vue`, `admin-layout.vue`

3. **Layouts**: Place in `js/layouts/` (not in `components/`)
   - ✅ `layouts/AdminLayout.vue`
   - ❌ `components/layouts/AdminLayout.vue`

4. **Views**: Place in `js/views/` with kebab-case folders
   - ✅ `views/admin/contents/Index.vue`
   - ❌ `views/admin/Contents/Index.vue`

---

## ✅ Checklist

- [x] Remove duplicate layouts folder
- [x] Rename all admin view folders to kebab-case
- [x] Update router imports
- [x] Verify all routes work
- [x] Update documentation

---

## 📊 Summary of Changes

### Files Deleted
- ✅ `resources/js/components/layouts/AdminLayout.vue` (duplicate/old version)
- ✅ `resources/js/components/layouts/` (empty folder removed)

### Folders Renamed
- ✅ `views/admin/Categories/` → `views/admin/categories/`
- ✅ `views/admin/Contents/` → `views/admin/contents/`
- ✅ `views/admin/Media/` → `views/admin/media/`
- ✅ `views/admin/Settings/` → `views/admin/settings/`
- ✅ `views/admin/Users/` → `views/admin/users/`

### Files Updated
- ✅ `resources/js/router/index.js` (all imports updated to kebab-case)

---

**Status:** ✅ **Complete**  
**Last Updated:** November 14, 2025

