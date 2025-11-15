# JA-CMS API Documentation

## Base URL
```
/api/v1
```

## Authentication

JA-CMS API uses Laravel Sanctum for authentication. Most endpoints require authentication via Bearer token.

### Getting an Access Token

**Login Endpoint:**
```
POST /api/v1/login
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "roles": [],
      "permissions": []
    },
    "token": "1|xxxxxxxxxxxx"
  }
}
```

**Error Responses:**
- `422` - Validation error (invalid credentials)
- `403` - Email not verified

**Using the Token:**
Include the token in the Authorization header:
```
Authorization: Bearer 1|xxxxxxxxxxxx
```

---

## API Endpoints

### Authentication Endpoints

#### Login
- **POST** `/api/v1/login`
- **Description:** Authenticate user and get access token
- **Rate Limit:** 5 attempts per minute
- **Request:** `{ email, password }`
- **Response:** `{ success, message, data: { user, token } }`

#### Register
- **POST** `/api/v1/register`
- **Description:** Register new user account
- **Rate Limit:** 3 attempts per minute
- **Request:** `{ name, email, password, password_confirmation }`
- **Response:** `{ success, message, data: { user, token } }`

#### Logout
- **POST** `/api/v1/logout`
- **Description:** Logout current user
- **Auth Required:** Yes
- **Response:** `{ success, message }`

#### Get Current User
- **GET** `/api/v1/user`
- **Description:** Get authenticated user profile
- **Auth Required:** Yes
- **Response:** `{ success, message, data: { user } }`

#### Forgot Password
- **POST** `/api/v1/forgot-password`
- **Description:** Request password reset link
- **Rate Limit:** 3 attempts per minute
- **Request:** `{ email }`
- **Response:** `{ success, message }`

#### Reset Password
- **POST** `/api/v1/reset-password`
- **Description:** Reset password with token
- **Rate Limit:** 3 attempts per minute
- **Request:** `{ token, email, password, password_confirmation }`
- **Response:** `{ success, message }`

---

### Content Management Endpoints

#### List Contents (Admin)
- **GET** `/api/v1/admin/cms/contents`
- **Description:** Get paginated list of all contents
- **Auth Required:** Yes
- **Permissions:** `manage content`
- **Query Parameters:**
  - `status` - Filter by status (draft, published, archived)
  - `page` - Page number
- **Response:** `{ success, message, data: { data: [], pagination: {} } }`

#### Get Content Details (Admin)
- **GET** `/api/v1/admin/cms/contents/{id}`
- **Description:** Get single content details
- **Auth Required:** Yes
- **Permissions:** `manage content`
- **Response:** `{ success, message, data: { content } }`

#### Create Content
- **POST** `/api/v1/admin/cms/contents`
- **Description:** Create new content
- **Auth Required:** Yes
- **Permissions:** `create content`
- **Request:**
```json
{
  "title": "Content Title",
  "slug": "content-slug",
  "body": "Content body",
  "excerpt": "Content excerpt",
  "status": "draft",
  "type": "post",
  "category_id": 1,
  "tags": [1, 2],
  "published_at": "2024-01-01 00:00:00"
}
```
- **Response:** `{ success, message, data: { content } }`

#### Update Content
- **PUT** `/api/v1/admin/cms/contents/{id}`
- **Description:** Update existing content
- **Auth Required:** Yes
- **Permissions:** `edit content`
- **Request:** Same as create
- **Response:** `{ success, message, data: { content } }`

#### Delete Content
- **DELETE** `/api/v1/admin/cms/contents/{id}`
- **Description:** Delete content (soft delete)
- **Auth Required:** Yes
- **Permissions:** `delete content`
- **Response:** `{ success, message }`

#### Duplicate Content
- **POST** `/api/v1/admin/cms/contents/{id}/duplicate`
- **Description:** Duplicate existing content
- **Auth Required:** Yes
- **Permissions:** `create content`
- **Response:** `{ success, message, data: { content } }`

#### Bulk Actions
- **POST** `/api/v1/admin/cms/contents/bulk-action`
- **Description:** Perform bulk actions on contents
- **Auth Required:** Yes
- **Permissions:** `edit content`
- **Request:**
```json
{
  "action": "delete",
  "ids": [1, 2, 3]
}
```
- **Response:** `{ success, message }`

#### Lock Content
- **POST** `/api/v1/admin/cms/contents/{id}/lock`
- **Description:** Lock content for editing
- **Auth Required:** Yes
- **Response:** `{ success, message }`

#### Unlock Content
- **POST** `/api/v1/admin/cms/contents/{id}/unlock`
- **Description:** Unlock content
- **Auth Required:** Yes
- **Response:** `{ success, message }`

#### Preview Content
- **GET** `/api/v1/admin/cms/contents/{id}/preview`
- **Description:** Preview content (including drafts)
- **Auth Required:** Yes
- **Permissions:** `edit content`
- **Response:** `{ success, message, data: { content } }`

---

### Content Revisions

#### List Revisions
- **GET** `/api/v1/admin/cms/contents/{content}/revisions`
- **Description:** Get all revisions for a content
- **Auth Required:** Yes
- **Response:** `{ success, message, data: { data: [], pagination: {} } }`

#### Get Revision
- **GET** `/api/v1/admin/cms/contents/{content}/revisions/{revision}`
- **Description:** Get single revision details
- **Auth Required:** Yes
- **Response:** `{ success, message, data: { revision } }`

#### Create Revision
- **POST** `/api/v1/admin/cms/contents/{content}/revisions`
- **Description:** Create new revision
- **Auth Required:** Yes
- **Request:** `{ note: "Optional note" }`
- **Response:** `{ success, message, data: { revision } }`

#### Restore Revision
- **POST** `/api/v1/admin/cms/contents/{content}/revisions/{revision}/restore`
- **Description:** Restore content from revision
- **Auth Required:** Yes
- **Permissions:** `edit content`
- **Response:** `{ success, message, data: { content } }`

#### Delete Revision
- **DELETE** `/api/v1/admin/cms/contents/{content}/revisions/{revision}`
- **Description:** Delete revision
- **Auth Required:** Yes
- **Response:** `{ success, message }`

---

### Media Management Endpoints

#### Upload Media
- **POST** `/api/v1/admin/cms/media/upload`
- **Description:** Upload media file
- **Auth Required:** Yes
- **Permissions:** `manage media`
- **Request:** Multipart form data
  - `file` - File to upload (max 10MB)
  - `folder_id` - Optional folder ID
  - `alt` - Optional alt text
  - `description` - Optional description
  - `optimize` - Boolean, optimize image (default: true)
- **Response:** `{ success, message, data: { media, url } }`

#### List Media
- **GET** `/api/v1/admin/cms/media`
- **Description:** Get paginated list of media
- **Auth Required:** Yes
- **Query Parameters:**
  - `folder_id` - Filter by folder
  - `mime_type` - Filter by MIME type
  - `search` - Search by name/filename
  - `usage` - Filter by usage (used/unused)
  - `view` - View type (grid/list)
- **Response:** `{ success, message, data: { data: [], pagination: {} } }`

#### Get Media Details
- **GET** `/api/v1/admin/cms/media/{id}`
- **Description:** Get single media details
- **Auth Required:** Yes
- **Response:** `{ success, message, data: { media } }`

#### Update Media
- **PUT** `/api/v1/admin/cms/media/{id}`
- **Description:** Update media metadata
- **Auth Required:** Yes
- **Permissions:** `manage media`
- **Request:** `{ name, alt, description }`
- **Response:** `{ success, message, data: { media } }`

#### Delete Media
- **DELETE** `/api/v1/admin/cms/media/{id}`
- **Description:** Delete media (soft delete)
- **Auth Required:** Yes
- **Permissions:** `manage media`
- **Response:** `{ success, message }`

#### Bulk Actions
- **POST** `/api/v1/admin/cms/media/bulk-action`
- **Description:** Perform bulk actions on media
- **Auth Required:** Yes
- **Permissions:** `manage media`
- **Request:**
```json
{
  "action": "delete",
  "ids": [1, 2, 3]
}
```
or
```json
{
  "action": "move",
  "ids": [1, 2, 3],
  "folder_id": 5
}
```
- **Response:** `{ success, message }`

#### Generate Thumbnail
- **POST** `/api/v1/admin/cms/media/{id}/thumbnail`
- **Description:** Generate thumbnail for image
- **Auth Required:** Yes
- **Permissions:** `manage media`
- **Response:** `{ success, message, data: { thumbnail_url } }`

#### Resize Image
- **POST** `/api/v1/admin/cms/media/{id}/resize`
- **Description:** Resize image
- **Auth Required:** Yes
- **Permissions:** `manage media`
- **Request:**
```json
{
  "width": 800,
  "height": 600
}
```
- **Response:** `{ success, message, data: { url } }`

#### Get Media Usage
- **GET** `/api/v1/admin/cms/media/{id}/usage`
- **Description:** Get media usage information
- **Auth Required:** Yes
- **Permissions:** `manage media`
- **Response:** `{ success, message, data: { usage_count, usages: [] } }`

---

### Category Management Endpoints

#### List Categories
- **GET** `/api/v1/admin/cms/categories`
- **Description:** Get list of categories
- **Auth Required:** Yes
- **Permissions:** `manage categories`
- **Response:** `{ success, message, data: [] }`

#### Get Category
- **GET** `/api/v1/admin/cms/categories/{id}`
- **Description:** Get single category
- **Auth Required:** Yes
- **Permissions:** `manage categories`
- **Response:** `{ success, message, data: { category } }`

#### Create Category
- **POST** `/api/v1/admin/cms/categories`
- **Description:** Create new category
- **Auth Required:** Yes
- **Permissions:** `manage categories`
- **Request:** `{ name, slug, description, parent_id, is_active }`
- **Response:** `{ success, message, data: { category } }`

#### Update Category
- **PUT** `/api/v1/admin/cms/categories/{id}`
- **Description:** Update category
- **Auth Required:** Yes
- **Permissions:** `manage categories`
- **Request:** Same as create
- **Response:** `{ success, message, data: { category } }`

#### Delete Category
- **DELETE** `/api/v1/admin/cms/categories/{id}`
- **Description:** Delete category
- **Auth Required:** Yes
- **Permissions:** `manage categories`
- **Response:** `{ success, message }`

#### Move Category
- **POST** `/api/v1/admin/cms/categories/{id}/move`
- **Description:** Move category to different parent
- **Auth Required:** Yes
- **Permissions:** `manage categories`
- **Request:** `{ parent_id }`
- **Response:** `{ success, message, data: { category } }`

---

### Tag Management Endpoints

#### List Tags
- **GET** `/api/v1/admin/cms/tags`
- **Description:** Get list of tags
- **Auth Required:** Yes
- **Permissions:** `manage tags`
- **Response:** `{ success, message, data: [] }`

#### Get Tag Statistics
- **GET** `/api/v1/admin/cms/tags/statistics`
- **Description:** Get tag statistics
- **Auth Required:** Yes
- **Permissions:** `manage tags`
- **Response:** `{ success, message, data: { total, used, unused } }`

#### Create Tag
- **POST** `/api/v1/admin/cms/tags`
- **Description:** Create new tag
- **Auth Required:** Yes
- **Permissions:** `manage tags`
- **Request:** `{ name, slug, description }`
- **Response:** `{ success, message, data: { tag } }`

#### Update Tag
- **PUT** `/api/v1/admin/cms/tags/{id}`
- **Description:** Update tag
- **Auth Required:** Yes
- **Permissions:** `manage tags`
- **Request:** Same as create
- **Response:** `{ success, message, data: { tag } }`

#### Delete Tag
- **DELETE** `/api/v1/admin/cms/tags/{id}`
- **Description:** Delete tag
- **Auth Required:** Yes
- **Permissions:** `manage tags`
- **Response:** `{ success, message }`

---

### User Management Endpoints

#### List Users
- **GET** `/api/v1/admin/cms/users`
- **Description:** Get paginated list of users
- **Auth Required:** Yes
- **Permissions:** `manage users`
- **Response:** `{ success, message, data: { data: [], pagination: {} } }`

#### Get User
- **GET** `/api/v1/admin/cms/users/{id}`
- **Description:** Get single user details
- **Auth Required:** Yes
- **Permissions:** `manage users`
- **Response:** `{ success, message, data: { user } }`

#### Create User
- **POST** `/api/v1/admin/cms/users`
- **Description:** Create new user
- **Auth Required:** Yes
- **Permissions:** `manage users`
- **Request:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password",
  "password_confirmation": "password",
  "roles": [1, 2]
}
```
- **Response:** `{ success, message, data: { user } }`

#### Update User
- **PUT** `/api/v1/admin/cms/users/{id}`
- **Description:** Update user
- **Auth Required:** Yes
- **Permissions:** `manage users`
- **Request:** `{ name, email, roles }`
- **Response:** `{ success, message, data: { user } }`

#### Delete User
- **DELETE** `/api/v1/admin/cms/users/{id}`
- **Description:** Delete user
- **Auth Required:** Yes
- **Permissions:** `manage users`
- **Response:** `{ success, message }`

#### Get Roles
- **GET** `/api/v1/admin/cms/roles`
- **Description:** Get list of all roles
- **Auth Required:** Yes
- **Permissions:** `manage users`
- **Response:** `{ success, message, data: [] }`

---

### Settings Endpoints

#### Get Settings
- **GET** `/api/v1/admin/cms/settings`
- **Description:** Get all settings
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Query Parameters:**
  - `group` - Filter by group (General, Email, SEO, etc.)
- **Response:** `{ success, message, data: [] }`

#### Get Setting Group
- **GET** `/api/v1/admin/cms/settings/{group}`
- **Description:** Get settings by group
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: [] }`

#### Get Single Setting
- **GET** `/api/v1/admin/cms/settings/{group}/{key}`
- **Description:** Get single setting value
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: { setting } }`

#### Update Setting
- **PUT** `/api/v1/admin/cms/settings/{group}/{key}`
- **Description:** Update single setting
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** `{ value }`
- **Response:** `{ success, message, data: { setting } }`

#### Bulk Update Settings
- **POST** `/api/v1/admin/cms/settings/bulk-update`
- **Description:** Update multiple settings at once
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:**
```json
{
  "settings": [
    { "key": "site_name", "value": "My Site", "type": "string", "group": "General" },
    { "key": "site_email", "value": "admin@example.com", "type": "string", "group": "General" }
  ]
}
```
- **Response:** `{ success, message }`

---

### Comment Management Endpoints

#### List Comments (Admin)
- **GET** `/api/v1/admin/cms/comments`
- **Description:** Get paginated list of comments
- **Auth Required:** Yes
- **Permissions:** `manage comments`
- **Query Parameters:**
  - `status` - Filter by status (pending, approved, rejected, spam)
  - `content_id` - Filter by content
- **Response:** `{ success, message, data: { data: [], pagination: {} } }`

#### Approve Comment
- **PUT** `/api/v1/admin/cms/comments/{id}/approve`
- **Description:** Approve comment
- **Auth Required:** Yes
- **Permissions:** `manage comments`
- **Response:** `{ success, message, data: { comment } }`

#### Reject Comment
- **PUT** `/api/v1/admin/cms/comments/{id}/reject`
- **Description:** Reject comment
- **Auth Required:** Yes
- **Permissions:** `manage comments`
- **Response:** `{ success, message, data: { comment } }`

#### Delete Comment
- **DELETE** `/api/v1/admin/cms/comments/{id}`
- **Description:** Delete comment
- **Auth Required:** Yes
- **Permissions:** `manage comments`
- **Response:** `{ success, message }`

---

### SEO Endpoints

#### Generate Sitemap
- **POST** `/api/v1/admin/cms/seo/sitemap`
- **Description:** Generate sitemap.xml
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: { url } }`

#### Get Robots.txt
- **GET** `/api/v1/admin/cms/seo/robots-txt`
- **Description:** Get robots.txt content
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: { content } }`

#### Update Robots.txt
- **PUT** `/api/v1/admin/cms/seo/robots-txt`
- **Description:** Update robots.txt
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** `{ content }`
- **Response:** `{ success, message }`

#### Analyze Content SEO
- **GET** `/api/v1/admin/cms/contents/{content}/seo-analysis`
- **Description:** Analyze content for SEO
- **Auth Required:** Yes
- **Permissions:** `edit content`
- **Response:** `{ success, message, data: { analysis } }`

#### Generate Schema
- **GET** `/api/v1/admin/cms/contents/{content}/schema`
- **Description:** Generate JSON-LD schema for content
- **Auth Required:** Yes
- **Response:** `{ success, message, data: { schema } }`

---

### Redirect Management Endpoints

#### List Redirects
- **GET** `/api/v1/admin/cms/redirects`
- **Description:** Get list of redirects
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: [] }`

#### Get Redirect Statistics
- **GET** `/api/v1/admin/cms/redirects/statistics`
- **Description:** Get redirect statistics
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: { total, active, hits } }`

#### Create Redirect
- **POST** `/api/v1/admin/cms/redirects`
- **Description:** Create new redirect
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** `{ from_url, to_url, type: 301|302, is_active }`
- **Response:** `{ success, message, data: { redirect } }`

#### Update Redirect
- **PUT** `/api/v1/admin/cms/redirects/{id}`
- **Description:** Update redirect
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** Same as create
- **Response:** `{ success, message, data: { redirect } }`

#### Delete Redirect
- **DELETE** `/api/v1/admin/cms/redirects/{id}`
- **Description:** Delete redirect
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message }`

---

### Backup Management Endpoints

#### List Backups
- **GET** `/api/v1/admin/cms/backups`
- **Description:** Get list of backups
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: [] }`

#### Get Backup Statistics
- **GET** `/api/v1/admin/cms/backups/statistics`
- **Description:** Get backup statistics
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: { total, total_size, latest } }`

#### Create Backup
- **POST** `/api/v1/admin/cms/backups`
- **Description:** Create new backup
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** `{ name, description }`
- **Response:** `{ success, message, data: { backup } }`

#### Restore Backup
- **POST** `/api/v1/admin/cms/backups/{id}/restore`
- **Description:** Restore from backup
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message }`

#### Download Backup
- **GET** `/api/v1/admin/cms/backups/{id}/download`
- **Description:** Download backup file
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** File download

#### Delete Backup
- **DELETE** `/api/v1/admin/cms/backups/{id}`
- **Description:** Delete backup
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message }`

---

### Security Endpoints

#### Get Security Logs
- **GET** `/api/v1/admin/cms/security/logs`
- **Description:** Get security logs
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: [] }`

#### Get Security Statistics
- **GET** `/api/v1/admin/cms/security/stats`
- **Description:** Get security statistics
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: { blocked_ips, failed_attempts } }`

#### Block IP
- **POST** `/api/v1/admin/cms/security/block-ip`
- **Description:** Block IP address
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** `{ ip_address }`
- **Response:** `{ success, message }`

#### Unblock IP
- **POST** `/api/v1/admin/cms/security/unblock-ip`
- **Description:** Unblock IP address
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** `{ ip_address }`
- **Response:** `{ success, message }`

#### Check IP Status
- **GET** `/api/v1/admin/cms/security/check-ip`
- **Description:** Check if IP is blocked
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Query Parameters:** `ip_address`
- **Response:** `{ success, message, data: { is_blocked } }`

#### Clear Failed Attempts
- **POST** `/api/v1/admin/cms/security/clear-failed-attempts`
- **Description:** Clear failed login attempts
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message }`

---

### System Information Endpoints

#### Get System Info
- **GET** `/api/v1/admin/cms/system/info`
- **Description:** Get system information
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: { php_version, laravel_version, database, etc } }`

#### Get System Health
- **GET** `/api/v1/admin/cms/system/health`
- **Description:** Get system health status
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: { status, checks: [] } }`

#### Get System Statistics
- **GET** `/api/v1/admin/cms/system/statistics`
- **Description:** Get system statistics
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: { users, contents, media, etc } }`

#### Get Cache Status
- **GET** `/api/v1/admin/cms/system/cache`
- **Description:** Get cache status
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: { driver, status } }`

#### Clear Cache
- **POST** `/api/v1/admin/cms/system/clear-cache`
- **Description:** Clear application cache
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message }`

---

### Activity Logs Endpoints

#### List Activity Logs
- **GET** `/api/v1/admin/cms/activity-logs`
- **Description:** Get paginated activity logs
- **Auth Required:** Yes
- **Permissions:** `manage users`
- **Query Parameters:**
  - `user_id` - Filter by user
  - `action` - Filter by action
  - `model_type` - Filter by model type
- **Response:** `{ success, message, data: { data: [], pagination: {} } }`

#### Get Activity Log
- **GET** `/api/v1/admin/cms/activity-logs/{id}`
- **Description:** Get single activity log
- **Auth Required:** Yes
- **Permissions:** `manage users`
- **Response:** `{ success, message, data: { log } }`

#### Get Recent Activity
- **GET** `/api/v1/admin/cms/activity-logs/recent`
- **Description:** Get recent activity logs
- **Auth Required:** Yes
- **Permissions:** `manage users`
- **Response:** `{ success, message, data: [] }`

#### Get Activity Statistics
- **GET** `/api/v1/admin/cms/activity-logs/statistics`
- **Description:** Get activity statistics
- **Auth Required:** Yes
- **Permissions:** `manage users`
- **Response:** `{ success, message, data: { total, by_action, by_user } }`

#### Get User Activity
- **GET** `/api/v1/admin/cms/activity-logs/user/{userId}`
- **Description:** Get activity logs for specific user
- **Auth Required:** Yes
- **Permissions:** `manage users`
- **Response:** `{ success, message, data: [] }`

---

### Notification Endpoints

#### List Notifications
- **GET** `/api/v1/admin/cms/notifications`
- **Description:** Get user notifications
- **Auth Required:** Yes
- **Query Parameters:**
  - `is_read` - Filter by read status
  - `type` - Filter by type
  - `limit` - Limit results
- **Response:** `{ success, message, data: { data: [], total } }` or paginated

#### Get Unread Count
- **GET** `/api/v1/admin/cms/notifications/unread-count`
- **Description:** Get unread notification count
- **Auth Required:** Yes
- **Response:** `{ success, message, data: { count } }`

#### Mark as Read
- **POST** `/api/v1/admin/cms/notifications/{id}/read`
- **Description:** Mark notification as read
- **Auth Required:** Yes
- **Response:** `{ success, message, data: { notification } }`

#### Mark All as Read
- **POST** `/api/v1/admin/cms/notifications/read-all`
- **Description:** Mark all notifications as read
- **Auth Required:** Yes
- **Response:** `{ success, message }`

#### Delete Notification
- **DELETE** `/api/v1/admin/cms/notifications/{id}`
- **Description:** Delete notification
- **Auth Required:** Yes
- **Response:** `{ success, message }`

---

### Form Management Endpoints

#### List Forms
- **GET** `/api/v1/admin/cms/forms`
- **Description:** Get list of forms
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Response:** `{ success, message, data: [] }`

#### Get Form
- **GET** `/api/v1/admin/cms/forms/{id}`
- **Description:** Get single form with fields
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Response:** `{ success, message, data: { form } }`

#### Create Form
- **POST** `/api/v1/admin/cms/forms`
- **Description:** Create new form
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Request:**
```json
{
  "name": "Contact Form",
  "slug": "contact-form",
  "description": "Contact form description",
  "fields": [
    { "name": "name", "type": "text", "label": "Name", "required": true }
  ]
}
```
- **Response:** `{ success, message, data: { form } }`

#### Update Form
- **PUT** `/api/v1/admin/cms/forms/{id}`
- **Description:** Update form
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Request:** Same as create
- **Response:** `{ success, message, data: { form } }`

#### Delete Form
- **DELETE** `/api/v1/admin/cms/forms/{id}`
- **Description:** Delete form
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Response:** `{ success, message }`

#### Add Form Field
- **POST** `/api/v1/admin/cms/forms/{form}/fields`
- **Description:** Add field to form
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Request:** `{ name, type, label, required, options }`
- **Response:** `{ success, message, data: { field } }`

#### Update Form Field
- **PUT** `/api/v1/admin/cms/forms/{form}/fields/{field}`
- **Description:** Update form field
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Request:** Same as add field
- **Response:** `{ success, message, data: { field } }`

#### Delete Form Field
- **DELETE** `/api/v1/admin/cms/forms/{form}/fields/{field}`
- **Description:** Delete form field
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Response:** `{ success, message }`

#### Reorder Form Fields
- **POST** `/api/v1/admin/cms/forms/{form}/reorder-fields`
- **Description:** Reorder form fields
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Request:** `{ field_ids: [1, 2, 3] }`
- **Response:** `{ success, message }`

#### Submit Form (Public)
- **POST** `/api/v1/cms/forms/{slug}/submit`
- **Description:** Submit form (public endpoint)
- **Auth Required:** No
- **Request:** Form data based on form fields
- **Response:** `{ success, message }`

---

### Form Submission Endpoints

#### List Submissions
- **GET** `/api/v1/admin/cms/forms/{form}/submissions`
- **Description:** Get form submissions
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Response:** `{ success, message, data: { data: [], pagination: {} } }`

#### Get Submission
- **GET** `/api/v1/admin/cms/submissions/{id}`
- **Description:** Get single submission
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Response:** `{ success, message, data: { submission } }`

#### Mark Submission as Read
- **POST** `/api/v1/admin/cms/submissions/{id}/read`
- **Description:** Mark submission as read
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Response:** `{ success, message }`

#### Archive Submission
- **POST** `/api/v1/admin/cms/submissions/{id}/archive`
- **Description:** Archive submission
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Response:** `{ success, message }`

#### Delete Submission
- **DELETE** `/api/v1/admin/cms/submissions/{id}`
- **Description:** Delete submission
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Response:** `{ success, message }`

#### Export Submissions
- **GET** `/api/v1/admin/cms/forms/{form}/submissions/export`
- **Description:** Export submissions as JSON
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Response:** `{ success, message, data: { form, total, data: [] } }`

#### Get Submission Statistics
- **GET** `/api/v1/admin/cms/forms/{form}/submissions/statistics`
- **Description:** Get submission statistics
- **Auth Required:** Yes
- **Permissions:** `manage forms`
- **Response:** `{ success, message, data: { total, new, read, archived, today, this_week, this_month } }`

---

### Analytics Endpoints

#### Get Overview
- **GET** `/api/v1/admin/cms/analytics/overview`
- **Description:** Get analytics overview
- **Auth Required:** Yes
- **Permissions:** `view analytics`
- **Query Parameters:**
  - `date_from` - Start date (Y-m-d)
  - `date_to` - End date (Y-m-d)
- **Response:** `{ success, message, data: { visitors, page_views, bounce_rate, etc } }`

#### Get Visits
- **GET** `/api/v1/admin/cms/analytics/visits`
- **Description:** Get visits data
- **Auth Required:** Yes
- **Permissions:** `view analytics`
- **Query Parameters:**
  - `date_from`, `date_to`
  - `group_by` - day, week, month
- **Response:** `{ success, message, data: [] }`

#### Get Top Pages
- **GET** `/api/v1/admin/cms/analytics/top-pages`
- **Description:** Get top pages
- **Auth Required:** Yes
- **Permissions:** `view analytics`
- **Query Parameters:**
  - `date_from`, `date_to`
  - `limit` - Number of results
- **Response:** `{ success, message, data: [] }`

#### Get Top Content
- **GET** `/api/v1/admin/cms/analytics/top-content`
- **Description:** Get top content
- **Auth Required:** Yes
- **Permissions:** `view analytics`
- **Query Parameters:**
  - `date_from`, `date_to`
  - `limit`
- **Response:** `{ success, message, data: [] }`

#### Get Devices
- **GET** `/api/v1/admin/cms/analytics/devices`
- **Description:** Get device analytics
- **Auth Required:** Yes
- **Permissions:** `view analytics`
- **Response:** `{ success, message, data: [] }`

#### Get Browsers
- **GET** `/api/v1/admin/cms/analytics/browsers`
- **Description:** Get browser analytics
- **Auth Required:** Yes
- **Permissions:** `view analytics`
- **Response:** `{ success, message, data: [] }`

#### Get Countries
- **GET** `/api/v1/admin/cms/analytics/countries`
- **Description:** Get country analytics
- **Auth Required:** Yes
- **Permissions:** `view analytics`
- **Response:** `{ success, message, data: [] }`

#### Get Referrers
- **GET** `/api/v1/admin/cms/analytics/referrers`
- **Description:** Get referrer analytics
- **Auth Required:** Yes
- **Permissions:** `view analytics`
- **Response:** `{ success, message, data: [] }`

#### Get Events
- **GET** `/api/v1/admin/cms/analytics/events`
- **Description:** Get analytics events
- **Auth Required:** Yes
- **Permissions:** `view analytics`
- **Query Parameters:**
  - `date_from`, `date_to`
  - `event_type`
- **Response:** `{ success, message, data: { data: [], pagination: {} } }`

#### Get Event Statistics
- **GET** `/api/v1/admin/cms/analytics/event-stats`
- **Description:** Get event statistics
- **Auth Required:** Yes
- **Permissions:** `view analytics`
- **Response:** `{ success, message, data: [] }`

#### Get Real-time Analytics
- **GET** `/api/v1/admin/cms/analytics/real-time`
- **Description:** Get real-time analytics
- **Auth Required:** Yes
- **Permissions:** `view analytics`
- **Response:** `{ success, message, data: { active_sessions, visits_last_hour, top_pages_now } }`

---

### Search Endpoints

#### Search
- **GET** `/api/v1/admin/cms/search`
- **Description:** Global search
- **Auth Required:** Yes
- **Query Parameters:**
  - `q` - Search query
  - `type` - Filter by type (content, user, media, etc.)
- **Response:** `{ success, message, data: { results: [] } }`

#### Get Suggestions
- **GET** `/api/v1/admin/cms/search/suggestions`
- **Description:** Get search suggestions
- **Auth Required:** Yes
- **Query Parameters:** `q`
- **Response:** `{ success, message, data: [] }`

#### Get Popular Queries
- **GET** `/api/v1/admin/cms/search/popular`
- **Description:** Get popular search queries
- **Auth Required:** Yes
- **Response:** `{ success, message, data: [] }`

#### Get No Results Queries
- **GET** `/api/v1/admin/cms/search/no-results`
- **Description:** Get queries with no results
- **Auth Required:** Yes
- **Response:** `{ success, message, data: [] }`

#### Get Search Statistics
- **GET** `/api/v1/admin/cms/search/statistics`
- **Description:** Get search statistics
- **Auth Required:** Yes
- **Response:** `{ success, message, data: { total_searches, popular_queries } }`

#### Reindex
- **POST** `/api/v1/admin/cms/search/reindex`
- **Description:** Reindex search data
- **Auth Required:** Yes
- **Response:** `{ success, message }`

---

### Cache Management Endpoints

#### Clear Cache
- **POST** `/api/v1/admin/cms/cache/clear`
- **Description:** Clear application cache
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** `{ type: "all"|"config"|"route"|"view" }`
- **Response:** `{ success, message }`

#### Warm Up Cache
- **POST** `/api/v1/admin/cms/cache/warm-up`
- **Description:** Warm up cache
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message }`

---

### Webhook Management Endpoints

#### List Webhooks
- **GET** `/api/v1/admin/cms/webhooks`
- **Description:** Get list of webhooks
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: [] }`

#### Get Webhook Statistics
- **GET** `/api/v1/admin/cms/webhooks/statistics`
- **Description:** Get webhook statistics
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: { total, active, failed } }`

#### Create Webhook
- **POST** `/api/v1/admin/cms/webhooks`
- **Description:** Create new webhook
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:**
```json
{
  "name": "Webhook Name",
  "url": "https://example.com/webhook",
  "events": ["content.created", "content.updated"],
  "is_active": true
}
```
- **Response:** `{ success, message, data: { webhook } }`

#### Update Webhook
- **PUT** `/api/v1/admin/cms/webhooks/{id}`
- **Description:** Update webhook
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** Same as create
- **Response:** `{ success, message, data: { webhook } }`

#### Delete Webhook
- **DELETE** `/api/v1/admin/cms/webhooks/{id}`
- **Description:** Delete webhook
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message }`

#### Test Webhook
- **POST** `/api/v1/admin/cms/webhooks/{id}/test`
- **Description:** Test webhook
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message }`

---

### Menu Management Endpoints

#### List Menus
- **GET** `/api/v1/admin/cms/menus`
- **Description:** Get list of menus
- **Auth Required:** Yes
- **Permissions:** `manage menus`
- **Response:** `{ success, message, data: [] }`

#### Get Menu
- **GET** `/api/v1/admin/cms/menus/{id}`
- **Description:** Get single menu with items
- **Auth Required:** Yes
- **Permissions:** `manage menus`
- **Response:** `{ success, message, data: { menu } }`

#### Create Menu
- **POST** `/api/v1/admin/cms/menus`
- **Description:** Create new menu
- **Auth Required:** Yes
- **Permissions:** `manage menus`
- **Request:**
```json
{
  "name": "Main Menu",
  "location": "header",
  "items": [
    { "title": "Home", "url": "/", "order": 1 }
  ]
}
```
- **Response:** `{ success, message, data: { menu } }`

#### Update Menu
- **PUT** `/api/v1/admin/cms/menus/{id}`
- **Description:** Update menu
- **Auth Required:** Yes
- **Permissions:** `manage menus`
- **Request:** Same as create
- **Response:** `{ success, message, data: { menu } }`

#### Delete Menu
- **DELETE** `/api/v1/admin/cms/menus/{id}`
- **Description:** Delete menu
- **Auth Required:** Yes
- **Permissions:** `manage menus`
- **Response:** `{ success, message }`

#### Add Menu Item
- **POST** `/api/v1/admin/cms/menus/{menu}/items`
- **Description:** Add item to menu
- **Auth Required:** Yes
- **Permissions:** `manage menus`
- **Request:** `{ title, url, parent_id, order }`
- **Response:** `{ success, message, data: { item } }`

#### Update Menu Item
- **PUT** `/api/v1/admin/cms/menus/{menu}/items/{item}`
- **Description:** Update menu item
- **Auth Required:** Yes
- **Permissions:** `manage menus`
- **Request:** Same as add item
- **Response:** `{ success, message, data: { item } }`

#### Delete Menu Item
- **DELETE** `/api/v1/admin/cms/menus/{menu}/items/{item}`
- **Description:** Delete menu item
- **Auth Required:** Yes
- **Permissions:** `manage menus`
- **Response:** `{ success, message }`

#### Reorder Menu Items
- **POST** `/api/v1/admin/cms/menus/{menu}/reorder`
- **Description:** Reorder menu items
- **Auth Required:** Yes
- **Permissions:** `manage menus`
- **Request:** `{ item_ids: [1, 2, 3] }`
- **Response:** `{ success, message }`

#### Get Menu by Location
- **GET** `/api/v1/admin/cms/menus/location/{location}`
- **Description:** Get menu by location (public endpoint)
- **Auth Required:** No
- **Response:** `{ success, message, data: { menu } }`

---

### Widget Management Endpoints

#### List Widgets
- **GET** `/api/v1/admin/cms/widgets`
- **Description:** Get list of widgets
- **Auth Required:** Yes
- **Permissions:** `manage widgets`
- **Response:** `{ success, message, data: [] }`

#### Get Widget
- **GET** `/api/v1/admin/cms/widgets/{id}`
- **Description:** Get single widget
- **Auth Required:** Yes
- **Permissions:** `manage widgets`
- **Response:** `{ success, message, data: { widget } }`

#### Create Widget
- **POST** `/api/v1/admin/cms/widgets`
- **Description:** Create new widget
- **Auth Required:** Yes
- **Permissions:** `manage widgets`
- **Request:**
```json
{
  "name": "Widget Name",
  "type": "text",
  "location": "sidebar",
  "content": "Widget content",
  "settings": {}
}
```
- **Response:** `{ success, message, data: { widget } }`

#### Update Widget
- **PUT** `/api/v1/admin/cms/widgets/{id}`
- **Description:** Update widget
- **Auth Required:** Yes
- **Permissions:** `manage widgets`
- **Request:** Same as create
- **Response:** `{ success, message, data: { widget } }`

#### Delete Widget
- **DELETE** `/api/v1/admin/cms/widgets/{id}`
- **Description:** Delete widget
- **Auth Required:** Yes
- **Permissions:** `manage widgets`
- **Response:** `{ success, message }`

#### Get Widgets by Location
- **GET** `/api/v1/admin/cms/widgets/location/{location}`
- **Description:** Get widgets by location (public endpoint)
- **Auth Required:** No
- **Response:** `{ success, message, data: [] }`

#### Reorder Widgets
- **POST** `/api/v1/admin/cms/widgets/reorder`
- **Description:** Reorder widgets
- **Auth Required:** Yes
- **Permissions:** `manage widgets`
- **Request:** `{ widget_ids: [1, 2, 3] }`
- **Response:** `{ success, message }`

---

### Theme Management Endpoints

#### List Themes
- **GET** `/api/v1/admin/cms/themes`
- **Description:** Get list of themes
- **Auth Required:** Yes
- **Permissions:** `manage themes`
- **Response:** `{ success, message, data: [] }`

#### Get Theme
- **GET** `/api/v1/admin/cms/themes/{id}`
- **Description:** Get single theme
- **Auth Required:** Yes
- **Permissions:** `manage themes`
- **Response:** `{ success, message, data: { theme } }`

#### Create Theme
- **POST** `/api/v1/admin/cms/themes`
- **Description:** Create new theme
- **Auth Required:** Yes
- **Permissions:** `manage themes`
- **Request:**
```json
{
  "name": "Theme Name",
  "slug": "theme-slug",
  "description": "Theme description",
  "version": "1.0.0"
}
```
- **Response:** `{ success, message, data: { theme } }`

#### Update Theme
- **PUT** `/api/v1/admin/cms/themes/{id}`
- **Description:** Update theme
- **Auth Required:** Yes
- **Permissions:** `manage themes`
- **Request:** Same as create
- **Response:** `{ success, message, data: { theme } }`

#### Delete Theme
- **DELETE** `/api/v1/admin/cms/themes/{id}`
- **Description:** Delete theme
- **Auth Required:** Yes
- **Permissions:** `manage themes`
- **Response:** `{ success, message }`

#### Activate Theme
- **POST** `/api/v1/admin/cms/themes/{id}/activate`
- **Description:** Activate theme
- **Auth Required:** Yes
- **Permissions:** `manage themes`
- **Response:** `{ success, message }`

#### Get Active Theme
- **GET** `/api/v1/admin/cms/themes/active`
- **Description:** Get active theme
- **Auth Required:** Yes
- **Permissions:** `manage themes`
- **Response:** `{ success, message, data: { theme } }`

#### Update Theme Settings
- **PUT** `/api/v1/admin/cms/themes/{id}/settings`
- **Description:** Update theme settings
- **Auth Required:** Yes
- **Permissions:** `manage themes`
- **Request:** `{ settings: {} }`
- **Response:** `{ success, message }`

#### Update Custom CSS
- **PUT** `/api/v1/admin/cms/themes/{id}/custom-css`
- **Description:** Update theme custom CSS
- **Auth Required:** Yes
- **Permissions:** `manage themes`
- **Request:** `{ css: "..." }`
- **Response:** `{ success, message }`

---

### Plugin Management Endpoints

#### List Plugins
- **GET** `/api/v1/admin/cms/plugins`
- **Description:** Get list of plugins
- **Auth Required:** Yes
- **Permissions:** `manage plugins`
- **Response:** `{ success, message, data: [] }`

#### Get Plugin
- **GET** `/api/v1/admin/cms/plugins/{id}`
- **Description:** Get single plugin
- **Auth Required:** Yes
- **Permissions:** `manage plugins`
- **Response:** `{ success, message, data: { plugin } }`

#### Create Plugin
- **POST** `/api/v1/admin/cms/plugins`
- **Description:** Create new plugin
- **Auth Required:** Yes
- **Permissions:** `manage plugins`
- **Request:**
```json
{
  "name": "Plugin Name",
  "slug": "plugin-slug",
  "description": "Plugin description",
  "version": "1.0.0"
}
```
- **Response:** `{ success, message, data: { plugin } }`

#### Update Plugin
- **PUT** `/api/v1/admin/cms/plugins/{id}`
- **Description:** Update plugin
- **Auth Required:** Yes
- **Permissions:** `manage plugins`
- **Request:** Same as create
- **Response:** `{ success, message, data: { plugin } }`

#### Delete Plugin
- **DELETE** `/api/v1/admin/cms/plugins/{id}`
- **Description:** Delete plugin
- **Auth Required:** Yes
- **Permissions:** `manage plugins`
- **Response:** `{ success, message }`

#### Activate Plugin
- **POST** `/api/v1/admin/cms/plugins/{id}/activate`
- **Description:** Activate plugin
- **Auth Required:** Yes
- **Permissions:** `manage plugins`
- **Response:** `{ success, message }`

#### Deactivate Plugin
- **POST** `/api/v1/admin/cms/plugins/{id}/deactivate`
- **Description:** Deactivate plugin
- **Auth Required:** Yes
- **Permissions:** `manage plugins`
- **Response:** `{ success, message }`

#### Update Plugin Settings
- **PUT** `/api/v1/admin/cms/plugins/{id}/settings`
- **Description:** Update plugin settings
- **Auth Required:** Yes
- **Permissions:** `manage plugins`
- **Request:** `{ settings: {} }`
- **Response:** `{ success, message }`

#### Get Active Plugins
- **GET** `/api/v1/admin/cms/plugins/active`
- **Description:** Get active plugins
- **Auth Required:** Yes
- **Permissions:** `manage plugins`
- **Response:** `{ success, message, data: [] }`

---

### Custom Fields Endpoints

#### List Field Groups
- **GET** `/api/v1/admin/cms/field-groups`
- **Description:** Get list of field groups
- **Auth Required:** Yes
- **Permissions:** `manage content`
- **Response:** `{ success, message, data: [] }`

#### Create Field Group
- **POST** `/api/v1/admin/cms/field-groups`
- **Description:** Create field group
- **Auth Required:** Yes
- **Permissions:** `manage content`
- **Request:**
```json
{
  "name": "Group Name",
  "slug": "group-slug",
  "applies_to": "content",
  "conditions": {}
}
```
- **Response:** `{ success, message, data: { group } }`

#### List Custom Fields
- **GET** `/api/v1/admin/cms/custom-fields`
- **Description:** Get list of custom fields
- **Auth Required:** Yes
- **Permissions:** `manage content`
- **Response:** `{ success, message, data: [] }`

#### Get Field Types
- **GET** `/api/v1/admin/cms/custom-fields/types`
- **Description:** Get available field types
- **Auth Required:** Yes
- **Permissions:** `manage content`
- **Response:** `{ success, message, data: {} }`

#### Create Custom Field
- **POST** `/api/v1/admin/cms/custom-fields`
- **Description:** Create custom field
- **Auth Required:** Yes
- **Permissions:** `manage content`
- **Request:**
```json
{
  "field_group_id": 1,
  "name": "Field Name",
  "slug": "field-slug",
  "type": "text",
  "label": "Field Label",
  "is_required": false
}
```
- **Response:** `{ success, message, data: { field } }`

---

### Email Template Endpoints

#### List Email Templates
- **GET** `/api/v1/admin/cms/email-templates`
- **Description:** Get list of email templates
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: [] }`

#### Get Email Template
- **GET** `/api/v1/admin/cms/email-templates/{id}`
- **Description:** Get single email template
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: { template } }`

#### Create Email Template
- **POST** `/api/v1/admin/cms/email-templates`
- **Description:** Create email template
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:**
```json
{
  "name": "Template Name",
  "slug": "template-slug",
  "subject": "Email Subject",
  "body": "Email body with {{ variable }}",
  "variables": ["variable"]
}
```
- **Response:** `{ success, message, data: { template } }`

#### Update Email Template
- **PUT** `/api/v1/admin/cms/email-templates/{id}`
- **Description:** Update email template
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** Same as create
- **Response:** `{ success, message, data: { template } }`

#### Delete Email Template
- **DELETE** `/api/v1/admin/cms/email-templates/{id}`
- **Description:** Delete email template
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message }`

#### Preview Email Template
- **POST** `/api/v1/admin/cms/email-templates/{id}/preview`
- **Description:** Preview email template with data
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** `{ data: {} }`
- **Response:** `{ success, message, data: { rendered } }`

#### Send Test Email
- **POST** `/api/v1/admin/cms/email-templates/{id}/test`
- **Description:** Send test email
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** `{ email, data: {} }`
- **Response:** `{ success, message }`

---

### Content Template Endpoints

#### List Content Templates
- **GET** `/api/v1/admin/cms/content-templates`
- **Description:** Get list of content templates
- **Auth Required:** Yes
- **Permissions:** `manage content`
- **Response:** `{ success, message, data: [] }`

#### Get Content Template
- **GET** `/api/v1/admin/cms/content-templates/{id}`
- **Description:** Get single content template
- **Auth Required:** Yes
- **Permissions:** `manage content`
- **Response:** `{ success, message, data: { template } }`

#### Create Content Template
- **POST** `/api/v1/admin/cms/content-templates`
- **Description:** Create content template
- **Auth Required:** Yes
- **Permissions:** `manage content`
- **Request:**
```json
{
  "name": "Template Name",
  "slug": "template-slug",
  "type": "post",
  "body_template": "Template body",
  "default_fields": {}
}
```
- **Response:** `{ success, message, data: { template } }`

#### Update Content Template
- **PUT** `/api/v1/admin/cms/content-templates/{id}`
- **Description:** Update content template
- **Auth Required:** Yes
- **Permissions:** `manage content`
- **Request:** Same as create
- **Response:** `{ success, message, data: { template } }`

#### Delete Content Template
- **DELETE** `/api/v1/admin/cms/content-templates/{id}`
- **Description:** Delete content template
- **Auth Required:** Yes
- **Permissions:** `manage content`
- **Response:** `{ success, message }`

#### Create Content from Template
- **POST** `/api/v1/admin/cms/content-templates/{id}/create-content`
- **Description:** Create content from template
- **Auth Required:** Yes
- **Permissions:** `manage content`
- **Request:** `{ data: {} }`
- **Response:** `{ success, message, data: { content } }`

---

### Scheduled Task Endpoints

#### List Scheduled Tasks
- **GET** `/api/v1/admin/cms/scheduled-tasks`
- **Description:** Get list of scheduled tasks
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: [] }`

#### Get Scheduled Task
- **GET** `/api/v1/admin/cms/scheduled-tasks/{id}`
- **Description:** Get single scheduled task
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: { task } }`

#### Create Scheduled Task
- **POST** `/api/v1/admin/cms/scheduled-tasks`
- **Description:** Create scheduled task
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:**
```json
{
  "name": "Task Name",
  "command": "command:name",
  "schedule": "* * * * *",
  "is_active": true
}
```
- **Response:** `{ success, message, data: { task } }`

#### Update Scheduled Task
- **PUT** `/api/v1/admin/cms/scheduled-tasks/{id}`
- **Description:** Update scheduled task
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** Same as create
- **Response:** `{ success, message, data: { task } }`

#### Run Scheduled Task
- **POST** `/api/v1/admin/cms/scheduled-tasks/{id}/run`
- **Description:** Manually run scheduled task
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message }`

#### Delete Scheduled Task
- **DELETE** `/api/v1/admin/cms/scheduled-tasks/{id}`
- **Description:** Delete scheduled task
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message }`

---

### File Manager Endpoints

#### List Files
- **GET** `/api/v1/admin/cms/file-manager`
- **Description:** List files in directory
- **Auth Required:** Yes
- **Permissions:** `manage media`
- **Query Parameters:**
  - `path` - Directory path
- **Response:** `{ success, message, data: { files: [], directories: [] } }`

#### Upload File
- **POST** `/api/v1/admin/cms/file-manager/upload`
- **Description:** Upload file
- **Auth Required:** Yes
- **Permissions:** `manage media`
- **Request:** Multipart form data with `file` and `path`
- **Response:** `{ success, message, data: { file } }`

#### Create Folder
- **POST** `/api/v1/admin/cms/file-manager/folder`
- **Description:** Create folder
- **Auth Required:** Yes
- **Permissions:** `manage media`
- **Request:** `{ name, path }`
- **Response:** `{ success, message }`

#### Delete File/Folder
- **DELETE** `/api/v1/admin/cms/file-manager`
- **Description:** Delete file or folder
- **Auth Required:** Yes
- **Permissions:** `manage media`
- **Request:** `{ path, type: "file"|"folder" }`
- **Response:** `{ success, message }`

---

### Log Viewer Endpoints

#### List Logs
- **GET** `/api/v1/admin/cms/logs`
- **Description:** Get application logs
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Query Parameters:**
  - `lines` - Number of lines (default: 100)
  - `level` - Filter by level (error, warning, info)
- **Response:** `{ success, message, data: { logs: [], total } }`

#### Clear Logs
- **POST** `/api/v1/admin/cms/logs/clear`
- **Description:** Clear log file
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message }`

#### Download Logs
- **GET** `/api/v1/admin/cms/logs/download`
- **Description:** Download log file
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** File download

---

### Language Management Endpoints

#### List Languages
- **GET** `/api/v1/admin/cms/languages`
- **Description:** Get list of languages
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: [] }`

#### Create Language
- **POST** `/api/v1/admin/cms/languages`
- **Description:** Create new language
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** `{ code, name, is_active, is_default }`
- **Response:** `{ success, message, data: { language } }`

#### Update Language
- **PUT** `/api/v1/admin/cms/languages/{id}`
- **Description:** Update language
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** Same as create
- **Response:** `{ success, message, data: { language } }`

#### Delete Language
- **DELETE** `/api/v1/admin/cms/languages/{id}`
- **Description:** Delete language
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message }`

#### Get Translations
- **GET** `/api/v1/admin/cms/languages/{id}/translations`
- **Description:** Get translations for language
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Response:** `{ success, message, data: { translations: {} } }`

#### Set Translation
- **PUT** `/api/v1/admin/cms/languages/{id}/translations`
- **Description:** Set translation value
- **Auth Required:** Yes
- **Permissions:** `manage settings`
- **Request:** `{ key, value }`
- **Response:** `{ success, message }`

---

## Response Format

All API responses follow a standardized format:

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "error_code": "ERROR_CODE",
  "trace_id": "err_1234567890",
  "errors": { ... }
}
```

### Validation Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "error_code": "VALIDATION_ERROR",
  "trace_id": "err_1234567890",
  "errors": {
    "field_name": ["The field name is required."]
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": {
    "data": [ ... ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 100,
      "last_page": 7,
      "from": 1,
      "to": 15
    }
  }
}
```

---

## HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests (Rate Limited)
- `500` - Server Error

---

## Rate Limiting

Some endpoints have rate limiting:
- **Login:** 5 attempts per minute
- **Register:** 3 attempts per minute
- **Password Reset:** 3 attempts per minute
- **Admin API:** 60 requests per minute

Rate limit headers are included in responses:
- `X-RateLimit-Limit` - Maximum requests allowed
- `X-RateLimit-Remaining` - Remaining requests
- `X-RateLimit-Reset` - Time when limit resets

---

## Permissions

Most admin endpoints require specific permissions:
- `manage content` - Content management
- `create content` - Create content
- `edit content` - Edit content
- `delete content` - Delete content
- `publish content` - Publish content
- `manage categories` - Category management
- `manage tags` - Tag management
- `manage media` - Media management
- `manage comments` - Comment management
- `manage users` - User management
- `manage settings` - Settings management
- `manage forms` - Form management
- `view analytics` - View analytics
- `manage themes` - Theme management
- `manage plugins` - Plugin management
- `manage menus` - Menu management
- `manage widgets` - Widget management

---

## Interactive API Documentation

Swagger UI is available at:
```
/api/documentation
```

Access the interactive API documentation to test endpoints directly from your browser.

