## JA‑CMS Deep Audit — 22 Januari 2026

Lokasi project: `/var/www/ja-cms`  
Stack utama: **Laravel 12 (PHP 8.2)** + **Vue 3 SPA (Vite)** + **Redis** + **MariaDB/MySQL**

---

## 1. Gambaran Umum

- **Arsitektur**
  - Backend Laravel 12 dengan routing baru via `bootstrap/app.php`.
  - Frontend SPA (Vue 3) dengan route utama yang selalu me-render `view('app')`.
  - API utama di `routes/api.php` dengan prefix `api/v1`, termasuk:
    - Public API (CMS, captcha, analytics, forms).
    - Admin API di bawah `api/v1/admin/ja/*` dengan `auth:sanctum` + permission granular (Spatie Permission).
  - Nginx vhost `ja-cms-ssl` di port internal 8443 (di-depanin NPM/Cloudflare), handle:
    - Static assets (`/build`, `/storage`) dengan cache panjang.
    - Route `/api` langsung ke Laravel.

- **Feature high‑level**
  - CMS lengkap (content, categories, tags, forms, media library, themes, widgets, plugins).
  - RBAC via Spatie Permission (roles, permissions).
  - Analytics internal (visits, events, dashboards).
  - Backup/restore database, file manager, scheduled tasks, security logs & blocklist.

---

## 2. Authentication, Session & Security Layer

### 2.1 Login, Lockout, dan 2FA

- `Api\V1\AuthController@login`:
  - Validasi `email` + `password` + optional **captcha** (tergantung Setting `enable_captcha`, `captcha_on_login`).
  - Ambil IP via `IpHelper::getClientIp()` yang mempertimbangkan header proxy (`CF-Connecting-IP`, `X-Real-IP`, `X-Forwarded-For`, dst).
  - **SecurityService** dipakai untuk:
    - Cek apakah IP sudah diblok sementara/permanen.
    - Cek apakah akun terkunci (terlalu banyak percobaan).
    - Record failed login & progressive blocking (offense naik → durasi blok makin panjang, auto‑expire via cache).
  - Login gagal:
    - Nambah counter attempt (per IP & per email).
    - Bisa memicu IP block sementara.
    - Tercatat di `LoginHistory`.
  - Login sukses:
    - Update `last_login_at`, `last_login_ip`.
    - Tulis ke `LoginHistory` & optional `ActivityLog`.

- **Two‑Factor Authentication (2FA)**:
  - Jika user punya 2FA, flow dibikin dua langkah:
    - Step 1: login sukses tapi API balas `requires_two_factor=true` + `user_id`.
    - Step 2: kirim `two_factor_code`, diverifikasi:
      - TOTP via `PragmaRX\Google2FA`.
      - Jika gagal, cek backup code.
  - Failed 2FA juga dihitung sebagai failed login + dicatat di `LoginHistory`.

- **Single Session & Session Limit**:
  - Ada dua mekanisme:
    - `single_session_enabled`: revoke semua token lama, login web guard, `logoutOtherDevices`, dan set lifetime via `SessionManager`.
    - `max_concurrent_sessions`: limit jumlah token Sanctum; jika lebih, token tertua dihapus.

### 2.2 Session & Cookie

- `config/session.php`:
  - Default driver: **database** (`SESSION_DRIVER`), dengan opsi khusus:
    - `admin_lifetime` dan `user_lifetime` (tiered lifetime).
  - Pengaturan `SESSION_DOMAIN`, `SESSION_SECURE_COOKIE`, dan `same_site` bisa di‑tune via env.
- `AppServiceProvider`:
  - Bisa override `session.lifetime` dari Setting `session_lifetime`.

### 2.3 Sanctum & Guards

- `config/sanctum.php`:
  - `stateful` diambil dari env `SANCTUM_STATEFUL_DOMAINS` + host saat ini.
  - Guard yang dicek: `['web']`.
- Routes:
  - Banyak endpoint admin/privat dibungkus `auth:sanctum` + middleware `permission`.
  - Beberapa endpoint public (CMS, analytics, captcha, forms) tidak require auth, tapi ada rate‑limit per route.

**Kesimpulan**: Flow auth cukup matang (lockout bertingkat, IP + account level, 2FA, login history, activity log). Kualitas keamanan sangat tergantung konfigurasi proxy dan join‑up antara Sanctum stateful, cookie settings, dan deployment (Cloudflare / NPM) yang konsisten.

---

## 3. IP, Proxy & Rate‑Limit (Area Sensitif)

### 3.1 TrustProxies & IpHelper

- `App\Http\Middleware\TrustProxies`:
  - `protected $proxies = '*'`, namun implementasinya:
    - Menggunakan `REMOTE_ADDR` saat ini sebagai trusted proxy, lalu
    - Mengambil IP riil dari header prioritas: `CF-Connecting-IP` → `X-Real-IP` → `True-Client-IP` → `X-Forwarded-For`.
  - IP hasil ini disimpan sebagai `real_client_ip` dan juga men‑override `REMOTE_ADDR`.

- `App\Helpers\IpHelper`:
  - Juga membaca header proxy yang sama untuk fallback.

**Implikasi**:
- Kalau **SELALU** ada proxy/CDN di depan (Cloudflare/NPM) dan koneksi direct ke origin tidak diizinkan, pola ini cukup aman.
- Kalau origin bisa diakses langsung (mis. dari subnet internal tanpa WAF), attacker berpotensi:
  - Spoof header `X-Forwarded-For` / `CF-Connecting-IP`.
  - Bypass atau memanipulasi:
    - Rate limiting per IP,
    - IP blocklist / whitelist,
    - Pencatatan `SecurityLog`, `LoginHistory`, `Analytics`.

### 3.2 Rate‑Limiting

- Laravel throttle digunakan secara agresif di:
  - Login / register / password reset.
  - Public CMS API (`cms/*`).
  - Forms submission.
  - Analytics (`analytics/*`).
  - Realtime analytics endpoint punya limit lebih longgar namun tetap dibatasi.
- `bootstrap/app.php` juga menambahkan handler khusus untuk `TooManyRequestsHttpException`:
  - Output JSON standar dengan `retry_after` + header rate‑limit tambahan.

**Catatan**: Semakin kritis endpoint‑nya, semakin tergantung akurasi IP (lihat poin TrustProxies di atas).

---

## 4. File Upload, Media & File Manager

### 4.1 Media Upload (CMS)

- `Api\V1\MediaController@upload`:
  - Validasi:
    - `file` wajib, `max` size berdasarkan Setting `max_upload_size` (default 10MB).
    - Dimensi optional (`min_width`, `max_width`, dll.) untuk image.
  - Jika file image dan ada constraint dimensi, divalidasi lagi dengan rule `dimensions`.
  - Media diserahkan ke `MediaService::upload()`:
    - Disimpan di disk `public` (`storage/app/public/media`).
    - Jika `optimize` true dan mime `image/*`, dilakukan kompresi/resize ringan.
    - Dibuat record `Media` (name, path, mime, size, author, folder).
    - Auto‑generate thumbnail untuk image (termasuk SVG → PNG jika memungkinkan).

- `MediaSettingsHelper`:
  - Default allowed image types: `jpg,jpeg,png,gif,webp,svg`.
  - Default allowed file types: `pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar`.
  - Bisa diubah via Setting group `media`.

**Catatan keamanan**:
- **SVG** diizinkan dan dapat di‑serve sebagai image. SVG bisa membawa JavaScript/links (XSS) jika direfleksikan inline. Ini aman *kalau* hanya dipakai sebagai `<img src>` dari domain sama, tapi tetap perlu waspada.

### 4.2 File Manager (Admin‑level File System) — Area High Risk

- `Api\V1\FileManagerController` menyediakan:
  - Listing direktori (`index`).
  - Download file/folder (ZIP).
  - Upload file.
  - Delete (soft ke `.trash` + table `deleted_files`) dan permanent delete.
  - Create/move/copy/rename folder & file.
  - Extract archive (`zip`, `tar`, `tar.gz`, `tgz`).
  - Empty trash, restore item dari trash.

- Proteksi:
  - Semua endpoint file manager di `routes/api.php` berada di dalam group:
    - `auth:sanctum`,
    - plus middleware permission `manage files`.
  - Jadi hanya user dengan permission `manage files` yang punya akses.

**Potensi masalah teknis**:
- Parameter `disk`:
  - Default `public`, tapi nilai `disk` diambil dari request dan langsung diteruskan ke `Storage::disk($disk)`.
  - Tidak ada allowlist eksplisit di controller.
  - `config/filesystems.php` mendefinisikan disk:
    - `local` → `storage/app/private` (serve=false).
    - `public` → `storage/app/public`.
    - `s3` (opsional, via env).
  - Bila UI/FE mengizinkan user memilih disk sembarang, admin dengan `manage files` bisa mengutak‑atik disk lain (mis. `local`).

- Path:
  - Banyak operasi memanggil `Storage::disk($disk)->path($path)` dan kemudian `scandir()` / `File::moveDirectory()` / `ZipArchive::extractTo()`.
  - Sanitasi path dasar (`trim('/', path)`), tetapi:
    - Tidak ada eksplisit blok untuk `..` atau path absolut.
    - Bergantung pada lapisan Storage & OS untuk mencegah traversal.

- Extract archive:

```12:15:/var/www/ja-cms/app/Http/Controllers/Api/V1/FileManagerController.php
if ($extension === 'zip') {
    $zip = new \ZipArchive;
    if ($zip->open($fullPath) === true) {
        $zip->extractTo($extractPath);
        $zip->close();
    }
}
```

  - Tidak ada filter entry → secara teori rentan **ZipSlip** (file di archive dengan path `../../something` bisa keluar dari folder target) jika OS path tidak di‑sanitize.

**Kesimpulan**:
- Selama hanya admin yang benar‑benar dipercaya yang dapat permission `manage files`, fitur ini wajar untuk panel admin.
- Kalau `manage files` diberi ke pihak ketiga/klien, risiko meningkat: path traversal, baca/tulis file sensitif di `storage/app/private` (dan potensi remote file overwrite jika dikombinasikan dengan bug lain).

---

## 5. Backup & Restore

### 5.1 Pembuatan Backup

- `App\Services\BackupService::createDatabaseBackup()`:
  - Membuat record `Backup` (status `in_progress`).
  - Menentukan path target `backups/Y/m/backup_name.zip` di disk `local` (private).
  - Dump:
    - `sqlite`: copy file DB.
    - `mysql`: pakai `mysqldump` dengan `escapeshellarg()` untuk host/port/user/pass/db.
    - `pgsql`: pakai `pg_dump` dengan `PGPASSWORD`.
  - Setelah dump `.sql`:
    - Bungkus jadi ZIP.
    - Jika `BACKUP_ARCHIVE_PASSWORD` diset (atau fallback `APP_KEY`), akan mengenkripsi entry SQL di ZIP (`EM_AES_256` bila tersedia).
  - Hapus file `.sql` sementara, update size & status `completed`.

### 5.2 Restore Backup

- `BackupService::restoreDatabaseBackup()`:
  - Pastikan backup type `database` dan status `completed`.
  - Baca file backup dari disk `backup->disk` (`local`), detect apakah ZIP.
  - Kalau ZIP:
    - Extract ke folder `restore_*` sementara.
    - Jika ada password, set ke `BACKUP_ARCHIVE_PASSWORD` atau `APP_KEY`.
    - Cari file `.sql` atau `.sqlite` di dalam.
  - Menjalankan:
    - `mysql ... < file.sql`.
    - atau `psql ... < file.sql`.
    - atau copy file sqlite ke target.

**Risiko & catatan**:
- Jalur ini high‑impact secara alamiah (backup/restore = full DB control). Aman kalau:
  - Permission `manage backups` hanya dimiliki superadmin internal.
  - Environment (host DB, user DB) tidak bisa diganti seenaknya oleh attacker.
- Penggunaan `APP_KEY` sebagai fallback password enkripsi:
  - Praktis, tapi membuat rotasi `APP_KEY` berpotensi memutus kemampuan decrypt backup lama.

---

## 6. Caching, Performance & Observability

### 6.1 Cache & CDN

- `config/cache.php`:
  - Default store: `file` (bisa di‑override `CACHE_STORE`).
  - Tersedia store `redis`, `redis_failover`, `failover`.
- `AppServiceProvider`:
  - Bisa mematikan cache paksa (pakai store `array`) jika Setting `enable_cache=false`.
  - Bisa override `cache.default` sesuai Setting `cache_driver` dari DB.
  - Config CDN juga diinject dari Setting group `performance/media`.

- `CacheResponse` middleware:
  - Cache hanya untuk:
    - Request `GET`.
    - User **tidak** ter‑auth.
    - Bukan route `api/*`.
  - Key: `response_cache_md5(fullUrl)`.

**Catatan**:
- `Cache::remember` menyimpan object `Response` apa adanya – ini umum di Laravel, tapi butuh store yang kompatibel (file/redis) dan hati‑hati jika menambahkan custom stream/data di response.

### 6.2 Slow Query Logging & Query Analytics

- `LogSlowQueries` middleware:
  - Aktif hanya jika `app.debug` true atau `database.log_slow_queries=true`.
  - Mengaktifkan `DB::enableQueryLog()`, mengukur total waktu query, dan:
    - Kalau total > threshold (default 100ms), log warning dengan query2 lambat.
    - Jika `store_slow_queries=true`, simpan ke table `slow_queries`.
  - Untuk request `api/*`, juga cache metrik (jumlah query, total waktu) untuk dashboard via `QueryPerformanceService`.

**Kesan**:
- Performa sudah dapat tooling untuk:
  - Deteksi slow query.
  - Deteksi duplicate query + N+1 pattern.
  - Monitoring ringkas lewat cache.

### 6.3 Queue & Background Jobs

- Queue didesain memakai **Redis** untuk production, `database`/`sync` untuk dev/testing (lihat `docs/QUEUE_IMPLEMENTATION.md`).
- Job utama:
  - `ProcessImageJob`, `SendEmailJob`, `CreateBackupJob`, `IndexSearchJob`, dll.
- `docs/DEPLOYMENT.md` dan `docs/QUEUE_IMPLEMENTATION.md` menggambarkan run queue via **Supervisor** atau **systemd**, dengan log di `storage/logs/worker.log`.

**Temuan dari log**:
- Ada error **Redis connection refused** di masa lalu:

```1:30:/var/www/ja-cms/storage/logs/ja-cms.log
production.ERROR: Connection refused ... PhpRedisConnector.php
```

- Saat audit, redis-server sudah `active (running)` pada `127.0.0.1:6379`. Kemungkinan besar error terjadi ketika:
  - Redis belum start, tapi worker sudah jalan.
  - Atau config env queue/cache mengarah ke host berbeda saat itu.

---

## 7. Scheduled Tasks & Artisan Execution

- `ScheduledTask` model + `ScheduledTaskController` menyediakan UI untuk:
  - Membuat scheduled task dengan cron expression.
  - Menentukan `command` Artisan yang akan dijalankan.
  - Menjalankan task secara manual via API.
- Safety:
  - Ada whitelist `ALLOWED_COMMANDS` (cache, logs cleanup, media thumbnail, backup, security clear, queue, optimize, dll).
  - Ada blacklist `BLOCKED_COMMANDS` (migrate, db:wipe, down, key:generate, tinker, dll).
  - Validasi cron expression menggunakan `Cron\CronExpression`.
  - Semua route `scheduled-tasks` sudah diproteksi:
    - `auth:sanctum` + permission `manage scheduled tasks`.

**Catatan**:
- Ini tetap remote execution surface melalui Artisan, tapi sudah cukup di‑lock ke set command yang dianggap aman.

---

## 8. Security Tooling & Dependency Audit

- `SecurityService` + `SecurityController`:
  - Kelola blocklist/whitelist IP (DB + cache).
  - Clear failed attempts / lockout via API.
  - Menyimpan log keamanan di table `security_logs`.
- `DependencyVulnerabilityController` + `SecurityAuditDependencies` command:
  - Menjalankan `composer audit` dan `npm audit`.
  - Parse output ke table `dependency_vulnerabilities`.
  - Admin bisa melihat, filter, dan update status vuln (new/acknowledged/patched/ignored) via API.

**Hal yang perlu diingat**:
- Di `composer.json` masih ada beberapa dependency dengan versi `*`:
  - `pragmarx/google2fa-laravel: "*"`
  - `rap2hpoutre/laravel-log-viewer: "*"`
  - Ini meningkatkan risiko supply‑chain dan breaking change otomatis saat `composer update`.

---

## 9. Observasi Ops & Logging

- `config/logging.php`:
  - Default channel `stack` → `single`/`daily` bergantung env.
  - Path log utama: `storage/logs/ja-cms.log`.
  - Log worker: `storage/logs/worker.log`.
- Log Laravel default (`laravel.log`) hampir kosong, artinya aplikasi sudah diarahkan ke `ja-cms.log`.

**Good practices yang sudah ada**:
- Banyak endpoint critical log ke `SecurityLog`, `ActivityLog`, `LoginHistory`.
- Slow queries bisa disimpan ke DB dan dipantau via API.
- Analytics berbasis event & visit punya model terdedikasi.

---

## 10. Rekomendasi Prioritas

### P0 – Tinggi (Security Core)

1. **Perketat TrustProxies & IP handling**
   - Batasi list proxy tepercaya (Cloudflare/NPM IP ranges) daripada `*`.
   - Pastikan origin tidak bisa diakses langsung oleh publik tanpa WAF/proxy.
   - Pertimbangkan untuk “strip” header proxy pada koneksi direct di nginx.

2. **Hardening File Manager**
   - Terapkan allowlist `disk` di level controller (misal: hanya `public` kecuali superadmin).
   - Normalisasi dan validasi path: tolak `..`, path absolut, dan pola yang mencurigakan.
   - Lindungi operasi `extractTo()` dari **ZipSlip** dengan memeriksa setiap entry di archive sebelum extract.

3. **SVG & Upload Policy**
   - Evaluasi kembali apakah SVG benar‑benar perlu diizinkan:
     - Jika iya, gunakan sanitization (library SVG sanitizer) sebelum publish.
     - Alternatif: serve SVG sebagai download (`Content-Disposition: attachment`) untuk file tertentu.

### P1 – Sedang (Ops & Reliability)

4. **Stabilkan Redis & Queue Worker**
   - Pastikan worker queue hanya start setelah Redis siap:
     - misalnya lewat dependency di systemd atau check script.
   - Konsolidasikan config cache/queue (`CACHE_DRIVER`, `QUEUE_CONNECTION`) agar:
     - Tidak berubah di tengah jalan karena Setting DB yang tidak sinkron.

5. **Backup Encryption Strategy**
   - Pertimbangkan password enkripsi khusus backup (`BACKUP_ARCHIVE_PASSWORD`) yang tidak sama dengan `APP_KEY`.
   - Definisikan SOP rotasi password backup dan APP_KEY agar backup lama tetap bisa di‑restore dengan aman.

### P2 – Rendah (Maintainability & Hardening Lanjutan)

6. **Pin Versi Dependency**
   - Ganti versi `*` pada Composer ke versi eksplisit (misal `^4.0`).
   - Jadwalkan `security:audit-dependencies` berkala via ScheduledTask + review hasilnya di admin panel.

7. **Review Response Caching**
   - Verifikasi bahwa store cache yang dipakai (`file`/`redis`) aman untuk menyimpan object `Response`.
   - Pastikan tidak ada informasi sensitif yang ikut tercache di response publik (misalnya header auth, cookie khusus).

8. **Dokumentasi & Playbook**
   - Dokumenkan dengan jelas:
     - Role/permission minimal untuk fitur high‑risk (file manager, backup, scheduled tasks).
     - Prosedur incident response untuk case IP salah blok, DB restore, atau file system corruption akibat zip slip.

---

## 11. Penutup

Secara keseluruhan, **JA‑CMS** sudah dibangun dengan mindset keamanan dan operasional yang cukup matang: ada layer lockout, 2FA, audit log, analytics internal, backup & scheduler, hingga dependency audit. Titik paling penting ke depan adalah:
- Merapikan **trust boundary** (proxy & IP),
- Mengurangi attack surface di **file manager & backup**,
- Menjaga konfigurasi (queue/cache/redis) tetap konsisten antara kode, `.env`, dan infrastruktur.

Dengan beberapa hardening di atas, platform ini sangat layak dipakai sebagai **CMS high‑value** di environment produksi.

