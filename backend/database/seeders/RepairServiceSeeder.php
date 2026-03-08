<?php

namespace Database\Seeders;

use App\Models\RepairService;
use Illuminate\Database\Seeder;

class RepairServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Sua chua Laptop',
                'slug' => 'sua-chua-laptop',
                'category' => 'laptop',
                'base_price' => 150000,
                'estimated_days_min' => 1,
                'estimated_days_max' => 3,
                'description' => 'Kiem tra va sua chua cac loi phan cung, phan mem tren laptop.',
            ],
            [
                'name' => 'Thay man hinh Laptop',
                'slug' => 'thay-man-hinh-laptop',
                'category' => 'laptop',
                'base_price' => 800000,
                'estimated_days_min' => 2,
                'estimated_days_max' => 5,
                'description' => 'Thay man hinh laptop chinh hang, bao hanh 3 thang.',
            ],
            [
                'name' => 'Ve sinh Laptop',
                'slug' => 've-sinh-laptop',
                'category' => 'laptop',
                'base_price' => 100000,
                'estimated_days_min' => 1,
                'estimated_days_max' => 1,
                'description' => 'Ve sinh toan bo linh kien, thay keo tan nhiet.',
            ],
            [
                'name' => 'Sua chua dien thoai',
                'slug' => 'sua-chua-dien-thoai',
                'category' => 'phone',
                'base_price' => 100000,
                'estimated_days_min' => 1,
                'estimated_days_max' => 3,
                'description' => 'Sua chua cac loi phan cung, phan mem tren dien thoai.',
            ],
            [
                'name' => 'Nang cap RAM/SSD',
                'slug' => 'nang-cap-ram-ssd',
                'category' => 'laptop',
                'base_price' => 80000,
                'estimated_days_min' => 1,
                'estimated_days_max' => 1,
                'description' => 'Nang cap RAM va SSD de tang hieu nang may tinh.',
            ],
            [
                'name' => 'Cai dat Windows',
                'slug' => 'cai-dat-windows',
                'category' => 'laptop',
                'base_price' => 250000,
                'estimated_days_min' => 1,
                'estimated_days_max' => 1,
                'description' => 'Cai dat Windows ban quyen, driver day du.',
            ],
            [
                'name' => 'Thay man hinh dien thoai',
                'slug' => 'thay-man-hinh-dien-thoai',
                'category' => 'phone',
                'base_price' => 500000,
                'estimated_days_min' => 1,
                'estimated_days_max' => 2,
                'description' => 'Thay man hinh dien thoai chinh hang, bao hanh 3 thang.',
            ],
        ];

        foreach ($services as $idx => $service) {
            RepairService::firstOrCreate(
                ['slug' => $service['slug']],
                array_merge($service, ['is_active' => true, 'sort_order' => $idx])
            );
        }
    }
}
