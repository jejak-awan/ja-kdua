# K2NET

K2NET is a powerful, modern, and flexible Headless CMS built with **Laravel 12** and **Vue 3**. It is designed to provide a robust backend API service paired with a dynamic Single Page Application (SPA) admin interface.

## 🚀 Key Features

### Content Management
*   **Visual Page Builder:** Drag-and-drop builder with responsive controls, undo/redo, and custom modules.
*   **Content Studio:** Rich text editing (Tiptap), layout management, revision history, and autosave.
*   **Taxonomies:** Unlimited categories and tags.
*   **Custom Fields:** Flexible data modeling for various content types.
*   **Media Management:** Advanced media library with image optimization, cropping, and folder organization.

### Marketing & SEO
*   **Built-in Analytics:** Track visits, top pages, user devices, and custom events without external services.
*   **SEO Tools:** Automated Sitemap generation, `robots.txt` editor, content analysis, and JSON-LD schema support.
*   **Newsletter:** Subscriber management and bulk actions.
*   **Form Builder:** Create and manage custom forms with submission tracking.

### System & Security
*   **Role-Based Access Control (RBAC):** Granular permissions and role management.
*   **Security Suite:** IP Blocking/Whitelisting, CSP Reporting, Login History, and 2FA support.
*   **Performance:** Redis integration for caching, query performance monitoring, and database optimization tools.
*   **System Health:** Real-time monitoring of server resources, queues, and backups.
*   **Activity Logs:** Comprehensive audit trails for user actions.

### Developer Experience
*   **API-First:** Fully documented REST API (Swagger/OpenAPI) for frontend consumption.
*   **Theme Engine:** Support for custom themes with asset compilation.
*   **Plugin System:** Extensible architecture for adding new functionality.
*   **Multi-language:** UI localization support (English/Indonesian).

### ISP Module
*   **Customer Management:** Subscriber lifecycle, contracts, partner/reseller management.
*   **Network Operations:** Multi-vendor OLT support (ZTE, Huawei, VSOL, Global, HIOSO, HSQG, Zymlink), NOC Dashboard, Coverage Heatmap.
*   **Billing Engine:** Automated invoicing, multi-gateway payments (Midtrans/Xendit), plan management, proration, tax (PPN/BHP/USO).
*   **RADIUS Integration:** FreeRADIUS AAA, CoA disconnect, session monitoring, HA cluster support.
*   **Field Operations:** Intelligent technician dispatch (Haversine proximity), service request workflow, troubleshoot wizard.
*   **Inventory & Vouchers:** Hardware tracking, hotspot/PPPoE voucher generation with QR codes.
*   **Enterprise Reporting:** Excel/PDF export, Network Health Certificates, SLA compliance reports.
*   **WhatsApp Integration:** Templated messaging, broadcast campaigns, scheduled notifications.

---

## 🛠 Technology Stack

### Backend
*   **Framework:** [Laravel 12.x](https://laravel.com)
*   **Language:** PHP 8.2+
*   **Database:** PostgreSQL (primary), MySQL (legacy support)
*   **Cache/Queue:** Redis using `predis`
*   **Excel/PDF:** Maatwebsite Excel, mPDF
*   **API Docs:** L5-Swagger
*   **Authentication:** Laravel Sanctum

### Frontend (Admin Panel)
*   **Framework:** [Vue 3.5+](https://vuejs.org) (Composition API, Script Setup)
*   **Build Tool:** [Vite 7.x](https://vitejs.dev)
*   **Language:** TypeScript 5.9+
*   **Styling:** [Tailwind CSS 4.0](https://tailwindcss.com)
*   **State Management:** Pinia 3
*   **Components:** Radix Vue (Headless), Lucide Icons, Shadcn-like UI
*   **Charts:** Chart.js 4.5+

---

## ⚙️ Installation & Setup

### Prerequisites
*   PHP >= 8.2
*   Composer
*   Node.js & NPM
*   Redis Server

### Local Development

1.  **Clone the repository**
    ```bash
    git clone https://github.com/jejak-awan/ja-cmspro.git
    cd ja-cms
    ```

2.  **Install Backend Dependencies**
    ```bash
    composer install
    ```

3.  **Environment Setup**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Configure your database and Redis credentials in `.env`.*

4.  **Run Migrations & Seeders**
    ```bash
    php artisan migrate --seed
    ```

5.  **Install Frontend Dependencies**
    ```bash
    npm install
    ```

6.  **Start Development Server**
    ```bash
    npm run dev
    # Or for backend only: php artisan serve
    ```

### Production Build

To build the frontend assets for production:

```bash
npm run build
```

This will compile the Vue application into `public/build` for Laravel to serve.

---

## 📂 Project Structure

```
/var/www/ja-kdua
├── app/                  # Laravel Core Code (Models, Controllers, Services)
├── config/               # Application Configuration
├── database/             # Migrations, Factories, Seeders
├── public/               # Public entry point & compiled assets
├── resources/
│   ├── js/
│   │   ├── components/   # Reusable Vue Components (UI, Builder, etc.)
│   │   ├── services/     # API Service definitions
│   │   ├── stats/        # Pinia Stores
│   │   ├── views/        # Admin Views (Pages) & Theme Files
│   │   ├── app.ts        # App Entry Point
│   │   └── router.ts     # Vue Router Configuration
│   └── views/            # Laravel Blade Templates (Root entry)
├── routes/               # API and Web Routes
└── tests/                # Feature and Unit Tests
```

## 🧪 Testing

*   **Backend Tests:** `php artisan test`
*   **Type Check:** `npm run type-check`

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
