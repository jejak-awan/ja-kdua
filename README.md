# JA-CMS

**Jejakawan Content Management System**

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green.svg)](https://vuejs.org)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

A modern, feature-rich Content Management System built with Laravel and Vue.js, designed for performance, security, and ease of use.

---

## 🚀 Features

### Content Management
- **Rich Text Editor** - Quill-based WYSIWYG editor with media embedding
- **Content Templates** - Reusable templates for consistent content creation
- **Content Revisions** - Track and restore previous versions
- **Content Locking** - Prevent concurrent editing conflicts
- **Bulk Actions** - Manage multiple content items at once
- **Content Duplication** - Quickly duplicate existing content
- **Scheduled Publishing** - Schedule content for future publication
- **Categories & Tags** - Organize content with hierarchical categories and tags

### Media Management
- **Drag & Drop Upload** - Intuitive file upload interface
- **Media Library** - Organize media in folders
- **Automatic Thumbnails** - Auto-generate image thumbnails
- **Image Optimization** - Automatic image compression and resizing
- **Lazy Loading** - Optimized image loading for better performance
- **Usage Tracking** - See where media is used across the site
- **CDN Support** - Integrate with CDN for faster media delivery

### User & Permission Management
- **Role-Based Access Control** - Flexible permission system using Spatie Permission
- **User Roles** - Admin, Editor, Author, Member roles
- **Granular Permissions** - Fine-grained control over features
- **Activity Logging** - Track user actions and system events
- **Email Verification** - Secure email verification for new users

### SEO & Analytics
- **SEO Tools** - Meta tags, sitemap generation, robots.txt
- **Schema Markup** - Automatic structured data generation
- **Content Analysis** - SEO analysis for content optimization
- **Analytics Dashboard** - Track visits, top pages, referrers

### Advanced Features
- **Email Templates** - Customizable email templates with variables
- **Forms Builder** - Create custom forms with various field types
- **Form Submissions** - Manage and export form submissions
- **Menus Management** - Dynamic menu system with drag-and-drop
- **Widgets** - Reusable widgets for pages
- **Custom Fields** - Extend content with custom field groups
- **Multi-language Support** - High-performance JSON-based translation system with easy import/export
- **Webhooks** - Integrate with external services
- **Scheduled Tasks** - Cron-like task scheduling
- **Search** - Full-text search with indexing
- **Comments System** - Moderation and approval workflow

### System Management
- **Settings Management** - Comprehensive settings interface
- **Backup & Restore** - Database and file backups
- **Security Logs** - Monitor security events and IP blocking
- **Activity Logs** - Detailed activity tracking
- **Notifications** - Real-time notification system
- **File Manager** - Built-in file browser and manager
- **Log Viewer** - View and download application logs
- **System Information** - Monitor system health and statistics

### Performance & Optimization
- **Redis Caching** - High-performance caching with Redis
- **Queue System** - Asynchronous job processing
- **CDN Integration** - Content Delivery Network support
- **Image Optimization** - Automatic image compression
- **Lazy Loading** - Optimized resource loading
- **API Response Caching** - Cache frequently accessed endpoints

### Security
- **Laravel Sanctum** - Secure API authentication
- **Rate Limiting** - Protect against abuse
- **Email Verification** - Verify user emails
- **IP Blocking** - Automatic IP blocking for failed attempts
- **Activity Logging** - Comprehensive audit trail
- **Permission System** - Role-based access control

---

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL 5.7+ / MariaDB 10.3+ / SQLite 3.8.8+
- Redis (optional, for caching and queues)
- GD or Imagick extension (for image processing)

---

## 🛠️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/jejak-awan/ja-cmspro.git
cd ja-cms
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Environment

Edit `.env` file with your database and application settings:

```env
APP_NAME="JA-CMS"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ja_cms
DB_USERNAME=root
DB_PASSWORD=

# Optional: Redis for caching
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# Optional: CDN
CDN_ENABLED=false
CDN_URL=
```

### 5. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed database with default data
php artisan db:seed
```

### 6. Create Admin User

```bash
php artisan user:create-admin
```

### 7. Build Assets

```bash
# Production build
npm run build

# Or for development
npm run dev
```

### 8. Create Storage Link

```bash
php artisan storage:link
```

### 9. Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

---

## 🚀 Quick Start

### Development Mode

```bash
# Start all services (server, queue, logs, vite)
composer run dev
```

This will start:
- Laravel development server
- Queue worker
- Log viewer (Pail)
- Vite dev server

### Production Setup

1. **Set environment to production:**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Optimize application:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan optimize
   ```

3. **Build assets:**
   ```bash
   npm run build
   ```

4. **Setup queue worker** (Supervisor/Systemd):
   ```bash
   php artisan queue:work --daemon
   ```

---

## 📚 Documentation

Comprehensive documentation is available in the `docs/` directory:

### Public Documentation

- **[User Guide](docs/USER_GUIDE.md)** - Complete user manual
- **[Developer Guide](docs/DEVELOPER_GUIDE.md)** - Setup and development guide
- **[API Documentation](docs/API_DOCUMENTATION.md)** - API endpoints reference
- **[Contribution Guide](CONTRIBUTING.md)** - How to contribute
- **[Deployment Guide](docs/DEPLOYMENT.md)** - Production deployment guide

### Technical Guides

- **[Redis Setup](docs/REDIS_SETUP.md)** - Redis installation and configuration
- **[Cache Implementation](docs/CACHE_IMPLEMENTATION.md)** - Caching guide
- **[Queue Implementation](docs/QUEUE_IMPLEMENTATION.md)** - Queue system guide
- **[CDN Setup](docs/CDN_SETUP.md)** - CDN configuration
- **[Image Optimization](docs/IMAGE_OPTIMIZATION.md)** - Image optimization guide

### Internal Documentation

Internal development documentation (roadmaps, audits, status reports) is available in [`docs/dev/`](docs/dev/) for contributors and maintainers.

### API Documentation

Interactive API documentation is available at:
- **Swagger UI:** `/api/documentation`
- **API Base URL:** `/api/v1`

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter AuthenticationTest

# Run with coverage
php artisan test --coverage
```

### Test Coverage

- ✅ Authentication (18 tests)
- ✅ Content Management (20 tests)
- ✅ Media Management (18 tests)
- ✅ Permissions (20 tests)

---

## 🛠️ Development Tools

### Code Formatting

```bash
# Format PHP code with Laravel Pint
./vendor/bin/pint

# Check formatting
./vendor/bin/pint --test
```

### Code Quality

```bash
# Run PHPStan (static analysis)
./vendor/bin/phpstan analyse

# Run ESLint (JavaScript/Vue)
npm run lint
npm run lint:fix
```

---

## 🏗️ Project Structure

```
ja-cms/
├── app/
│   ├── Console/          # Artisan commands
│   ├── Http/
│   │   ├── Controllers/  # API controllers
│   │   ├── Middleware/  # Custom middleware
│   │   └── Requests/     # Form requests
│   ├── Helpers/          # Helper classes
│   ├── Jobs/             # Queue jobs
│   ├── Models/           # Eloquent models
│   └── Services/         # Service classes
├── database/
│   ├── factories/        # Model factories
│   ├── migrations/       # Database migrations
│   └── seeders/          # Database seeders
├── resources/
│   └── js/               # Vue.js frontend
│       ├── components/   # Vue components
│       ├── views/        # Vue views
│       ├── stores/       # Pinia stores
│       └── router/       # Vue Router
├── routes/
│   ├── api.php           # API routes
│   └── web.php           # Web routes
├── tests/                # Tests
└── docs/                 # Documentation
```

---

## 🔧 Configuration

### Cache

Configure caching in `.env`:

```env
CACHE_STORE=redis  # or 'file' for development
```

### Queue

Configure queue in `.env`:

```env
QUEUE_CONNECTION=redis  # or 'database' for development
```

### CDN

Enable CDN in `.env`:

```env
CDN_ENABLED=true
CDN_URL=https://cdn.example.com
```

---

## 🔐 Security

- **Email Verification** - Required for login
- **Rate Limiting** - API and authentication endpoints
- **IP Blocking** - Automatic blocking for failed attempts
- **Permission System** - Role-based access control
- **Activity Logging** - Comprehensive audit trail
- **CSRF Protection** - Built-in Laravel CSRF protection
- **XSS Protection** - Input sanitization
- **SQL Injection Protection** - Eloquent ORM

---

## 📊 Performance

### Caching
- Redis caching for high performance
- API response caching
- Database query caching
- Statistics caching

### Queue System
- Asynchronous image processing
- Background email sending
- Scheduled backup creation
- Search indexing

### Image Optimization
- Automatic thumbnail generation
- Image compression
- Lazy loading
- CDN integration

---

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

### Development Workflow

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests (`php artisan test`)
5. Format code (`./vendor/bin/pint`)
6. Commit changes (`git commit -m 'Add amazing feature'`)
7. Push to branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👥 Team

**Jejakawan** - Content Management System

- **Repository:** [github.com/jejak-awan/ja-cmspro](https://github.com/jejak-awan/ja-cmspro)
- **Issues:** [GitHub Issues](https://github.com/jejak-awan/ja-cmspro/issues)
- **Email:** support@jejakawan.com

---

## 🎯 Roadmap

### Completed ✅
- ✅ Phase 1: Production Hardening (Security, Code Quality, Testing, Documentation)
- ✅ Phase 2: Performance Optimization (Cache, Queue, CDN, Image Optimization)
- ✅ Phase 3: Documentation & Developer Experience (Docs, Tools, CI/CD)
- ✅ Phase 4: Language System Overhaul (JSON-based, UI Management, Import/Export)

### Future Enhancements
- [ ] Multi-site support
- [ ] Advanced analytics
- [ ] Plugin marketplace
- [ ] Theme system
- [ ] API rate limiting dashboard
- [ ] Advanced search features

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [Vue.js](https://vuejs.org) - The Progressive JavaScript Framework
- [Spatie Laravel Permission](https://github.com/spatie/laravel-permission) - Permission management
- [Intervention Image](https://image.intervention.io/) - Image processing
- [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger) - API documentation

---

## 📞 Support

For support, email support@jejakawan.com or open an issue on GitHub.

---

**Made with ❤️ by Jejakawan**

