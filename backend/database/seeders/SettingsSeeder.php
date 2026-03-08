<?php

namespace DatabaseSeeders;

use IlluminateDatabaseSeeder;
use IlluminateSupportFacadesDB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'Web Computer Store', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_phone', 'value' => '1900 1234', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_email', 'value' => 'info@webcomputer.vn', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_address', 'value' => '123 Nguyen Hue, Q1, TP.HCM', 'type' => 'string', 'group' => 'general'],
            ['key' => 'payment_cod_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'payment'],
            ['key' => 'payment_momo_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'payment'],
            ['key' => 'payment_zalopay_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'payment'],
            ['key' => 'payment_vnpay_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'payment'],
            ['key' => 'payment_sepay_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'payment'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                [...$setting, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
