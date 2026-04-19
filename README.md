# Undangan Pernikahan Digital & Admin Dashboard (PHP Native)

Proyek ini adalah aplikasi web dinamis untuk Undangan Pernikahan Digital yang dilengkapi dengan Panel Admin (Dashboard) terintegrasi menggunakan **PHP Native** dan database **MySQL**. Proyek ini dibuat untuk memudahkan pengelolaan tamu, RSVP, dan konten undangan secara dinamis melalui antarmuka admin yang responsif dan dilengkapi dengan pembatasan hak akses (*Role-Based Access Control*).

## 🚀 Teknologi yang Digunakan

* **Backend:** PHP Native
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, CSS3, Vanilla JavaScript
* **Library/Asset Pendukung:** Tailwind CSS (CDN), FontAwesome (v6.4.0), Google Fonts (*Poppins* & *Great Vibes*).
* **Environment:** XAMPP / Laragon (Apache & MySQL)

## ✨ Fitur Utama

### 1. Halaman Undangan (Client-Side)
* **Cover Interaktif:** Halaman sampul dengan nama tamu yang dipersonalisasi.
* **Background Music:** Pemutar musik latar otomatis (autoplay) dilengkapi tombol putar/jeda.
* **Countdown Timer:** Penghitung waktu mundur menuju hari-H acara.
* **Buku Tamu & RSVP:** Form bagi tamu untuk mengonfirmasi kehadiran dan memberikan ucapan yang akan langsung tersimpan ke *database*.
* **Manajemen Konten Dinamis:** Teks, foto, dan informasi acara ditarik langsung dari *database*, bukan *hardcode*.

### 2. Panel Admin (Dashboard)
* **Sistem Login Fleksibel:** Mendukung login menggunakan *Username* ataupun *Email*, dilengkapi fitur "Show/Hide Password".
* **Role-Based Access Control (RBAC):** Pembatasan hak akses di mana halaman *User Management* hanya bisa diakses oleh **Admin**, sedangkan **User** biasa tidak bisa mengaksesnya.
* **Validasi Upload:** Fitur unggah media dilengkapi validasi ekstensi (hanya menerima file gambar/audio tertentu) dengan notifikasi *modal error*.
* **Manajemen RSVP & Tamu:** CRUD (Create, Read, Update, Delete) daftar tamu dan melihat konfirmasi kehadiran.
* **Manajemen Konten:** Memungkinkan admin mengubah teks dan gambar undangan secara langsung dari *dashboard*.
* **Layout Responsif:** Sidebar otomatis bersembunyi (off-canvas) pada layar *mobile* dan tabel dibungkus agar dapat digulir (*scrollable*).

## 📁 Struktur Direktori

```text
📂 undangan-praktikum-dpw-php
├── 📄 index.php              # Halaman utama undangan (menampilkan data dari DB)
├── 📄 login.php              # Halaman login & otentikasi
├── 📄 koneksi.php            # File konfigurasi koneksi ke database MySQL
├── 📄 README.md              # Dokumentasi proyek
├── 📂 assets                 # Direktori untuk menyimpan file gambar, audio, dll
└── 📂 dashboard              # Direktori panel admin (dilindungi sesi login)
    ├── 📄 content.php        # Halaman manajemen konten undangan
    ├── 📄 content-form.php   # Proses tambah/edit konten
    ├── 📄 guests.php         # Halaman daftar tamu undangan
    ├── 📄 rsvp.php           # Halaman daftar konfirmasi kehadiran
    ├── 📄 users.php          # Halaman manajemen admin (Khusus Role Admin)
    └── 📄 logout.php         # Proses penghancuran sesi (logout)
```

## 🛠️ Cara Menjalankan Program (XAMPP / Laragon)

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek secara lokal:

1. **Siapkan Web Server Lokal:**
   * Buka aplikasi **XAMPP** atau **Laragon** Anda.
   * Jalankan layanan **Apache** dan **MySQL**.
2. **Pindahkan Folder Proyek:**
   * Ekstrak atau *clone* proyek ini.
   * Pindahkan folder proyek ke direktori *document root* web server:
     * **XAMPP:** Pindahkan ke `C:\xampp\htdocs\nama-folder-proyek`
     * **Laragon:** Pindahkan ke `C:\laragon\www\nama-folder-proyek`
3. **Konfigurasi Database:**
   * Buka browser dan akses **phpMyAdmin** (biasanya `http://localhost/phpmyadmin`).
   * Buat *database* baru dengan nama: **`undangan_db`**.
   * Eksekusi (*Run*) kode SQL Sample yang ada di bagian bawah dokumen ini, atau *import* file `undangan_db.sql` jika tersedia di dalam folder proyek.
4. **Cek Koneksi Database (Opsional):**
   * Buka file `koneksi.php` menggunakan *code editor*. Pastikan kredensialnya sudah sesuai dengan pengaturan lokal Anda (biasanya `user = "root"` dan `password = ""`).
5. **Akses Aplikasi di Browser:**
   * **Halaman Undangan Utama:** Akses URL `http://localhost/nama-folder-proyek/index.php`
   * **Halaman Login Admin:** Akses URL `http://localhost/nama-folder-proyek/login.php`
6. **Data Login Default (Administrator):**
   * **Email/Username:** `admin@undangan.com` atau `admin`
   * **Password:** `123456`


## 🗄️ Sample Database (SQL)

Berikut adalah sampel *script* SQL untuk membangun struktur tabel dasar beserta data akun *default* agar Anda bisa langsung *login*. Jalankan *script* ini di menu "SQL" pada phpMyAdmin Anda di dalam *database* `undangan_db`.

```sql
-- Buat struktur tabel users
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert 1 data admin default (Password: 123456 - MD5 Hash)
INSERT INTO `users` (`nama`, `username`, `email`, `password`, `role`) VALUES
('Admin Utama', 'admin', 'admin@undangan.com', 'e10adc3949ba59abbe56e057f20f883e', 'admin');

-- Buat struktur tabel contents (untuk konten dinamis)
CREATE TABLE `contents` (
  `id_key` varchar(50) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `tipe` varchar(20) NOT NULL,
  `bagian` varchar(100) NOT NULL,
  `isi` text NOT NULL,
  PRIMARY KEY (`id_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Buat struktur tabel guests
CREATE TABLE `guests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Buat struktur tabel rsvps
CREATE TABLE `rsvps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `status` enum('Hadir','Tidak Hadir','Ragu-ragu') NOT NULL,
  `pesan` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```