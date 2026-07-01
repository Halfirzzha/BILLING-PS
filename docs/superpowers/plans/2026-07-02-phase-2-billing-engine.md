# Phase 2 — Billing Engine Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: superpowers:executing-plans / subagent-driven-development. Steps use checkbox (`- [ ]`).

**Goal:** Implement `BillingService` — the money+time engine: top-up wallet, buy time package (via wallet or cash), each atomic and recorded in the double ledger.

**Architecture:** A single `App\Services\BillingService` wraps all money/time mutations in `DB::transaction`. Money changes create `wallet_transactions`; time changes create `time_ledger_entries`. Balances stay ledger-derived (Phase 1). Operations are outlet-aware (transactions carry the outlet where they happened).

**Tech Stack:** Laravel 13, PHPUnit 12 (SQLite `:memory:`).

## Global Constraints

- Money = integer rupiah; time = integer minutes.
- Never edit balances directly — only via ledger rows.
- Every money/time mutation atomic (`DB::transaction`).
- Wallet purchase: `time_purchase` (negative `amount`, `affects_balance=true`) + `credit` time entry.
- Cash purchase: `cash_sale` (positive `amount`, `affects_balance=false`) + `credit` time entry — wallet balance unchanged.
- Top-up: `top_up` (positive `amount`, `affects_balance=true`).
- Reference codes unique per row: `TOP-`, `PKG-`, `CSH-` + 10 random upper chars.
- Commit per task with `Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>`.

---

## File structure

```
app/Services/BillingService.php              (new)
tests/Feature/Billing/TopUpTest.php          (new)
tests/Feature/Billing/PurchaseWalletTest.php (new)
tests/Feature/Billing/PurchaseCashTest.php   (new)
```

---

### Task 1: BillingService + topUpWallet

**Files:**
- Create: `app/Services/BillingService.php`
- Test: `tests/Feature/Billing/TopUpTest.php`

**Interfaces:**
- Produces:
  - `BillingService::ensureMember(User $user): void` — assigns `member` role if missing.
  - `BillingService::topUpWallet(User $user, int $amount, PaymentMethod $method = PaymentMethod::Cash, ?User $operator = null, ?int $outletId = null, ?string $notes = null): WalletTransaction` — creates a `top_up` credit; throws `RuntimeException` if `$amount <= 0`. `outletId` defaults to operator's `outlet_id`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Billing/TopUpTest.php
namespace Tests\Feature\Billing;

use App\Enums\PaymentMethod;
use App\Enums\RoleName;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class TopUpTest extends TestCase
{
    use RefreshDatabase;

    private function service(): BillingService
    {
        return app(BillingService::class);
    }

    public function test_top_up_increases_wallet_balance_and_assigns_member_role(): void
    {
        $user = User::factory()->create();

        $txn = $this->service()->topUpWallet($user, 50000);

        $this->assertSame(50000, $user->fresh()->wallet_balance);
        $this->assertTrue($user->fresh()->hasRole(RoleName::Member->value));
        $this->assertTrue($txn->affects_balance);
        $this->assertStringStartsWith('TOP-', $txn->reference);
    }

    public function test_top_up_rejects_non_positive_amount(): void
    {
        $user = User::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->service()->topUpWallet($user, 0, PaymentMethod::Cash);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TopUpTest`
Expected: FAIL — `BillingService` not found.

- [ ] **Step 3: Create the service**

```php
<?php
// app/Services/BillingService.php
namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\RoleName;
use App\Enums\TimeLedgerType;
use App\Enums\WalletTransactionType;
use App\Models\TimeLedgerEntry;
use App\Models\TimePackage;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class BillingService
{
    public function ensureMember(User $user): void
    {
        if (! $user->hasRole(RoleName::Member->value)) {
            $user->assignRole(RoleName::Member->value);
        }
    }

    public function topUpWallet(
        User $user,
        int $amount,
        PaymentMethod $method = PaymentMethod::Cash,
        ?User $operator = null,
        ?int $outletId = null,
        ?string $notes = null,
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new RuntimeException('Top up amount must be greater than zero.');
        }

        $this->ensureMember($user);

        return WalletTransaction::create([
            'user_id' => $user->id,
            'outlet_id' => $outletId ?? $operator?->outlet_id,
            'operator_id' => $operator?->id,
            'type' => WalletTransactionType::TopUp->value,
            'payment_method' => $method->value,
            'amount' => $amount,
            'affects_balance' => true,
            'reference' => 'TOP-'.Str::upper(Str::random(10)),
            'notes' => $notes,
        ]);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=TopUpTest`
Expected: PASS (2 tests).

- [ ] **Step 5: Commit**

```bash
git add -A && git commit -m "feat: BillingService topUpWallet + ensureMember

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

### Task 2: purchaseTimePackage via wallet

**Files:**
- Modify: `app/Services/BillingService.php`
- Test: `tests/Feature/Billing/PurchaseWalletTest.php`

**Interfaces:**
- Produces:
  - `BillingService::purchaseTimePackage(User $user, TimePackage $package, PaymentMethod $method, ?User $operator = null, ?string $notes = null): TimeLedgerEntry` — atomic. For `PaymentMethod::Wallet`: throws if `$package` inactive or wallet balance `< price`; creates negative `time_purchase` wallet row + positive `credit` time row (outlet = package outlet). Returns the time credit entry.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Billing/PurchaseWalletTest.php
namespace Tests\Feature\Billing;

use App\Enums\PaymentMethod;
use App\Models\TimePackage;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PurchaseWalletTest extends TestCase
{
    use RefreshDatabase;

    private function service(): BillingService
    {
        return app(BillingService::class);
    }

    public function test_wallet_purchase_debits_money_and_credits_time(): void
    {
        $user = User::factory()->create();
        $package = TimePackage::factory()->create(['minutes' => 60, 'price' => 20000]);

        $this->service()->topUpWallet($user, 50000);
        $entry = $this->service()->purchaseTimePackage($user, $package, PaymentMethod::Wallet);

        $user->refresh();
        $this->assertSame(30000, $user->wallet_balance);   // 50000 - 20000
        $this->assertSame(60, $user->remaining_minutes);
        $this->assertSame(60, $entry->minutes);
        $this->assertSame($package->outlet_id, $entry->outlet_id);
    }

    public function test_wallet_purchase_fails_with_insufficient_balance(): void
    {
        $user = User::factory()->create();
        $package = TimePackage::factory()->create(['minutes' => 60, 'price' => 20000]);

        $this->service()->topUpWallet($user, 5000);

        $this->expectException(RuntimeException::class);
        $this->service()->purchaseTimePackage($user, $package, PaymentMethod::Wallet);
    }

    public function test_wallet_purchase_fails_for_inactive_package(): void
    {
        $user = User::factory()->create();
        $package = TimePackage::factory()->create(['is_active' => false, 'price' => 1000]);

        $this->service()->topUpWallet($user, 50000);

        $this->expectException(RuntimeException::class);
        $this->service()->purchaseTimePackage($user, $package, PaymentMethod::Wallet);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PurchaseWalletTest`
Expected: FAIL — `purchaseTimePackage` not defined.

- [ ] **Step 3: Add purchaseTimePackage to the service**

Add these `use` imports are already present from Task 1 (`TimeLedgerEntry`, `TimePackage`, `TimeLedgerType`, `DB`). Append this method inside the class:

```php
    public function purchaseTimePackage(
        User $user,
        TimePackage $package,
        PaymentMethod $method,
        ?User $operator = null,
        ?string $notes = null,
    ): TimeLedgerEntry {
        if (! $package->is_active) {
            throw new RuntimeException('Selected package is inactive.');
        }

        $this->ensureMember($user);

        return DB::transaction(function () use ($user, $package, $method, $operator, $notes): TimeLedgerEntry {
            if ($method === PaymentMethod::Wallet) {
                if ($user->wallet_balance < $package->price) {
                    throw new RuntimeException('Wallet balance is not enough.');
                }

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'outlet_id' => $package->outlet_id,
                    'operator_id' => $operator?->id,
                    'type' => WalletTransactionType::TimePurchase->value,
                    'payment_method' => PaymentMethod::Wallet->value,
                    'amount' => -$package->price,
                    'affects_balance' => true,
                    'reference' => 'PKG-'.Str::upper(Str::random(10)),
                    'notes' => $notes ?? "Beli paket {$package->name}",
                ]);
            } else {
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'outlet_id' => $package->outlet_id,
                    'operator_id' => $operator?->id,
                    'type' => WalletTransactionType::CashSale->value,
                    'payment_method' => $method->value,
                    'amount' => $package->price,
                    'affects_balance' => false,
                    'reference' => 'CSH-'.Str::upper(Str::random(10)),
                    'notes' => $notes ?? "Penjualan cash paket {$package->name}",
                ]);
            }

            return TimeLedgerEntry::create([
                'user_id' => $user->id,
                'outlet_id' => $package->outlet_id,
                'operator_id' => $operator?->id,
                'time_package_id' => $package->id,
                'type' => TimeLedgerType::Credit->value,
                'minutes' => $package->minutes,
                'notes' => $notes ?? "Kredit waktu paket {$package->name}",
                'meta' => [
                    'payment_method' => $method->value,
                    'price' => $package->price,
                ],
            ]);
        });
    }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=PurchaseWalletTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add -A && git commit -m "feat: BillingService purchaseTimePackage via wallet

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

### Task 3: purchaseTimePackage via cash

**Files:**
- Test: `tests/Feature/Billing/PurchaseCashTest.php` (method already handles cash via the `else` branch from Task 2)

**Interfaces:**
- Consumes: `BillingService::purchaseTimePackage` (Task 2) with `PaymentMethod::Cash`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Billing/PurchaseCashTest.php
namespace Tests\Feature\Billing;

use App\Enums\PaymentMethod;
use App\Models\TimePackage;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseCashTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_purchase_credits_time_without_touching_wallet(): void
    {
        $user = User::factory()->create();
        $operator = User::factory()->create();
        $package = TimePackage::factory()->create(['minutes' => 120, 'price' => 34000]);

        $entry = app(BillingService::class)
            ->purchaseTimePackage($user, $package, PaymentMethod::Cash, $operator);

        $user->refresh();
        $this->assertSame(0, $user->wallet_balance);        // cash sale must not change wallet
        $this->assertSame(120, $user->remaining_minutes);
        $this->assertSame($operator->id, $entry->operator_id);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => 'cash_sale',
            'affects_balance' => false,
            'amount' => 34000,
        ]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails, then passes**

Run: `php artisan test --filter=PurchaseCashTest`
Expected: PASS immediately (logic implemented in Task 2). If it fails, fix the cash branch.

- [ ] **Step 3: Run full billing suite + whole suite**

Run: `php artisan test --filter=Billing` then `php artisan test`
Expected: all PASS.

- [ ] **Step 4: Commit**

```bash
git add -A && git commit -m "test: BillingService cash purchase coverage

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

## Self-Review

- **Spec coverage:** top-up cash (T1), buy package wallet (T2), buy package cash (T3), double-ledger + atomicity (T2/T3), balances derived (verified via Phase 1 accessors). Gateway top-up crediting = Phase 8 (out of scope).
- **Placeholder scan:** none.
- **Type consistency:** `topUpWallet → WalletTransaction`, `purchaseTimePackage → TimeLedgerEntry`; `PaymentMethod` enum used throughout; wallet debit uses negative `amount` with `affects_balance=true`, cash uses `affects_balance=false`. Consistent with Phase 1 balance derivation (`Σ amount WHERE affects_balance`).
