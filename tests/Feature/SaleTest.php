<?php

namespace Tests\Feature;

use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InventoryHelpers;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase, InventoryHelpers;

    public function test_successful_sale_reduces_stock_and_uses_database_price(): void
    {
        $staff = $this->makeUser('staff');
        $product = $this->makeProduct(['stock' => 10, 'selling_price' => 7000]);

        // Sengaja mengirim unit_price palsu (1). Server harus mengabaikannya dan memakai harga dari database.
        $this->actingAs($staff)->post('/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 1],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertEquals(7, $product->fresh()->stock); // 10 - 3

        $sale = Sale::first();
        $this->assertEquals(21000, (float) $sale->total); // 3 x 7000
        $this->assertEquals(7000, (float) $sale->items()->first()->unit_price);
    }

    public function test_cancelling_a_sale_restores_stock(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(['stock' => 10]);

        $this->actingAs($admin)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
        ])->assertSessionHasNoErrors();

        $this->assertEquals(6, $product->fresh()->stock);

        $sale = Sale::first();
        $this->actingAs($admin)->post(route('sales.cancel', $sale));

        $this->assertEquals(10, $product->fresh()->stock); // stok kembali
        $this->assertSame('cancelled', $sale->fresh()->status);
    }
}
