# AGROCOM - Backend & Admin Panel (Laravel + MySQL)

Aplikasi manajemen kebun cabai terpadu untuk monitoring kondisi kebun, rekap absensi berbasis GPS Geofencing, laporan hama/penyakit, dan integrasi penyimpanan foto ke **Google Drive**.

---

## 🚀 Konfigurasi Database (MySQL)
Konfigurasi database di [.env]
sudah terpasang:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Database `agro` sudah dibuat dan dimigrasi lengkap dengan seeder awal.

---

## ☁️ Konfigurasi Penyimpanan Google Drive
Aplikasi menggunakan **Google API Client** (`google/apiclient`) melalui service [GoogleDriveService.php].

Tambahkan kredensial OAuth2 Google Drive Anda di file `.env`:
```env
STORAGE_DRIVER=google
GOOGLE_DRIVE_CLIENT_ID=your_client_id_here
GOOGLE_DRIVE_CLIENT_SECRET=your_client_secret_here
GOOGLE_DRIVE_REFRESH_TOKEN=your_refresh_token_here
GOOGLE_DRIVE_FOLDER_ID=your_folder_id_here
```
> **Catatan Penting:** Jika kredensial Google Drive di atas belum diisi, sistem secara otomatis mengaktifkan **fallback storage lokal** (`storage/app/public`) sehingga aplikasi tetap berjalan 100% normal tanpa error saat Anda mencoba upload foto selfie atau laporan kebun.

---

## 👤 Akun Demo Login
- **Admin (Pemilik Kebun)**:
  - Username: `admin`
  - Password: `123456`
- **Pekerja Lapangan**:
  - Username: `andi`
  - Password: `123456`

---

## 🏃 Cara Menjalankan Server Laravel
Buka terminal dan jalankan:
```bash
cd 
php artisan serve --host=0.0.0.0 --port=8000
```
Akses web admin di browser: [http://localhost:8000/admin](http://localhost:8000/admin)

---

## 📡 Daftar Endpoint REST API Mobile
- `POST /api/login` - Login pekerja & admin
- `GET /api/me` - Profil pekerja & status absensi hari ini
- `POST /api/absen/masuk` - Absen selfie pagi + validasi GPS Geofencing (radius $\le 50$ meter)
- `POST /api/absen/pulang` - Absen selfie sore + status pekerjaan (selesai/sebagian/belum)
- `GET /api/tugas` - Checklist 8 tugas harian
- `POST /api/tugas/toggle/{id}` - Centang tugas selesai
- `POST /api/pemeriksaan` - Catat kondisi daun, batang, bunga, buah + foto
- `POST /api/laporan-masalah` - Laporkan hama/penyakit per blok lahan + foto
- `POST /api/laporan-harian` - Rangkuman kegiatan harian + Foto Sebelum & Sesudah
- `GET /api/monitoring-kebun` - Status blok kebun (Normal / Perhatian / Masalah)
- `GET /api/rekap-laporan` - Rekapitulasi kehadiran & masalah
