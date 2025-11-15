# JA-CMS User Guide

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard](#dashboard)
3. [Content Management](#content-management)
4. [Media Management](#media-management)
5. [User Management](#user-management)
6. [Settings](#settings)
7. [Advanced Features](#advanced-features)

---

## Getting Started

### First Login

1. Navigate to `/login`
2. Enter your email and password
3. Click "Sign in"
4. You'll be redirected to the dashboard

### Dashboard Overview

The dashboard provides:
- **Statistics:** Content, users, media counts
- **Recent Activity:** Latest actions and updates
- **Quick Actions:** Common tasks shortcuts
- **Notifications:** System and user notifications

---

## Content Management

### Creating Content

1. Navigate to **Contents** → **Create**
2. Fill in the form:
   - **Title:** Content title
   - **Slug:** URL-friendly identifier (auto-generated)
   - **Body:** Content body (rich text editor)
   - **Excerpt:** Short description
   - **Category:** Select category
   - **Tags:** Add tags
   - **Status:** Draft, Published, or Archived
   - **Published At:** Schedule publication
3. Click **Save** or **Publish**

### Editing Content

1. Go to **Contents** → Select content
2. Click **Edit**
3. Make changes
4. Click **Save**

### Content Features

- **Duplicate:** Create a copy of existing content
- **Preview:** Preview content before publishing
- **Revisions:** View and restore previous versions
- **Lock:** Lock content to prevent concurrent editing
- **Bulk Actions:** Delete, publish, or archive multiple items

---

## Media Management

### Uploading Media

1. Go to **Media** → **Upload**
2. Select file(s) or drag and drop
3. Add metadata (alt text, description)
4. Select folder (optional)
5. Click **Upload**

**Supported Formats:**
- Images: JPG, PNG, GIF, WebP
- Documents: PDF, DOC, DOCX
- Max size: 10MB

### Organizing Media

- **Folders:** Create folders to organize media
- **Search:** Search by name, filename, or alt text
- **Filter:** Filter by type, folder, or usage
- **Bulk Actions:** Delete or move multiple files

### Media Features

- **Thumbnail Generation:** Automatic for images
- **Resize:** Resize images to specific dimensions
- **Usage Tracking:** See where media is used
- **Lazy Loading:** Images load as you scroll

---

## User Management

### Creating Users

1. Go to **Users** → **Create**
2. Fill in:
   - Name
   - Email
   - Password
   - Roles
3. Click **Create**

### Managing Roles

- **Admin:** Full access
- **Editor:** Content management
- **Author:** Create and edit own content
- **Member:** Basic access

---

## Settings

### General Settings

- Site name
- Site description
- Site email
- Timezone
- Date format

### Email Settings

- SMTP configuration
- Email templates
- Notification settings

### SEO Settings

- Meta tags
- Sitemap generation
- Robots.txt configuration
- Schema markup

### Security Settings

- Password requirements
- Session timeout
- IP blocking
- Failed login attempts

---

## Advanced Features

### Content Templates

Create reusable content templates:
1. Go to **Content Templates** → **Create**
2. Define template structure
3. Use template when creating content

### Email Templates

Create custom email templates:
1. Go to **Email Templates** → **Create**
2. Design template with variables
3. Use in notifications

### Forms

Create custom forms:
1. Go to **Forms** → **Create**
2. Add fields
3. Configure settings
4. Get form submissions

### Menus

Manage navigation menus:
1. Go to **Menus** → **Create**
2. Add menu items
3. Assign to locations
4. Reorder items

### Widgets

Add widgets to pages:
1. Go to **Widgets** → **Create**
2. Configure widget
3. Assign to location
4. Customize display

### Backups

Create and manage backups:
1. Go to **Backups** → **Create**
2. Select backup type
3. Download or restore backups

### Activity Logs

View system activity:
1. Go to **Activity Logs**
2. Filter by user, action, or date
3. View details

---

## Common Tasks

### Publishing Content

1. Create or edit content
2. Set status to "Published"
3. Set publication date
4. Click **Publish**

### Scheduling Content

1. Create content
2. Set status to "Published"
3. Set future publication date
4. Content will publish automatically

### Managing Comments

1. Go to **Comments**
2. Review pending comments
3. Approve or reject
4. Reply to comments

### Generating Sitemap

1. Go to **SEO** → **Sitemap**
2. Click **Generate Sitemap**
3. Sitemap will be available at `/sitemap.xml`

### Creating Redirects

1. Go to **Redirects** → **Create**
2. Enter source URL
3. Enter destination URL
4. Select redirect type (301/302)
5. Save

---

## Tips & Best Practices

### Content

- Use descriptive titles
- Add excerpts for better SEO
- Use categories and tags
- Optimize images before upload
- Preview before publishing

### Media

- Use descriptive filenames
- Add alt text for images
- Organize with folders
- Compress large images
- Use appropriate formats

### SEO

- Write descriptive titles
- Add meta descriptions
- Use keywords naturally
- Optimize images
- Create quality content

### Security

- Use strong passwords
- Enable two-factor authentication
- Review activity logs regularly
- Keep software updated
- Backup regularly

---

## Troubleshooting

### Can't Upload Media

- Check file size (max 10MB)
- Verify file format is supported
- Check storage permissions
- Review error messages

### Content Not Publishing

- Check publication date
- Verify status is "Published"
- Check permissions
- Review activity logs

### Images Not Loading

- Check CDN configuration
- Verify storage link: `php artisan storage:link`
- Check file permissions
- Review browser console

---

## Support

For help:
- Check documentation
- Review FAQ
- Contact support: support@jejakawan.com
- GitHub Issues: https://github.com/jejak-awan/ja-cmspro/issues

