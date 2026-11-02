<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Admin IvoirePay',
            'phone' => '2250000000000',
            'email' => 'admin@ivoirepay.ci',
            'role' => 'admin',
            'pin_hash' => \Illuminate\Support\Facades\Hash::make('123456'),
            'is_active' => true,
        ]);
    }
}
