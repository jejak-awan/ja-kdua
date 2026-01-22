# 📖 JA-CMS User Guide

Panduan lengkap untuk content authors dan editors menggunakan JA-CMS.

## Daftar Isi

1. [Pendahuluan](#pendahuluan)
2. [Login & Dashboard](#login--dashboard)
3. [Membuat Konten](#membuat-konten)
4. [Mengelola Media](#mengelola-media)
5. [Kategori & Tags](#kategori--tags)
6. [Komentar](#komentar)
7. [Tips & Trik](#tips--trik)

---

## Pendahuluan

JA-CMS adalah Content Management System modern yang memudahkan Anda mengelola konten website. Panduan ini akan membantu Anda memahami fitur-fitur utama dan cara menggunakannya.

### Persyaratan

- Akun pengguna dengan role yang sesuai (Author/Editor/Admin)
- Browser modern (Chrome, Firefox, Safari, Edge)
- Koneksi internet

---

## Login & Dashboard

### Cara Login

1. Buka halaman login: `https://your-domain.com/login`
2. Masukkan **Email** dan **Password**
3. Klik tombol **Login**

![Login Page](https://via.placeholder.com/800x400?text=Login+Page)

### Dashboard Overview

Setelah login, Anda akan melihat Dashboard dengan:

- **Statistik**: Total konten, published posts, media files, users
- **Quick Actions**: Tombol pintas untuk aksi cepat
  - Buat Artikel
  - Buat Halaman
  - Upload Media
  - Kelola Kategori
  - Kelola Tag
  - Lihat Komentar
- **Recent Actions**: Histori aksi terakhir Anda

---

## Membuat Konten

### Membuat Artikel Baru

1. Klik **Quick Actions** → **Buat Artikel**
   *ATAU*
   Sidebar → **Konten** → **Buat Baru**

2. Isi form konten:
   - **Judul**: Masukkan judul artikel (wajib)
   - **Slug**: URL-friendly (otomatis dari judul)
   - **Konten**: Tulis konten menggunakan Rich Text Editor
   - **Excerpt**: Ringkasan artikel (optional)
   - **Featured Image**: Upload gambar utama
   - **Kategori**: Pilih kategori
   - **Tags**: Tambahkan tags untuk SEO

3. **Status Konten**:
   - **Draft**: Simpan sebagai draft (tidak tampil)
   - **Published**: Publish langsung
   - **Scheduled**: Jadwalkan publikasi

4. **SEO Settings** (optional):
   - Meta Title
   - Meta Description
   - Meta Keywords
   - OG Image (untuk social sharing)

5. Klik **Simpan** atau **Publish**

### Rich Text Editor

Editor mendukung:

- **Format Text**: Bold, Italic, Underline, Strike-through
- **Heading**: H1, H2, H3, H4, H5, H6
- **List**: Bullet list, numbered list
- **Link**: Insert/edit links
- **Image**: Upload dan embed images
- **Video**: Embed YouTube/Vimeo videos
- **Code**: Code blocks dengan syntax highlighting
- **Table**: Insert dan edit tables
- **Quote**: Blockquotes
- **Alignment**: Left, Center, Right, Justify

### Auto-Save

Konten otomatis di-save setiap **30 detik** untuk mencegah kehilangan data.

Indikator di kanan atas menunjukkan:
- ⏱️ "Auto-saving..." - Sedang menyimpan
- ✅ "Saved at 14:32" - Tersimpan
- ⚠️ "Not saved" - Belum tersimpan

### Content Revisions

Sistem menyimpan revisi setiap perubahan:

1. Buka konten yang sudah ada
2. Klik tab **Revisions**
3. Lihat daftar revisi dengan:
   - Tanggal & waktu
   - User yang mengubah
   - Note/keterangan perubahan
4. Klik **Restore** untuk kembalikan ke revisi tertentu

### Duplicate Content

Untuk duplikasi konten:
1. Buka konten
2. Klik **Duplicate** di toolbar
3. Edit judul dan slug (otomatis ditambah "-copy")
4. Simpan sebagai konten baru

---

## Mengelola Media

### Upload Media

#### Via Media Library

1. Sidebar → **Media**
2. Klik **Upload** atau drag & drop files
3. Pilih file (max 10MB per file)
4. Tunggu proses upload
5. Media siap digunakan

#### Via Content Editor

1. Saat menulis konten
2. Klik icon **Image** di toolbar
3. Pilih **Upload** atau **Media Library**
4. Pilih/upload gambar
5. Atur alignment dan size

### Jenis File yang Didukung

- **Gambar**: JPG, PNG, GIF, WebP, SVG
- **Dokumen**: PDF, DOC, DOCX
- **Video**: MP4, MOV (max 50MB)

### Organize Media

- **Filter**: By type, date uploaded
- **Search**: Cari berdasarkan nama file
- **View**: Grid view atau List view
- **Sort**: Name, Date, Size

### Edit Media Info

1. Klik media thumbnail
2. Edit:
   - **Alt Text**: Untuk SEO dan accessibility
   - **Title**: Judul media
   - **Caption**: Keterangan
   - **Description**: Deskripsi detail
3. Klik **Update**

### Bulk Operations

Select multiple media:
1. Checkbox pada media
2. Pilih action:
   - **Delete**: Hapus media (bulk)
   - **Download**: Download sebagai ZIP
   - **Move to folder**: (jika ada folder system)

---

## Kategori & Tags

### Membuat Kategori

1. Sidebar → **Kategori** → **Buat Baru**
2. Isi:
   - **Nama**: Nama kategori
   - **Slug**: URL-friendly
   - **Deskripsi**: Optional
   - **Parent**: Untuk sub-kategori
3. Klik **Simpan**

### Kategori Hierarki

Buat struktur kategori:

```
Blog
├── Technology
│   ├── Web Development
│   └── Mobile Apps
├── Lifestyle
└── Business
```

### Membuat Tags

1. Sidebar → **Tags** → **Buat Baru**
2. Isi Nama dan Slug
3. Klik **Simpan**

### Best Practices

✅ **DO**:
- Gunakan kategori untuk topik besar
- Gunakan tags untuk detail spesifik
- Max 5-7 tags per artikel
- Konsisten dalam penamaan

❌ **DON'T**:
- Jangan buat terlalu banyak kategori (max 10-15)
- Hindari duplikasi kategori dan tags
- Jangan gunakan kategori sebagai tags

---

## Komentar

### Moderasi Komentar

1. Sidebar → **Komentar**
2. Lihat daftar komentar:
   - **Pending**: Menunggu approval
   - **Approved**: Sudah di-approve
   - **Spam**: Ditandai spam
   - **Trash**: Sudah dihapus

### Actions

- **Approve**: Setujui komentar
- **Reject**: Tolak (masuk trash)
- **Mark as Spam**: Tandai sebagai spam
- **Reply**: Balas komentar langsung
- **Edit**: Edit isi komentar
- **Delete**: Hapus permanent

### Bulk Actions

1. Select multiple comments
2. Pilih action di dropdown
3. Klik **Apply**

---

## Tips & Trik

### 🚀 Productivity Tips

1. **Keyboard Shortcuts**:
   - `Ctrl/Cmd + S`: Save draft
   - `Ctrl/Cmd + B`: Bold
   - `Ctrl/Cmd + I`: Italic
   - `Ctrl/Cmd + K`: Insert link
   - `Ctrl/Cmd + Z`: Undo
   - `Ctrl/Cmd + Y`: Redo

2. **Quick Actions**: Gunakan Quick Actions di dashboard untuk akses cepat

3. **Auto-Save**: Jangan khawatir kehilangan data, auto-save aktif tiap 30 detik

4. **Revisions**: Manfaatkan revisions untuk tracking perubahan

5. **Bulk Edit**: Gunakan bulk operations untuk efisiensi

### ✏️ Writing Tips

1. **SEO Optimization**:
   - Gunakan heading structure yang benar (H1 → H2 → H3)
   - Tambahkan alt text pada gambar
   - Isi meta description (150-160 karakter)
   - Gunakan internal linking
   - Optimasi featured image (< 200KB, 1200x630px)

2. **Content Structure**:
   - Buat intro yang menarik
   - Gunakan subheading untuk readability
   - Paragraf pendek (3-4 baris)
   - Gunakan bullet points dan lists
   - Tambahkan call-to-action di akhir

3. **Formatting**:
   - **Bold** untuk emphasis
   - *Italic* untuk subtle emphasis
   - `Code` untuk technical terms
   - > Quotes untuk highlight
   - Links untuk references

### 🖼️ Media Best Practices

1. **Image Optimization**:
   - Compress sebelum upload (use TinyPNG)
   - Optimal size: 1200x800px untuk featured image
   - Format: WebP (recommended) atau JPG
   - Max file size: 200KB

2. **File Naming**:
   - Gunakan descriptive names
   - Lowercase, dash-separated
   - Good: `modern-web-design-2024.jpg`
   - Bad: `IMG_1234.JPG`

3. **Alt Text**:
   - Deskriptif dan concise
   - Include keywords (naturally)
   - Max 125 karakter

### 🔍 SEO Checklist

Sebelum publish, pastikan:

- [ ] Judul menarik dan mengandung keyword
- [ ] Meta description filled (150-160 chars)
- [ ] URL slug clean dan descriptive
- [ ] Featured image ada dan optimized
- [ ] Alt text pada semua gambar
- [ ] Minimal 1 internal link
- [ ] Kategori dan tags sudah dipilih
- [ ] Content > 500 kata (for blog)
- [ ] Heading structure correct (H1 → H2 → H3)

### 📱 Mobile Preview

Sebelum publish:
1. Click **Preview** button
2. Test di mobile view
3. Check:
   - Layout responsive
   - Images load properly
   - Text readable
   - Links clickable

### 🔐 Security Tips

1. **Password**:
   - Min 8 karakter
   - Include uppercase, lowercase, number
   - Change regularly (every 3-6 months)

2. **Session**:
   - Auto-logout setelah 8 jam inactive
   - Warning 5 menit sebelum logout
   - Extend session jika masih bekerja

3. **Login History**:
   - Check login history di Profile
   - Report suspicious activity
   - Enable 2FA jika tersedia

---

## Troubleshooting

### Content Tidak Tersimpan

1. Check internet connection
2. Reload page (auto-save akan restore)
3. Check browser console for errors
4. Try different browser

### Media Gagal Upload

1. Check file size (max 10MB)
2. Check file format (supported types)
3. Clear browser cache
4. Try compress image first

### Rich Text Editor Error

1. Refresh page
2. Clear browser cache
3. Disable browser extensions
4. Try incognito mode

### Slow Performance

1. Clear browser cache
2. Close unused tabs
3. Compress images before upload
4. Use lazy loading (auto-enabled)

---

## Support & Help

### Kontak

- **Email**: support@ja-cms.com
- **Documentation**: https://docs.ja-cms.com
- **Community Forum**: https://forum.ja-cms.com
- **Video Tutorials**: https://youtube.com/ja-cms

### Useful Resources

- [API Documentation](/docs/api)
- [Theme Customization](/docs/themes)
- [Plugin Development](/docs/plugins)
- [Best Practices](/docs/best-practices)

---

## Changelog

### v2.0.0 (2025-11-16)
- ✅ Custom error pages (404, 500, 403)
- ✅ Session timeout warning
- ✅ Breadcrumbs navigation
- ✅ Social share buttons
- ✅ Related posts section
- ✅ Quick actions widget
- ✅ Image lazy loading
- ✅ Auto-save drafts
- ✅ Content revisions

---

**Happy Writing! 🎉**

*Last updated: November 16, 2025*
