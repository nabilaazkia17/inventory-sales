@extends('layouts')
@section('content')
<div class="card">
    <h1>{{ $product->exists ? 'Edit' : 'Tambah' }} Produk</h1>
    <form method="post" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}">
        @csrf
        @if($product->exists) @method('PUT') @endif
        <label>Kategori</label>
        <select name="category_id" required>
            <option value="">-- pilih kategori --</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <label>SKU</label>
        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required>
        <label>Nama Produk</label>
        <input type="text" name="name" value="{{ old('name', $product->name) }}" required>
        <label>Harga Beli</label>
        <input type="number" name="purchase_price" min="0" step="0.01" value="{{ old('purchase_price', $product->purchase_price) }}" required>
        <label>Harga Jual</label>
        <input type="number" name="selling_price" min="0" step="0.01" value="{{ old('selling_price', $product->selling_price) }}" required>
        @if($product->exists)
            <label>Stok saat ini</label>
            <input type="text" value="{{ $product->stock }}" disabled>
            <small>Stok hanya berubah lewat transaksi pembelian dan penjualan.</small>
        @else
            <label>Stok awal</label>
            <input type="number" name="stock" min="0" step="1" value="{{ old('stock', 0) }}" required>
        @endif
        <label>Stok minimum</label>
        <input type="number" name="minimum_stock" min="0" step="1" value="{{ old('minimum_stock', $product->minimum_stock ?? 0) }}" required>
        <button class="btn">Simpan</button>
        <a class="btn btn-gray" href="{{ route('products.index') }}">Batal</a>
    </form>
</div>
@endsection
