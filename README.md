# Hidroponik-iot1

Sistem monitoring dan kontrol hidroponik berbasis IoT menggunakan ESP32 dan Laravel.

## Menjalankan WebSocket Server

Aplikasi ini menggunakan Workerman untuk WebSocket. Jalankan server dengan:

```bash
php artisan websocket:serve
```

Implementasi lama `App\WebSocket\WebSocketServer` berbasis Ratchet sudah tidak digunakan lagi dan telah dihapus.

## Konfigurasi Telegram

Agar notifikasi Telegram berfungsi, setel variabel `TELEGRAM_BOT_TOKEN` pada file `.env` dengan token bot Anda.
