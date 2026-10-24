<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InventoryHelpers;
use Tests\TestCase;

class NewPagesTest extends TestCase
{
    use RefreshDatabase, InventoryHelpers;

    public function test_product_detail_page_shows_the_product(): void
    {
        $customer = $this->makeUser('customer');
        $product = $this->makeProduct(['name' => 'Biskuit Detail']);

        $this->actingAs($customer)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Biskuit Detail');
    }

    public function test_low_stock_page_lists_only_low_stock_products(): void
    {
        $staff = $this->makeUser('staff');
        $this->makeProduct(['name' => 'Stok Tipis', 'stock' => 1, 'minimum_stock' => 5]);
        $this->makeProduct(['name' => 'Stok Aman', 'stock' => 50, 'minimum_stock' => 5]);

        $this->actingAs($staff)
            ->get(route('products.low-stock'))
            ->assertOk()
            ->assertSee('Stok Tipis')
            ->assertDontSee('Stok Aman');
    }

    public function test_admin_sales_report_shows_totals(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(['stock' => 10, 'selling_price' => 7000]);

        $this->actingAs($admin)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ])->assertSessionHasNoErrors();

        $this->get(route('reports.sales'))
            ->assertOk()
            ->assertSee('Rp 21.000'); // 3 x 7000
    }

    public function test_staff_cannot_open_the_sales_report(): void
    {
        $staff = $this->makeUser('staff');

        $this->actingAs($staff)->get(route('reports.sales'))->assertForbidden();
    }

    public function test_report_rejects_an_invalid_date_range(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get('/reports/sales?from=2026-10-10&to=2026-10-01')
            ->assertSessionHasErrors('to');
    }
}
