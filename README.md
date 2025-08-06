# Proyek Bot PHP Modular dengan Dasbor Log yang Kuat

Ini adalah proyek dasar untuk bot PHP yang dirancang agar modular dan mudah di-host di lingkungan hosting bersama. Proyek ini dilengkapi dengan dasbor log yang kuat dan penuh fitur untuk memantau aktivitas bot secara real-time.

## Fitur Utama

*   **Bot Echo Sederhana**: Bot akan membalas setiap pesan teks yang diterimanya.
*   **Logika Modular**: Kode inti bot (`bot/BotHandler.php`) sepenuhnya terpisah dari kerangka kerja CodeIgniter, sehingga mudah untuk dikelola atau diintegrasikan di tempat lain.
*   **Backend CodeIgniter 3**: Menggunakan CodeIgniter 3 untuk menyediakan backend yang stabil, perutean, dan fungsionalitas dasbor.
*   **Dasbor Log Komprehensif**: Antarmuka web untuk melihat dan mengelola log bot.

---

## Dasbor Log yang Ditingkatkan

Dasbor log telah dirubah total untuk memberikan wawasan dan kontrol yang mendalam atas aktivitas bot.

### Fitur Dasbor

*   **Dasbor Statistik**: Kartu di bagian atas menampilkan statistik penting:
    *   **Total Log**: Jumlah total catatan log dalam database.
    *   **Log (24 Jam)**: Jumlah log yang dibuat dalam 24 jam terakhir.
    *   **Jumlah Berdasarkan Jenis**: Perincian log berdasarkan jenis (`incoming`, `outgoing`, `error`).
*   **Pemfilteran & Pencarian Lanjutan**: Anda dapat memfilter log secara dinamis berdasarkan:
    *   **Tipe Log**: Pilih dari menu dropdown.
    *   **ID Obrolan**: Filter berdasarkan ID obrolan yang tepat.
    *   **Nama Obrolan**: Cari berdasarkan nama pengguna atau judul grup (mendukung pencocokan sebagian).
    *   **Kata Kunci**: Cari teks tertentu di dalam pesan log.
*   **Paginasi**: Untuk menangani volume log yang besar secara efisien, dasbor secara otomatis memberi nomor halaman pada hasil, memastikan waktu muat yang cepat.
*   **Manajemen Log**:
    *   **Hapus Log Individual**: Setiap entri log memiliki tombol **Hapus** dengan dialog konfirmasi.
    *   **Hapus Semua Log**: Tombol **Hapus Semua** memungkinkan Anda untuk membersihkan seluruh riwayat log. Tindakan ini memerlukan konfirmasi untuk mencegah penghapusan yang tidak disengaja.
*   **Antarmuka Pengguna Modern**: UI telah didesain ulang sepenuhnya menggunakan **Bootstrap 5** untuk memberikan pengalaman yang bersih, modern, dan responsif di semua perangkat.
*   **Pesan JSON yang Diformat**: Jika pesan log adalah string JSON (seperti pembaruan mentah dari API bot), dasbor akan secara otomatis memformatnya agar mudah dibaca.

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