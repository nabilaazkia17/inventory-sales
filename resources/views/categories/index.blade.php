@extends('layouts')
@section('content')
@php $isAdmin = auth()->user()->hasRole('admin'); @endphp
<div class="card">
    <div class="row" style="justify-content:space-between">
        <h1 style="margin:0">Kategori</h1>
        @if($isAdmin)<a class="btn" href="{{ route('categories.create') }}">+ Tambah Kategori</a>@endif
    </div>
    <form method="get" class="row" style="margin:16px 0">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama kategori...">
        <button class="btn">Cari</button>
        @if(request('q'))<a class="btn btn-gray" href="{{ route('categories.index') }}">Reset</a>@endif
    </form>
    <table>
        <tr><th>Nama</th><th>Deskripsi</th><th>Jumlah Produk</th>@if($isAdmin)<th>Aksi</th>@endif</tr>
        @forelse($categories as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td>{{ $c->description }}</td>
                <td>{{ $c->products_count }}</td>
                @if($isAdmin)
                <td class="row">
                    <a class="btn" href="{{ route('categories.edit', $c) }}">Edit</a>
                    <form method="post" action="{{ route('categories.destroy', $c) }}" onsubmit="return confirm('Hapus kategori ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger">Hapus</button>
                    </form>
                </td>
                @endif
            </tr>
        @empty
            <tr><td colspan="4">Belum ada data.</td></tr>
        @endforelse
    </table>
    {{ $categories->links() }}
</div>
@endsection
