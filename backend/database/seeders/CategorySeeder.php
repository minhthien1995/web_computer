<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Laptop', 'slug' => 'laptop',
                'children' => [
                    ['name' => 'Laptop Gaming',    'slug' => 'laptop-gaming'],
                    ['name' => 'Laptop Van Phong',  'slug' => 'laptop-van-phong'],
                    ['name' => 'MacBook',           'slug' => 'macbook'],
                ],
            ],
            [
                'name' => 'PC Ban', 'slug' => 'pc-ban',
                'children' => [
                    ['name' => 'PC Gaming',    'slug' => 'pc-gaming'],
                    ['name' => 'PC Dong Bo',   'slug' => 'pc-dong-bo'],
                ],
            ],
            [
                'name' => 'Linh Kien', 'slug' => 'linh-kien',
                'children' => [
                    ['name' => 'CPU',       'slug' => 'cpu'],
                    ['name' => 'RAM',       'slug' => 'ram'],
                    ['name' => 'O Cung',    'slug' => 'o-cung'],
                    ['name' => 'VGA',       'slug' => 'vga'],
                    ['name' => 'Mainboard', 'slug' => 'mainboard'],
                    ['name' => 'PSU',       'slug' => 'psu'],
                ],
            ],
            ['name' => 'Man Hinh', 'slug' => 'man-hinh', 'children' => []],
            [
                'name' => 'Phu Kien', 'slug' => 'phu-kien',
                'children' => [
                    ['name' => 'Chuot',    'slug' => 'chuot'],
                    ['name' => 'Ban Phim', 'slug' => 'ban-phim'],
                    ['name' => 'Tai Nghe', 'slug' => 'tai-nghe'],
                    ['name' => 'Webcam',   'slug' => 'webcam'],
                ],
            ],
            ['name' => 'Mang & Wifi', 'slug' => 'mang-wifi', 'children' => []],
        ];

        foreach ($categories as $catData) {
            $children = $catData['children'] ?? [];
            unset($catData['children']);

            $parent = Category::firstOrCreate(
                ['slug' => $catData['slug']],
                array_merge($catData, ['is_active' => true, 'sort_order' => 0])
            );

            foreach ($children as $childData) {
                Category::firstOrCreate(
                    ['slug' => $childData['slug']],
                    array_merge($childData, ['parent_id' => $parent->id, 'is_active' => true, 'sort_order' => 0])
                );
            }
        }
    }
}
