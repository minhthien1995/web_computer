<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@webcomputer.vn'],
            [
                'name'               => 'Admin',
                'phone'              => '0901234567',
                'password'           => Hash::make('Admin@123456'),
                'email_verified_at'  => now(),
            ]
        );
        $admin->assignRole('admin');

        $tech = User::firstOrCreate(
            ['email' => 'tech@webcomputer.vn'],
            [
                'name'              => 'Ky Thuat Vien',
                'phone'             => '0901234568',
                'password'          => Hash::make('Admin@123456'),
                'email_verified_at' => now(),
            ]
        );
        $tech->assignRole('technician');
    }
}
