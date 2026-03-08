<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'ASUS',          'slug' => 'asus'],
            ['name' => 'MSI',           'slug' => 'msi'],
            ['name' => 'Acer',          'slug' => 'acer'],
            ['name' => 'HP',            'slug' => 'hp'],
            ['name' => 'Dell',          'slug' => 'dell'],
            ['name' => 'Lenovo',        'slug' => 'lenovo'],
            ['name' => 'Apple',         'slug' => 'apple'],
            ['name' => 'Samsung',       'slug' => 'samsung'],
            ['name' => 'LG',            'slug' => 'lg'],
            ['name' => 'Intel',         'slug' => 'intel'],
            ['name' => 'AMD',           'slug' => 'amd'],
            ['name' => 'NVIDIA',        'slug' => 'nvidia'],
            ['name' => 'Kingston',      'slug' => 'kingston'],
            ['name' => 'Western Digital', 'slug' => 'western-digital'],
            ['name' => 'Seagate',       'slug' => 'seagate'],
            ['name' => 'Corsair',       'slug' => 'corsair'],
            ['name' => 'Logitech',      'slug' => 'logitech'],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(
                ['slug' => $brand['slug']],
                array_merge($brand, ['is_active' => true])
            );
        }
    }
}
