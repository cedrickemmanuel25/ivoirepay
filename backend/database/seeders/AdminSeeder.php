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
        // firstOrCreate: seeding twice must not fail on the unique email/phone
        \App\Models\User::firstOrCreate(['email' => 'admin@ivoirepay.ci'], [
            'name' => 'Admin IvoirePay',
            'phone' => '2250000000000',
            'role' => 'admin',
            'pin_hash' => \Illuminate\Support\Facades\Hash::make('123456'),
            'is_active' => true,
        ]);
    }
}
