# Hidroponik-iot1

Sistem monitoring dan kontrol hidroponik berbasis IoT menggunakan ESP32 dan Laravel.

## Setup Lingkungan
1. Instal dependensi PHP dan JavaScript:
   ```bash
   composer install
   npm install
   ```
2. Salin berkas contoh env dan buat key aplikasi:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Atur konfigurasi database serta kredensial Telegram dan WebSocket di `.env`:
   ```env
   DB_CONNECTION=mysql # atau sqlite
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hidroponik
   DB_USERNAME=root
   DB_PASSWORD=

   TELEGRAM_BOT_TOKEN=your_bot_token
   TELEGRAM_DEFAULT_CHAT_ID=your_chat_id

   WEBSOCKET_PORT=6001
   WEBSOCKET_HOST=0.0.0.0
   ```
4. Jika memakai SQLite jalankan `touch database/database.sqlite` untuk membuat berkas database.

## Migrasi Database
Jalankan perintah berikut untuk membuat tabel dan data awal:
```bash
php artisan migrate --seed
```

## Menjalankan Scheduler
Scheduler menjalankan perintah `schedule:process-pins` setiap menit. Gunakan:
```bash
php artisan schedule:work
```

## Menjalankan WebSocket Server
Server WebSocket dapat dijalankan dengan:
```bash
php artisan websocket:serve --port=${WEBSOCKET_PORT}
```
Port dan host dapat diatur melalui variabel `.env` seperti yang disebutkan di atas.

## Contoh Penggunaan API
- **Update status pin perangkat**
  ```bash
  curl -X POST http://localhost/api/devices/status \
       -H "Content-Type: application/json" \
       -d '{"device_key":"DEVICE_KEY","is_active":true,"pins":[{"pin_number":1,"value":1}]}'
  ```
- **Kirim data sensor**
  ```bash
  curl -X POST http://localhost/api/sensor-data \
       -H "X-Device-Key: DEVICE_KEY" \
       -d "temperature=25&humidity=70"
  ```
- **Stream data sensor**
  ```bash
  curl http://localhost/api/sensor-stream?device_id=1
  ```
