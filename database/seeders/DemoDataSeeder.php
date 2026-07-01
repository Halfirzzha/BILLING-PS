<?php

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
