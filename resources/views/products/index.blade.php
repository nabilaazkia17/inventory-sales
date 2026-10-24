@extends('layouts')
@section('content')
@php $isAdmin = auth()->user()->hasRole('admin'); @endphp
<div class="card">
    <div class="row" style="justify-content:space-between">
        <h1 style="margin:0">Produk</h1>
        <div class="row">
            <a class="btn btn-gray" href="{{ route('products.low-stock') }}">Stok Rendah</a>
            @if($isAdmin)<a class="btn" href="{{ route('products.create') }}">+ Tambah Produk</a>@endif
        </div>
    </div>
    <form method="get" class="row" style="margin:16px 0">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / SKU...">
        <select name="category_id">
            <option value="">Semua kategori</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button class="btn">Cari</button>
        @if(request('q') || request('category_id'))<a class="btn btn-gray" href="{{ route('products.index') }}">Reset</a>@endif
    </form>
    <table>
        <tr><th>SKU</th><th>Nama</th><th>Kategori</th><th>Harga Beli</th><th>Harga Jual</th><th>Stok</th>@if($isAdmin)<th>Aksi</th>@endif</tr>
        @forelse($products as $p)
            <tr>
                <td>{{ $p->sku }}</td>
                <td><a href="{{ route('products.show', $p) }}" style="color:#3d6862;font-weight:bold;text-decoration:none">{{ $p->name }}</a></td>
                <td>{{ $p->category->name }}</td>
                <td>Rp {{ number_format($p->purchase_price, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($p->selling_price, 0, ',', '.') }}</td>
                <td class="{{ $p->stock <= $p->minimum_stock ? 'low' : '' }}">{{ $p->stock }}</td>
                @if($isAdmin)
                <td class="row">
                    <a class="btn" href="{{ route('products.edit', $p) }}">Edit</a>
                    <form method="post" action="{{ route('products.destroy', $p) }}" onsubmit="return confirm('Hapus produk ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger">Hapus</button>
                    </form>
                </td>
                @endif
            </tr>
        @empty
            <tr><td colspan="7">Belum ada data.</td></tr>
        @endforelse
    </table>
    {{ $products->links() }}
</div>
@endsection
