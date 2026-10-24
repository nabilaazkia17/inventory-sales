@extends('layouts')
@section('content')
<div class="card">
    <h1>{{ $supplier->exists ? 'Edit' : 'Tambah' }} Supplier</h1>
    <form method="post" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}">
        @csrf
        @if($supplier->exists) @method('PUT') @endif
        <label>Nama</label>
        <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required>
        <label>Telepon (opsional)</label>
        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}">
        <label>Email (opsional)</label>
        <input type="email" name="email" value="{{ old('email', $supplier->email) }}">
        <button class="btn">Simpan</button>
        <a class="btn btn-gray" href="{{ route('suppliers.index') }}">Batal</a>
    </form>
</div>
@endsection
