# JA-CMS Developer Guide

## Table of Contents

1. [Getting Started](#getting-started)
2. [Project Structure](#project-structure)
3. [Architecture Overview](#architecture-overview)
4. [Development Setup](#development-setup)
5. [Code Style](#code-style)
6. [Testing](#testing)
7. [API Development](#api-development)
8. [Frontend Development](#frontend-development)
9. [Database](#database)
10. [Deployment](#deployment)

---

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL/SQLite
- Redis (optional, for caching and queues)
- Git

### Installation

1. **Clone the repository:**
```bash
git clone https://github.com/jejak-awan/ja-cmspro.git
cd ja-cms
```

2. **Install dependencies:**
```bash
composer install
npm install
```

3. **Environment setup:**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database setup:**
```bash
php artisan migrate --seed
```

5. **Build assets:**
```bash
npm run build
# or for development
npm run dev
```

6. **Start development server:**
```bash
php artisan serve
```

---

## Project Structure

```
ja-cms/
├── app/
│   ├── Console/          # Artisan commands
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/V1/   # API controllers
│   │   ├── Middleware/   # Custom middleware
│   │   └── Requests/     # Form requests
│   ├── Helpers/          # Helper classes
│   ├── Jobs/             # Queue jobs
│   ├── Models/           # Eloquent models
│   ├── Notifications/    # Notification classes
│   ├── Services/         # Service classes
│   └── Providers/        # Service providers
├── bootstrap/            # Bootstrap files
├── config/               # Configuration files
├── database/
│   ├── factories/        # Model factories
│   ├── migrations/       # Database migrations
│   └── seeders/          # Database seeders
├── docs/                 # Documentation
├── public/               # Public assets
├── resources/
│   ├── js/               # Vue.js frontend
│   │   ├── components/   # Vue components
│   │   ├── layouts/      # Layout components
│   │   ├── router/       # Vue Router
│   │   ├── services/     # API services
│   │   ├── stores/       # Pinia stores
│   │   └── views/        # Vue views
│   └── views/            # Blade templates
├── routes/
│   ├── api.php           # API routes
│   └── web.php           # Web routes
├── storage/              # Storage files
├── tests/                # Tests
│   ├── Feature/          # Feature tests
│   ├── Unit/             # Unit tests
│   └── Helpers/          # Test helpers
└── vendor/               # Composer dependencies
```

---

## Architecture Overview

### Backend Architecture

**Laravel Framework:**
- MVC architecture
- RESTful API design
- Service layer pattern
- Repository pattern (where applicable)

**Key Components:**
- **Controllers:** Handle HTTP requests, extend `BaseApiController`
- **Models:** Eloquent ORM models with relationships
- **Services:** Business logic (BackupService, SecurityService, etc.)
- **Jobs:** Asynchronous processing (queues)
- **Middleware:** Request/response processing

### Frontend Architecture

**Vue.js 3 with Composition API:**
- Component-based architecture
- Pinia for state management
- Vue Router for navigation
- Axios for API calls

**Key Components:**
- **Views:** Page components
- **Components:** Reusable UI components
- **Stores:** State management (Pinia)
- **Services:** API service layer

### API Design

**RESTful API:**
- Base URL: `/api/v1`
- Standardized responses via `BaseApiController`
- Authentication: Laravel Sanctum
- Rate limiting: Laravel Throttle
- Permission system: Spatie Permission

**Response Format:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

---

## Development Setup

### Environment Configuration

Key environment variables:

```env
APP_NAME="JA-CMS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

CACHE_STORE=file
QUEUE_CONNECTION=database

CDN_ENABLED=false
CDN_URL=
```

### Development Commands

```bash
# Run development server
php artisan serve

# Run queue worker
php artisan queue:work

# Watch for changes (frontend)
npm run dev

# Run tests
php artisan test

# Code formatting
./vendor/bin/pint

# Clear cache
php artisan optimize:clear
```

### IDE Setup

**Recommended Extensions:**
- PHP Intelephense
- Vue Language Features (Volar)
- ESLint
- Prettier

**VS Code Settings:**
```json
{
  "editor.formatOnSave": true,
  "editor.defaultFormatter": "esbenp.prettier-vscode",
  "[php]": {
    "editor.defaultFormatter": "open-southeners.laravel-pint"
  }
}
```

---

## Code Style

### PHP Code Style

**Laravel Pint:**
- Uses Laravel preset
- PSR-12 coding standard
- Auto-formatting on save

**Run Pint:**
```bash
# Check formatting
./vendor/bin/pint --test

# Format code
./vendor/bin/pint
```

**Key Rules:**
- Use type hints for parameters and return types
- Use strict types: `declare(strict_types=1);`
- Follow PSR-12 coding standard
- Use meaningful variable names
- Add PHPDoc comments

### JavaScript/Vue Code Style

**ESLint + Prettier:**
- Vue 3 recommended rules
- Prettier for formatting
- Consistent code style

**Run Linting:**
```bash
npm run lint
npm run lint:fix
```

---

## Testing

### Test Structure

```
tests/
├── Feature/          # Feature/integration tests
│   ├── AuthenticationTest.php
│   ├── ContentManagementTest.php
│   ├── MediaManagementTest.php
│   └── PermissionTest.php
├── Unit/             # Unit tests
└── Helpers/          # Test helpers
    └── TestHelpers.php
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter AuthenticationTest

# Run with coverage
php artisan test --coverage
```

### Writing Tests

**Example:**
```php
public function test_user_can_create_content(): void
{
    $user = $this->createUser();
    $this->actingAs($user, 'sanctum');

    $contentData = TestHelpers::getContentData();

    $response = $this->postJson('/api/v1/admin/cms/contents', $contentData);

    TestHelpers::assertApiSuccess($response, 201);
    $this->assertDatabaseHas('contents', [
        'title' => $contentData['title'],
    ]);
}
```

---

## API Development

### Creating API Endpoints

1. **Create Controller:**
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use Illuminate\Http\Request;

class ExampleController extends BaseApiController
{
    public function index(Request $request)
    {
        // Your logic here
        return $this->success($data, 'Data retrieved successfully');
    }
}
```

2. **Add Route:**
```php
// routes/api.php
Route::get('examples', [ExampleController::class, 'index'])
    ->middleware('auth:sanctum')
    ->middleware('permission:manage examples');
```

3. **Add Permission:**
```php
// database/seeders/RolePermissionSeeder.php
Permission::create(['name' => 'manage examples']);
```

### API Response Standards

**Success Response:**
```php
return $this->success($data, 'Message');
```

**Error Response:**
```php
return $this->error('Error message', 400);
```

**Validation Error:**
```php
return $this->validationError($errors);
```

**Paginated Response:**
```php
return $this->paginated($paginator, 'Message');
```

---

## Frontend Development

### Creating Vue Components

**Component Structure:**
```vue
<template>
    <div>
        <!-- Template -->
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '@/services/api';

// Props
const props = defineProps({
    // props
});

// Emits
const emit = defineEmits(['event']);

// State
const loading = ref(false);

// Methods
const handleAction = async () => {
    // Logic
};
</script>

<style scoped>
/* Styles */
</style>
```

### State Management (Pinia)

**Store Example:**
```javascript
import { defineStore } from 'pinia';

export const useExampleStore = defineStore('example', {
    state: () => ({
        items: [],
        loading: false,
    }),
    
    actions: {
        async fetchItems() {
            this.loading = true;
            const response = await api.get('/examples');
            this.items = response.data.data;
            this.loading = false;
        },
    },
});
```

### API Service

**Service Example:**
```javascript
// resources/js/services/api.js
export default {
    async getExamples() {
        return await axios.get('/api/v1/examples');
    },
};
```

---

## Database

### Migrations

**Create Migration:**
```bash
php artisan make:migration create_examples_table
```

**Migration Example:**
```php
Schema::create('examples', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
    $table->softDeletes();
});
```

### Models

**Model Example:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Example extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];

    // Relationships
    public function relatedModel()
    {
        return $this->belongsTo(RelatedModel::class);
    }
}
```

### Factories

**Factory Example:**
```php
Example::factory()->create();
Example::factory()->count(10)->create();
```

---

## Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure database
- [ ] Set up Redis (if using)
- [ ] Configure queue worker (Supervisor/Systemd)
- [ ] Set up CDN (if using)
- [ ] Run migrations
- [ ] Build assets: `npm run build`
- [ ] Optimize: `php artisan optimize`
- [ ] Set up SSL/HTTPS
- [ ] Configure backup system

### Deployment Commands

```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
sudo supervisorctl restart laravel-worker:*
```

---

## Common Tasks

### Adding a New Feature

1. Create migration
2. Create model
3. Create controller
4. Add routes
5. Add permissions
6. Create frontend components
7. Write tests
8. Update documentation

### Debugging

**Laravel Logs:**
```bash
tail -f storage/logs/laravel.log
```

**Queue Jobs:**
```bash
php artisan queue:work --verbose
```

**Database Queries:**
```php
DB::enableQueryLog();
// Your code
dd(DB::getQueryLog());
```

---

## Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/)
- [Pinia Documentation](https://pinia.vuejs.org/)
- [Laravel Pint](https://laravel.com/docs/pint)
- [PHPUnit](https://phpunit.de/)

---

## Support

For questions or issues:
- GitHub Issues: https://github.com/jejak-awan/ja-cmspro/issues
- Email: support@jejakawan.com

