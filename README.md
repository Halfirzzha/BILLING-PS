# Billing PS5 + Android TV

Platform SaaS multi-outlet untuk rental PlayStation 5 + Android TV, berbasis Laravel 13 + Filament 4.
Inti sistem adalah **saldo waktu bermain (time balance)** per member yang bersifat global lintas outlet.

> Rebuild bertahap. Dokumen desain & rencana ada di `docs/superpowers/`.
> Fase 1 (Fondasi) sudah selesai; fase berikutnya menyusul.

## Konsep inti

- **Multi-tenant**: banyak outlet dikelola terpusat (single DB, row-level `outlet_id`).
- **Wallet (uang)** & **Time balance (menit)** — keduanya global per member, diturunkan dari ledger.
- **Play session** per station dengan **auto-stop** saat saldo waktu habis.
- **Android TV** native app + **ADB** untuk orchestration/recovery.
- **Role**: Developer (god mode), Super Admin (semua outlet), Operator/Kasir (1 outlet), Member.

## Menjalankan secara lokal (Docker / Laravel Sail)

Prasyarat: Docker Desktop.

```bash
# 1. Siapkan environment
cp .env.example .env            # (jika belum ada .env)

# 2. Build & jalankan stack (app PHP 8.5 + MySQL 8.4 + Redis)
./vendor/bin/sail up -d --build

# 3. Generate app key (jika kosong) & migrasi + seed data demo
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed

# 4. Build asset frontend
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

Aplikasi: http://localhost — Admin panel: http://localhost/admin

Menghentikan: `./vendor/bin/sail down` (tambah `-v` untuk hapus volume DB).

## Menjalankan test

Test memakai SQLite in-memory (cepat, independen dari DB Docker):

```bash
./vendor/bin/sail artisan test      # di dalam Docker
# atau, jika ada PHP lokal:
php artisan test
```

## Akun demo (password: `password`)

| Role | Email |
|---|---|
| Developer | `developer@billingps5.local` |
| Super Admin | `superadmin@billingps5.local` |
| Operator/Kasir | `operator@billingps5.local` |
| Member | `member@billingps5.local` |

## Struktur domain (Fase 1)

- Tenancy: `outlets`, `users` (+ `outlet_id`), role/permission via spatie + Shield.
- Uang & waktu: `time_packages`, `wallet_transactions` (ledger uang), `time_ledger_entries` (ledger waktu), `payments`.
- Operasional: `stations`, `play_sessions`, `station_commands`.
- Saldo diturunkan dari ledger: `wallet_balance = Σ amount (affects_balance)`, `remaining_minutes = max(0, Σ minutes)`.

## Dokumentasi

- Desain arsitektur: `docs/superpowers/specs/2026-07-01-billing-ps5-rebuild-design.md`
- Rencana Fase 1: `docs/superpowers/plans/2026-07-01-phase-1-foundation.md`
