<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;

class DashboardController extends Controller
{
    public function index()
    {
        $isStaff = auth()->user()->hasRole('admin', 'staff');

        $productCount = Product::count();
        $lowCount = Product::whereColumn('stock', '<=', 'minimum_stock')->count();
        $emptyCount = Product::where('stock', 0)->count();

        // Semua angka dihitung dari database. Transaksi yang dibatalkan tidak dihitung.
        $data = [
            'productCount' => $productCount,
            'salesCount' => Sale::where('status', 'completed')->count(),
            'purchasesCount' => Purchase::where('status', 'completed')->count(),
            'lowCount' => $lowCount,
            // Kondisi stok: aman / menipis / habis (dipakai untuk grafik donat)
            'stockStatus' => [
                'ok' => max($productCount - $lowCount, 0),
                'low' => max($lowCount - $emptyCount, 0),
                'empty' => $emptyCount,
            ],
            'low' => Product::with('category')
                ->whereColumn('stock', '<=', 'minimum_stock')
                ->orderBy('stock')
                ->paginate(10),
            'revenue' => 0,
            'spending' => 0,
            'todayRevenue' => 0,
            'monthRevenue' => 0,
            'revenueChange' => null,
            'stockValue' => 0,
            'chart' => collect(),
            'chartMax' => 0,
            'topProducts' => collect(),
            'activity' => collect(),
        ];

        // Angka uang, grafik, produk terlaris, dan aktivitas hanya untuk admin dan staff
        if ($isStaff) {
            $data['revenue'] = Sale::where('status', 'completed')->sum('total');
            $data['spending'] = Purchase::where('status', 'completed')->sum('total');
            $data['monthRevenue'] = Sale::where('status', 'completed')
                ->whereBetween('sold_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total');
            $data['stockValue'] = Product::selectRaw('COALESCE(SUM(stock * purchase_price), 0) as total_value')->value('total_value');

            // Penjualan hari ini dibandingkan kemarin
            $today = Sale::where('status', 'completed')->whereDate('sold_at', today())->sum('total');
            $yesterday = Sale::where('status', 'completed')->whereDate('sold_at', today()->subDay())->sum('total');
            $data['todayRevenue'] = $today;
            $data['revenueChange'] = $yesterday > 0 ? (int) round((($today - $yesterday) / $yesterday) * 100) : null;

            // Total penjualan 7 hari terakhir (termasuk hari ini), dikelompokkan per tanggal
            $perDay = Sale::where('status', 'completed')
                ->where('sold_at', '>=', now()->subDays(6)->startOfDay())
                ->get(['total', 'sold_at'])
                ->groupBy(fn ($sale) => $sale->sold_at->format('Y-m-d'))
                ->map(fn ($group) => (float) $group->sum('total'));

            $data['chart'] = collect(range(6, 0))->map(function ($i) use ($perDay) {
                $date = now()->subDays($i);

                return [
                    'label' => $date->format('d/m'),
                    'total' => $perDay[$date->format('Y-m-d')] ?? 0,
                ];
            });
            $data['chartMax'] = $data['chart']->max('total');

            // 5 produk terlaris berdasarkan jumlah terjual (hanya penjualan yang selesai)
            $data['topProducts'] = SaleItem::query()
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->join('products', 'products.id', '=', 'sale_items.product_id')
                ->where('sales.status', 'completed')
                ->selectRaw('products.name, products.sku, SUM(sale_items.quantity) as qty, SUM(sale_items.subtotal) as revenue')
                ->groupBy('products.id', 'products.name', 'products.sku')
                ->orderByDesc('qty')
                ->limit(5)
                ->get();

            // Aktivitas terbaru: gabungan penjualan dan pembelian, diurutkan dari yang paling baru
            $sales = Sale::with('user')->latest('sold_at')->latest('id')->limit(5)->get()->map(fn ($s) => [
                'type' => 'sale',
                'number' => $s->sale_number,
                'who' => $s->user->name,
                'amount' => $s->total,
                'status' => $s->status,
                'at' => $s->sold_at,
                'url' => route('sales.show', $s),
            ]);

            $purchases = Purchase::with('supplier')->latest('purchased_at')->latest('id')->limit(5)->get()->map(fn ($p) => [
                'type' => 'purchase',
                'number' => $p->purchase_number,
                'who' => $p->supplier->name,
                'amount' => $p->total,
                'status' => $p->status,
                'at' => $p->purchased_at,
                'url' => route('purchases.show', $p),
            ]);

            $data['activity'] = $sales->concat($purchases)->sortByDesc('at')->take(6)->values();
        }

        return view('dashboard', $data);
    }
}
