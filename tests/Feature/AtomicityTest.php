<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InventoryHelpers;
use Tests\TestCase;

class AtomicityTest extends TestCase
{
    use RefreshDatabase, InventoryHelpers;

    public function test_if_one_item_fails_the_whole_transaction_is_rolled_back(): void
    {
        $staff = $this->makeUser('staff');
        $a = $this->makeProduct(['stock' => 10]); // item pertama: stok cukup
        $b = $this->makeProduct(['stock' => 1]);  // item kedua: stok tidak cukup

        $this->actingAs($staff)->post('/sales', [
            'items' => [
                ['product_id' => $a->id, 'quantity' => 3],
                ['product_id' => $b->id, 'quantity' => 5],
            ],
        ])->assertSessionHasErrors('items.1.quantity');

        // Item pertama sempat diproses, tetapi karena item kedua gagal, semuanya dibatalkan (rollback)
        $this->assertEquals(10, $a->fresh()->stock);
        $this->assertEquals(1, $b->fresh()->stock);
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, SaleItem::count());
    }
}
