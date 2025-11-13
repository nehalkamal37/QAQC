<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::insert([
            ['name' => 'Admin'],
            ['name' => 'PM'],
            ['name' => 'Senior Reviewer'],
            ['name' => 'Engineer'],
            ['name' => 'Night Vision'],
        ]);
    }
}
