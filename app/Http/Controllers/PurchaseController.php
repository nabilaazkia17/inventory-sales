<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $purchases = Purchase::with(['supplier', 'user'])
            ->when($request->q, fn ($query, $v) => $query->where('purchase_number', 'like', "%{$v}%"))
            ->when($request->status, fn ($query, $v) => $query->where('status', $v))
            ->when($request->from, fn ($query, $v) => $query->whereDate('purchased_at', '>=', $v))
            ->when($request->to, fn ($query, $v) => $query->whereDate('purchased_at', '<=', $v))
            ->latest('purchased_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view('purchases.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'productData' => $products->map(fn ($p) => [
                'id' => $p->id,
                'name' => "{$p->sku} - {$p->name}",
                'price' => (float) $p->purchase_price,
            ])->values(),
            'initialItems' => array_values(old('items', [['product_id' => '', 'quantity' => 1]])),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchased_at' => ['required', 'date', 'before_or_equal:today'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Tambahkan minimal satu produk.',
            'items.*.product_id.required' => 'Pilih produk pada setiap baris.',
            'items.*.product_id.distinct' => 'Satu produk tidak boleh dipilih dua kali.',
            'items.*.quantity.min' => 'Jumlah minimal 1.',
            'items.*.quantity.integer' => 'Jumlah harus berupa bilangan bulat.',
        ]);

        // Semua langkah di dalam satu transaksi: kalau satu gagal, semuanya dibatalkan (rollback)
        $purchase = DB::transaction(function () use ($data, $request) {
            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => $request->user()->id,
                'purchase_number' => 'PUR-'.now()->format('Ymd').'-'.strtoupper(Str::random(5)),
                'total' => 0,
                'status' => 'completed',
                'purchased_at' => $data['purchased_at'],
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {
                // lockForUpdate: kunci baris produk supaya tidak ditimpa transaksi lain pada saat yang sama
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                $subtotal = round($item['quantity'] * $item['unit_cost'], 2);

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);

                $product->increment('stock', $item['quantity']); // stok bertambah
                $total += $subtotal;
            }

            $purchase->update(['total' => $total]); // total dihitung di server, bukan dari input form

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Pembelian berhasil disimpan, stok produk bertambah.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            // Baca ulang dengan kunci, supaya status yang dicek adalah yang terbaru
            $purchase = Purchase::lockForUpdate()->findOrFail($purchase->id);

            if ($purchase->status === 'cancelled') {
                throw ValidationException::withMessages(['cancel' => 'Pembelian ini sudah dibatalkan.']);
            }

            foreach ($purchase->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);

                // Membatalkan pembelian = mengurangi stok. Tidak boleh sampai minus.
                if ($product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cancel' => "Tidak bisa dibatalkan: stok {$product->name} sudah terpakai (stok {$product->stock}, perlu dikurangi {$item->quantity}).",
                    ]);
                }

                $product->decrement('stock', $item->quantity);
            }

            $purchase->update(['status' => 'cancelled']);
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Pembelian dibatalkan, stok dikembalikan.');
    }
}
