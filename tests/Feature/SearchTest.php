<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InventoryHelpers;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase, InventoryHelpers;

    public function test_search_returns_only_matching_products(): void
    {
        $admin = $this->makeUser('admin');
        $this->makeProduct(['name' => 'Biskuit Cokelat', 'sku' => 'BSK-001']);
        $this->makeProduct(['name' => 'Susu UHT', 'sku' => 'SUS-001']);

        $this->actingAs($admin)->get('/products?q=Biskuit')
            ->assertOk()
            ->assertSee('Biskuit Cokelat')
            ->assertDontSee('Susu UHT');
    }

    public function test_category_filter_returns_only_that_category(): void
    {
        $admin = $this->makeUser('admin');
        $makanan = Category::create(['name' => 'Makanan']);
        $minuman = Category::create(['name' => 'Minuman']);
        $this->makeProduct(['name' => 'Roti Tawar', 'category_id' => $makanan->id]);
        $this->makeProduct(['name' => 'Teh Botol', 'category_id' => $minuman->id]);

        $this->actingAs($admin)->get('/products?category_id='.$makanan->id)
            ->assertOk()
            ->assertSee('Roti Tawar')
            ->assertDontSee('Teh Botol');
    }

    public function test_pagination_splits_products_into_pages_of_ten(): void
    {
        $admin = $this->makeUser('admin');
        for ($i = 1; $i <= 12; $i++) {
            $this->makeProduct(['name' => sprintf('Produk %02d', $i), 'sku' => "PGN-$i"]);
        }

        $page1 = $this->actingAs($admin)->get('/products')->assertOk()->viewData('products');
        $this->assertSame(12, $page1->total());
        $this->assertCount(10, $page1->items());

        $page2 = $this->actingAs($admin)->get('/products?page=2')->assertOk()->viewData('products');
        $this->assertCount(2, $page2->items());
    }
}
