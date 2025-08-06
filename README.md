# Proyek Bot PHP Modular dengan Dasbor Log yang Kuat

Ini adalah proyek dasar untuk bot PHP yang dirancang agar modular dan mudah di-host di lingkungan hosting bersama. Proyek ini dilengkapi dengan dasbor log yang kuat dan penuh fitur untuk memantau aktivitas bot secara real-time.

## Fitur Utama

*   **Bot Echo Sederhana**: Bot akan membalas setiap pesan teks yang diterimanya.
*   **Logika Modular**: Kode inti bot (`bot/BotHandler.php`) sepenuhnya terpisah dari kerangka kerja CodeIgniter, sehingga mudah untuk dikelola atau diintegrasikan di tempat lain.
*   **Backend CodeIgniter 3**: Menggunakan CodeIgniter 3 untuk menyediakan backend yang stabil, perutean, dan fungsionalitas dasbor.
*   **Dasbor Log Komprehensif**: Antarmuka web untuk melihat dan mengelola log bot.

---

## Dasbor Log yang Ditingkatkan: Fitur Lengkap

Dasbor log telah dirubah total untuk memberikan wawasan dan kontrol yang mendalam atas aktivitas bot. Berikut adalah rincian lengkap dari fitur-fiturnya:

### Detail Fitur Dasbor yang Ditingkatkan

*   **Panel Statistik Real-time:**
    *   **Total Log Tercatat:** Agregasi dari semua log yang pernah disimpan.
    *   **Aktivitas 24 Jam Terakhir:** Hitungan log yang masuk dalam 24 jam terakhir untuk memantau aktivitas terkini.
    *   **Analisis Tipe Log:** Rincian kuantitatif untuk setiap tipe log (`incoming`, `outgoing`, `error`) untuk identifikasi cepat tren atau masalah.

*   **Sistem Pemfilteran Multi-dimensi:**
    *   **Filter berdasarkan Tipe Log:** Dropdown untuk mengisolasi log berdasarkan kategorinya.
    *   **Filter berdasarkan ID Obrolan Pengguna/Grup:** Input teks untuk melacak interaksi dengan pengguna atau grup tertentu.
    *   **Pencarian Nama Obrolan:** Cari berdasarkan nama pengguna (misalnya, "John Doe") atau nama grup (misalnya, "Proyek Tim") dengan pencocokan parsial.
    *   **Pencarian Kata Kunci dalam Pesan:** Pencarian teks lengkap di dalam konten pesan log untuk menemukan interaksi atau kesalahan tertentu.

*   **Manajemen Data & Kinerja:**
    *   **Paginasi Cerdas:** Secara otomatis membagi kumpulan data log yang besar menjadi halaman-halaman untuk mencegah kelambatan dan memastikan UI tetap responsif.
    *   **Tombol Reset Filter:** Tombol sekali klik untuk menghapus semua kriteria filter dan kembali ke tampilan default.

*   **Kontrol Manajemen Log:**
    *   **Penghapusan Log Individual:** Setiap baris log memiliki tombol "Hapus" dengan dialog konfirmasi (`"Yakin ingin menghapus log ini?"`) untuk mencegah penghapusan yang tidak disengaja.
    *   **Pembersihan Seluruh Log:** Tombol "Hapus Semua" yang ditempatkan secara mencolok untuk menghapus *semua* data log dari tabel. Tindakan ini dilindungi oleh dialog konfirmasi yang lebih tegas.

*   **Peningkatan Antarmuka Pengguna (UI/UX):**
    *   **Desain Responsif dengan Bootstrap 5:** Tampilan dan nuansa modern yang beradaptasi dengan mulus ke desktop, tablet, dan perangkat seluler.
    *   **Indikator Visual (Badge):** Tipe log ditandai dengan lencana berkode warna (hijau untuk `incoming`, biru untuk `outgoing`, merah untuk `error`) untuk pemindaian visual yang cepat.
    *   **Pretty-Printing JSON:** Pesan log yang berisi data JSON secara otomatis diformat dengan indentasi dan penyorotan sintaks untuk keterbacaan maksimal, sangat berguna untuk debugging payload API.

---

## Instruksi untuk Pengembang

### **PENTING: Menjalankan Migrasi Database**

Pembaruan terbaru menambahkan kolom `chat_id` dan `chat_name` ke tabel `bot_logs` untuk memungkinkan pemfilteran yang ditingkatkan. Anda **harus menjalankan migrasi database** agar dasbor berfungsi dengan benar.

Sebuah file migrasi baru telah dibuat di:
`application/migrations/20250806142800_add_chat_info_to_bot_logs.php`

Untuk menjalankan migrasi ini, cukup akses URL berikut di browser Anda saat server pengembangan Anda berjalan:
```
http://localhost/index.php/migrate
```
Anda akan melihat pesan "Migrations ran successfully!" jika berhasil.

### Detail Teknis

Perubahan utama dilakukan pada file-file berikut:

*   `application/controllers/Dashboard.php`: Diperbarui untuk menangani logika pemfilteran, paginasi, statistik, dan penghapusan.
*   `application/models/Log_model.php`: Diperluas secara signifikan dengan metode untuk kueri yang kompleks, penghitungan statistik, dan manajemen data.
*   `application/views/log_view.php`: Didesain ulang sepenuhnya dengan Bootstrap dan PHP untuk membangun UI dinamis yang baru.
*   `application/config/routes.php`: Menambahkan rute baru untuk tindakan `delete` dan `clear_logs`.
*   `bot/BotHandler.php`: Dimodifikasi untuk menangkap dan meneruskan `chat_id` dan `chat_name` ke `Log_model` saat membuat log.

### Dependensi

*   Tidak ada dependensi PHP baru yang ditambahkan.
*   Dasbor menggunakan **Bootstrap 5** dari CDN, sehingga tidak memerlukan instalasi file CSS atau JS lokal.

---

## Pengaturan Awal

1.  **Kloning Repositori**:
    ```bash
    git clone https://github.com/username/repo-name.git
    cd repo-name
    ```
2.  **Konfigurasi Lingkungan**:
    *   Salin `.env.example` menjadi `.env`.
    *   Buka file `.env` dan atur `TELEGRAM_BOT_TOKEN` dan `TELEGRAM_WEBHOOK_URL` Anda.
3.  **Instal Dependensi**:
    *   Jalankan `composer install` untuk menginstal pustaka PHP yang diperlukan.
4.  **Atur Webhook**:
    *   Arahkan webhook bot Telegram Anda ke URL yang ditentukan dalam file `.env` Anda. Pastikan itu menunjuk ke `index.php/bot`.
5.  **Izin Direktori**:
    *   Pastikan direktori `application/cache` dan `application/logs` dapat ditulis oleh server web Anda.
    ```bash
    chmod -R 777 application/cache
    chmod -R 777 application/logs
    ```
6.  **Jalankan Migrasi Awal**:
    *   Akses `http://localhost/index.php/migrate` untuk membuat tabel `bot_logs` dan `settings` awal, serta menambahkan kolom baru.