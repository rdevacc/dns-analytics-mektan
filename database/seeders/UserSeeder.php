<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@dns.local',
            ],
            [
                'name' => 'Administrator',
                'password' => 'password',
                'is_active' => true,
            ]
        );
    }
}