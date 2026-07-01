<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\RoleName;
use App\Models\Station;
use App\Models\TimePackage;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Outerweb\Settings\Facades\Setting;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (RoleName::cases() as $role) {
            Role::findOrCreate($role->value, 'web');
        }

        $admin = User::query()->firstOrCreate([
            'email' => 'admin@billingps5.local',
        ], [
            'name' => 'System Admin',
            'phone' => '081200000001',
            'password' => 'password',
        ]);
        $admin->syncRoles([RoleName::SuperAdmin->value]);

        $developer = User::query()->updateOrCreate([
            'email' => 'Halfirzzha@gmail.com',
        ], [
            'name' => 'Halfirzzha Developer',
            'phone' => '081200000003',
            'password' => 'password',
            'is_active' => true,
        ]);
        $developer->syncRoles([
            RoleName::Developer->value,
            RoleName::SuperAdmin->value,
        ]);

        $member = User::query()->firstOrCreate([
            'email' => 'member@billingps5.local',
        ], [
            'name' => 'Demo Member',
            'phone' => '081200000002',
            'password' => 'password',
        ]);
        $member->syncRoles([RoleName::Member->value]);

        collect([
            ['name' => 'Paket 1 Jam', 'slug' => 'paket-1-jam', 'minutes' => 60, 'price' => 25000, 'description' => 'Paket reguler satu jam.'],
            ['name' => 'Paket 2 Jam', 'slug' => 'paket-2-jam', 'minutes' => 120, 'price' => 45000, 'description' => 'Lebih hemat untuk sesi yang lebih panjang.'],
            ['name' => 'Paket 3 Jam', 'slug' => 'paket-3-jam', 'minutes' => 180, 'price' => 60000, 'description' => 'Paket maraton untuk main rame-rame.'],
        ])->map(fn (array $package) => TimePackage::query()->updateOrCreate(
            ['slug' => $package['slug']],
            $package,
        ));

        $starterPackage = TimePackage::query()->where('slug', 'paket-1-jam')->firstOrFail();

        collect([
            ['name' => 'Station A', 'code' => 'ST-A', 'tv_label' => 'TV 55 A', 'ps_label' => 'PS5 A', 'location' => 'Lantai 1'],
            ['name' => 'Station B', 'code' => 'ST-B', 'tv_label' => 'TV 55 B', 'ps_label' => 'PS5 B', 'location' => 'Lantai 1'],
        ])->each(fn (array $station) => Station::query()->updateOrCreate(
            ['code' => $station['code']],
            array_merge($station, [
                'status' => 'idle',
                'device_status' => 'offline',
                'app_mode' => 'qr',
                'default_hourly_rate' => 25000,
                'is_active' => true,
                'qr_token' => Station::query()->where('code', $station['code'])->value('qr_token') ?? (string) \Illuminate\Support\Str::uuid(),
                'device_token' => Station::query()->where('code', $station['code'])->value('device_token') ?? \Illuminate\Support\Str::random(40),
            ]),
        ));

        Setting::set([
            'business.name' => 'Billing PS5',
            'business.currency' => 'IDR',
            'billing.default_hourly_rate' => 25000,
            'billing.allow_wallet_purchase' => true,
        ]);

        $billingService = app(BillingService::class);

        if ($member->walletTransactions()->count() === 0) {
            $billingService->topUpWallet($member, 100000, PaymentMethod::Cash, $admin, 'Initial demo balance');
            $billingService->purchaseTimePackage($member, $starterPackage, PaymentMethod::Wallet, $admin, 'Initial demo package');
        }
    }
}
