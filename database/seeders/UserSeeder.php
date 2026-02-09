<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Underwriter user
        User::updateOrCreate(
            ['email' => 'uw@insurance.local'],
            [
                'name' => 'Underwriter',
                'password' => Hash::make('password123'),
                'role' => 'underwriter',
            ]
        );

        // Marketing user
        User::updateOrCreate(
            ['email' => 'marketing@insurance.local'],
            [
                'name' => 'Marketing',
                'password' => Hash::make('password123'),
                'role' => 'marketing',
            ]
        );

        // Update existing admin to underwriter (if exists)
        User::where('email', 'admin@insurance.local')
            ->update(['role' => 'underwriter']);
    }
}
