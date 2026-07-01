# Billing PS5 — Android TV App

Native Android TV kiosk client (Kotlin + Jetpack Compose). Saat station idle
menampilkan **QR** untuk join; saat sesi aktif menampilkan **info sesi + countdown**.
Data diambil dengan polling ke Device API backend.

## Cara pakai

1. Buka folder `android-tv/` di **Android Studio** (Giraffe+/Koala+). Biarkan
   Android Studio menyiapkan Gradle wrapper & sync.
2. Edit `app/src/main/java/com/billingps5/tv/Config.kt`:
   - `BASE_URL` — alamat server Billing PS5 (akhiri dengan `/`).
     - Emulator → host: `http://10.0.2.2/`
     - TV fisik di LAN: `http://<IP-server>/`
   - `STATION_CODE` & `STATION_TOKEN` — dari Admin panel → Stations
     (`code` dan `device_token` tiap station).
3. Run ke perangkat/emulator Android TV (API 26+).

## Yang dilakukan app

- Polling `POST /api/device/stations/{code}/heartbeat` tiap 5 detik
  (sekaligus menandai TV online) dan me-render `state`:
  - `mode = "qr"` → tampilkan QR dari `qr.join_url`.
  - `mode = "session"` → tampilkan member + countdown dari `session.planned_end_at`.
- Header auth: `X-Station-Token`.

## Recovery via ADB (opsional)

Backend bisa mengirim command device (wake/relaunch/reboot) yang dijalankan
oleh daemon `php artisan stations:process-commands` di sisi outlet. App ini
fokus rendering; command level-perangkat ditangani ADB agent.

## Catatan

- Scaffold ini belum di-compile otomatis; saat pertama sync, Android Studio
  mungkin menyarankan update versi plugin/SDK — ikuti sarannya bila muncul.
- Package aplikasi `com.billingps5.tv` cocok dengan `ADB_APP_PACKAGE` di backend
  (untuk relaunch via ADB).
