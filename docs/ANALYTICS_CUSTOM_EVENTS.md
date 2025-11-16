# Analytics Custom Events Tracking

## Overview

The JA-CMS includes a comprehensive custom event tracking system that allows you to track user interactions, content engagement, form submissions, downloads, and more.

## API Endpoints

### Track Single Event

**POST** `/api/v1/analytics/track`

**Rate Limit**: 60 requests per minute

**Request Body**:
```json
{
  "event_type": "click",
  "event_name": "Button Click: Subscribe",
  "event_data": {
    "button_id": "subscribe-btn",
    "page": "homepage"
  },
  "content_id": 123
}
```

**Response**:
```json
{
  "success": true,
  "message": "Event tracked successfully",
  "data": {
    "id": 1,
    "event_type": "click",
    "event_name": "Button Click: Subscribe",
    "event_data": {...},
    "occurred_at": "2025-01-15T10:30:00Z"
  }
}
```

### Track Batch Events

**POST** `/api/v1/analytics/track/batch`

**Rate Limit**: 60 requests per minute

**Request Body**:
```json
{
  "events": [
    {
      "type": "click",
      "name": "Button Click: Subscribe",
      "data": {"button_id": "subscribe-btn"}
    },
    {
      "type": "form_submit",
      "name": "Form Submit: Newsletter",
      "data": {"form_id": "newsletter"}
    }
  ]
}
```

**Response**:
```json
{
  "success": true,
  "message": "Events tracked successfully",
  "data": {
    "tracked_count": 2,
    "events": [...]
  }
}
```

## Backend Usage (PHP)

### Using AnalyticsService

```php
use App\Services\AnalyticsService;

// Track a custom event
AnalyticsService::trackEvent('click', 'Button Click: Subscribe', [
    'button_id' => 'subscribe-btn',
    'page' => 'homepage'
]);

// Track content interaction
AnalyticsService::trackContentInteraction($contentId, 'like', [
    'user_id' => auth()->id()
]);

// Track form submission
AnalyticsService::trackFormSubmission('Contact Form', [
    'fields' => ['name', 'email']
]);

// Track download
AnalyticsService::trackDownload('document.pdf', 'pdf', $mediaId);

// Track search
AnalyticsService::trackSearch('laravel tutorial', 15);

// Track video play
AnalyticsService::trackVideoPlay('Introduction Video', $contentId);

// Track button/link click
AnalyticsService::trackClick('Subscribe Button', '/subscribe');
```

### Using AnalyticsEvent Model Directly

```php
use App\Models\AnalyticsEvent;

AnalyticsEvent::track('click', 'Button Click', ['button_id' => 'btn-1']);
```

## Frontend Usage (Vue/JavaScript)

### Basic Tracking

```javascript
// Track single event
await axios.post('/api/v1/analytics/track', {
  event_type: 'click',
  event_name: 'Button Click: Subscribe',
  event_data: {
    button_id: 'subscribe-btn',
    page: 'homepage'
  }
});

// Track batch events
await axios.post('/api/v1/analytics/track/batch', {
  events: [
    {
      type: 'click',
      name: 'Button Click',
      data: { button_id: 'btn-1' }
    },
    {
      type: 'form_submit',
      name: 'Form Submit',
      data: { form_id: 'contact' }
    }
  ]
});
```

### Vue Composable Example

Create `resources/js/composables/useAnalytics.js`:

```javascript
import { ref } from 'vue';
import axios from 'axios';

export function useAnalytics() {
  const tracking = ref(false);

  const trackEvent = async (eventType, eventName, data = {}, contentId = null) => {
    if (tracking.value) return; // Prevent duplicate tracking
    
    tracking.value = true;
    try {
      await axios.post('/api/v1/analytics/track', {
        event_type: eventType,
        event_name: eventName,
        event_data: data,
        content_id: contentId
      });
    } catch (error) {
      console.error('Analytics tracking failed:', error);
    } finally {
      tracking.value = false;
    }
  };

  const trackClick = (buttonName, url) => {
    trackEvent('click', `Click: ${buttonName}`, { url });
  };

  const trackDownload = (fileName, fileType) => {
    trackEvent('download', `Download: ${fileName}`, { file_type: fileType });
  };

  const trackFormSubmit = (formName, formData = {}) => {
    trackEvent('form_submit', `Form Submit: ${formName}`, formData);
  };

  return {
    trackEvent,
    trackClick,
    trackDownload,
    trackFormSubmit
  };
}
```

### Usage in Vue Component

```vue
<template>
  <button @click="handleSubscribe">Subscribe</button>
</template>

<script setup>
import { useAnalytics } from '@/composables/useAnalytics';

const { trackClick } = useAnalytics();

const handleSubscribe = () => {
  trackClick('Subscribe Button', '/subscribe');
  // ... rest of subscribe logic
};
</script>
```

## Event Types

Common event types you can track:

- `click` - Button/link clicks
- `form_submit` - Form submissions
- `download` - File downloads
- `search` - Search queries
- `video_play` - Video plays
- `content_interaction` - Content likes, shares, comments
- `page_view` - Custom page views
- `custom` - Any custom event

## Viewing Events

Access tracked events via admin panel:

- **API**: `GET /api/v1/admin/cms/analytics/events`
- **Stats**: `GET /api/v1/admin/cms/analytics/event-stats`

## Best Practices

1. **Use Descriptive Names**: `"Button Click: Subscribe"` instead of `"click"`
2. **Include Context**: Add relevant data in `event_data`
3. **Batch When Possible**: Use batch endpoint for multiple events
4. **Respect Rate Limits**: 60 events per minute
5. **Don't Track Sensitive Data**: Never include passwords, tokens, etc.
6. **Use Consistent Naming**: Follow a naming convention

## Examples

### Track Content Engagement

```php
// When user likes content
AnalyticsService::trackContentInteraction($contentId, 'like');

// When user shares content
AnalyticsService::trackContentInteraction($contentId, 'share', [
    'platform' => 'facebook'
]);

// When user comments
AnalyticsService::trackContentInteraction($contentId, 'comment', [
    'comment_length' => strlen($comment)
]);
```

### Track E-commerce Events

```php
// Track product view
AnalyticsService::trackEvent('product_view', 'Product View: iPhone 15', [
    'product_id' => 123,
    'category' => 'Electronics',
    'price' => 999.99
]);

// Track add to cart
AnalyticsService::trackEvent('add_to_cart', 'Add to Cart', [
    'product_id' => 123,
    'quantity' => 2
]);

// Track purchase
AnalyticsService::trackEvent('purchase', 'Purchase Complete', [
    'order_id' => 'ORD-12345',
    'total' => 1999.98,
    'items' => 2
]);
```

### Track User Journey

```php
// Track funnel steps
AnalyticsService::trackEvent('funnel', 'Funnel: Step 1 - Landing', [
    'step' => 1,
    'funnel_name' => 'signup'
]);

AnalyticsService::trackEvent('funnel', 'Funnel: Step 2 - Form', [
    'step' => 2,
    'funnel_name' => 'signup'
]);
```

