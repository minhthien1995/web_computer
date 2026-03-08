<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpec;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Lookup categories by slug
        $cats = Category::pluck('id', 'slug');
        // Lookup brands by slug
        $brands = Brand::pluck('id', 'slug');

        $products = [
            // 1. ASUS TUF Gaming A15
            [
                'category_id'     => $cats['laptop-gaming'] ?? null,
                'brand_id'        => $brands['asus'] ?? null,
                'name'            => 'ASUS TUF Gaming A15 FA507NV',
                'slug'            => 'asus-tuf-gaming-a15-fa507nv',
                'sku'             => 'ASUS-TUF-A15-FA507NV',
                'description'     => 'Laptop gaming ASUS TUF A15 trang bị AMD Ryzen 7, NVIDIA RTX 4060, màn hình 144Hz. Thiết kế quân đội bền bỉ, tản nhiệt mạnh mẽ.',
                'base_price'      => 22990000,
                'sale_price'      => 21490000,
                'stock_qty'       => 15,
                'is_featured'     => true,
                'status'          => 'active',
                'warranty_months' => 24,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/1a1a2e/white?text=ASUS+TUF+A15', 'is_primary' => true],
                    ['url' => 'https://placehold.co/800x600/16213e/white?text=ASUS+TUF+Side'],
                ],
                'specs'           => [
                    ['Thông Số', 'CPU', 'AMD Ryzen 7 7745HX'],
                    ['Thông Số', 'RAM', '16GB DDR5 4800MHz'],
                    ['Thông Số', 'Ổ Cứng', '512GB NVMe SSD'],
                    ['Thông Số', 'GPU', 'NVIDIA GeForce RTX 4060 8GB'],
                    ['Thông Số', 'Màn Hình', '15.6" FHD 144Hz IPS'],
                ],
            ],
            // 2. MSI Thin GF63
            [
                'category_id'     => $cats['laptop-gaming'] ?? null,
                'brand_id'        => $brands['msi'] ?? null,
                'name'            => 'MSI Thin GF63 12UC',
                'slug'            => 'msi-thin-gf63-12uc',
                'sku'             => 'MSI-GF63-12UC',
                'description'     => 'Laptop gaming mỏng nhẹ MSI Thin GF63 với Intel Core i5 thế hệ 12, RTX 3050. Phù hợp cho gamer di động.',
                'base_price'      => 19990000,
                'sale_price'      => 18490000,
                'stock_qty'       => 10,
                'is_featured'     => true,
                'status'          => 'active',
                'warranty_months' => 24,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/0f3460/white?text=MSI+GF63', 'is_primary' => true],
                    ['url' => 'https://placehold.co/800x600/533483/white?text=MSI+GF63+Open'],
                ],
                'specs'           => [
                    ['Thông Số', 'CPU', 'Intel Core i5-12450H'],
                    ['Thông Số', 'RAM', '8GB DDR4 3200MHz'],
                    ['Thông Số', 'Ổ Cứng', '512GB NVMe SSD'],
                    ['Thông Số', 'GPU', 'NVIDIA GeForce RTX 3050 4GB'],
                    ['Thông Số', 'Màn Hình', '15.6" FHD 144Hz IPS'],
                ],
            ],
            // 3. HP Pavilion 15
            [
                'category_id'     => $cats['laptop-van-phong'] ?? null,
                'brand_id'        => $brands['hp'] ?? null,
                'name'            => 'HP Pavilion 15-eg3085TU',
                'slug'            => 'hp-pavilion-15-eg3085tu',
                'sku'             => 'HP-PAV15-EG3085TU',
                'description'     => 'Laptop văn phòng HP Pavilion 15 với Intel Core i7 thế hệ 13, màn hình Full HD. Lý tưởng cho công việc hàng ngày.',
                'base_price'      => 15990000,
                'sale_price'      => null,
                'stock_qty'       => 20,
                'is_featured'     => true,
                'status'          => 'active',
                'warranty_months' => 12,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/0096c7/white?text=HP+Pavilion+15', 'is_primary' => true],
                ],
                'specs'           => [
                    ['Thông Số', 'CPU', 'Intel Core i7-1355U'],
                    ['Thông Số', 'RAM', '8GB DDR4 3200MHz'],
                    ['Thông Số', 'Ổ Cứng', '512GB NVMe SSD'],
                    ['Thông Số', 'Màn Hình', '15.6" FHD IPS Anti-glare'],
                    ['Thông Số', 'Pin', '41Wh, Up to 8 hours'],
                ],
            ],
            // 4. Dell Inspiron 15 3520
            [
                'category_id'     => $cats['laptop-van-phong'] ?? null,
                'brand_id'        => $brands['dell'] ?? null,
                'name'            => 'Dell Inspiron 15 3520',
                'slug'            => 'dell-inspiron-15-3520',
                'sku'             => 'DELL-INS15-3520',
                'description'     => 'Laptop văn phòng Dell Inspiron 15 bền bỉ, đáng tin cậy với Intel Core i5. Hiệu năng ổn định cho mọi tác vụ văn phòng.',
                'base_price'      => 14490000,
                'sale_price'      => null,
                'stock_qty'       => 25,
                'is_featured'     => true,
                'status'          => 'active',
                'warranty_months' => 12,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/004e98/white?text=Dell+Inspiron+15', 'is_primary' => true],
                    ['url' => 'https://placehold.co/800x600/0070cd/white?text=Dell+Inspiron+Side'],
                ],
                'specs'           => [
                    ['Thông Số', 'CPU', 'Intel Core i5-1235U'],
                    ['Thông Số', 'RAM', '8GB DDR4 3200MHz'],
                    ['Thông Số', 'Ổ Cứng', '512GB SSD'],
                    ['Thông Số', 'Màn Hình', '15.6" FHD Anti-Glare WVA'],
                    ['Thông Số', 'Hệ Điều Hành', 'Windows 11 Home'],
                ],
            ],
            // 5. Apple MacBook Air M2
            [
                'category_id'     => $cats['macbook'] ?? null,
                'brand_id'        => $brands['apple'] ?? null,
                'name'            => 'Apple MacBook Air M2 2022',
                'slug'            => 'apple-macbook-air-m2-2022',
                'sku'             => 'APPLE-MBA-M2-2022',
                'description'     => 'MacBook Air M2 với chip Apple M2 mạnh mẽ, thiết kế siêu mỏng, pin cực bền. Màn hình Liquid Retina 13.6".',
                'base_price'      => 29990000,
                'sale_price'      => null,
                'stock_qty'       => 8,
                'is_featured'     => true,
                'status'          => 'active',
                'warranty_months' => 12,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/555555/white?text=MacBook+Air+M2', 'is_primary' => true],
                    ['url' => 'https://placehold.co/800x600/888888/white?text=MacBook+Air+Open'],
                    ['url' => 'https://placehold.co/800x600/aaaaaa/white?text=MacBook+Air+Side'],
                ],
                'specs'           => [
                    ['Thông Số', 'CPU', 'Apple M2 (8-core CPU)'],
                    ['Thông Số', 'RAM', '8GB Unified Memory'],
                    ['Thông Số', 'Ổ Cứng', '256GB SSD'],
                    ['Thông Số', 'GPU', 'Apple M2 (8-core GPU)'],
                    ['Thông Số', 'Màn Hình', '13.6" Liquid Retina 2560x1664'],
                ],
            ],
            // 6. ASUS VivoBook 15
            [
                'category_id'     => $cats['laptop-van-phong'] ?? null,
                'brand_id'        => $brands['asus'] ?? null,
                'name'            => 'ASUS VivoBook 15 X1504VA',
                'slug'            => 'asus-vivobook-15-x1504va',
                'sku'             => 'ASUS-VB15-X1504VA',
                'description'     => 'Laptop văn phòng ASUS VivoBook 15 mỏng nhẹ, Intel Core i5 thế hệ 13, màn hình OLED sắc nét.',
                'base_price'      => 13990000,
                'sale_price'      => null,
                'stock_qty'       => 30,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 24,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/2196f3/white?text=ASUS+VivoBook+15', 'is_primary' => true],
                ],
                'specs'           => [
                    ['Thông Số', 'CPU', 'Intel Core i5-1335U'],
                    ['Thông Số', 'RAM', '8GB DDR4 3200MHz'],
                    ['Thông Số', 'Ổ Cứng', '512GB PCIe NVMe SSD'],
                    ['Thông Số', 'Màn Hình', '15.6" FHD IPS 60Hz'],
                ],
            ],
            // 7. Acer Aspire 7
            [
                'category_id'     => $cats['laptop-gaming'] ?? null,
                'brand_id'        => $brands['acer'] ?? null,
                'name'            => 'Acer Aspire 7 A715-76G',
                'slug'            => 'acer-aspire-7-a715-76g',
                'sku'             => 'ACER-A7-A715-76G',
                'description'     => 'Laptop gaming tầm trung Acer Aspire 7 với Intel Core i5 thế hệ 12, GTX 1650. Lựa chọn tốt cho game thủ budget.',
                'base_price'      => 17490000,
                'sale_price'      => 15990000,
                'stock_qty'       => 12,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 12,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/83c5be/white?text=Acer+Aspire+7', 'is_primary' => true],
                    ['url' => 'https://placehold.co/800x600/006d77/white?text=Acer+Aspire+7+Back'],
                ],
                'specs'           => [
                    ['Thông Số', 'CPU', 'Intel Core i5-12450H'],
                    ['Thông Số', 'RAM', '8GB DDR4 3200MHz'],
                    ['Thông Số', 'Ổ Cứng', '512GB NVMe SSD'],
                    ['Thông Số', 'GPU', 'NVIDIA GeForce GTX 1650 4GB'],
                    ['Thông Số', 'Màn Hình', '15.6" FHD IPS 144Hz'],
                ],
            ],
            // 8. Lenovo IdeaPad Gaming 3
            [
                'category_id'     => $cats['laptop-gaming'] ?? null,
                'brand_id'        => $brands['lenovo'] ?? null,
                'name'            => 'Lenovo IdeaPad Gaming 3 15ARH7',
                'slug'            => 'lenovo-ideapad-gaming-3-15arh7',
                'sku'             => 'LNV-IPG3-15ARH7',
                'description'     => 'Laptop gaming Lenovo IdeaPad Gaming 3 với AMD Ryzen 5, RX 6500M. Hiệu năng gaming tốt với giá cả hợp lý.',
                'base_price'      => 18990000,
                'sale_price'      => null,
                'stock_qty'       => 7,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 12,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/e63946/white?text=Lenovo+Gaming+3', 'is_primary' => true],
                ],
                'specs'           => [
                    ['Thông Số', 'CPU', 'AMD Ryzen 5 6600H'],
                    ['Thông Số', 'RAM', '8GB DDR5 4800MHz'],
                    ['Thông Số', 'Ổ Cứng', '512GB NVMe SSD'],
                    ['Thông Số', 'GPU', 'AMD Radeon RX 6500M 4GB'],
                    ['Thông Số', 'Màn Hình', '15.6" FHD IPS 120Hz'],
                ],
            ],
            // 9. Samsung Odyssey G5 27"
            [
                'category_id'     => $cats['man-hinh'] ?? null,
                'brand_id'        => $brands['samsung'] ?? null,
                'name'            => 'Samsung Odyssey G5 27" QHD 165Hz',
                'slug'            => 'samsung-odyssey-g5-27-qhd-165hz',
                'sku'             => 'SAM-OG5-27-QHD',
                'description'     => 'Màn hình cong Samsung Odyssey G5 27" QHD 165Hz, độ cong 1000R. Hoàn hảo cho gaming và giải trí đa phương tiện.',
                'base_price'      => 8990000,
                'sale_price'      => null,
                'stock_qty'       => 20,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 36,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/1b4332/white?text=Samsung+Odyssey+G5', 'is_primary' => true],
                    ['url' => 'https://placehold.co/800x600/2d6a4f/white?text=Samsung+G5+Angle'],
                ],
                'specs'           => [
                    ['Thông Số', 'Kích Thước', '27"'],
                    ['Thông Số', 'Độ Phân Giải', '2560x1440 (QHD)'],
                    ['Thông Số', 'Tần Số Quét', '165Hz'],
                    ['Thông Số', 'Thời Gian Phản Hồi', '1ms (MPRT)'],
                    ['Thông Số', 'Tấm Nền', 'VA Curved 1000R'],
                ],
            ],
            // 10. LG UltraGear 24GN60R
            [
                'category_id'     => $cats['man-hinh'] ?? null,
                'brand_id'        => $brands['lg'] ?? null,
                'name'            => 'LG UltraGear 24GN60R-B 24" IPS 144Hz',
                'slug'            => 'lg-ultragear-24gn60r-b-24-ips-144hz',
                'sku'             => 'LG-UG24-24GN60R',
                'description'     => 'Màn hình gaming LG UltraGear 24" IPS 144Hz, HDR10. Màu sắc chân thực, tốc độ cao cho trải nghiệm gaming đỉnh cao.',
                'base_price'      => 5490000,
                'sale_price'      => null,
                'stock_qty'       => 35,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 36,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/c0392b/white?text=LG+UltraGear+24', 'is_primary' => true],
                ],
                'specs'           => [
                    ['Thông Số', 'Kích Thước', '24"'],
                    ['Thông Số', 'Độ Phân Giải', '1920x1080 (FHD)'],
                    ['Thông Số', 'Tần Số Quét', '144Hz'],
                    ['Thông Số', 'Thời Gian Phản Hồi', '1ms (GtG)'],
                    ['Thông Số', 'Tấm Nền', 'IPS HDR10'],
                ],
            ],
            // 11. Kingston 16GB DDR4
            [
                'category_id'     => $cats['ram'] ?? $cats['linh-kien'] ?? null,
                'brand_id'        => $brands['kingston'] ?? null,
                'name'            => 'Kingston 16GB DDR4 3200MHz FURY Beast',
                'slug'            => 'kingston-16gb-ddr4-3200mhz-fury-beast',
                'sku'             => 'KNG-16G-DDR4-3200',
                'description'     => 'RAM Kingston FURY Beast 16GB DDR4 3200MHz, tương thích rộng rãi. Hiệu năng cao, ổn định cho PC và laptop.',
                'base_price'      => 890000,
                'sale_price'      => null,
                'stock_qty'       => 50,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 12,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/2c3e50/white?text=Kingston+16GB+DDR4', 'is_primary' => true],
                ],
                'specs'           => [
                    ['Thông Số', 'Dung Lượng', '16GB (1x16GB)'],
                    ['Thông Số', 'Loại', 'DDR4'],
                    ['Thông Số', 'Tốc Độ', '3200MHz'],
                    ['Thông Số', 'Điện Áp', '1.35V'],
                ],
            ],
            // 12. WD Blue 1TB SSD
            [
                'category_id'     => $cats['o-cung'] ?? $cats['linh-kien'] ?? null,
                'brand_id'        => $brands['western-digital'] ?? null,
                'name'            => 'Western Digital Blue SN570 1TB NVMe SSD',
                'slug'            => 'western-digital-blue-sn570-1tb-nvme-ssd',
                'sku'             => 'WD-BLUE-SN570-1TB',
                'description'     => 'SSD WD Blue SN570 1TB NVMe, tốc độ đọc 3500MB/s. Nâng cấp lưu trữ lý tưởng cho PC và laptop.',
                'base_price'      => 1990000,
                'sale_price'      => null,
                'stock_qty'       => 40,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 60,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/0066cc/white?text=WD+Blue+1TB+SSD', 'is_primary' => true],
                ],
                'specs'           => [
                    ['Thông Số', 'Dung Lượng', '1TB'],
                    ['Thông Số', 'Chuẩn Kết Nối', 'PCIe Gen3 NVMe M.2 2280'],
                    ['Thông Số', 'Tốc Độ Đọc', '3500 MB/s'],
                    ['Thông Số', 'Tốc Độ Ghi', '3000 MB/s'],
                ],
            ],
            // 13. Seagate BarraCuda 2TB
            [
                'category_id'     => $cats['o-cung'] ?? $cats['linh-kien'] ?? null,
                'brand_id'        => $brands['seagate'] ?? null,
                'name'            => 'Seagate BarraCuda 2TB HDD 7200RPM',
                'slug'            => 'seagate-barracuda-2tb-hdd-7200rpm',
                'sku'             => 'SEA-BARCUDA-2TB',
                'description'     => 'HDD Seagate BarraCuda 2TB 7200RPM, cache 256MB. Lưu trữ dung lượng lớn với độ bền cao.',
                'base_price'      => 1490000,
                'sale_price'      => null,
                'stock_qty'       => 30,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 24,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/1a1a1a/white?text=Seagate+BarraCuda+2TB', 'is_primary' => true],
                ],
                'specs'           => [
                    ['Thông Số', 'Dung Lượng', '2TB'],
                    ['Thông Số', 'Tốc Độ Quay', '7200 RPM'],
                    ['Thông Số', 'Cache', '256MB'],
                    ['Thông Số', 'Chuẩn Kết Nối', 'SATA 6Gb/s'],
                ],
            ],
            // 14. Corsair K70 RGB MK.2
            [
                'category_id'     => $cats['ban-phim'] ?? $cats['phu-kien'] ?? null,
                'brand_id'        => $brands['corsair'] ?? null,
                'name'            => 'Corsair K70 RGB MK.2 Mechanical Keyboard',
                'slug'            => 'corsair-k70-rgb-mk2-mechanical-keyboard',
                'sku'             => 'CRS-K70-RGB-MK2',
                'description'     => 'Bàn phím cơ Corsair K70 RGB MK.2 với switch Cherry MX, đèn RGB 16.8 triệu màu. Chuẩn mực cho game thủ.',
                'base_price'      => 3990000,
                'sale_price'      => null,
                'stock_qty'       => 15,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 24,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/ffbe0b/black?text=Corsair+K70+RGB', 'is_primary' => true],
                    ['url' => 'https://placehold.co/800x600/fb5607/white?text=K70+RGB+Detail'],
                ],
                'specs'           => [
                    ['Thông Số', 'Switch', 'Cherry MX Red'],
                    ['Thông Số', 'Đèn Nền', 'RGB 16.8 triệu màu'],
                    ['Thông Số', 'Kết Nối', 'USB Type-A'],
                    ['Thông Số', 'Layout', 'Full-size (104 keys)'],
                ],
            ],
            // 15. Logitech G502 X Plus
            [
                'category_id'     => $cats['chuot'] ?? $cats['phu-kien'] ?? null,
                'brand_id'        => $brands['logitech'] ?? null,
                'name'            => 'Logitech G502 X Plus Wireless Gaming Mouse',
                'slug'            => 'logitech-g502-x-plus-wireless-gaming-mouse',
                'sku'             => 'LOG-G502X-PLUS',
                'description'     => 'Chuột gaming không dây Logitech G502 X Plus với LIGHTFORCE switch, cảm biến HERO 25K, đèn LIGHTSPEED.',
                'base_price'      => 1990000,
                'sale_price'      => null,
                'stock_qty'       => 25,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 24,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/3a86ff/white?text=Logitech+G502+X', 'is_primary' => true],
                ],
                'specs'           => [
                    ['Thông Số', 'Cảm Biến', 'HERO 25K'],
                    ['Thông Số', 'DPI', '25,600 DPI max'],
                    ['Thông Số', 'Kết Nối', 'LIGHTSPEED Wireless + Bluetooth'],
                    ['Thông Số', 'Pin', 'Up to 130 hours'],
                ],
            ],
            // 16. Intel Core i7-13700K
            [
                'category_id'     => $cats['cpu'] ?? $cats['linh-kien'] ?? null,
                'brand_id'        => $brands['intel'] ?? null,
                'name'            => 'Intel Core i7-13700K Processor',
                'slug'            => 'intel-core-i7-13700k-processor',
                'sku'             => 'INTEL-I7-13700K',
                'description'     => 'CPU Intel Core i7-13700K thế hệ 13, 16 nhân 24 luồng, boost đến 5.4GHz. Hiệu năng đỉnh cao cho PC gaming và workstation.',
                'base_price'      => 10990000,
                'sale_price'      => null,
                'stock_qty'       => 10,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 36,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/0068b5/white?text=Intel+i7-13700K', 'is_primary' => true],
                ],
                'specs'           => [
                    ['Thông Số', 'Socket', 'LGA1700'],
                    ['Thông Số', 'Số Nhân/Luồng', '16C/24T (8P+8E)'],
                    ['Thông Số', 'Base Clock', '3.4 GHz'],
                    ['Thông Số', 'Boost Clock', '5.4 GHz'],
                    ['Thông Số', 'TDP', '125W'],
                ],
            ],
            // 17. AMD Ryzen 7 7700X
            [
                'category_id'     => $cats['cpu'] ?? $cats['linh-kien'] ?? null,
                'brand_id'        => $brands['amd'] ?? null,
                'name'            => 'AMD Ryzen 7 7700X Processor',
                'slug'            => 'amd-ryzen-7-7700x-processor',
                'sku'             => 'AMD-R7-7700X',
                'description'     => 'CPU AMD Ryzen 7 7700X Zen 4, 8 nhân 16 luồng, boost 5.4GHz. Hiệu năng gaming vượt trội với nền tảng AM5.',
                'base_price'      => 9490000,
                'sale_price'      => null,
                'stock_qty'       => 12,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 36,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/ed1c24/white?text=AMD+Ryzen+7+7700X', 'is_primary' => true],
                ],
                'specs'           => [
                    ['Thông Số', 'Socket', 'AM5'],
                    ['Thông Số', 'Số Nhân/Luồng', '8C/16T'],
                    ['Thông Số', 'Base Clock', '4.5 GHz'],
                    ['Thông Số', 'Boost Clock', '5.4 GHz'],
                    ['Thông Số', 'TDP', '105W'],
                ],
            ],
            // 18. ASUS ROG Strix B650E-F
            [
                'category_id'     => $cats['mainboard'] ?? $cats['linh-kien'] ?? null,
                'brand_id'        => $brands['asus'] ?? null,
                'name'            => 'ASUS ROG Strix B650E-F Gaming WiFi Mainboard',
                'slug'            => 'asus-rog-strix-b650e-f-gaming-wifi',
                'sku'             => 'ASUS-ROG-B650E-F',
                'description'     => 'Mainboard ASUS ROG Strix B650E-F hỗ trợ AMD AM5, DDR5, PCIe 5.0, WiFi 6E. Nền tảng lý tưởng cho hệ thống cao cấp.',
                'base_price'      => 8990000,
                'sale_price'      => null,
                'stock_qty'       => 8,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 36,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/222222/white?text=ASUS+ROG+B650E-F', 'is_primary' => true],
                    ['url' => 'https://placehold.co/800x600/444444/white?text=ROG+B650E+Detail'],
                ],
                'specs'           => [
                    ['Thông Số', 'Socket', 'AM5 (LGA1718)'],
                    ['Thông Số', 'Chipset', 'AMD B650E'],
                    ['Thông Số', 'RAM', '4x DDR5 DIMM, up to 128GB'],
                    ['Thông Số', 'PCIe', 'PCIe 5.0 x16 + PCIe 4.0 x4'],
                    ['Thông Số', 'Kết Nối', 'WiFi 6E, 2.5G LAN'],
                ],
            ],
            // 19. Corsair Vengeance 32GB DDR5
            [
                'category_id'     => $cats['ram'] ?? $cats['linh-kien'] ?? null,
                'brand_id'        => $brands['corsair'] ?? null,
                'name'            => 'Corsair Vengeance 32GB DDR5 5600MHz (2x16GB)',
                'slug'            => 'corsair-vengeance-32gb-ddr5-5600mhz',
                'sku'             => 'CRS-VNG-32G-DDR5-5600',
                'description'     => 'RAM Corsair Vengeance DDR5 32GB kit (2x16GB) 5600MHz, tương thích Intel 12th/13th gen và AMD Ryzen 7000.',
                'base_price'      => 2990000,
                'sale_price'      => null,
                'stock_qty'       => 20,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 12,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/6c1b1b/white?text=Corsair+DDR5+32GB', 'is_primary' => true],
                ],
                'specs'           => [
                    ['Thông Số', 'Dung Lượng', '32GB (2x16GB)'],
                    ['Thông Số', 'Loại', 'DDR5'],
                    ['Thông Số', 'Tốc Độ', '5600MHz'],
                    ['Thông Số', 'Điện Áp', '1.25V'],
                ],
            ],
            // 20. MSI GeForce RTX 4060 Ti
            [
                'category_id'     => $cats['vga'] ?? $cats['linh-kien'] ?? null,
                'brand_id'        => $brands['msi'] ?? null,
                'name'            => 'MSI GeForce RTX 4060 Ti Gaming X 8GB',
                'slug'            => 'msi-geforce-rtx-4060-ti-gaming-x-8gb',
                'sku'             => 'MSI-RTX4060TI-8G',
                'description'     => 'Card đồ họa MSI GeForce RTX 4060 Ti Gaming X 8GB GDDR6, kiến trúc Ada Lovelace. DLSS 3, Ray Tracing, hiệu năng cao.',
                'base_price'      => 12990000,
                'sale_price'      => 11490000,
                'stock_qty'       => 6,
                'is_featured'     => false,
                'status'          => 'active',
                'warranty_months' => 36,
                'images'          => [
                    ['url' => 'https://placehold.co/800x600/cc0000/white?text=MSI+RTX+4060+Ti', 'is_primary' => true],
                    ['url' => 'https://placehold.co/800x600/990000/white?text=RTX+4060Ti+Side'],
                ],
                'specs'           => [
                    ['Thông Số', 'GPU', 'NVIDIA GeForce RTX 4060 Ti'],
                    ['Thông Số', 'VRAM', '8GB GDDR6'],
                    ['Thông Số', 'Bus', '128-bit'],
                    ['Thông Số', 'Boost Clock', '2745 MHz'],
                    ['Thông Số', 'TDP', '165W'],
                ],
            ],
        ];

        foreach ($products as $index => $data) {
            $images = $data['images'];
            $specs  = $data['specs'];
            unset($data['images'], $data['specs']);

            // Skip if category not found
            if (!$data['category_id']) {
                $this->command->warn("Category not found for product: {$data['name']}, skipping.");
                continue;
            }

            $product = Product::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            // Create images
            foreach ($images as $sortOrder => $img) {
                ProductImage::firstOrCreate(
                    ['product_id' => $product->id, 'url' => $img['url']],
                    [
                        'alt_text'   => $product->name,
                        'sort_order' => $sortOrder,
                        'is_primary' => $img['is_primary'] ?? false,
                    ]
                );
            }

            // Create specs
            foreach ($specs as $sortOrder => $spec) {
                ProductSpec::firstOrCreate(
                    ['product_id' => $product->id, 'spec_key' => $spec[1]],
                    [
                        'spec_group' => $spec[0],
                        'spec_value' => $spec[2],
                        'sort_order' => $sortOrder,
                    ]
                );
            }
        }
    }
}
