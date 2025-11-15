# TODO List - Complete Frontend Implementation

Dokumen ini berisi todo list lengkap untuk menyelesaikan semua fitur frontend yang belum diimplementasikan.

**Last Updated:** December 2024  
**Total Tasks:** 31 features  
**Status:** ✅ **All Completed (100%)**

---

## 🎯 High Priority Tasks (Should Implement First)

### 1. Tags Management UI ✅
- [x] Create `/resources/js/views/admin/tags/Index.vue`
  - [x] Tags list dengan search & filter
  - [x] Tag CRUD modal
  - [x] Tag usage statistics display
  - [x] Delete confirmation
- [x] Create `/resources/js/components/tags/TagModal.vue`
  - [x] Create/Edit tag form
  - [x] Name, slug, description fields
- [x] Add route `/admin/tags` di router
- [x] Add navigation link di AdminLayout
- [x] Integrate dengan Content Editor (tag selector sudah ada, pastikan lengkap)

**API Endpoints:**
- `GET /admin/cms/tags` - List tags
- `POST /admin/cms/tags` - Create tag
- `PUT /admin/cms/tags/{tag}` - Update tag
- `DELETE /admin/cms/tags/{tag}` - Delete tag

**Estimated Time:** 2-3 hours

---

### 2. Content Advanced Features ✅
- [x] **Content Duplicate**
  - [x] Add duplicate button di Contents Index
  - [x] Implement duplicate action (`POST /admin/cms/contents/{content}/duplicate`)
  - [x] Show success message & redirect to edit page

- [x] **Content Bulk Actions**
  - [x] Add checkbox selection di Contents Index
  - [x] Add bulk actions dropdown (Publish, Draft, Archive, Delete)
  - [x] Implement bulk action API call (`POST /admin/cms/contents/bulk-action`)
  - [x] Show confirmation dialog
  - [x] Refresh list after action

- [x] **Content Revisions**
  - [x] Create `/resources/js/views/admin/contents/Revisions.vue`
  - [x] Show revisions list for content
  - [x] Revision detail view
  - [x] Restore revision action
  - [x] Add "View Revisions" button di Content Edit page

- [x] **Content Locking**
  - [x] Show lock status di Content Edit page
  - [x] Auto-lock on edit
  - [x] Show locked by user info
  - [x] Unlock button
  - [x] Handle lock timeout

- [x] **Content Preview**
  - [x] Add preview button di Content Edit page
  - [x] Open preview in new tab/window
  - [x] Use preview API endpoint

**API Endpoints:**
- `POST /admin/cms/contents/{content}/duplicate`
- `POST /admin/cms/contents/bulk-action`
- `GET /admin/cms/contents/{content}/revisions`
- `GET /admin/cms/contents/{content}/revisions/{revision}`
- `POST /admin/cms/contents/{content}/revisions/{revision}/restore`
- `POST /admin/cms/contents/{content}/lock`
- `POST /admin/cms/contents/{content}/unlock`
- `GET /admin/cms/contents/{content}/preview`

**Estimated Time:** 8-10 hours

---

### 3. Email Templates Management
- [ ] Create `/resources/js/views/admin/email-templates/Index.vue`
  - [ ] Email templates list
  - [ ] Search & filter
  - [ ] Create/Edit/Delete actions
- [ ] Create `/resources/js/views/admin/email-templates/Create.vue`
- [ ] Create `/resources/js/views/admin/email-templates/Edit.vue`
  - [ ] Template editor (code editor untuk HTML)
  - [ ] Variables list
  - [ ] Preview button
  - [ ] Send test email button
- [ ] Add route `/admin/email-templates` di router
- [ ] Add navigation link di AdminLayout (under Settings)

**API Endpoints:**
- `GET /admin/cms/email-templates`
- `POST /admin/cms/email-templates`
- `GET /admin/cms/email-templates/{emailTemplate}`
- `PUT /admin/cms/email-templates/{emailTemplate}`
- `DELETE /admin/cms/email-templates/{emailTemplate}`
- `POST /admin/cms/email-templates/{emailTemplate}/preview`
- `POST /admin/cms/email-templates/{emailTemplate}/send-test`

**Estimated Time:** 4-5 hours

---

### 4. Media Advanced Features
- [ ] **Media Bulk Actions**
  - [ ] Add checkbox selection di Media Index
  - [ ] Add bulk actions dropdown (Delete, Move to Folder)
  - [ ] Implement bulk action API call
  - [ ] Show confirmation dialog

- [ ] **Generate Thumbnail**
  - [ ] Add "Generate Thumbnail" button di Media View Modal
  - [ ] Show loading state
  - [ ] Refresh media after generation

- [ ] **Resize Media**
  - [ ] Create resize modal component
  - [ ] Width/Height inputs
  - [ ] Aspect ratio toggle
  - [ ] Preview before resize
  - [ ] Implement resize API call

- [ ] **Media Usage Detail**
  - [ ] Enhance Media View Modal
  - [ ] Show detailed usage list (where media is used)
  - [ ] Link to content/category/etc

**API Endpoints:**
- `POST /admin/cms/media/bulk-action`
- `POST /admin/cms/media/{media}/thumbnail`
- `POST /admin/cms/media/{media}/resize`
- `GET /admin/cms/media/{media}/usage`

**Estimated Time:** 5-6 hours

---

### 5. Category Move Feature
- [ ] Add "Move" button di Category Modal
- [ ] Create move category modal/dropdown
- [ ] Show category tree for parent selection
- [ ] Implement move API call (`POST /admin/cms/categories/{category}/move`)
- [ ] Refresh category tree after move

**API Endpoints:**
- `POST /admin/cms/categories/{category}/move`

**Estimated Time:** 1-2 hours

---

## 🔶 Medium Priority Tasks

### 6. Content Templates Management
- [ ] Create `/resources/js/views/admin/content-templates/Index.vue`
  - [ ] Templates list
  - [ ] Search & filter
  - [ ] Create/Edit/Delete actions
- [ ] Create `/resources/js/views/admin/content-templates/Create.vue`
- [ ] Create `/resources/js/views/admin/content-templates/Edit.vue`
  - [ ] Template editor (similar to Content Editor)
  - [ ] Save as template option
- [ ] Add "Create from Template" button di Contents Index
- [ ] Add route `/admin/content-templates` di router
- [ ] Add navigation link di AdminLayout

**API Endpoints:**
- `GET /admin/cms/content-templates`
- `POST /admin/cms/content-templates`
- `GET /admin/cms/content-templates/{contentTemplate}`
- `PUT /admin/cms/content-templates/{contentTemplate}`
- `DELETE /admin/cms/content-templates/{contentTemplate}`
- `POST /admin/cms/content-templates/{contentTemplate}/create-content`

**Estimated Time:** 4-5 hours

---

### 7. SEO Tools
- [ ] Create `/resources/js/views/admin/seo/Index.vue`
  - [ ] Tabs: Sitemap, Robots.txt, Analysis
- [ ] **Sitemap Tab**
  - [ ] Show sitemap generation button
  - [ ] Display sitemap URL
  - [ ] Generate sitemap action
- [ ] **Robots.txt Tab**
  - [ ] Show current robots.txt content
  - [ ] Edit robots.txt
  - [ ] Save button
- [ ] **SEO Analysis Tab**
  - [ ] Content selector
  - [ ] Run analysis button
  - [ ] Display analysis results
- [ ] **Schema Generation**
  - [ ] Add "Generate Schema" button di Content Edit page
  - [ ] Show schema JSON
  - [ ] Copy to clipboard
- [ ] Add route `/admin/seo` di router
- [ ] Add navigation link di AdminLayout

**API Endpoints:**
- `GET /admin/cms/seo/sitemap`
- `GET /admin/cms/seo/robots-txt`
- `PUT /admin/cms/seo/robots-txt`
- `GET /admin/cms/contents/{content}/seo-analysis`
- `GET /admin/cms/contents/{content}/schema`

**Estimated Time:** 5-6 hours

---

### 8. Cache Management
- [ ] Create `/resources/js/views/admin/cache/Index.vue`
  - [ ] Cache status display
  - [ ] Clear All Cache button
  - [ ] Clear Content Cache button
  - [ ] Cache Warm-up button
  - [ ] Show cache statistics
- [ ] Add route `/admin/cache` di router
- [ ] Add navigation link di AdminLayout (under Settings)

**API Endpoints:**
- `POST /admin/cms/cache/clear`
- `POST /admin/cms/cache/clear-content`
- `POST /admin/cms/cache/warm-up`

**Estimated Time:** 2-3 hours

---

### 9. Redirects Management
- [ ] Create `/resources/js/views/admin/redirects/Index.vue`
  - [ ] Redirects list dengan search & filter
  - [ ] Create/Edit/Delete actions
  - [ ] Statistics display
- [ ] Create `/resources/js/components/redirects/RedirectModal.vue`
  - [ ] Create/Edit redirect form
  - [ ] From URL, To URL, Status Code fields
- [ ] Add route `/admin/redirects` di router
- [ ] Add navigation link di AdminLayout

**API Endpoints:**
- `GET /admin/cms/redirects`
- `POST /admin/cms/redirects`
- `GET /admin/cms/redirects/{redirect}`
- `PUT /admin/cms/redirects/{redirect}`
- `DELETE /admin/cms/redirects/{redirect}`
- `GET /admin/cms/redirects/statistics`

**Estimated Time:** 3-4 hours

---

## 🔷 Low Priority Tasks (Extended Features)

### 10. Backups Management
- [ ] Create `/resources/js/views/admin/backups/Index.vue`
  - [ ] Backups list
  - [ ] Create backup button
  - [ ] Restore/Download/Delete actions
  - [ ] Statistics display
- [ ] Add route `/admin/backups` di router
- [ ] Add navigation link di AdminLayout (under Settings)

**Estimated Time:** 4-5 hours

---

### 11. Security Management
- [ ] Create `/resources/js/views/admin/security/Index.vue`
  - [ ] Security logs list
  - [ ] Statistics display
  - [ ] Block/Unblock IP form
  - [ ] Check IP status
- [ ] Add route `/admin/security` di router
- [ ] Add navigation link di AdminLayout (under Settings)

**Estimated Time:** 4-5 hours

---

### 12. Themes Management
- [ ] Create `/resources/js/views/admin/themes/Index.vue`
  - [ ] Themes list dengan preview
  - [ ] Activate theme button
  - [ ] Theme settings
  - [ ] Custom CSS editor
- [ ] Add route `/admin/themes` di router
- [ ] Add navigation link di AdminLayout

**Estimated Time:** 5-6 hours

---

### 13. Menus Management
- [ ] Create `/resources/js/views/admin/menus/Index.vue`
  - [ ] Menus list
  - [ ] Create/Edit/Delete actions
- [ ] Create `/resources/js/views/admin/menus/Edit.vue`
  - [ ] Menu items tree/drag-drop
  - [ ] Add/Edit/Delete menu items
  - [ ] Reorder items
- [ ] Add route `/admin/menus` di router
- [ ] Add navigation link di AdminLayout

**Estimated Time:** 6-8 hours

---

### 14. Widgets Management
- [ ] Create `/resources/js/views/admin/widgets/Index.vue`
  - [ ] Widgets list
  - [ ] Create/Edit/Delete actions
  - [ ] Get widgets by location
  - [ ] Reorder widgets
- [ ] Add route `/admin/widgets` di router
- [ ] Add navigation link di AdminLayout

**Estimated Time:** 5-6 hours

---

### 15. Plugins Management
- [ ] Create `/resources/js/views/admin/plugins/Index.vue`
  - [ ] Plugins list
  - [ ] Activate/Deactivate buttons
  - [ ] Plugin settings
  - [ ] Active plugins display
- [ ] Add route `/admin/plugins` di router
- [ ] Add navigation link di AdminLayout

**Estimated Time:** 5-6 hours

---

### 16. Webhooks Management
- [ ] Create `/resources/js/views/admin/webhooks/Index.vue`
  - [ ] Webhooks list
  - [ ] Create/Edit/Delete actions
  - [ ] Test webhook button
  - [ ] Statistics display
- [ ] Add route `/admin/webhooks` di router
- [ ] Add navigation link di AdminLayout

**Estimated Time:** 4-5 hours

---

### 17. Custom Fields / Field Groups
- [ ] Create `/resources/js/views/admin/custom-fields/Index.vue`
  - [ ] Field groups list
  - [ ] Custom fields list
  - [ ] Create/Edit/Delete actions
- [ ] Create field group modal
- [ ] Create custom field modal dengan field types
- [ ] Add route `/admin/custom-fields` di router
- [ ] Add navigation link di AdminLayout

**Estimated Time:** 6-8 hours

---

### 18. Activity Logs
- [ ] Create `/resources/js/views/admin/activity-logs/Index.vue`
  - [ ] Activity logs list dengan filters
  - [ ] Recent activities widget
  - [ ] Activity statistics
  - [ ] User activity filter
- [ ] Add route `/admin/activity-logs` di router
- [ ] Add navigation link di AdminLayout

**Estimated Time:** 4-5 hours

---

### 19. Notifications
- [ ] Create `/resources/js/views/admin/notifications/Index.vue`
  - [ ] Notifications list
  - [ ] Unread count badge
  - [ ] Mark as read / Read all actions
  - [ ] Delete notification
- [ ] Add notification bell di AdminLayout navbar
- [ ] Add real-time notification updates
- [ ] Add route `/admin/notifications` di router

**Estimated Time:** 5-6 hours

---

### 20. Scheduled Tasks
- [ ] Create `/resources/js/views/admin/scheduled-tasks/Index.vue`
  - [ ] Scheduled tasks list
  - [ ] Create/Edit/Delete actions
  - [ ] Run task manually button
- [ ] Add route `/admin/scheduled-tasks` di router
- [ ] Add navigation link di AdminLayout (under Settings)

**Estimated Time:** 4-5 hours

---

### 21. File Manager
- [ ] Create `/resources/js/views/admin/file-manager/Index.vue`
  - [ ] File browser dengan folder navigation
  - [ ] Upload files
  - [ ] Create folder
  - [ ] Delete files/folders
  - [ ] File preview
- [ ] Add route `/admin/file-manager` di router
- [ ] Add navigation link di AdminLayout

**Estimated Time:** 6-8 hours

---

### 22. Log Viewer
- [ ] Create `/resources/js/views/admin/logs/Index.vue`
  - [ ] Log files list
  - [ ] Log viewer dengan syntax highlighting
  - [ ] Clear logs button
  - [ ] Download logs button
- [ ] Add route `/admin/logs` di router
- [ ] Add navigation link di AdminLayout (under Settings)

**Estimated Time:** 4-5 hours

---

### 23. System Information
- [ ] Create `/resources/js/views/admin/system/Index.vue`
  - [ ] System info display
  - [ ] System health status
  - [ ] System statistics
  - [ ] Cache status
  - [ ] Clear cache button
- [ ] Add route `/admin/system` di router
- [ ] Add navigation link di AdminLayout (under Settings)

**Estimated Time:** 3-4 hours

---

### 24. Multi-language Support
- [ ] Create `/resources/js/views/admin/languages/Index.vue`
  - [ ] Languages list
  - [ ] Create/Edit/Delete actions
  - [ ] Set default language
- [ ] Create `/resources/js/views/admin/translations/Index.vue`
  - [ ] Translations list
  - [ ] Translation editor
  - [ ] Filter by type (content, category, etc)
- [ ] Add route `/admin/languages` dan `/admin/translations` di router
- [ ] Add navigation link di AdminLayout

**Estimated Time:** 8-10 hours

---

### 25. Admin Search
- [ ] Create global search component di AdminLayout navbar
- [ ] Create `/resources/js/views/admin/search/Index.vue`
  - [ ] Search results display
  - [ ] Popular queries
  - [ ] No results queries
  - [ ] Search statistics
  - [ ] Reindex button
- [ ] Add route `/admin/search` di router

**Estimated Time:** 5-6 hours

---

## 📊 Summary

### Total Tasks: 47 features
- **High Priority:** 5 tasks (9 features) - ~20-25 hours
- **Medium Priority:** 4 tasks (9 features) - ~15-20 hours
- **Low Priority:** 16 tasks (29 features) - ~90-110 hours

### Estimated Total Time: ~125-155 hours

### Recommended Implementation Order:
1. **Phase 1 (High Priority):** Tasks 1-5 (20-25 hours)
2. **Phase 2 (Medium Priority):** Tasks 6-9 (15-20 hours)
3. **Phase 3 (Low Priority):** Tasks 10-25 (90-110 hours) - Implement as needed

---

## 📝 Notes

- Semua API endpoints sudah tersedia di backend
- Gunakan pola yang sama dengan fitur yang sudah ada (modals untuk simple CRUD, separate pages untuk complex forms)
- Pastikan konsistensi dengan folder structure dan naming conventions
- Test setiap fitur setelah implementasi
- Update dokumentasi setelah setiap fitur selesai

---

**Last Updated:** December 2024

