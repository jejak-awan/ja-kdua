# Logs & Analytics System Documentation
**Date:** 2025-12-24
**Version:** 1.0.0
**Status:** Production Ready

---

## 1. Overview

JA-CMS memiliki sistem logging dan analytics yang terintegrasi untuk monitoring aktivitas user, keamanan, dan performa website.

---

## 2. Logs System

### 2.1 Activity Logs
**Model:** `App\Models\ActivityLog`
**Table:** `activity_logs`

Mencatat semua aktivitas CRUD di sistem:
- Create, Update, Delete content
- User management actions
- System configuration changes

```php
ActivityLog::log('created', $model, $changes);
```

### 2.2 Security Logs  
**Model:** `App\Models\SecurityLog`
**Table:** `security_logs`

Mencatat event keamanan:
- Failed login attempts
- Password changes
- Permission changes
- Suspicious activities

### 2.3 Login History
**Model:** `App\Models\LoginHistory`
**Table:** `login_histories`

Mencatat riwayat login:
- Successful logins
- Login location (IP, User Agent)
- Login timestamps

### 2.4 Cleanup Command
```bash
# Cleanup logs older than 30 days (default)
php artisan logs:cleanup

# Cleanup with custom retention
php artisan logs:cleanup --days=60
```

**Scheduled:** Daily at 03:00 AM

---

## 3. Analytics System

### 3.1 Database Schema

#### analytics_sessions
| Column | Type | Description |
|--------|------|-------------|
| session_id | string | Unique session identifier |
| user_id | int | Nullable, logged user |
| ip_address | string | Visitor IP |
| device_type | string | desktop/mobile/tablet |
| browser | string | chrome/firefox/safari/edge |
| os | string | windows/macos/linux/android/ios |
| country | string | GeoIP country |
| city | string | GeoIP city |
| page_views | int | Total pages viewed |
| duration | int | Session duration (seconds) |
| started_at | datetime | Session start |
| ended_at | datetime | Session end |

#### analytics_visits
| Column | Type | Description |
|--------|------|-------------|
| session_id | string | Foreign key to session |
| user_id | int | Nullable |
| ip_address | string | Visitor IP |
| url | string | Page URL visited |
| referer | string | Referrer URL |
| method | string | HTTP method |
| status_code | int | Response status |
| visited_at | datetime | Visit timestamp |

#### analytics_events
| Column | Type | Description |
|--------|------|-------------|
| session_id | string | Session reference |
| event_type | string | click/download/search/form/custom |
| event_name | string | Event identifier |
| event_data | json | Additional data |
| content_id | int | Related content |
| occurred_at | datetime | Event timestamp |

### 3.2 Tracking Middleware
**File:** `app/Http/Middleware/TrackAnalytics.php`

Automatically tracks page visits on frontend routes.

**Excluded Routes:**
- `/admin/*` - Admin pages
- `/api/*` - API endpoints
- Static assets (css, js, images)

**Registration:** `bootstrap/app.php`
```php
$middleware->web(append: [
    \App\Http\Middleware\TrackAnalytics::class,
]);
```

### 3.3 GeoIP Service
**File:** `app/Services/GeoIpService.php`

- Uses ip-api.com (free tier: 45 req/min)
- 24-hour caching for IP lookups
- Handles private/local IPs gracefully

```php
$geoService = app(GeoIpService::class);
$location = $geoService->getLocation('8.8.8.8');
// Returns: ['country' => 'United States', 'city' => 'Ashburn']
```

### 3.4 Frontend Event Tracking
**File:** `resources/js/composables/useAnalytics.js`

```javascript
import { useAnalytics } from '@/composables/useAnalytics';

const { trackClick, trackDownload, trackSearch, trackFormSubmit } = useAnalytics();

// Usage
trackSearch('keyword', resultsCount);
trackDownload('file.pdf', 'pdf');
trackClick('button_name', '/page-url');
```

### 3.5 Cleanup Command
```bash
# Cleanup analytics older than 90 days
php artisan analytics:cleanup --days=90

# Dry run (preview only)
php artisan analytics:cleanup --days=90 --dry-run
```

**Scheduled:** Weekly on Sunday at 03:30 AM

---

## 4. API Endpoints

### Analytics Dashboard Data
| Endpoint | Description |
|----------|-------------|
| `GET /api/v1/admin/cms/analytics/overview` | Overview stats |
| `GET /api/v1/admin/cms/analytics/visits` | Visits over time |
| `GET /api/v1/admin/cms/analytics/top-pages` | Top visited pages |
| `GET /api/v1/admin/cms/analytics/top-content` | Top content |
| `GET /api/v1/admin/cms/analytics/devices` | Device breakdown |
| `GET /api/v1/admin/cms/analytics/browsers` | Browser breakdown |
| `GET /api/v1/admin/cms/analytics/countries` | Country breakdown |
| `GET /api/v1/admin/cms/analytics/referrers` | Top referrers |
| `GET /api/v1/admin/cms/analytics/realtime` | Real-time activity |
| `GET /api/v1/admin/cms/analytics/export` | Export CSV |

**Query Parameters:**
- `date_from` - Start date (Y-m-d)
- `date_to` - End date (Y-m-d)
- `type` - Export type (visits/events/sessions)

---

## 5. Admin UI

### Analytics Dashboard
**File:** `resources/js/views/admin/analytics/Index.vue`

**Sections:**
1. **Overview Stats** - Total visits, unique visitors, sessions, bounce rate
2. **Real-time Activity** - Active sessions, visits last hour, active pages
3. **Traffic** - Visits chart, top pages
4. **Technology** - Devices, browsers breakdown (donut charts)
5. **Geography** - Countries, referrers
6. **Top Content** - Most visited content

**Features:**
- Date range picker
- Export to CSV (visits/events/sessions)
- Real-time data refresh (30s interval)

---

## 6. Bounce Rate Calculation

```php
// A bounce is defined as:
// - Single page view session, OR
// - Session with duration < 10 seconds

$bounceSessions = AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])
    ->where(function ($query) {
        $query->where('page_views', 1)
            ->orWhere('duration', '<', 10);
    })
    ->count();

$bounceRate = ($bounceSessions / $totalSessions) * 100;
```

---

## 7. Data Normalization

Device/browser/geo data stored ONLY in `analytics_sessions` (not duplicated in visits).

`AnalyticsVisit` model uses accessors to get this data from related session:
```php
public function getDeviceTypeAttribute()
{
    return $this->session?->device_type;
}
```

---

## 8. Scheduled Tasks

| Command | Schedule | Description |
|---------|----------|-------------|
| `logs:cleanup` | Daily 03:00 | Clean old logs |
| `analytics:cleanup --days=90` | Sunday 03:30 | Clean old analytics |
| `cache:warm` | Daily 02:00 | Warm cache |

**File:** `routes/console.php`

---

## 9. Troubleshooting

### Issue: Analytics shows 0 data
**Cause:** Date range query issue
**Solution:** `getDateRange()` helper adds time to dates:
- Start: `Y-m-d 00:00:00`
- End: `Y-m-d 23:59:59`

### Issue: GeoIP returns empty
**Cause:** Private/local IP addresses
**Solution:** Configure reverse proxy to forward real IP via `X-Forwarded-For`

### Issue: API routes being tracked
**Cause:** Old data or middleware misconfiguration
**Solution:** Middleware excludes `/api/*` routes. Clean old data:
```bash
php artisan tinker --execute="
App\Models\AnalyticsVisit::where('url', 'LIKE', '%/api/%')->delete();
"
```

---

## 10. Files Reference

| File | Purpose |
|------|---------|
| `app/Http/Middleware/TrackAnalytics.php` | Page visit tracking |
| `app/Models/AnalyticsSession.php` | Session model |
| `app/Models/AnalyticsVisit.php` | Visit model |
| `app/Models/AnalyticsEvent.php` | Event model |
| `app/Services/GeoIpService.php` | GeoIP lookup |
| `app/Services/AnalyticsService.php` | Analytics helper |
| `app/Console/Commands/CleanupAnalytics.php` | Cleanup command |
| `app/Http/Controllers/Api/V1/AnalyticsController.php` | API controller |
| `resources/js/composables/useAnalytics.js` | Frontend tracking |
| `resources/js/views/admin/analytics/Index.vue` | Dashboard UI |
