@extends('layouts')
@section('content')
<style>
    .ls-level{width:110px;height:8px;border-radius:99px;background:#e2e6e0;overflow:hidden}
    .ls-level span{display:block;height:100%;border-radius:99px}
    .ls-red{background:#c2574a}.ls-amber{background:#c9974a}
    .ls-pill{display:inline-block;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:bold}
    .ls-p-red{background:#f8e3df;color:#a8402f}.ls-p-amber{background:#f6ecd9;color:#8a5f1f}
</style>
<div class="card">
    <div class="row" style="justify-content:space-between">
        <div>
            <h1 style="margin:0">Daftar Stok Rendah</h1>
            <div style="color:#6d7b7e;font-size:13px;margin-top:4px">Produk yang stoknya sama dengan atau di bawah stok minimum</div>
        </div>
        <a class="btn btn-gray" href="{{ route('products.index') }}">Semua Produk</a>
    </div>

    <form method="get" class="row" style="margin:16px 0">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / SKU...">
        <button class="btn">Cari</button>
        @if(request('q'))<a class="btn btn-gray" href="{{ route('products.low-stock') }}">Reset</a>@endif
    </form>

    <table>
        <tr><th>SKU</th><th>Produk</th><th>Kategori</th><th>Stok</th><th>Minimum</th><th>Level</th><th>Status</th></tr>
        @forelse($products as $p)
            @php $pct = $p->minimum_stock > 0 ? min(100, (int) round($p->stock / $p->minimum_stock * 100)) : 0; @endphp
            <tr>
                <td>{{ $p->sku }}</td>
                <td><a href="{{ route('products.show', $p) }}" style="color:#3d6862;font-weight:bold;text-decoration:none">{{ $p->name }}</a></td>
                <td>{{ $p->category->name }}</td>
                <td class="low">{{ $p->stock }}</td>
                <td>{{ $p->minimum_stock }}</td>
                <td><div class="ls-level"><span class="{{ $p->stock == 0 || $pct < 50 ? 'ls-red' : 'ls-amber' }}" style="width:{{ max($pct, 4) }}%"></span></div></td>
                <td><span class="ls-pill {{ $p->stock == 0 ? 'ls-p-red' : 'ls-p-amber' }}">{{ $p->stock == 0 ? 'Habis' : 'Menipis' }}</span></td>
            </tr>
        @empty
            <tr><td colspan="7">Tidak ada produk dengan stok rendah.</td></tr>
        @endforelse
    </table>
    {{ $products->links() }}
</div>
@endsection
