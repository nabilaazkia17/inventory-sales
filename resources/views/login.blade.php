@extends('layouts')
@section('content')
<div class="card" style="max-width:420px;margin:60px auto">
    <h1>Login</h1>
    <form method="post" action="/login">
        @csrf
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button class="btn">Login</button>
    </form>
</div>
@endsection
