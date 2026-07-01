# Phase 1 — Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the clean, tested domain foundation for the multi-tenant PS5 billing platform: enums, multi-tenant data model (outlets → stations/sessions/transactions), 4-role auth with developer god-mode, ledger-derived balances, demo seeders, and factories.

**Architecture:** Single-database row-level multi-tenancy. `Outlet` is the tenant root; operational entities carry `outlet_id`. Members, wallet money, and time balance are global (no `outlet_id`). Money is stored as integer rupiah, time as integer minutes; both balances are **always derived from ledger tables** (`wallet_transactions`, `time_ledger_entries`) — never edited directly. This phase delivers a working `php artisan migrate:fresh --seed` with a fully tested domain layer. Filament resources, member portal, device API, and realtime come in later phases.

**Tech Stack:** Laravel 13.18, PHP 8.5, Filament 4, spatie/laravel-permission 7, bezhansalleh/filament-shield 4, simplesoftwareio/simple-qrcode, PHPUnit 12.

## Global Constraints

- PHP `^8.3` (env has 8.5); Laravel `^13.8`; Filament `^4.0` — do not change composer version floors.
- Money: **integer rupiah** (bigint), never float/decimal. Time: **integer minutes**.
- Balances derived from ledgers only: `wallet_balance = Σ wallet_transactions.amount WHERE affects_balance = true`; `remaining_minutes = max(0, Σ time_ledger_entries.minutes)`.
- Roles (exact string values): `developer`, `super_admin`, `operator`, `member`. Guard: `web`.
- Developer + Super Admin get `Gate::before ⇒ true` (god mode).
- Multi-tenancy: operational tables carry `outlet_id`; `users`, `wallet_transactions.affects_balance` money balance, and `time_ledger_entries` time balance are global (member scope).
- All enums are PHP backed `string` enums under `App\Enums`.
- Tests use `RefreshDatabase`; run with `php artisan test`.
- Use Laravel 13 anonymous-class migrations and attribute-based `#[Fillable]`/`#[Hidden]` on models (match existing style).
- Commit after every task with a `feat:`/`chore:`/`test:` message + `Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>` trailer.

---

## File structure (created/modified in this phase)

```
app/Enums/
  RoleName.php  StationStatus.php  StationAppMode.php  PlaySessionStatus.php
  PaymentMethod.php  WalletTransactionType.php  TimeLedgerType.php
  StationCommandType.php  StationCommandStatus.php
app/Models/
  Outlet.php  User.php  TimePackage.php  WalletTransaction.php
  TimeLedgerEntry.php  Payment.php  Station.php  PlaySession.php  StationCommand.php
app/Models/Concerns/BelongsToOutlet.php
app/Providers/AppServiceProvider.php            (Gate::before)
database/migrations/
  0001_01_01_000000_create_users_table.php      (modified: domain columns)
  2026_07_01_150000_create_outlets_table.php    (+ FK users.outlet_id)
  2026_07_01_150010_create_time_packages_table.php
  2026_07_01_150020_create_wallet_transactions_table.php
  2026_07_01_150030_create_time_ledger_entries_table.php
  2026_07_01_150040_create_payments_table.php
  2026_07_01_150050_create_stations_table.php
  2026_07_01_150060_create_play_sessions_table.php
  2026_07_01_150070_create_station_commands_table.php
database/factories/
  OutletFactory.php  UserFactory.php  TimePackageFactory.php
  WalletTransactionFactory.php  TimeLedgerEntryFactory.php  PaymentFactory.php
  StationFactory.php  PlaySessionFactory.php  StationCommandFactory.php
database/seeders/
  DatabaseSeeder.php  RolePermissionSeeder.php  DemoDataSeeder.php
tests/Feature/Foundation/  (per-task test files)
```

**Cleanup note (rebuild):** Task 1 removes stale domain files that belong to later phases so the foundation starts clean. Old Filament resources, HTTP controllers, services, views, and old domain migrations are deleted; the Laravel skeleton (bootstrap, config, public, base providers) is kept.

---

### Task 1: Clean slate + domain enums

**Files:**
- Delete: `app/Filament/Resources/`, `app/Filament/Widgets/`, `app/Filament/Pages/`, `app/Http/Controllers/StationPortalController.php`, `app/Http/Controllers/PurchaseController.php`, `app/Http/Controllers/SessionController.php`, `app/Http/Controllers/MemberPortalController.php`, `app/Http/Controllers/Auth/`, `app/Http/Controllers/Api/`, `app/Services/`, `app/Policies/`, old domain migrations `database/migrations/2026_07_01_134034_*`, `2026_07_01_140500_*`, `2026_07_01_140510_*`, old domain views under `resources/views/{stations,portal,auth,home.blade.php}`.
- Keep: `app/Filament/Providers`/`AdminPanelProvider`, `app/Providers/AppServiceProvider.php`, config, migrations for cache/jobs/settings/permission.
- Create: `app/Enums/{RoleName,StationStatus,StationAppMode,PlaySessionStatus,PaymentMethod,WalletTransactionType,TimeLedgerType,StationCommandType,StationCommandStatus}.php`
- Test: `tests/Feature/Foundation/EnumTest.php`

**Interfaces:**
- Produces: enums with these exact cases/values:
  - `RoleName`: Developer='developer', SuperAdmin='super_admin', Operator='operator', Member='member'
  - `StationStatus`: Idle='idle', Active='active', Maintenance='maintenance'
  - `StationAppMode`: Qr='qr', Session='session', Maintenance='maintenance'
  - `PlaySessionStatus`: Active='active', Completed='completed', Cancelled='cancelled'
  - `PaymentMethod`: Cash='cash', Wallet='wallet', Gateway='gateway', TimeBalance='time_balance'
  - `WalletTransactionType`: TopUp='top_up', TimePurchase='time_purchase', CashSale='cash_sale', Adjustment='adjustment', Refund='refund'
  - `TimeLedgerType`: Credit='credit', SessionDebit='session_debit', Adjustment='adjustment', Expiry='expiry'
  - `StationCommandType`: Wake='wake', RelaunchApp='relaunch_app', Reboot='reboot', RefreshState='refresh_state', CustomAdb='custom_adb'
  - `StationCommandStatus`: Pending='pending', Dispatched='dispatched', Acknowledged='acknowledged', Failed='failed'

- [ ] **Step 1: Delete stale domain files**

```bash
cd /Users/halfirzzha/Documents/VStudio/NOW-PROJECT/BILLINGPS5
rm -rf app/Filament/Resources app/Filament/Widgets app/Filament/Pages \
       app/Http/Controllers/Api app/Http/Controllers/Auth app/Services app/Policies
rm -f app/Http/Controllers/StationPortalController.php app/Http/Controllers/PurchaseController.php \
      app/Http/Controllers/SessionController.php app/Http/Controllers/MemberPortalController.php
rm -f database/migrations/2026_07_01_134034_*.php \
      database/migrations/2026_07_01_140500_*.php database/migrations/2026_07_01_140510_*.php
rm -rf resources/views/stations resources/views/portal resources/views/auth
rm -f resources/views/home.blade.php
# reset routes to skeleton (domain routes return in later phases)
printf '%s\n' '<?php' '' 'use Illuminate\Support\Facades\Route;' '' "Route::get('/', fn () => view('welcome'))->name('home');" > routes/web.php
printf '%s\n' '<?php' '' 'use Illuminate\Support\Facades\Route;' > routes/api.php
```

- [ ] **Step 2: Write the failing test**

```php
<?php
// tests/Feature/Foundation/EnumTest.php
namespace Tests\Feature\Foundation;

use App\Enums\PaymentMethod;
use App\Enums\PlaySessionStatus;
use App\Enums\RoleName;
use App\Enums\StationAppMode;
use App\Enums\StationCommandStatus;
use App\Enums\StationCommandType;
use App\Enums\StationStatus;
use App\Enums\TimeLedgerType;
use App\Enums\WalletTransactionType;
use Tests\TestCase;

class EnumTest extends TestCase
{
    public function test_role_names_have_expected_values(): void
    {
        $this->assertSame('developer', RoleName::Developer->value);
        $this->assertSame('super_admin', RoleName::SuperAdmin->value);
        $this->assertSame('operator', RoleName::Operator->value);
        $this->assertSame('member', RoleName::Member->value);
        $this->assertCount(4, RoleName::cases());
    }

    public function test_domain_enums_expose_core_values(): void
    {
        $this->assertSame('idle', StationStatus::Idle->value);
        $this->assertSame('qr', StationAppMode::Qr->value);
        $this->assertSame('active', PlaySessionStatus::Active->value);
        $this->assertSame('time_balance', PaymentMethod::TimeBalance->value);
        $this->assertSame('top_up', WalletTransactionType::TopUp->value);
        $this->assertSame('session_debit', TimeLedgerType::SessionDebit->value);
        $this->assertSame('refresh_state', StationCommandType::RefreshState->value);
        $this->assertSame('pending', StationCommandStatus::Pending->value);
    }
}
```

- [ ] **Step 3: Run test to verify it fails**

Run: `php artisan test --filter=EnumTest`
Expected: FAIL — enum classes not found.

- [ ] **Step 4: Create the enums**

```php
<?php
// app/Enums/RoleName.php
namespace App\Enums;

enum RoleName: string
{
    case Developer = 'developer';
    case SuperAdmin = 'super_admin';
    case Operator = 'operator';
    case Member = 'member';
}
```

```php
<?php
// app/Enums/StationStatus.php
namespace App\Enums;

enum StationStatus: string
{
    case Idle = 'idle';
    case Active = 'active';
    case Maintenance = 'maintenance';
}
```

```php
<?php
// app/Enums/StationAppMode.php
namespace App\Enums;

enum StationAppMode: string
{
    case Qr = 'qr';
    case Session = 'session';
    case Maintenance = 'maintenance';
}
```

```php
<?php
// app/Enums/PlaySessionStatus.php
namespace App\Enums;

enum PlaySessionStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
```

```php
<?php
// app/Enums/PaymentMethod.php
namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Wallet = 'wallet';
    case Gateway = 'gateway';
    case TimeBalance = 'time_balance';
}
```

```php
<?php
// app/Enums/WalletTransactionType.php
namespace App\Enums;

enum WalletTransactionType: string
{
    case TopUp = 'top_up';
    case TimePurchase = 'time_purchase';
    case CashSale = 'cash_sale';
    case Adjustment = 'adjustment';
    case Refund = 'refund';
}
```

```php
<?php
// app/Enums/TimeLedgerType.php
namespace App\Enums;

enum TimeLedgerType: string
{
    case Credit = 'credit';
    case SessionDebit = 'session_debit';
    case Adjustment = 'adjustment';
    case Expiry = 'expiry';
}
```

```php
<?php
// app/Enums/StationCommandType.php
namespace App\Enums;

enum StationCommandType: string
{
    case Wake = 'wake';
    case RelaunchApp = 'relaunch_app';
    case Reboot = 'reboot';
    case RefreshState = 'refresh_state';
    case CustomAdb = 'custom_adb';
}
```

```php
<?php
// app/Enums/StationCommandStatus.php
namespace App\Enums;

enum StationCommandStatus: string
{
    case Pending = 'pending';
    case Dispatched = 'dispatched';
    case Acknowledged = 'acknowledged';
    case Failed = 'failed';
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=EnumTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "chore: clean slate + domain enums for rebuild

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

### Task 2: Users table (domain columns) + User model + factory

**Files:**
- Modify: `database/migrations/0001_01_01_000000_create_users_table.php`
- Create: `app/Models/User.php` (rewrite), `database/factories/UserFactory.php`
- Test: `tests/Feature/Foundation/UserModelTest.php`

**Interfaces:**
- Produces:
  - `users` columns: `id, name, email(unique), phone(null), password, member_code(unique,null), outlet_id(unsignedBigInt,null,index — FK added in Task 3), is_active(bool,default true), last_seen_at(null), remember_token, timestamps`.
  - `User::walletTransactions(): HasMany`, `User::timeLedgerEntries(): HasMany`, `User::playSessions(): HasMany`, `User::outlet(): BelongsTo`.
  - `User::getWalletBalanceAttribute(): int`, `User::getRemainingMinutesAttribute(): int`, `User::isMember(): bool`.
  - Auto-generates `member_code` (`MBR-######`) on create when absent.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Foundation/UserModelTest.php
namespace Tests\Feature\Foundation;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_code_is_generated_on_create(): void
    {
        $user = User::factory()->create(['member_code' => null]);

        $this->assertMatchesRegularExpression('/^MBR-\d{6}$/', $user->member_code);
    }

    public function test_balances_default_to_zero(): void
    {
        $user = User::factory()->create();

        $this->assertSame(0, $user->wallet_balance);
        $this->assertSame(0, $user->remaining_minutes);
    }

    public function test_is_active_casts_to_boolean(): void
    {
        $user = User::factory()->create(['is_active' => 1]);

        $this->assertTrue($user->is_active);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=UserModelTest`
Expected: FAIL — unknown column `member_code`/`outlet_id` or missing accessors.

- [ ] **Step 3: Rewrite the users migration**

```php
<?php
// database/migrations/0001_01_01_000000_create_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('member_code')->nullable()->unique();
            $table->unsignedBigInteger('outlet_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
```

- [ ] **Step 4: Rewrite the User model**

```php
<?php
// app/Models/User.php
namespace App\Models;

use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'member_code', 'outlet_id', 'is_active', 'last_seen_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected string $guard_name = 'web';

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (! $user->member_code) {
                $user->member_code = static::generateMemberCode();
            }
        });
    }

    public static function generateMemberCode(): string
    {
        do {
            $code = 'MBR-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('member_code', $code)->exists());

        return $code;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->hasAnyRole([
            RoleName::Developer->value,
            RoleName::SuperAdmin->value,
            RoleName::Operator->value,
        ]);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function timeLedgerEntries(): HasMany
    {
        return $this->hasMany(TimeLedgerEntry::class);
    }

    public function playSessions(): HasMany
    {
        return $this->hasMany(PlaySession::class);
    }

    public function getWalletBalanceAttribute(): int
    {
        return (int) $this->walletTransactions()->where('affects_balance', true)->sum('amount');
    }

    public function getRemainingMinutesAttribute(): int
    {
        return max(0, (int) $this->timeLedgerEntries()->sum('minutes'));
    }

    public function isMember(): bool
    {
        return $this->hasRole(RoleName::Member->value);
    }
}
```

- [ ] **Step 5: Write the UserFactory**

```php
<?php
// database/factories/UserFactory.php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('08##########'),
            'password' => Hash::make('password'),
            'member_code' => null,
            'outlet_id' => null,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
```

- [ ] **Step 6: Run migrations + test**

Run: `php artisan migrate:fresh && php artisan test --filter=UserModelTest`
Expected: PASS (3 tests).

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: users domain columns, User model, factory

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

### Task 3: Outlet model + migration (+ users.outlet_id FK) + factory

**Files:**
- Create: `database/migrations/2026_07_01_150000_create_outlets_table.php`, `app/Models/Outlet.php`, `app/Models/Concerns/BelongsToOutlet.php`, `database/factories/OutletFactory.php`
- Test: `tests/Feature/Foundation/OutletModelTest.php`

**Interfaces:**
- Consumes: `users.outlet_id` column (Task 2).
- Produces:
  - `outlets` columns: `id, name, code(unique), slug(unique), timezone(default 'Asia/Jakarta'), address(null), phone(null), is_active(bool,default true), settings(json,null), timestamps`.
  - FK `users.outlet_id → outlets.id nullOnDelete`.
  - `Outlet::users(): HasMany`, `Outlet::stations(): HasMany`, `Outlet::timePackages(): HasMany`.
  - Trait `App\Models\Concerns\BelongsToOutlet` providing `outlet(): BelongsTo` + `scopeForOutlet($q, $outletId)`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Foundation/OutletModelTest.php
namespace Tests\Feature\Foundation;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_outlet_has_defaults_and_users_relation(): void
    {
        $outlet = Outlet::factory()->create(['code' => 'JKT-01']);
        $user = User::factory()->create(['outlet_id' => $outlet->id]);

        $this->assertSame('Asia/Jakarta', $outlet->timezone);
        $this->assertTrue($outlet->is_active);
        $this->assertTrue($outlet->users->contains($user));
        $this->assertSame($outlet->id, $user->outlet->id);
    }

    public function test_settings_are_cast_to_array(): void
    {
        $outlet = Outlet::factory()->create(['settings' => ['tax_percent' => 10]]);

        $this->assertSame(10, $outlet->fresh()->settings['tax_percent']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=OutletModelTest`
Expected: FAIL — `outlets` table / `Outlet` model missing.

- [ ] **Step 3: Create the outlets migration (with FK to users)**

```php
<?php
// database/migrations/2026_07_01_150000_create_outlets_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('slug')->unique();
            $table->string('timezone')->default('Asia/Jakarta');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('outlet_id')->references('id')->on('outlets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['outlet_id']);
        });
        Schema::dropIfExists('outlets');
    }
};
```

- [ ] **Step 4: Create the BelongsToOutlet trait**

```php
<?php
// app/Models/Concerns/BelongsToOutlet.php
namespace App\Models\Concerns;

use App\Models\Outlet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOutlet
{
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function scopeForOutlet(Builder $query, int $outletId): Builder
    {
        return $query->where($this->getTable().'.outlet_id', $outletId);
    }
}
```

- [ ] **Step 5: Create the Outlet model**

```php
<?php
// app/Models/Outlet.php
namespace App\Models;

use Database\Factories\OutletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'code', 'slug', 'timezone', 'address', 'phone', 'is_active', 'settings'])]
class Outlet extends Model
{
    /** @use HasFactory<OutletFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $outlet): void {
            if (! $outlet->slug) {
                $outlet->slug = Str::slug($outlet->name).'-'.Str::lower(Str::random(4));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function stations(): HasMany
    {
        return $this->hasMany(Station::class);
    }

    public function timePackages(): HasMany
    {
        return $this->hasMany(TimePackage::class);
    }
}
```

- [ ] **Step 6: Create the OutletFactory**

```php
<?php
// database/factories/OutletFactory.php
namespace Database\Factories;

use App\Models\Outlet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Outlet> */
class OutletFactory extends Factory
{
    protected $model = Outlet::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'code' => Str::upper(Str::random(6)),
            'slug' => null,
            'timezone' => 'Asia/Jakarta',
            'address' => fake()->address(),
            'phone' => fake()->numerify('02########'),
            'is_active' => true,
            'settings' => null,
        ];
    }
}
```

- [ ] **Step 7: Run migrations + test**

Run: `php artisan migrate:fresh && php artisan test --filter=OutletModelTest`
Expected: PASS (2 tests).

- [ ] **Step 8: Commit**

```bash
git add -A
git commit -m "feat: Outlet tenant model, migration, BelongsToOutlet trait

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

### Task 4: Roles, permissions seeder + developer god-mode gate

**Files:**
- Create: `database/seeders/RolePermissionSeeder.php`
- Modify: `app/Providers/AppServiceProvider.php` (keep `Gate::before`), `database/seeders/DatabaseSeeder.php` (call seeder)
- Test: `tests/Feature/Foundation/RolePermissionTest.php`

**Interfaces:**
- Consumes: spatie `permission` tables (existing migration `2026_07_01_134137_create_permission_tables.php`), `RoleName` enum.
- Produces: 4 roles (`developer, super_admin, operator, member`) seeded for guard `web`; `Gate::before` returns `true` for Developer & Super Admin.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Foundation/RolePermissionTest.php
namespace Tests\Feature\Foundation;

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_all_four_roles_are_seeded(): void
    {
        foreach (RoleName::cases() as $role) {
            $this->assertDatabaseHas('roles', ['name' => $role->value, 'guard_name' => 'web']);
        }
    }

    public function test_developer_passes_any_gate(): void
    {
        $dev = User::factory()->create();
        $dev->assignRole(RoleName::Developer->value);

        $this->assertTrue(Gate::forUser($dev)->allows('anything-at-all'));
    }

    public function test_member_does_not_pass_arbitrary_gate(): void
    {
        $member = User::factory()->create();
        $member->assignRole(RoleName::Member->value);

        $this->assertFalse(Gate::forUser($member)->allows('some-admin-only-ability'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=RolePermissionTest`
Expected: FAIL — `RolePermissionSeeder` not found.

- [ ] **Step 3: Create the RolePermissionSeeder**

```php
<?php
// database/seeders/RolePermissionSeeder.php
namespace Database\Seeders;

use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RoleName::cases() as $role) {
            Role::findOrCreate($role->value, 'web');
        }
    }
}
```

- [ ] **Step 4: Ensure Gate::before is present in AppServiceProvider**

```php
<?php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use App\Enums\RoleName;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user): ?bool {
            if ($user->hasAnyRole([RoleName::Developer->value, RoleName::SuperAdmin->value])) {
                return true;
            }

            return null;
        });
    }
}
```

- [ ] **Step 5: Wire the seeder into DatabaseSeeder**

```php
<?php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=RolePermissionTest`
Expected: PASS (3 tests).

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: role/permission seeder + developer god-mode gate

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

### Task 5: TimePackage model + migration + factory

**Files:**
- Create: `database/migrations/2026_07_01_150010_create_time_packages_table.php`, `app/Models/TimePackage.php`, `database/factories/TimePackageFactory.php`
- Test: `tests/Feature/Foundation/TimePackageModelTest.php`

**Interfaces:**
- Consumes: `outlets` (Task 3), `BelongsToOutlet` trait.
- Produces:
  - `time_packages` columns: `id, outlet_id(FK,cascade), name, minutes(unsignedInt), price(unsignedBigInt), is_active(bool,default true), sort(unsignedInt,default 0), timestamps`.
  - `TimePackage::outlet()` (via trait), casts `price`/`minutes` to int.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Foundation/TimePackageModelTest.php
namespace Tests\Feature\Foundation;

use App\Models\Outlet;
use App\Models\TimePackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimePackageModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_belongs_to_outlet_and_casts_ints(): void
    {
        $outlet = Outlet::factory()->create();
        $package = TimePackage::factory()->for($outlet)->create([
            'minutes' => 60,
            'price' => 20000,
        ]);

        $this->assertSame($outlet->id, $package->outlet->id);
        $this->assertSame(60, $package->minutes);
        $this->assertSame(20000, $package->price);
        $this->assertTrue($package->is_active);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TimePackageModelTest`
Expected: FAIL — table/model missing.

- [ ] **Step 3: Create the migration**

```php
<?php
// database/migrations/2026_07_01_150010_create_time_packages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('minutes');
            $table->unsignedBigInteger('price');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_packages');
    }
};
```

- [ ] **Step 4: Create the model**

```php
<?php
// app/Models/TimePackage.php
namespace App\Models;

use App\Models\Concerns\BelongsToOutlet;
use Database\Factories\TimePackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['outlet_id', 'name', 'minutes', 'price', 'is_active', 'sort'])]
class TimePackage extends Model
{
    /** @use HasFactory<TimePackageFactory> */
    use HasFactory;
    use BelongsToOutlet;

    protected function casts(): array
    {
        return [
            'minutes' => 'integer',
            'price' => 'integer',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }
}
```

- [ ] **Step 5: Create the factory**

```php
<?php
// database/factories/TimePackageFactory.php
namespace Database\Factories;

use App\Models\Outlet;
use App\Models\TimePackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TimePackage> */
class TimePackageFactory extends Factory
{
    protected $model = TimePackage::class;

    public function definition(): array
    {
        $minutes = fake()->randomElement([30, 60, 120, 180]);

        return [
            'outlet_id' => Outlet::factory(),
            'name' => $minutes.' Menit',
            'minutes' => $minutes,
            'price' => $minutes * 300,
            'is_active' => true,
            'sort' => 0,
        ];
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan migrate:fresh && php artisan test --filter=TimePackageModelTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: TimePackage model, migration, factory

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

### Task 6: WalletTransaction (money ledger) + wallet_balance derivation

**Files:**
- Create: `database/migrations/2026_07_01_150020_create_wallet_transactions_table.php`, `app/Models/WalletTransaction.php`, `database/factories/WalletTransactionFactory.php`
- Test: `tests/Feature/Foundation/WalletBalanceTest.php`

**Interfaces:**
- Consumes: `users`, `outlets`, enums `WalletTransactionType`, `PaymentMethod`.
- Produces:
  - `wallet_transactions` columns: `id, user_id(FK,cascade), outlet_id(FK,null,nullOnDelete), operator_id(FK users,null,nullOnDelete), type, payment_method, amount(bigint signed), affects_balance(bool,default true), reference(unique), gateway_ref(null), notes(null), meta(json,null), timestamps`.
  - `WalletTransaction::user()`, `::operator()`, `::outlet()`; casts `amount`→int, `affects_balance`→bool, `meta`→array.
  - Confirms `User::getWalletBalanceAttribute` sums only `affects_balance = true`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Foundation/WalletBalanceTest.php
namespace Tests\Feature\Foundation;

use App\Enums\PaymentMethod;
use App\Enums\WalletTransactionType;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_balance_sums_only_affecting_transactions(): void
    {
        $user = User::factory()->create();

        WalletTransaction::factory()->for($user)->create([
            'type' => WalletTransactionType::TopUp->value,
            'payment_method' => PaymentMethod::Cash->value,
            'amount' => 50000,
            'affects_balance' => true,
        ]);
        WalletTransaction::factory()->for($user)->create([
            'type' => WalletTransactionType::TimePurchase->value,
            'payment_method' => PaymentMethod::Wallet->value,
            'amount' => -20000,
            'affects_balance' => true,
        ]);
        // Cash sale must NOT affect wallet balance.
        WalletTransaction::factory()->for($user)->create([
            'type' => WalletTransactionType::CashSale->value,
            'payment_method' => PaymentMethod::Cash->value,
            'amount' => 15000,
            'affects_balance' => false,
        ]);

        $this->assertSame(30000, $user->fresh()->wallet_balance);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=WalletBalanceTest`
Expected: FAIL — table/model missing.

- [ ] **Step 3: Create the migration**

```php
<?php
// database/migrations/2026_07_01_150020_create_wallet_transactions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->string('payment_method');
            $table->bigInteger('amount');
            $table->boolean('affects_balance')->default(true);
            $table->string('reference')->unique();
            $table->string('gateway_ref')->nullable();
            $table->string('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'affects_balance']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
```

- [ ] **Step 4: Create the model**

```php
<?php
// app/Models/WalletTransaction.php
namespace App\Models;

use App\Models\Concerns\BelongsToOutlet;
use Database\Factories\WalletTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'outlet_id', 'operator_id', 'type', 'payment_method', 'amount', 'affects_balance', 'reference', 'gateway_ref', 'notes', 'meta'])]
class WalletTransaction extends Model
{
    /** @use HasFactory<WalletTransactionFactory> */
    use HasFactory;
    use BelongsToOutlet;

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'affects_balance' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
```

- [ ] **Step 5: Create the factory**

```php
<?php
// database/factories/WalletTransactionFactory.php
namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\WalletTransactionType;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<WalletTransaction> */
class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'outlet_id' => null,
            'operator_id' => null,
            'type' => WalletTransactionType::TopUp->value,
            'payment_method' => PaymentMethod::Cash->value,
            'amount' => 50000,
            'affects_balance' => true,
            'reference' => 'TXN-'.Str::upper(Str::random(10)),
            'gateway_ref' => null,
            'notes' => null,
            'meta' => null,
        ];
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan migrate:fresh && php artisan test --filter=WalletBalanceTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: WalletTransaction money ledger + balance derivation

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

### Task 7: TimeLedgerEntry (time ledger) + remaining_minutes derivation

**Files:**
- Create: `database/migrations/2026_07_01_150030_create_time_ledger_entries_table.php`, `app/Models/TimeLedgerEntry.php`, `database/factories/TimeLedgerEntryFactory.php`
- Test: `tests/Feature/Foundation/TimeBalanceTest.php`

**Interfaces:**
- Consumes: `users`, `outlets`, `time_packages`, enum `TimeLedgerType`.
- Produces:
  - `time_ledger_entries` columns: `id, user_id(FK,cascade), outlet_id(FK,null,nullOnDelete), operator_id(FK users,null,nullOnDelete), time_package_id(FK,null,nullOnDelete), play_session_id(unsignedBigInt,null,index — FK added in Task 10), type, minutes(int signed), notes(null), meta(json,null), timestamps`.
  - `TimeLedgerEntry::user()`, `::outlet()`, `::timePackage()`; casts `minutes`→int, `meta`→array.
  - Confirms `User::getRemainingMinutesAttribute` = `max(0, Σ minutes)`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Foundation/TimeBalanceTest.php
namespace Tests\Feature\Foundation;

use App\Enums\TimeLedgerType;
use App\Models\TimeLedgerEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_remaining_minutes_sum_credits_minus_debits(): void
    {
        $user = User::factory()->create();

        TimeLedgerEntry::factory()->for($user)->create([
            'type' => TimeLedgerType::Credit->value,
            'minutes' => 120,
        ]);
        TimeLedgerEntry::factory()->for($user)->create([
            'type' => TimeLedgerType::SessionDebit->value,
            'minutes' => -30,
        ]);

        $this->assertSame(90, $user->fresh()->remaining_minutes);
    }

    public function test_remaining_minutes_never_negative(): void
    {
        $user = User::factory()->create();

        TimeLedgerEntry::factory()->for($user)->create([
            'type' => TimeLedgerType::Adjustment->value,
            'minutes' => -50,
        ]);

        $this->assertSame(0, $user->fresh()->remaining_minutes);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TimeBalanceTest`
Expected: FAIL — table/model missing.

- [ ] **Step 3: Create the migration**

```php
<?php
// database/migrations/2026_07_01_150030_create_time_ledger_entries_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('time_package_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('play_session_id')->nullable()->index();
            $table->string('type');
            $table->integer('minutes');
            $table->string('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_ledger_entries');
    }
};
```

- [ ] **Step 4: Create the model**

```php
<?php
// app/Models/TimeLedgerEntry.php
namespace App\Models;

use App\Models\Concerns\BelongsToOutlet;
use Database\Factories\TimeLedgerEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'outlet_id', 'operator_id', 'time_package_id', 'play_session_id', 'type', 'minutes', 'notes', 'meta'])]
class TimeLedgerEntry extends Model
{
    /** @use HasFactory<TimeLedgerEntryFactory> */
    use HasFactory;
    use BelongsToOutlet;

    protected function casts(): array
    {
        return [
            'minutes' => 'integer',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function timePackage(): BelongsTo
    {
        return $this->belongsTo(TimePackage::class);
    }
}
```

- [ ] **Step 5: Create the factory**

```php
<?php
// database/factories/TimeLedgerEntryFactory.php
namespace Database\Factories;

use App\Enums\TimeLedgerType;
use App\Models\TimeLedgerEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TimeLedgerEntry> */
class TimeLedgerEntryFactory extends Factory
{
    protected $model = TimeLedgerEntry::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'outlet_id' => null,
            'operator_id' => null,
            'time_package_id' => null,
            'play_session_id' => null,
            'type' => TimeLedgerType::Credit->value,
            'minutes' => 60,
            'notes' => null,
            'meta' => null,
        ];
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan migrate:fresh && php artisan test --filter=TimeBalanceTest`
Expected: PASS (2 tests).

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: TimeLedgerEntry time ledger + remaining_minutes derivation

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

### Task 8: Payment model (online top-up) + migration + factory

**Files:**
- Create: `database/migrations/2026_07_01_150040_create_payments_table.php`, `app/Models/Payment.php`, `database/factories/PaymentFactory.php`
- Test: `tests/Feature/Foundation/PaymentModelTest.php`

**Interfaces:**
- Consumes: `users`, `wallet_transactions`.
- Produces:
  - `payments` columns: `id, user_id(FK,cascade), amount(bigint), provider, provider_ref(null,index), status(default 'pending'), paid_at(null), wallet_transaction_id(FK,null,nullOnDelete), payload(json,null), timestamps`.
  - `Payment::user()`, `::walletTransaction()`; casts `amount`→int, `paid_at`→datetime, `payload`→array.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Foundation/PaymentModelTest.php
namespace Tests\Feature\Foundation;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_defaults_to_pending_and_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->for($user)->create(['amount' => 100000]);

        $this->assertSame('pending', $payment->status);
        $this->assertSame(100000, $payment->amount);
        $this->assertSame($user->id, $payment->user->id);
        $this->assertNull($payment->paid_at);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PaymentModelTest`
Expected: FAIL — table/model missing.

- [ ] **Step 3: Create the migration**

```php
<?php
// database/migrations/2026_07_01_150040_create_payments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->string('provider');
            $table->string('provider_ref')->nullable()->index();
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('wallet_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
```

- [ ] **Step 4: Create the model**

```php
<?php
// app/Models/Payment.php
namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'amount', 'provider', 'provider_ref', 'status', 'paid_at', 'wallet_transaction_id', 'payload'])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }
}
```

- [ ] **Step 5: Create the factory**

```php
<?php
// database/factories/PaymentFactory.php
namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => 100000,
            'provider' => 'manual',
            'provider_ref' => null,
            'status' => 'pending',
            'paid_at' => null,
            'wallet_transaction_id' => null,
            'payload' => null,
        ];
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan migrate:fresh && php artisan test --filter=PaymentModelTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: Payment model, migration, factory

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

### Task 9: Station model + migration + factory

**Files:**
- Create: `database/migrations/2026_07_01_150050_create_stations_table.php`, `app/Models/Station.php`, `database/factories/StationFactory.php`
- Test: `tests/Feature/Foundation/StationModelTest.php`

**Interfaces:**
- Consumes: `outlets`, enums `StationStatus`, `StationAppMode`, `BelongsToOutlet`.
- Produces:
  - `stations` columns: `id, outlet_id(FK,cascade), code(unique), name, status(default 'idle'), app_mode(default 'qr'), is_active(bool,default true), qr_token(unique), device_token(null,unique), adb_identifier(null), current_session_id(unsignedBigInt,null,index — FK added in Task 10), last_heartbeat_at(null), timestamps`.
  - `Station::outlet()` (trait), `Station::playSessions(): HasMany`; casts `status`→StationStatus enum, `app_mode`→StationAppMode enum, `is_active`→bool, `last_heartbeat_at`→datetime.
  - Auto-generates `qr_token` + `device_token` on create when absent (random 40-char).

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Foundation/StationModelTest.php
namespace Tests\Feature\Foundation;

use App\Enums\StationAppMode;
use App\Enums\StationStatus;
use App\Models\Outlet;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_station_defaults_and_token_generation(): void
    {
        $outlet = Outlet::factory()->create();
        $station = Station::factory()->for($outlet)->create([
            'qr_token' => null,
            'device_token' => null,
        ]);

        $this->assertSame(StationStatus::Idle, $station->status);
        $this->assertSame(StationAppMode::Qr, $station->app_mode);
        $this->assertTrue($station->is_active);
        $this->assertNotEmpty($station->qr_token);
        $this->assertNotEmpty($station->device_token);
        $this->assertSame($outlet->id, $station->outlet->id);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=StationModelTest`
Expected: FAIL — table/model missing.

- [ ] **Step 3: Create the migration**

```php
<?php
// database/migrations/2026_07_01_150050_create_stations_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('idle');
            $table->string('app_mode')->default('qr');
            $table->boolean('is_active')->default(true);
            $table->string('qr_token')->unique();
            $table->string('device_token')->nullable()->unique();
            $table->string('adb_identifier')->nullable();
            $table->unsignedBigInteger('current_session_id')->nullable()->index();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
```

- [ ] **Step 4: Create the model**

```php
<?php
// app/Models/Station.php
namespace App\Models;

use App\Enums\StationAppMode;
use App\Enums\StationStatus;
use App\Models\Concerns\BelongsToOutlet;
use Database\Factories\StationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['outlet_id', 'code', 'name', 'status', 'app_mode', 'is_active', 'qr_token', 'device_token', 'adb_identifier', 'current_session_id', 'last_heartbeat_at'])]
class Station extends Model
{
    /** @use HasFactory<StationFactory> */
    use HasFactory;
    use BelongsToOutlet;

    protected static function booted(): void
    {
        static::creating(function (self $station): void {
            $station->qr_token ??= Str::random(40);
            $station->device_token ??= Str::random(40);
        });
    }

    protected function casts(): array
    {
        return [
            'status' => StationStatus::class,
            'app_mode' => StationAppMode::class,
            'is_active' => 'boolean',
            'last_heartbeat_at' => 'datetime',
        ];
    }

    public function playSessions(): HasMany
    {
        return $this->hasMany(PlaySession::class);
    }
}
```

- [ ] **Step 5: Create the factory**

```php
<?php
// database/factories/StationFactory.php
namespace Database\Factories;

use App\Models\Outlet;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Station> */
class StationFactory extends Factory
{
    protected $model = Station::class;

    public function definition(): array
    {
        return [
            'outlet_id' => Outlet::factory(),
            'code' => 'ST-'.Str::upper(Str::random(5)),
            'name' => 'Station '.fake()->numberBetween(1, 20),
            'status' => 'idle',
            'app_mode' => 'qr',
            'is_active' => true,
            'qr_token' => null,
            'device_token' => null,
            'adb_identifier' => null,
        ];
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan migrate:fresh && php artisan test --filter=StationModelTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: Station model, migration, factory

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

### Task 10: PlaySession model + migration (+ deferred FKs) + factory

**Files:**
- Create: `database/migrations/2026_07_01_150060_create_play_sessions_table.php`, `app/Models/PlaySession.php`, `database/factories/PlaySessionFactory.php`
- Test: `tests/Feature/Foundation/PlaySessionModelTest.php`

**Interfaces:**
- Consumes: `outlets`, `stations`, `users`, `time_ledger_entries.play_session_id`, `stations.current_session_id`, enums `PlaySessionStatus`, `PaymentMethod`.
- Produces:
  - `play_sessions` columns: `id, outlet_id(FK,cascade), station_id(FK,cascade), user_id(FK,cascade), status(default 'active'), payment_method(default 'time_balance'), started_at, planned_end_at(null), ended_at(null), started_with_minutes(unsignedInt,default 0), consumed_minutes(unsignedInt,default 0), minutes_debited(unsignedInt,default 0), ended_by(FK users,null,nullOnDelete), notes(null), timestamps`.
  - Adds deferred FKs: `time_ledger_entries.play_session_id → play_sessions.id nullOnDelete`, `stations.current_session_id → play_sessions.id nullOnDelete`.
  - `PlaySession::outlet()`, `::station()`, `::user()`; casts status→enum, dates→datetime, minute fields→int.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Foundation/PlaySessionModelTest.php
namespace Tests\Feature\Foundation;

use App\Enums\PlaySessionStatus;
use App\Models\PlaySession;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaySessionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_relations_and_casts(): void
    {
        $station = Station::factory()->create();
        $user = User::factory()->create();

        $session = PlaySession::factory()->create([
            'outlet_id' => $station->outlet_id,
            'station_id' => $station->id,
            'user_id' => $user->id,
            'started_with_minutes' => 60,
        ]);

        $this->assertSame(PlaySessionStatus::Active, $session->status);
        $this->assertSame($station->id, $session->station->id);
        $this->assertSame($user->id, $session->user->id);
        $this->assertSame(60, $session->started_with_minutes);
        $this->assertTrue($user->playSessions->contains($session));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PlaySessionModelTest`
Expected: FAIL — table/model missing.

- [ ] **Step 3: Create the migration**

```php
<?php
// database/migrations/2026_07_01_150060_create_play_sessions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('play_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->string('payment_method')->default('time_balance');
            $table->timestamp('started_at');
            $table->timestamp('planned_end_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('started_with_minutes')->default(0);
            $table->unsignedInteger('consumed_minutes')->default(0);
            $table->unsignedInteger('minutes_debited')->default(0);
            $table->foreignId('ended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['station_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        Schema::table('time_ledger_entries', function (Blueprint $table) {
            $table->foreign('play_session_id')->references('id')->on('play_sessions')->nullOnDelete();
        });

        Schema::table('stations', function (Blueprint $table) {
            $table->foreign('current_session_id')->references('id')->on('play_sessions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('time_ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['play_session_id']);
        });
        Schema::table('stations', function (Blueprint $table) {
            $table->dropForeign(['current_session_id']);
        });
        Schema::dropIfExists('play_sessions');
    }
};
```

- [ ] **Step 4: Create the model**

```php
<?php
// app/Models/PlaySession.php
namespace App\Models;

use App\Enums\PlaySessionStatus;
use App\Models\Concerns\BelongsToOutlet;
use Database\Factories\PlaySessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['outlet_id', 'station_id', 'user_id', 'status', 'payment_method', 'started_at', 'planned_end_at', 'ended_at', 'started_with_minutes', 'consumed_minutes', 'minutes_debited', 'ended_by', 'notes'])]
class PlaySession extends Model
{
    /** @use HasFactory<PlaySessionFactory> */
    use HasFactory;
    use BelongsToOutlet;

    protected function casts(): array
    {
        return [
            'status' => PlaySessionStatus::class,
            'started_at' => 'datetime',
            'planned_end_at' => 'datetime',
            'ended_at' => 'datetime',
            'started_with_minutes' => 'integer',
            'consumed_minutes' => 'integer',
            'minutes_debited' => 'integer',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

- [ ] **Step 5: Create the factory**

```php
<?php
// database/factories/PlaySessionFactory.php
namespace Database\Factories;

use App\Models\PlaySession;
use App\Models\Station;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PlaySession> */
class PlaySessionFactory extends Factory
{
    protected $model = PlaySession::class;

    public function definition(): array
    {
        $station = Station::factory();

        return [
            'outlet_id' => Outlet::factory(),
            'station_id' => $station,
            'user_id' => User::factory(),
            'status' => 'active',
            'payment_method' => 'time_balance',
            'started_at' => now(),
            'planned_end_at' => now()->addMinutes(60),
            'started_with_minutes' => 60,
            'consumed_minutes' => 0,
            'minutes_debited' => 0,
            'notes' => null,
        ];
    }
}
```

Note: import `use App\Models\Outlet;` at the top of the factory.

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan migrate:fresh && php artisan test --filter=PlaySessionModelTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: PlaySession model, migration, deferred FKs, factory

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

### Task 11: StationCommand model + migration + factory

**Files:**
- Create: `database/migrations/2026_07_01_150070_create_station_commands_table.php`, `app/Models/StationCommand.php`, `database/factories/StationCommandFactory.php`
- Test: `tests/Feature/Foundation/StationCommandModelTest.php`

**Interfaces:**
- Consumes: `outlets`, `stations`, `users`, enums `StationCommandType`, `StationCommandStatus`.
- Produces:
  - `station_commands` columns: `id, outlet_id(FK,cascade), station_id(FK,cascade), type, status(default 'pending'), payload(json,null), dispatched_at(null), acknowledged_at(null), error(null), created_by(FK users,null,nullOnDelete), timestamps`.
  - `StationCommand::station()`, `::outlet()`; casts type/status→enums, payload→array, dates→datetime.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Foundation/StationCommandModelTest.php
namespace Tests\Feature\Foundation;

use App\Enums\StationCommandStatus;
use App\Enums\StationCommandType;
use App\Models\Station;
use App\Models\StationCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StationCommandModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_defaults_and_casts(): void
    {
        $station = Station::factory()->create();
        $command = StationCommand::factory()->create([
            'outlet_id' => $station->outlet_id,
            'station_id' => $station->id,
            'type' => StationCommandType::Wake->value,
            'payload' => ['reason' => 'idle-wake'],
        ]);

        $this->assertSame(StationCommandStatus::Pending, $command->status);
        $this->assertSame(StationCommandType::Wake, $command->type);
        $this->assertSame('idle-wake', $command->payload['reason']);
        $this->assertSame($station->id, $command->station->id);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=StationCommandModelTest`
Expected: FAIL — table/model missing.

- [ ] **Step 3: Create the migration**

```php
<?php
// database/migrations/2026_07_01_150070_create_station_commands_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('station_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('status')->default('pending');
            $table->json('payload')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->string('error')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['station_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('station_commands');
    }
};
```

- [ ] **Step 4: Create the model**

```php
<?php
// app/Models/StationCommand.php
namespace App\Models;

use App\Enums\StationCommandStatus;
use App\Enums\StationCommandType;
use App\Models\Concerns\BelongsToOutlet;
use Database\Factories\StationCommandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['outlet_id', 'station_id', 'type', 'status', 'payload', 'dispatched_at', 'acknowledged_at', 'error', 'created_by'])]
class StationCommand extends Model
{
    /** @use HasFactory<StationCommandFactory> */
    use HasFactory;
    use BelongsToOutlet;

    protected function casts(): array
    {
        return [
            'type' => StationCommandType::class,
            'status' => StationCommandStatus::class,
            'payload' => 'array',
            'dispatched_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
```

- [ ] **Step 5: Create the factory**

```php
<?php
// database/factories/StationCommandFactory.php
namespace Database\Factories;

use App\Enums\StationCommandType;
use App\Models\Outlet;
use App\Models\Station;
use App\Models\StationCommand;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StationCommand> */
class StationCommandFactory extends Factory
{
    protected $model = StationCommand::class;

    public function definition(): array
    {
        return [
            'outlet_id' => Outlet::factory(),
            'station_id' => Station::factory(),
            'type' => StationCommandType::RefreshState->value,
            'status' => 'pending',
            'payload' => null,
            'created_by' => null,
        ];
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan migrate:fresh && php artisan test --filter=StationCommandModelTest`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: StationCommand model, migration, factory

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

### Task 12: Demo data seeder + full seed integration test

**Files:**
- Create: `database/seeders/DemoDataSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php` (call DemoDataSeeder after RolePermissionSeeder)
- Test: `tests/Feature/Foundation/SeedIntegrationTest.php`

**Interfaces:**
- Consumes: all models + `RolePermissionSeeder`.
- Produces: seeded demo world — 2 outlets, 4 users (one per role with known credentials), packages per outlet, stations per outlet. Demo credentials (all password `password`):
  - `developer@billingps5.local` (developer)
  - `superadmin@billingps5.local` (super_admin)
  - `operator@billingps5.local` (operator, outlet #1)
  - `member@billingps5.local` (member)

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Foundation/SeedIntegrationTest.php
namespace Tests\Feature\Foundation;

use App\Enums\RoleName;
use App\Models\Outlet;
use App\Models\Station;
use App\Models\TimePackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_demo_world_is_created(): void
    {
        $this->assertSame(2, Outlet::count());
        $this->assertGreaterThanOrEqual(4, User::count());
        $this->assertGreaterThan(0, TimePackage::count());
        $this->assertGreaterThan(0, Station::count());
    }

    public function test_demo_accounts_have_expected_roles(): void
    {
        $this->assertTrue(User::where('email', 'developer@billingps5.local')->first()->hasRole(RoleName::Developer->value));
        $this->assertTrue(User::where('email', 'superadmin@billingps5.local')->first()->hasRole(RoleName::SuperAdmin->value));

        $operator = User::where('email', 'operator@billingps5.local')->first();
        $this->assertTrue($operator->hasRole(RoleName::Operator->value));
        $this->assertNotNull($operator->outlet_id);

        $this->assertTrue(User::where('email', 'member@billingps5.local')->first()->hasRole(RoleName::Member->value));
    }

    public function test_seeded_member_starts_with_zero_balances(): void
    {
        $member = User::where('email', 'member@billingps5.local')->first();

        $this->assertSame(0, $member->wallet_balance);
        $this->assertSame(0, $member->remaining_minutes);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SeedIntegrationTest`
Expected: FAIL — `DemoDataSeeder` not found / demo accounts missing.

- [ ] **Step 3: Create the DemoDataSeeder**

```php
<?php
// database/seeders/DemoDataSeeder.php
namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Outlet;
use App\Models\Station;
use App\Models\TimePackage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $outlets = collect([
            ['name' => 'Outlet Jakarta', 'code' => 'JKT-01'],
            ['name' => 'Outlet Bandung', 'code' => 'BDG-01'],
        ])->map(fn (array $data) => Outlet::create([
            'name' => $data['name'],
            'code' => $data['code'],
            'slug' => Str::slug($data['name']),
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ]));

        $firstOutlet = $outlets->first();

        foreach ($outlets as $outlet) {
            foreach ([['30 Menit', 30, 10000], ['60 Menit', 60, 18000], ['120 Menit', 120, 34000]] as [$name, $minutes, $price]) {
                TimePackage::create([
                    'outlet_id' => $outlet->id,
                    'name' => $name,
                    'minutes' => $minutes,
                    'price' => $price,
                    'is_active' => true,
                ]);
            }

            for ($i = 1; $i <= 4; $i++) {
                Station::create([
                    'outlet_id' => $outlet->id,
                    'code' => $outlet->code.'-ST'.$i,
                    'name' => 'Station '.$i,
                    'status' => 'idle',
                    'app_mode' => 'qr',
                    'is_active' => true,
                ]);
            }
        }

        $accounts = [
            ['developer@billingps5.local', 'Developer', RoleName::Developer, null],
            ['superadmin@billingps5.local', 'Super Admin', RoleName::SuperAdmin, null],
            ['operator@billingps5.local', 'Operator Jakarta', RoleName::Operator, $firstOutlet->id],
            ['member@billingps5.local', 'Demo Member', RoleName::Member, null],
        ];

        foreach ($accounts as [$email, $name, $role, $outletId]) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'outlet_id' => $outletId,
                'is_active' => true,
            ]);
            $user->assignRole($role->value);
        }
    }
}
```

- [ ] **Step 4: Wire DemoDataSeeder into DatabaseSeeder**

```php
<?php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=SeedIntegrationTest`
Expected: PASS (3 tests).

- [ ] **Step 6: Full clean seed + whole suite**

Run: `php artisan migrate:fresh --seed && php artisan test`
Expected: migrations run clean, seed populates demo world, entire suite PASSES.

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "feat: demo data seeder + foundation integration tests

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
```

---

## Self-Review

**Spec coverage (§ from master design → task):**
- Multi-tenancy (single DB, outlet_id, global members) → Tasks 2, 3, 5–11 (outlet_id on operational tables, `BelongsToOutlet`), members global (no outlet_id on ledgers).
- Data model tables (§4) → outlets(T3), users(T2), time_packages(T5), wallet_transactions(T6), time_ledger_entries(T7), payments(T8), stations(T9), play_sessions(T10), station_commands(T11).
- Ledger-derived balances (§4.4) → T6 (wallet_balance), T7 (remaining_minutes).
- Roles + developer god-mode (§5) → T4.
- Enums (all statuses) → T1.
- Demo/seed + factories → T2–T12.
- **Out of scope (later phases, intentionally not here):** BillingService/purchase/session engine (Phase 2–3), Filament resources (Phase 4), member portal (Phase 5), device API/ADB (Phase 6), Android TV app (Phase 7), Reverb/payment gateway (Phase 8). Payment table exists now (T8) but webhook/crediting logic is Phase 8.

**Placeholder scan:** none — every step has complete code/commands.

**Type consistency check:** `wallet_balance:int`, `remaining_minutes:int` used consistently (T2 defines, T6/T7 verify). Enum casts consistent (`StationStatus`, `StationAppMode`, `PlaySessionStatus`, `StationCommandType/Status`). `BelongsToOutlet::outlet()` used by all operational models; `User::outlet()` defined separately in T2 (User doesn't use the trait to avoid duplicate method) — consistent. Deferred FK columns (`play_session_id` in T7, `current_session_id` in T9) are plain `unsignedBigInteger` + index, with real FKs added in T10 — consistent ordering.

**Note on rebuild:** Task 1 deletes old domain files. `AdminPanelProvider` (Filament) is kept but its referenced resources are removed; if `php artisan` errors on missing resource discovery, that is fine — Filament auto-discovers the (now empty) `Resources` dir. Verify `php artisan about` runs after Task 1.
