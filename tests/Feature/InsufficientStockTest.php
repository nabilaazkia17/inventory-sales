<?php

namespace Tests\Feature;

use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InventoryHelpers;
use Tests\TestCase;

class InsufficientStockTest extends TestCase
{
    use RefreshDatabase, InventoryHelpers;

    public function test_sale_with_insufficient_stock_is_rejected_without_changing_data(): void
    {
        $staff = $this->makeUser('staff');
        $product = $this->makeProduct(['stock' => 2]);

        $this->actingAs($staff)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 5]], // minta 5, stok cuma 2
        ])->assertSessionHasErrors('items.0.quantity');

        $this->assertEquals(2, $product->fresh()->stock); // stok tidak berubah
        $this->assertSame(0, Sale::count());              // tidak ada penjualan tersimpan
    }
}
