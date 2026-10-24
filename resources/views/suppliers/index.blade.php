@extends('layouts')
@section('content')
@php $isAdmin = auth()->user()->hasRole('admin'); @endphp
<div class="card">
    <div class="row" style="justify-content:space-between">
        <h1 style="margin:0">Supplier</h1>
        @if($isAdmin)<a class="btn" href="{{ route('suppliers.create') }}">+ Tambah Supplier</a>@endif
    </div>
    <form method="get" class="row" style="margin:16px 0">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / telepon...">
        <button class="btn">Cari</button>
        @if(request('q'))<a class="btn btn-gray" href="{{ route('suppliers.index') }}">Reset</a>@endif
    </form>
    <table>
        <tr><th>Nama</th><th>Telepon</th><th>Email</th><th>Jumlah Pembelian</th>@if($isAdmin)<th>Aksi</th>@endif</tr>
        @forelse($suppliers as $s)
            <tr>
                <td>{{ $s->name }}</td>
                <td>{{ $s->phone }}</td>
                <td>{{ $s->email }}</td>
                <td>{{ $s->purchases_count }}</td>
                @if($isAdmin)
                <td class="row">
                    <a class="btn" href="{{ route('suppliers.edit', $s) }}">Edit</a>
                    <form method="post" action="{{ route('suppliers.destroy', $s) }}" onsubmit="return confirm('Hapus supplier ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger">Hapus</button>
                    </form>
                </td>
                @endif
            </tr>
        @empty
            <tr><td colspan="5">Belum ada data.</td></tr>
        @endforelse
    </table>
    {{ $suppliers->links() }}
</div>
@endsection
