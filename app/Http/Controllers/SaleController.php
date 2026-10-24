<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $sales = Sale::with('user')
            ->when($request->q, fn ($query, $v) => $query->where('sale_number', 'like', "%{$v}%"))
            ->when($request->status, fn ($query, $v) => $query->where('status', $v))
            ->when($request->from, fn ($query, $v) => $query->whereDate('sold_at', '>=', $v))
            ->when($request->to, fn ($query, $v) => $query->whereDate('sold_at', '<=', $v))
            ->latest('sold_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        // Hanya produk yang masih ada stok yang bisa dijual
        $products = Product::where('stock', '>', 0)->orderBy('name')->get();

        return view('sales.create', [
            'productData' => $products->map(fn ($p) => [
                'id' => $p->id,
                'name' => "{$p->sku} - {$p->name}",
                'price' => (float) $p->selling_price,
                'stock' => $p->stock,
            ])->values(),
            'initialItems' => array_values(old('items', [['product_id' => '', 'quantity' => 1]])),
        ]);
    }

    public function store(Request $request)
    {
        // Perhatikan: harga TIDAK diminta dari form. Harga diambil dari database (selling_price).
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Tambahkan minimal satu produk.',
            'items.*.product_id.required' => 'Pilih produk pada setiap baris.',
            'items.*.product_id.distinct' => 'Satu produk tidak boleh dipilih dua kali.',
            'items.*.quantity.min' => 'Jumlah minimal 1.',
            'items.*.quantity.integer' => 'Jumlah harus berupa bilangan bulat.',
        ]);

        $sale = DB::transaction(function () use ($data, $request) {
            $sale = Sale::create([
                'user_id' => $request->user()->id,
                'sale_number' => 'SAL-'.now()->format('Ymd').'-'.strtoupper(Str::random(5)),
                'total' => 0,
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            $total = 0;

            foreach ($data['items'] as $i => $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                // Cek stok. Kalau kurang, lempar error: seluruh transaksi di-rollback dan tidak ada yang tersimpan.
                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        "items.$i.quantity" => "Stok {$product->name} tidak cukup (tersisa {$product->stock}).",
                    ]);
                }

                $subtotal = round($item['quantity'] * $product->selling_price, 2);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->selling_price, // harga dari database
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $item['quantity']); // stok berkurang
                $total += $subtotal;
            }

            $sale->update(['total' => $total]);

            return $sale;
        });

        return redirect()->route('sales.show', $sale)->with('success', 'Penjualan berhasil disimpan, stok produk berkurang.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['user', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    public function cancel(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            $sale = Sale::lockForUpdate()->findOrFail($sale->id);

            if ($sale->status === 'cancelled') {
                throw ValidationException::withMessages(['cancel' => 'Penjualan ini sudah dibatalkan.']);
            }

            // Membatalkan penjualan = stok dikembalikan
            foreach ($sale->items as $item) {
                Product::lockForUpdate()->findOrFail($item->product_id)->increment('stock', $item->quantity);
            }

            $sale->update(['status' => 'cancelled']);
        });

        return redirect()->route('sales.show', $sale)->with('success', 'Penjualan dibatalkan, stok dikembalikan.');
    }
}
