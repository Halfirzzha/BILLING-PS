# Billing PS5 + Android TV — Master Architecture & Rebuild Design

**Tanggal:** 2026-07-01
**Status:** Design (menunggu review user)
**Tipe:** Rebuild from scratch — platform operasional rental PS5 + Android TV kelas production.

---

## 1. Ringkasan & keputusan kunci

Platform SaaS multi-outlet untuk rental PlayStation 5 + Android TV. Inti sistem adalah
**saldo waktu bermain (time balance)** per member yang bersifat global lintas outlet.
Member scan QR di station, login/daftar, lalu bermain memakai saldo waktu; saat waktu
habis sesi berhenti otomatis dan TV kembali menampilkan QR.

| Aspek | Keputusan |
|---|---|
| Pendekatan | Rebuild from scratch (arsitektur bersih) |
| Stack | Laravel 13 + Filament 4 + Livewire 3 + Laravel Reverb (WebSocket) |
| Database | MySQL/PostgreSQL (produksi), single-DB row-level multi-tenancy |
| Deployment | Cloud, **multi-outlet SaaS** (multi-tenant) + outlet agent lokal untuk ADB |
| Android TV | **Native Android TV app (Kotlin, Android Studio)** + ADB untuk orchestration/recovery |
| Pembayaran | Cash (operator) **+** payment gateway online (lapisan abstrak, provider TBD) |
| Overage | **Auto-stop** saat saldo waktu mencapai 0 (prepaid murni, tanpa utang) |
| Saldo | **Global** per member (wallet & waktu) — dapat dipakai di outlet mana saja |
| Roles | Developer (god mode), Super Admin (semua outlet), Operator/Kasir (1 outlet), Member |

---

## 2. Peta komponen

```
                    ┌─────────────────────────────────────────────┐
                    │              CLOUD (Laravel 13)              │
   Member ────────► │  Member Portal (Livewire)                    │
   (browser/HP)     │  Payment webhooks (gateway abstrak)          │
   Operator ──────► │  Admin Panel (Filament 4, multi-tenant)      │
   Super Admin ───► │   - Kasir · station monitor · laporan        │
   Developer ─────► │   - God-mode / dev panel                     │
                    │  Domain core:                                │
                    │   Wallet · TimeBalance · Session · Billing   │
                    │   Ledger (uang & waktu) · Station lifecycle   │
                    │  Realtime: Reverb (WS) · Device API (REST)   │
                    └───────┬──────────────────────┬───────────────┘
                            │ WS / REST             │ REST (device token)
              ┌─────────────▼──────┐      ┌─────────▼───────────────┐
              │  OUTLET AGENT       │      │  ANDROID TV APP (native)│
              │  (per outlet, lokal)│─ADB─►│  per station            │
              │  - eksekusi ADB     │      │  - idle: tampil QR      │
              │  - wake/reboot/relau│      │  - aktif: info + count  │
              │  - poll command Q   │      │  - WS push + poll API   │
              └─────────────────────┘      └─────────────────────────┘
```

---

## 3. Multi-tenancy

- **Single database, row-level tenancy.** Entitas milik-outlet punya kolom `outlet_id`
  + global scope + Filament tenancy. Member, wallet, saldo waktu **tidak** ber-`outlet_id`
  (global).
- **Alasan:** jauh lebih sederhana daripada database-per-tenant; laporan lintas outlet
  mudah; tetap aman lewat scoping + policy. Skala ke banyak cabang tanpa migrasi besar.
- **Scope role:** Operator terikat 1 outlet (`users.outlet_id`); Super Admin & Developer
  akses semua outlet; Member global (tanpa outlet).

---

## 4. Data model

Semua uang disimpan sebagai **integer rupiah** (tanpa desimal). Semua waktu sebagai
**integer menit**. Saldo **selalu diturunkan dari ledger** (sumber kebenaran); snapshot
boleh di-cache untuk performa.

### 4.1 Tenancy & identitas
- **`outlets`** — `id, name, code(unique), slug, timezone, address, phone, is_active, settings(json), timestamps`
- **`users`** — `id, name, email(unique), phone, password, member_code(unique,null utk staff), outlet_id(FK,null), is_active, last_login_at, timestamps`. Role via spatie/permission.
- spatie tables: `roles, permissions, model_has_roles, model_has_permissions, role_has_permissions`.

### 4.2 Uang & waktu (inti)
- **`time_packages`** — `id, outlet_id(FK), name, minutes, price, is_active, sort, timestamps`. Per-outlet; menit yang dikreditkan bersifat global.
- **`wallet_transactions`** (ledger UANG) — `id, user_id(FK), outlet_id(FK,null), operator_id(FK,null), type(topup|time_purchase|cash_sale|refund|adjustment), payment_method(cash|wallet|gateway), amount(bigint signed), affects_balance(bool), reference(unique), gateway_ref(null), notes, meta(json), timestamps`.
  - **Wallet balance = Σ amount WHERE affects_balance = true.**
- **`time_ledger_entries`** (ledger WAKTU) — `id, user_id(FK), outlet_id(FK,null), operator_id(FK,null), time_package_id(FK,null), play_session_id(FK,null), type(credit|session_debit|adjustment|expiry), minutes(int signed), notes, meta(json), timestamps`.
  - **Time balance = Σ minutes.**
- **`payments`** (top-up online) — `id, user_id(FK), amount, provider, provider_ref(null), status(pending|paid|expired|failed), paid_at(null), wallet_transaction_id(FK,null), payload(json), timestamps`.

### 4.3 Operasional station
- **`stations`** — `id, outlet_id(FK), code(unique), name, status(idle|active|maintenance), is_active, qr_token(unique), device_token(unique,hashed), adb_identifier(null), app_mode, current_session_id(FK,null), last_heartbeat_at(null), timestamps`.
- **`play_sessions`** — `id, outlet_id(FK), station_id(FK), user_id(FK), status(active|completed|cancelled), started_at, planned_end_at, ended_at(null), started_with_minutes, consumed_minutes(default 0), minutes_debited(default 0), ended_by(FK,null), notes(null), timestamps`.
- **`station_commands`** (queue perintah device) — `id, outlet_id(FK), station_id(FK), type(wake|relaunch_app|reboot|refresh_state|custom_adb), payload(json), status(pending|dispatched|acknowledged|failed), dispatched_at(null), acknowledged_at(null), error(null), created_by(FK,null), timestamps`.

### 4.4 Aturan integritas
- Saldo **tidak pernah** diedit langsung — hanya lewat entri ledger baru (koreksi = `adjustment`).
- Semua mutasi uang+waktu dibungkus satu `DB::transaction`.
- Operasi kritikal (start/end session, purchase, webhook) **idempotent**.

---

## 5. Roles & akses

| Role | Akses |
|---|---|
| **Developer** | God mode. `Gate::before ⇒ true`. Semua outlet & resource + dev panel (impersonate member, ledger explorer, command runner, log/health viewer). |
| **Super Admin** | Semua outlet. Kelola outlet, staf, paket, member, laporan. Tanpa tools dev berbahaya. |
| **Operator/Kasir** | Scoped 1 outlet (Filament tenancy). Daftar member, top-up cash, jual paket, mulai/stop sesi, monitor station outlet-nya, transaksi outlet-nya. |
| **Member** | Member portal saja (guard terpisah). Wallet, saldo waktu, beli paket, top-up online, join station via QR, riwayat. |

Implementasi: spatie roles + Filament policies + Filament tenancy scoping + `Gate::before`
untuk Developer. Guard `web` (admin/staff) terpisah dari guard member portal.

---

## 6. Alur domain inti

### 6.1 Flow bermain (jantung sistem)
1. Station idle → TV app menampilkan **QR** (encode `qr_token` + station/outlet).
2. Member scan → halaman join portal. Belum login → login/daftar. Daftar = buat user +
   role member + `member_code`, wallet 0, waktu 0.
3. Cek saldo waktu: **>0 → tombol "Mulai main"**; **=0 → wajib beli paket** (bayar wallet /
   top-up / minta kasir cash).
4. Start session → validasi (station idle & aktif, member tak punya sesi aktif, waktu>0) →
   buat `play_session` active, `planned_end_at = now + remaining_minutes`, station→active,
   command TV `refresh_state`/push Reverb, jadwalkan auto-stop.
5. Saat main → TV app menampilkan countdown; sisa waktu ter-update realtime.
6. **Auto-stop di 0** (atau di-stop operator/member) → debit `session_debit` = menit
   terpakai, station→idle, TV→QR, event Reverb.
7. **Extend saat main**: member beli waktu lagi saat sesi aktif → `planned_end_at`
   diperpanjang otomatis, auto-stop dijadwal ulang.

### 6.2 Auto-stop (2 lapis, anti-gagal)
- (a) Delayed job dijadwalkan tepat di `planned_end_at`.
- (b) Scheduler tiap menit menyapu sesi aktif yang `planned_end_at <= now`.
- Redundan agar tidak ada sesi yang "lolos" walau queue/worker sempat mati.

### 6.3 Billing
- Top-up (cash oleh operator / gateway online) → entri ledger uang (`topup`).
- Beli paket:
  - **via wallet**: debit uang (`time_purchase`, affects_balance) + credit waktu (`credit`).
  - **via cash**: catat `cash_sale` (affects_balance=false) + credit waktu (`credit`).
- Semua atomic dalam satu transaksi DB.

---

## 7. Device orchestration (Android TV + ADB)

- **Device API (REST, auth header `X-Station-Token`):**
  - `POST /api/device/stations/{code}/heartbeat` — update `last_heartbeat_at`, balikkan state ringkas.
  - `GET /api/device/stations/{code}/state` — presentasi kini (idle+QR payload / active+session+sisa menit).
  - `GET /api/device/stations/{code}/commands/next` — command pending berikutnya.
  - `POST /api/device/stations/{code}/commands/{id}/ack` — ack sukses/gagal.
- **Native Android TV app (Kotlin):** register via device_token → subscribe **Reverb WS**
  (push instan start/stop) dengan **fallback polling** device API. Render QR saat idle,
  info sesi + countdown saat aktif; eksekusi command (refresh/relaunch).
- **Outlet ADB agent (daemon lokal per outlet):** connect ke cloud, poll command tipe ADB
  untuk station outlet-nya, eksekusi `adb -s <adb_identifier>` untuk **recovery/manajemen
  device** (wake, relaunch app, reboot). Rendering ditangani app native; ADB = pemulihan
  saat TV nyangkut.

---

## 8. Real-time monitoring & keamanan

- **Reverb (WebSocket)**, channel privat per outlet `outlet.{id}.stations`. Event:
  `SessionStarted/Ended`, `StationStatusChanged`, `HeartbeatReceived`, `LowTimeWarning`,
  `DeviceOffline`.
- **Monitor page** (Filament/Livewire): grid station live per outlet —
  idle/active/maintenance/**offline** (heartbeat basi), countdown sesi, aksi cepat
  (stop, wake, maintenance).
- **Keamanan:** device token per-station (rotatable, disimpan hashed) + rate limit; guard
  member terpisah dari admin; webhook gateway verifikasi signature + idempotent; policy +
  tenancy scoping ketat; audit trail lewat ledger.

---

## 9. Roadmap (decomposition)

Project terlalu besar untuk satu spec/implementasi. Dibangun sebagai **sub-project
berurutan**, tiap fase punya spec → plan → implementasi + validasi sendiri.

| Fase | Cakupan | Output |
|---|---|---|
| **1. Fondasi** | Skeleton Laravel bersih, multi-tenant, auth 4 role, data model inti + migrations, model + enum, seeders, factory, test dasar. | Basis kode berjalan + `migrate:fresh --seed`. |
| **2. Wallet + Waktu + Paket + Ledger** | Engine uang & waktu (BillingService, double ledger, top-up cash, beli paket wallet/cash), derivasi saldo, test. | Domain billing teruji. |
| **3. Session + Station lifecycle + Auto-stop** | Start/end session, potong waktu, planned_end_at, auto-stop 2 lapis, extend saat main. | Session engine teruji. |
| **4. Admin/Operator panel (Filament)** | Resource CRUD tenant-scoped per role, kasir workflow, laporan, dev panel. | Panel operasional. |
| **5. Member portal + QR join** | Registrasi, login guard member, join via QR, beli paket, mulai/akhiri sesi, riwayat. | Portal member. |
| **6. Device orchestration** | Device API + command queue + Outlet ADB agent (artisan daemon). | Kontrol device. |
| **7. Native Android TV app** | App Kotlin (Android Studio): QR idle, sesi aktif+countdown, WS+poll, eksekusi command. | APK TV. |
| **8. Realtime + Payment gateway online** | Reverb events + monitor live; integrasi gateway (abstrak) + webhook. | Monitoring + top-up online. |

**Kita spec + bangun Fase 1 dulu sampai solid, lalu lanjut fase berikutnya.**

---

## 10. Non-goals (YAGNI untuk sekarang)

- Database-per-tenant / sharding.
- Loyalty/points, promo/voucher kompleks (bisa fase lanjutan).
- Multi-currency (rupiah saja).
- Aplikasi mobile member native (portal web dulu).
- Integrasi hardware selain Android TV + ADB.

---

## 11. Risiko & mitigasi

| Risiko | Mitigasi |
|---|---|
| Sesi tidak ter-auto-stop (worker mati) | Auto-stop 2 lapis (delayed job + scheduler sweep). |
| Cloud tak bisa akses TV di jaringan lokal | Outlet agent lokal poll command + eksekusi ADB. |
| Race double-start / double-debit | DB transaction + lock + idempotency + unique constraint sesi aktif. |
| Saldo tidak konsisten | Saldo derived dari ledger; tak ada edit langsung; koreksi via adjustment. |
| Webhook pembayaran ganda | Idempotent by provider_ref + verifikasi signature. |
| Device token bocor | Token hashed, rotatable, rate limit, per-station. |
