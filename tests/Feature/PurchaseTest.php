<?php

namespace Tests\Feature;

use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InventoryHelpers;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase, InventoryHelpers;

    public function test_successful_purchase_increases_stock(): void
    {
        $staff = $this->makeUser('staff');
        $supplier = $this->makeSupplier();
        $product = $this->makeProduct(['stock' => 10]);

        $this->actingAs($staff)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'purchased_at' => now()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 5000],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertEquals(15, $product->fresh()->stock); // 10 + 5
        $this->assertDatabaseHas('purchases', ['supplier_id' => $supplier->id, 'status' => 'completed']);
        $this->assertEquals(25000, (float) Purchase::first()->total); // 5 x 5000
    }

    public function test_purchase_without_items_is_rejected(): void
    {
        $staff = $this->makeUser('staff');
        $supplier = $this->makeSupplier();

        $this->actingAs($staff)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'purchased_at' => now()->toDateString(),
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, Purchase::count());
    }
}
