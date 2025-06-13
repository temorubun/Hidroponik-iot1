# Hidroponik-iot1

Sistem monitoring dan kontrol hidroponik berbasis IoT menggunakan ESP32 dan Laravel.

## Instalasi Dependensi

Jalankan perintah berikut setelah mengkloning repositori:

```bash
composer install
npm install
```

Pastikan `npm` tersedia. File `package.json` tidak disertakan di repositori sehingga Anda perlu menyesuaikan sendiri apabila menggunakan integrasi front‑end.

## Persiapan Lingkungan

Salin berkas contoh konfigurasi dan buat kunci aplikasi:

```bash
cp .env.example .env
php artisan key:generate
```

Beberapa variabel penting yang harus diisi pada berkas `.env`:

- `APP_KEY` – dihasilkan dengan perintah di atas.
- `TELEGRAM_BOT_TOKEN` – token bot untuk notifikasi.
- Konfigurasi database (misal `DB_CONNECTION`, `DB_DATABASE`).

## Migrasi dan Seeder

Untuk menyiapkan basis data jalankan migrasi dan seeder:

```bash
php artisan migrate --seed
```

## Menjalankan WebSocket Server

Aplikasi ini menggunakan Workerman untuk WebSocket. Jalankan server dengan:

```bash
php artisan websocket:serve
```

Implementasi lama `App\WebSocket\WebSocketServer` berbasis Ratchet sudah tidak digunakan lagi dan telah dihapus.

## Menjalankan Scheduler

Gunakan perintah berikut untuk menjalankan tugas terjadwal secara terus-menerus:

```bash
php artisan schedule:work
```

## Konfigurasi Telegram

Agar notifikasi Telegram berfungsi, setel variabel `TELEGRAM_BOT_TOKEN` pada file `.env` dengan token bot Anda.

## Menjalankan Test

Gunakan perintah berikut untuk menjalankan seluruh test:

```bash
php artisan test
```
