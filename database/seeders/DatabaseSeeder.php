<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Password otomatis di-hash oleh cast 'hashed' di model User
        User::updateOrCreate(['email' => 'admin@inventory.test'], ['name' => 'Admin', 'password' => 'password123', 'role' => 'admin']);
        User::updateOrCreate(['email' => 'staff@inventory.test'], ['name' => 'Staff', 'password' => 'password123', 'role' => 'staff']);
        User::updateOrCreate(['email' => 'customer@inventory.test'], ['name' => 'Customer', 'password' => 'password123', 'role' => 'customer']);

        $suppliers = [
            ['PT Sumber Pangan Makmur', '081234567801', 'sumberpangan@example.test'],
            ['CV Segar Jaya', '081234567802', 'segarjaya@example.test'],
            ['UD Berkah Abadi', '081234567803', 'berkahabadi@example.test'],
        ];
        foreach ($suppliers as [$name, $phone, $email]) {
            Supplier::firstOrCreate(['name' => $name], ['phone' => $phone, 'email' => $email]);
        }

        // [sku, nama, kategori, harga beli, harga jual, stok, stok minimum]
        $products = [
            ['MKN-BSK-001', 'Biskuit Cokelat 100 g', 'Makanan', 6500, 8500, 40, 10],
            ['MKN-MIE-001', 'Mie Instan Goreng', 'Makanan', 2800, 3500, 120, 30],
            ['MKN-BRS-001', 'Beras Premium 5 kg', 'Makanan', 62000, 68000, 25, 8],
            ['MKN-MYK-001', 'Minyak Goreng 1 L', 'Makanan', 14500, 17000, 6, 10],
            ['MNM-AIR-001', 'Air Mineral 600 ml', 'Minuman', 2200, 3500, 96, 24],
            ['MNM-SUS-001', 'Susu UHT Cokelat 250 ml', 'Minuman', 5200, 7000, 8, 12],
            ['MNM-TEH-001', 'Teh Botol 350 ml', 'Minuman', 3800, 5000, 48, 12],
            ['SNK-KRP-001', 'Keripik Kentang 50 g', 'Snack', 6000, 8500, 30, 10],
            ['SNK-WFR-001', 'Wafer Cokelat', 'Snack', 3500, 5000, 0, 5],
            ['KRT-SBN-001', 'Sabun Cuci Piring 800 ml', 'Kebutuhan Rumah Tangga', 9000, 12500, 18, 6],
            ['KRT-DTG-001', 'Deterjen Bubuk 800 g', 'Kebutuhan Rumah Tangga', 14000, 18500, 22, 8],
            ['ATK-PLP-001', 'Pulpen Hitam', 'Alat Tulis', 2500, 4000, 150, 40],
            ['ATK-BKT-001', 'Buku Tulis 38 Lembar', 'Alat Tulis', 4000, 6000, 60, 20],
        ];

        foreach ($products as [$sku, $name, $categoryName, $buy, $sell, $stock, $min]) {
            $category = Category::firstOrCreate(['name' => $categoryName]);

            Product::firstOrCreate(['sku' => $sku], [
                'category_id' => $category->id,
                'name' => $name,
                'purchase_price' => $buy,
                'selling_price' => $sell,
                'stock' => $stock,
                'minimum_stock' => $min,
            ]);
        }
    }
}
