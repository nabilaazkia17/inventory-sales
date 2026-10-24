@extends('layouts')
@section('content')
<div class="card">
    <h1>{{ $category->exists ? 'Edit' : 'Tambah' }} Kategori</h1>
    <form method="post" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}">
        @csrf
        @if($category->exists) @method('PUT') @endif
        <label>Nama</label>
        <input type="text" name="name" value="{{ old('name', $category->name) }}" required>
        <label>Deskripsi (opsional)</label>
        <textarea name="description" rows="3">{{ old('description', $category->description) }}</textarea>
        <button class="btn">Simpan</button>
        <a class="btn btn-gray" href="{{ route('categories.index') }}">Batal</a>
    </form>
</div>
@endsection
