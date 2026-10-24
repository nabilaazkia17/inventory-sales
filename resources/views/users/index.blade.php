@extends('layouts')
@section('content')
<div class="card">
    <div class="row" style="justify-content:space-between">
        <h1 style="margin:0">Kelola User</h1>
        <a class="btn" href="{{ route('users.create') }}">+ Tambah User</a>
    </div>
    <form method="get" class="row" style="margin:16px 0">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / email...">
        <select name="role">
            <option value="">Semua role</option>
            @foreach(\App\Models\User::ROLES as $r)
                <option value="{{ $r }}" @selected(request('role') === $r)>{{ $r }}</option>
            @endforeach
        </select>
        <button class="btn">Cari</button>
        @if(request('q') || request('role'))<a class="btn btn-gray" href="{{ route('users.index') }}">Reset</a>@endif
    </form>
    <table>
        <tr><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr>
        @foreach($users as $u)
            <tr>
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td><span class="badge">{{ $u->role }}</span></td>
                <td class="row">
                    <a class="btn" href="{{ route('users.edit', $u) }}">Edit</a>
                    @unless($u->is(auth()->user()))
                    <form method="post" action="{{ route('users.destroy', $u) }}" onsubmit="return confirm('Hapus user ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger">Hapus</button>
                    </form>
                    @endunless
                </td>
            </tr>
        @endforeach
    </table>
    {{ $users->links() }}
</div>
@endsection
