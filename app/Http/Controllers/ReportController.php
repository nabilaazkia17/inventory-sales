<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        // Default periode: dari awal bulan ini sampai hari ini
        $from = Carbon::parse($request->filled('from') ? $request->from : now()->startOfMonth()->toDateString())->startOfDay();
        $to = Carbon::parse($request->filled('to') ? $request->to : now()->toDateString())->endOfDay();

        // Hanya transaksi yang selesai (yang dibatalkan tidak dihitung)
        $sales = Sale::where('status', 'completed')
            ->whereBetween('sold_at', [$from, $to])
            ->get(['id', 'total', 'sold_at']);

        $daily = $sales
            ->groupBy(fn ($sale) => $sale->sold_at->format('Y-m-d'))
            ->map(fn ($group) => ['count' => $group->count(), 'total' => (float) $group->sum('total')])
            ->sortKeys();

        $products = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [$from, $to])
            ->selectRaw('products.name, products.sku, SUM(sale_items.quantity) as qty, SUM(sale_items.subtotal) as revenue')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('revenue')
            ->get();

        $revenue = (float) $sales->sum('total');
        $count = $sales->count();

        return view('reports.sales', [
            'from' => $from,
            'to' => $to,
            'revenue' => $revenue,
            'count' => $count,
            'itemsSold' => (int) $products->sum('qty'),
            'average' => $count > 0 ? $revenue / $count : 0,
            'purchaseTotal' => Purchase::where('status', 'completed')->whereBetween('purchased_at', [$from, $to])->sum('total'),
            'daily' => $daily,
            'products' => $products,
        ]);
    }
}
