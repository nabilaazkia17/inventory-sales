<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->q, fn ($query, $q) => $query->where(
                fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%")
            ))
            ->when($request->category_id, fn ($query, $id) => $query->where('category_id', $id))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        $product->load('category');

        // Riwayat transaksi produk ini hanya untuk admin dan staff
        $saleItems = collect();
        $purchaseItems = collect();

        if (auth()->user()->hasRole('admin', 'staff')) {
            $saleItems = $product->saleItems()->with('sale')->latest()->limit(10)->get();
            $purchaseItems = $product->purchaseItems()->with('purchase')->latest()->limit(10)->get();
        }

        return view('products.show', compact('product', 'saleItems', 'purchaseItems'));
    }

    public function lowStock(Request $request)
    {
        // Produk yang stoknya sudah sama dengan atau di bawah stok minimum
        $products = Product::with('category')
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->when($request->q, fn ($query, $q) => $query->where(
                fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%")
            ))
            ->orderBy('stock')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('products.low-stock', compact('products'));
    }

    public function create()
    {
        return view('products.form', [
            'product' => new Product(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        // Stok awal hanya boleh diisi saat produk dibuat
        $data = $request->validate($this->rules() + [
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        // Stok TIDAK diubah di sini. Stok hanya berubah lewat transaksi pembelian/penjualan.
        $product->update($request->validate($this->rules($product)));

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        // Produk yang sudah pernah dibeli/dijual tidak boleh dihapus (riwayat transaksi harus tetap utuh)
        if ($product->saleItems()->exists() || $product->purchaseItems()->exists()) {
            return back()->withErrors(['delete' => 'Produk sudah punya riwayat transaksi, tidak bisa dihapus.']);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    private function rules(?Product $product = null): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product?->id)],
            'name' => ['required', 'string', 'max:150'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
