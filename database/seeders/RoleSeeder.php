<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::factory()->state(['name' => 'admin', 'description' => 'Администратор'])->create();
        Role::factory()->state(['name' => 'operator', 'description' => 'Оператор'])->create();
        Role::factory()->state(['name' => 'manager', 'description' => 'Менеджер магазина'])->create();
        Role::factory()->state(['name' => 'driver', 'description' => 'Курьер'])->create();
        Role::factory()->state(['name' => 'accountant', 'description' => 'Бухгалтер'])->create();
        Role::factory()->state(['name' => 'integration', 'description' => 'Интеграция'])->create();
    }
}
