<?php

namespace Tests\Feature;

use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InventoryHelpers;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase, InventoryHelpers;

    public function test_staff_cannot_access_admin_functions(): void
    {
        $staff = $this->makeUser('staff');
        $this->actingAs($staff);

        $this->get('/users')->assertForbidden();           // kelola user: khusus admin
        $this->get('/products/create')->assertForbidden(); // tambah produk: khusus admin
        $this->post('/products', [])->assertForbidden();
    }

    public function test_staff_cannot_cancel_a_sale(): void
    {
        $staff = $this->makeUser('staff');
        $product = $this->makeProduct(['stock' => 10]);

        $this->actingAs($staff)->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertSessionHasNoErrors();

        $sale = Sale::first();
        $this->post(route('sales.cancel', $sale))->assertForbidden();

        $this->assertSame('completed', $sale->fresh()->status); // status tidak berubah
    }

    public function test_customer_is_read_only(): void
    {
        $customer = $this->makeUser('customer');
        $this->actingAs($customer);

        $this->get('/products')->assertOk();            // boleh melihat produk
        $this->get('/sales')->assertForbidden();        // tidak boleh membuka penjualan
        $this->get('/purchases')->assertForbidden();    // tidak boleh membuka pembelian
        $this->get('/users')->assertForbidden();
    }
}
