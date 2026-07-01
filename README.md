# Billing PS5

Platform billing rental PS5 + Android TV berbasis Laravel dan Filament.

## Fitur inti

- Member dengan `member_code`
- Wallet top-up
- Time balance / paket waktu
- QR per station
- Session billing per station
- Dashboard admin Filament
- Role/permission via Shield
- Device orchestration untuk Android TV
- Command queue untuk ADB automation

## URL utama

- Home: `/`
- Portal member: `/portal`
- Admin: `/admin/login`
- TV display: `/tv/{station_code}`
- Device API:
  - `POST /api/device/stations/{station_code}/heartbeat`
  - `GET /api/device/stations/{station_code}/commands/next`
  - `POST /api/device/stations/{station_code}/commands/{id}/acknowledge`

## Akun demo

- Admin: `admin@billingps5.local` / `password`
- Member: `member@billingps5.local` / `password`

## Menjalankan aplikasi

```bash
composer install
npm install
php artisan migrate:fresh --seed
npm run build
php artisan serve --host=127.0.0.1 --port=8001
```

## Menjalankan processor ADB

```bash
php artisan stations:process-commands
```

Perintah ini membaca command queue station dan mengeksekusi `adb` ke Android TV sesuai `adb_identifier` pada station.

## Konfigurasi ADB

Set `.env`:

```env
ADB_ENABLED=true
ADB_BINARY=adb
ADB_BROWSER_PACKAGE=com.android.chrome
APP_URL=http://127.0.0.1:8001
```

## Contoh integrasi Android TV client

Header yang dipakai device:

```http
X-Station-Token: {device_token_station}
```

Flow device:

1. Kirim heartbeat.
2. Poll command berikutnya dari endpoint `commands/next`.
3. Jalankan command di app Android TV atau daemon lokal.
4. Kirim acknowledgement sukses/gagal.

## Catatan arsitektur

- User bermain memakai `time balance`.
- Saldo waktu dicatat di `time_ledger_entries`.
- Saldo uang dicatat di `wallet_transactions`.
- TV Android bisa dikontrol oleh:
  - Android TV app yang poll API device
  - daemon lokal yang menjalankan `php artisan stations:process-commands`
