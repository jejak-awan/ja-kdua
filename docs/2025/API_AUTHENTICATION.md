# 🔐 JA-CMS API Authentication Guide

Complete guide untuk menggunakan API dengan Laravel Sanctum authentication.

## Table of Contents

1. [Overview](#overview)
2. [Authentication Methods](#authentication-methods)
3. [Getting Started](#getting-started)
4. [API Endpoints](#api-endpoints)
5. [Code Examples](#code-examples)
6. [Error Handling](#error-handling)
7. [Best Practices](#best-practices)

---

## Overview

JA-CMS API menggunakan **Laravel Sanctum** untuk authentication dengan support untuk:

- SPA (Single Page Application) Authentication
- Token-based API Authentication
- CSRF Protection
- Rate Limiting

### Base URL

```
Production: https://your-domain.com/api/v1
Development: http://localhost/api/v1
```

### Response Format

Semua response menggunakan format JSON:

```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... },
  "meta": { ... }
}
```

---

## Authentication Methods

### 1. SPA Authentication (Cookie-based)

Untuk Vue.js/React SPA yang di-host di domain yang sama.

**Advantages**:
- Lebih secure (HttpOnly cookies)
- CSRF protection built-in
- Auto-refresh tokens

**Flow**:
1. Get CSRF cookie
2. Login dengan credentials
3. Subsequent requests authenticated via session cookie

### 2. Token Authentication (Bearer Token)

Untuk mobile apps, third-party integrations, atau cross-domain requests.

**Advantages**:
- Stateless
- Works cross-domain
- Good for mobile apps

**Flow**:
1. Login with credentials
2. Receive API token
3. Include token in Authorization header

---

## Getting Started

### Prerequisites

```bash
# Required headers for all requests
Content-Type: application/json
Accept: application/json
X-Requested-With: XMLHttpRequest
```

### Rate Limiting

- **Public endpoints**: 100 requests/minute
- **Authenticated endpoints**: 60 requests/minute
- **Admin endpoints**: 60 requests/minute

Rate limit headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
Retry-After: 60
```

---

## API Endpoints

### Authentication Endpoints

#### 1. Get CSRF Cookie (SPA only)

```http
GET /sanctum/csrf-cookie
```

**Purpose**: Get CSRF token before login

**Response**: Set-Cookie with XSRF-TOKEN

**Example**:
```javascript
await axios.get('/sanctum/csrf-cookie');
```

#### 2. Login

```http
POST /api/v1/auth/login
```

**Request Body**:
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response** (SPA):
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "author"
    }
  }
}
```

**Response** (Token):
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abc123xyz....",
    "token_type": "Bearer",
    "user": { ... }
  }
}
```

#### 3. Logout

```http
POST /api/v1/auth/logout
```

**Headers**:
```
Authorization: Bearer {token}
```

**Response**:
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

#### 4. Get Current User

```http
GET /api/v1/auth/user
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "author",
    "permissions": ["create content", "edit content"]
  }
}
```

#### 5. Refresh Token (Token Auth)

```http
POST /api/v1/auth/refresh
```

**Response**:
```json
{
  "success": true,
  "data": {
    "token": "2|new_token_here...",
    "token_type": "Bearer"
  }
}
```

### Content Endpoints

#### Get Contents

```http
GET /api/v1/cms/contents?page=1&limit=10&type=post&category=technology
```

**Query Parameters**:
- `page`: Page number (default: 1)
- `limit`: Items per page (default: 15)
- `type`: post|page|custom
- `category`: Category slug
- `tag`: Tag slug
- `is_featured`: true|false
- `sort`: -published_at (desc) or published_at (asc)

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "My Post Title",
      "slug": "my-post-title",
      "excerpt": "Short description...",
      "content": "Full content...",
      "featured_image": "https://...",
      "category": {
        "id": 1,
        "name": "Technology",
        "slug": "technology"
      },
      "tags": [
        { "id": 1, "name": "Laravel", "slug": "laravel" }
      ],
      "author": {
        "id": 1,
        "name": "John Doe"
      },
      "published_at": "2025-11-16T10:30:00Z",
      "views": 150
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 45,
    "last_page": 3
  }
}
```

#### Get Single Content

```http
GET /api/v1/cms/contents/{slug}
```

**Response**: Same as above but single object

#### Get Related Posts

```http
GET /api/v1/cms/contents/{slug}/related
```

**Response**: Array of related posts (max 5)

#### Create Content (Auth Required)

```http
POST /api/v1/admin/cms/contents
```

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "title": "New Post Title",
  "slug": "new-post-title",
  "content": "<p>Post content here...</p>",
  "excerpt": "Short description",
  "featured_image": "https://...",
  "status": "published",
  "type": "post",
  "category_id": 1,
  "tags": [1, 2, 3],
  "published_at": "2025-11-16 10:00:00",
  "meta_title": "SEO Title",
  "meta_description": "SEO Description",
  "meta_keywords": "keyword1, keyword2",
  "create_revision": true
}
```

**Response**:
```json
{
  "success": true,
  "message": "Content created successfully",
  "data": { ... }
}
```

#### Update Content

```http
PUT /api/v1/admin/cms/contents/{id}
```

**Request**: Same as create

#### Delete Content

```http
DELETE /api/v1/admin/cms/contents/{id}
```

### Media Endpoints

#### Upload Media

```http
POST /api/v1/admin/cms/media
```

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body** (FormData):
```javascript
const formData = new FormData();
formData.append('file', fileObject);
formData.append('alt_text', 'Image description');
formData.append('title', 'Image title');
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "filename": "image.jpg",
    "url": "https://.../storage/media/image.jpg",
    "mime_type": "image/jpeg",
    "size": 102400,
    "alt_text": "Image description"
  }
}
```

#### Get Media Library

```http
GET /api/v1/admin/cms/media?page=1&type=image&search=landscape
```

**Query Parameters**:
- `page`: Page number
- `type`: image|video|document
- `search`: Search term

---

## Code Examples

### JavaScript (Axios)

#### SPA Authentication

```javascript
import axios from 'axios';

// Configure Axios
const api = axios.create({
  baseURL: 'http://localhost/api/v1',
  withCredentials: true, // Important for cookies
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

// 1. Get CSRF cookie
await axios.get('http://localhost/sanctum/csrf-cookie');

// 2. Login
const login = async (email, password) => {
  try {
    const response = await api.post('/auth/login', {
      email,
      password,
    });
    return response.data;
  } catch (error) {
    console.error('Login failed:', error.response.data);
    throw error;
  }
};

// 3. Get contents (authenticated)
const getContents = async () => {
  try {
    const response = await api.get('/cms/contents');
    return response.data.data;
  } catch (error) {
    console.error('Failed to fetch contents:', error);
    throw error;
  }
};

// 4. Create content (authenticated)
const createContent = async (data) => {
  try {
    const response = await api.post('/admin/cms/contents', data);
    return response.data;
  } catch (error) {
    console.error('Failed to create content:', error.response.data);
    throw error;
  }
};

// 5. Logout
const logout = async () => {
  try {
    await api.post('/auth/logout');
  } catch (error) {
    console.error('Logout failed:', error);
  }
};
```

#### Token Authentication

```javascript
import axios from 'axios';

class ApiClient {
  constructor(baseURL) {
    this.api = axios.create({
      baseURL,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });
    
    this.token = null;
    
    // Intercept requests to add token
    this.api.interceptors.request.use((config) => {
      if (this.token) {
        config.headers.Authorization = `Bearer ${this.token}`;
      }
      return config;
    });
    
    // Intercept responses for error handling
    this.api.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response?.status === 401) {
          // Token expired, redirect to login
          this.token = null;
          localStorage.removeItem('api_token');
          // Redirect or refresh token
        }
        return Promise.reject(error);
      }
    );
  }
  
  async login(email, password) {
    try {
      const response = await this.api.post('/auth/login', {
        email,
        password,
      });
      
      this.token = response.data.data.token;
      localStorage.setItem('api_token', this.token);
      
      return response.data;
    } catch (error) {
      throw error.response.data;
    }
  }
  
  async getContents(params = {}) {
    const response = await this.api.get('/cms/contents', { params });
    return response.data.data;
  }
  
  async createContent(data) {
    const response = await this.api.post('/admin/cms/contents', data);
    return response.data;
  }
  
  logout() {
    this.token = null;
    localStorage.removeItem('api_token');
  }
}

// Usage
const client = new ApiClient('http://localhost/api/v1');

// Login
await client.login('user@example.com', 'password');

// Get contents
const contents = await client.getContents({ page: 1, limit: 10 });

// Create content
const newContent = await client.createContent({
  title: 'New Post',
  content: '<p>Content here</p>',
  status: 'published',
});
```

### PHP (Guzzle)

```php
<?php

use GuzzleHttp\Client;

class JaCmsApiClient
{
    private $client;
    private $token;
    
    public function __construct($baseUrl)
    {
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }
    
    public function login($email, $password)
    {
        $response = $this->client->post('/auth/login', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);
        
        $data = json_decode($response->getBody(), true);
        $this->token = $data['data']['token'];
        
        return $data;
    }
    
    public function getContents($params = [])
    {
        $response = $this->client->get('/cms/contents', [
            'query' => $params,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);
        
        return json_decode($response->getBody(), true);
    }
    
    public function createContent($data)
    {
        $response = $this->client->post('/admin/cms/contents', [
            'json' => $data,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);
        
        return json_decode($response->getBody(), true);
    }
}

// Usage
$api = new JaCmsApiClient('http://localhost/api/v1');

// Login
$api->login('user@example.com', 'password');

// Get contents
$contents = $api->getContents(['page' => 1, 'limit' => 10]);

// Create content
$newContent = $api->createContent([
    'title' => 'New Post',
    'content' => '<p>Content here</p>',
    'status' => 'published',
]);
```

### Python (Requests)

```python
import requests

class JaCmsApiClient:
    def __init__(self, base_url):
        self.base_url = base_url
        self.token = None
        self.session = requests.Session()
        self.session.headers.update({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        })
    
    def login(self, email, password):
        response = self.session.post(
            f'{self.base_url}/auth/login',
            json={'email': email, 'password': password}
        )
        response.raise_for_status()
        
        data = response.json()
        self.token = data['data']['token']
        self.session.headers.update({
            'Authorization': f'Bearer {self.token}'
        })
        
        return data
    
    def get_contents(self, params=None):
        response = self.session.get(
            f'{self.base_url}/cms/contents',
            params=params or {}
        )
        response.raise_for_status()
        return response.json()['data']
    
    def create_content(self, data):
        response = self.session.post(
            f'{self.base_url}/admin/cms/contents',
            json=data
        )
        response.raise_for_status()
        return response.json()

# Usage
api = JaCmsApiClient('http://localhost/api/v1')

# Login
api.login('user@example.com', 'password')

# Get contents
contents = api.get_contents({'page': 1, 'limit': 10})

# Create content
new_content = api.create_content({
    'title': 'New Post',
    'content': '<p>Content here</p>',
    'status': 'published',
})
```

---

## Error Handling

### Error Response Format

```json
{
  "success": false,
  "message": "Error message here",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  },
  "code": "VALIDATION_ERROR"
}
```

### Common HTTP Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request data |
| 401 | Unauthorized | Not authenticated |
| 403 | Forbidden | No permission |
| 404 | Not Found | Resource not found |
| 419 | CSRF Token Mismatch | Invalid or missing CSRF token (SPA) |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

### Error Handling Example

```javascript
try {
  const response = await api.post('/admin/cms/contents', data);
  return response.data;
} catch (error) {
  if (error.response) {
    // Server responded with error
    const { status, data } = error.response;
    
    switch (status) {
      case 401:
        // Redirect to login
        window.location.href = '/login';
        break;
      case 403:
        console.error('Permission denied:', data.message);
        break;
      case 422:
        // Validation errors
        console.error('Validation failed:', data.errors);
        break;
      case 429:
        // Rate limit
        console.error('Too many requests. Retry after:', error.response.headers['retry-after']);
        break;
      default:
        console.error('Error:', data.message);
    }
  } else if (error.request) {
    // No response received
    console.error('Network error: No response from server');
  } else {
    // Other errors
    console.error('Error:', error.message);
  }
  
  throw error;
}
```

---

## Best Practices

### Security

1. **HTTPS in Production**:
   ```javascript
   const baseURL = process.env.NODE_ENV === 'production' 
     ? 'https://api.domain.com/api/v1'
     : 'http://localhost/api/v1';
   ```

2. **Store Tokens Securely**:
   - Use HttpOnly cookies (SPA)
   - Store tokens in secure storage (mobile)
   - Never log tokens
   - Don't expose tokens in URLs

3. **Implement Token Refresh**:
   ```javascript
   api.interceptors.response.use(
     response => response,
     async (error) => {
       if (error.response?.status === 401) {
         // Try refresh token
         const newToken = await refreshToken();
         if (newToken) {
           // Retry original request
           error.config.headers.Authorization = `Bearer ${newToken}`;
           return api(error.config);
         }
       }
       return Promise.reject(error);
     }
   );
   ```

4. **CSRF Protection (SPA)**:
   - Always get CSRF cookie before login
   - Include XSRF-TOKEN header automatically
   - Use `withCredentials: true` in Axios

### Performance

1. **Caching**:
   ```javascript
   const cache = new Map();
   
   async function getContentsWithCache(params) {
     const cacheKey = JSON.stringify(params);
     
     if (cache.has(cacheKey)) {
       return cache.get(cacheKey);
     }
     
     const data = await api.get('/cms/contents', { params });
     cache.set(cacheKey, data, { ttl: 60000 }); // 1 min cache
     
     return data;
   }
   ```

2. **Pagination**:
   - Always use pagination for lists
   - Default limit: 15
   - Max limit: 100

3. **Selective Fields** (if supported):
   ```
   GET /api/v1/cms/contents?fields=id,title,slug,excerpt
   ```

### Rate Limiting

1. **Respect Rate Limits**:
   ```javascript
   const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));
   
   api.interceptors.response.use(
     response => response,
     async (error) => {
       if (error.response?.status === 429) {
         const retryAfter = error.response.headers['retry-after'] * 1000;
         await delay(retryAfter);
         return api(error.config); // Retry
       }
       return Promise.reject(error);
     }
   );
   ```

2. **Batch Requests**:
   ```javascript
   // Instead of multiple single requests
   for (const id of ids) {
     await api.get(`/contents/${id}`);
   }
   
   // Use batch or filter
   const ids = '1,2,3,4,5';
   await api.get(`/contents?ids=${ids}`);
   ```

### Monitoring

1. **Log API Calls** (development):
   ```javascript
   api.interceptors.request.use(config => {
     console.log(`[API] ${config.method.toUpperCase()} ${config.url}`);
     return config;
   });
   
   api.interceptors.response.use(
     response => {
       console.log(`[API] ${response.status} ${response.config.url}`);
       return response;
     },
     error => {
       console.error(`[API Error] ${error.response?.status} ${error.config.url}`);
       return Promise.reject(error);
     }
   );
   ```

2. **Track Performance**:
   ```javascript
   api.interceptors.request.use(config => {
     config.metadata = { startTime: Date.now() };
     return config;
   });
   
   api.interceptors.response.use(response => {
     const duration = Date.now() - response.config.metadata.startTime;
     console.log(`[API Performance] ${response.config.url}: ${duration}ms`);
     return response;
   });
   ```

---

## Webhooks

### Subscribe to Events

```http
POST /api/v1/admin/cms/webhooks
```

**Request**:
```json
{
  "url": "https://your-domain.com/webhook",
  "events": ["content.created", "content.updated", "content.deleted"],
  "secret": "your_webhook_secret"
}
```

### Webhook Payload Example

```json
{
  "event": "content.created",
  "timestamp": "2025-11-16T10:30:00Z",
  "data": {
    "id": 1,
    "title": "New Post",
    "status": "published",
    ...
  },
  "signature": "sha256=..."
}
```

---

## Support & Resources

- **API Status**: https://status.ja-cms.com
- **Swagger/OpenAPI Docs**: https://api.ja-cms.com/docs
- **Postman Collection**: [Download](https://ja-cms.com/postman)
- **GitHub**: https://github.com/jejak-awan/ja-cmspro
- **Community**: https://forum.ja-cms.com

---

**Last updated**: November 16, 2025


