<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Inventory Sales</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f4f5f1;margin:0;color:#26323a}
        .nav{background:#2f4f4f;color:#fff;padding:14px 18px;display:flex;flex-wrap:wrap;align-items:center;gap:16px}
        .nav a{color:#fff;text-decoration:none}
        .nav a:hover{text-decoration:underline}
        .nav .right{margin-left:auto;display:flex;align-items:center;gap:10px}
        .wrap{max-width:1000px;margin:25px auto;padding:0 15px}
        .card{background:#fff;padding:22px;border-radius:10px;margin:15px 0;box-shadow:0 2px 8px #0001}
        .btn{background:#4a7c74;color:#fff;padding:9px 13px;border:0;border-radius:6px;text-decoration:none;cursor:pointer;font-size:14px;display:inline-block}
        .btn-danger{background:#b85450}
        .btn-gray{background:#7a8a8c}
        label{font-size:14px;font-weight:bold}
        input,select,textarea{padding:10px;width:100%;box-sizing:border-box;margin:6px 0 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px}
        input:focus,select:focus,textarea:focus{outline:none;border-color:#4a7c74;box-shadow:0 0 0 3px #4a7c7433}
        .btn:hover{filter:brightness(.94)}
        .row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
        .row input,.row select{width:auto;margin:0}
        table{width:100%;border-collapse:collapse}
        td,th{padding:10px;border-bottom:1px solid #ddd;text-align:left;font-size:14px}
        td input,td select{margin:0}
        .ok{background:#e8f0e2;color:#3f5c34;padding:10px 14px;border-radius:6px}
        .err{background:#f8e3df;color:#a8402f;padding:10px 14px;border-radius:6px}
        .err p{margin:4px 0}
        .low{color:#a8402f;font-weight:bold}
        .badge{background:#e3efec;color:#2f5d56;padding:3px 8px;border-radius:99px;font-size:12px}
        .badge-ok{background:#e8f0e2;color:#3f5c34}
        .badge-cancel{background:#f8e3df;color:#a8402f}
        .pagination{list-style:none;display:flex;gap:6px;padding:0;margin:14px 0 0}
        .page-link{display:block;padding:6px 11px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#4a7c74;text-decoration:none}
        .active .page-link{background:#4a7c74;color:#fff}
        .disabled .page-link{color:#94a3b8}
    </style>
</head>
<body>
<div class="nav">
    <b>Inventory &amp; Sales</b>
    @auth
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <a href="{{ route('categories.index') }}">Kategori</a>
        <a href="{{ route('products.index') }}">Produk</a>
        <a href="{{ route('suppliers.index') }}">Supplier</a>
        @if(auth()->user()->hasRole('admin', 'staff'))
            <a href="{{ route('purchases.index') }}">Pembelian</a>
            <a href="{{ route('sales.index') }}">Penjualan</a>
        @endif
        @if(auth()->user()->hasRole('admin'))
            <a href="{{ route('reports.sales') }}">Laporan</a>
            <a href="{{ route('users.index') }}">User</a>
        @endif
        <span class="right">
            {{ auth()->user()->name }} <span class="badge">{{ auth()->user()->role }}</span>
            <form method="post" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button class="btn btn-gray">Logout</button>
            </form>
        </span>
    @endauth
</div>
<main class="wrap">
    @if(session('success'))<p class="ok">{{ session('success') }}</p>@endif
    @if($errors->any())
        <div class="err">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif
    @yield('content')
</main>
</body>
</html>
