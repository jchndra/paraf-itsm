<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $users = [
            [
                'name' => 'Pemohon Contoh',
                'email' => 'pemohon@paraf.local',
                'password' => Hash::make(env('SEEDER_PEMOHON_PASSWORD', 'Password123!')),
                'is_active' => true,
            ],
            [
                'name' => 'Admin Frontdesk',
                'email' => 'admin@paraf.local',
                'password' => Hash::make(env('SEEDER_ADMIN_PASSWORD', 'Password123!')),
                'is_active' => true,
            ],
            [
                'name' => 'Change Manager',
                'email' => 'cm@paraf.local',
                'password' => Hash::make(env('SEEDER_CHANGE_MANAGER_PASSWORD', 'Password123!')),
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
