<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Adjust if your Filament admin uses a different model, like App\Models\Admin

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'sharmlink39@gmail.com'],
            [
                'name' => 'Amr',
                'password' => Hash::make('123456789aA@'),
            ]
        );
    }
}