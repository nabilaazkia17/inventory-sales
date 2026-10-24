<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InventoryHelpers;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase, InventoryHelpers;

    private function validData(array $overrides = []): array
    {
        $category = Category::create(['name' => 'Makanan']);

        return array_merge([
            'category_id' => $category->id,
            'sku' => 'MKN-001',
            'name' => 'Biskuit',
            'purchase_price' => 5000,
            'selling_price' => 7000,
            'stock' => 10,
            'minimum_stock' => 2,
        ], $overrides);
    }

    public function test_admin_can_create_product(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->post('/products', $this->validData())
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['sku' => 'MKN-001', 'name' => 'Biskuit']);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $admin = $this->makeUser('admin');
        $this->makeProduct(['sku' => 'MKN-001']);

        $this->actingAs($admin)
            ->post('/products', $this->validData(['sku' => 'MKN-001']))
            ->assertSessionHasErrors('sku');

        $this->assertSame(1, Product::count()); // tidak ada produk baru yang tersimpan
    }

    public function test_negative_purchase_price_is_rejected(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->post('/products', $this->validData(['purchase_price' => -1]))
            ->assertSessionHasErrors('purchase_price');

        $this->assertSame(0, Product::count());
    }

    public function test_negative_selling_price_is_rejected(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->post('/products', $this->validData(['selling_price' => -5000]))
            ->assertSessionHasErrors('selling_price');

        $this->assertSame(0, Product::count());
    }
}
