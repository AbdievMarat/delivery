<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::query()->where('name', '=', 'admin')->first();

        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@admin.com',
        ])->roles()->attach($adminRole->id);

        $operatorRole = Role::query()->where('name', '=', 'operator')->first();
        $managerRole = Role::query()->where('name', '=', 'manager')->first();
        $driverRole = Role::query()->where('name', '=', 'driver')->first();
        $accountantRole = Role::query()->where('name', '=', 'accountant')->first();

        User::factory()
            ->count(5)
            ->hasAttached($operatorRole)
            ->create();

        User::factory()
            ->count(4)
            ->hasAttached($managerRole)
            ->create();
        User::factory()
            ->count(2)
            ->hasAttached($driverRole)
            ->create();

        User::factory()
            ->count(1)
            ->hasAttached($accountantRole)
            ->create();
    }
}
