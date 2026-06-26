<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Super Admin']);
        Role::firstOrCreate(['name' => 'Admin']);

        if (User::role('Super Admin')->doesntExist()) {
            User::query()
                ->oldest()
                ->first()
                ?->assignRole('Super Admin');
        }
    }
}
