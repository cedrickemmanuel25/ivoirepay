<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'app_name', 'value' => 'IvoirePay', 'group' => 'general', 'description' => 'Nom de l\'application'],
            ['key' => 'commission_rate', 'value' => '1.5', 'group' => 'transaction', 'description' => 'Taux de commission par défaut (%)'],
            ['key' => 'min_withdrawal', 'value' => '1000', 'group' => 'withdrawal', 'description' => 'Montant minimum de retrait'],
            ['key' => 'support_phone', 'value' => '+225 00 00 00 00', 'group' => 'support', 'description' => 'Numéro de téléphone du support'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
