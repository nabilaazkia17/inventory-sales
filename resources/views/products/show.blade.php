@extends('layouts')
@section('content')
@php
    $user = auth()->user();
    $isStaff = $user->hasRole('admin', 'staff');
    $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');
    $margin = $product->selling_price - $product->purchase_price;
    $status = $product->stock == 0
        ? ['Habis', 'pd-red']
        : ($product->stock <= $product->minimum_stock ? ['Menipis', 'pd-amber'] : ['Aman', 'pd-green']);
@endphp

<style>
    .pd-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px;margin:18px 0}
    .pd-box{background:#f7f8f5;border-radius:12px;padding:14px 16px}
    .pd-box small{display:block;color:#6d7b7e;font-size:12px;text-transform:uppercase;letter-spacing:.04em}
    .pd-box b{display:block;font-size:20px;margin-top:4px}
    .pd-pill{display:inline-block;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:bold}
    .pd-red{background:#f8e3df;color:#a8402f}
    .pd-amber{background:#f6ecd9;color:#8a5f1f}
    .pd-green{background:#e8f0e2;color:#4d6b41}
    .pd-muted{color:#6d7b7e;font-size:13px}
    .pd-two{display:grid;grid-template-columns:1fr 1fr;gap:18px}
    @media (max-width:860px){.pd-two{grid-template-columns:1fr}}
</style>

<div class="card">
    <div class="row" style="justify-content:space-between">
        <div>
            <h1 style="margin:0">{{ $product->name }}</h1>
            <div class="pd-muted">SKU {{ $product->sku }} &middot; Kategori {{ $product->category->name }}</div>
        </div>
        <span class="pd-pill {{ $status[1] }}">{{ $status[0] }}</span>
    </div>

    <div class="pd-grid">
        <div class="pd-box"><small>Harga beli</small><b>{{ $rp($product->purchase_price) }}</b></div>
        <div class="pd-box"><small>Harga jual</small><b>{{ $rp($product->selling_price) }}</b></div>
        <div class="pd-box"><small>Margin per unit</small><b>{{ $rp($margin) }}</b></div>
        <div class="pd-box"><small>Stok saat ini</small><b>{{ $product->stock }}</b></div>
        <div class="pd-box"><small>Stok minimum</small><b>{{ $product->minimum_stock }}</b></div>
    </div>

    <div class="row">
        <a class="btn btn-gray" href="{{ route('products.index') }}">Kembali</a>
        @if($user->hasRole('admin'))
            <a class="btn" href="{{ route('products.edit', $product) }}">Edit Produk</a>
        @endif
    </div>
</div>

@if($isStaff)
    <div class="pd-two">
        <div class="card">
            <h2 style="margin-top:0">Riwayat Penjualan</h2>
            @if($saleItems->isEmpty())
                <p class="pd-muted">Produk ini belum pernah dijual.</p>
            @else
                <table>
                    <tr><th>Nomor</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr>
                    @foreach($saleItems as $item)
                        <tr>
                            <td><a href="{{ route('sales.show', $item->sale) }}">{{ $item->sale->sale_number }}</a></td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $rp($item->unit_price) }}</td>
                            <td>{{ $rp($item->subtotal) }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>

        <div class="card">
            <h2 style="margin-top:0">Riwayat Pembelian</h2>
            @if($purchaseItems->isEmpty())
                <p class="pd-muted">Produk ini belum pernah dibeli.</p>
            @else
                <table>
                    <tr><th>Nomor</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr>
                    @foreach($purchaseItems as $item)
                        <tr>
                            <td><a href="{{ route('purchases.show', $item->purchase) }}">{{ $item->purchase->purchase_number }}</a></td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $rp($item->unit_cost) }}</td>
                            <td>{{ $rp($item->subtotal) }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>
    </div>
@endif
@endsection
