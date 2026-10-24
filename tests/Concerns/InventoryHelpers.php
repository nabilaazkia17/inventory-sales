<?php

namespace Tests\Concerns;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Str;

// Fungsi bantu supaya setiap test tidak perlu menulis ulang cara membuat data
trait InventoryHelpers
{
    protected function makeUser(string $role = 'staff'): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $role.'-'.Str::random(6).'@test.local',
            'password' => 'password123',
            'role' => $role,
        ]);
    }

    protected function makeProduct(array $overrides = []): Product
    {
        $category = Category::firstOrCreate(['name' => 'Kategori Test']);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'sku' => 'SKU-'.strtoupper(Str::random(6)),
            'name' => 'Produk Test',
            'purchase_price' => 5000,
            'selling_price' => 7000,
            'stock' => 10,
            'minimum_stock' => 2,
        ], $overrides));
    }

    protected function makeSupplier(): Supplier
    {
        return Supplier::create(['name' => 'Supplier Test']);
    }
}
