@extends('layouts')
@section('content')
<div class="card">
    <h1>{{ $user->exists ? 'Edit' : 'Tambah' }} User</h1>
    <form method="post" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">
        @csrf
        @if($user->exists) @method('PUT') @endif
        <label>Nama</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
        <label>Role</label>
        <select name="role" required>
            @foreach(\App\Models\User::ROLES as $r)
                <option value="{{ $r }}" @selected(old('role', $user->role ?? 'customer') === $r)>{{ $r }}</option>
            @endforeach
        </select>
        <label>Password @if($user->exists)(kosongkan jika tidak diganti)@endif</label>
        <input type="password" name="password" minlength="8" @if(!$user->exists) required @endif>
        <button class="btn">Simpan</button>
        <a class="btn btn-gray" href="{{ route('users.index') }}">Batal</a>
    </form>
</div>
@endsection
