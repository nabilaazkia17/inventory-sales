@extends('layouts')
@section('content')
@php $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.'); @endphp
<style>
    .rp-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px;margin:15px 0}
    .rp-box{background:#fff;border-radius:12px;padding:16px 18px;box-shadow:0 4px 14px #26323a14;border-left:5px solid #4a7c74}
    .rp-box small{display:block;color:#6d7b7e;font-size:12px;text-transform:uppercase;letter-spacing:.04em}
    .rp-box b{display:block;font-size:22px;margin-top:6px}
    .rp-muted{color:#6d7b7e;font-size:13px}
    .rp-two{display:grid;grid-template-columns:1fr 1fr;gap:18px}
    @media (max-width:860px){.rp-two{grid-template-columns:1fr}}
    @media print{.nav,.no-print{display:none!important}}
</style>

<div class="card">
    <div class="row" style="justify-content:space-between">
        <h1 style="margin:0">Laporan Penjualan</h1>
        <button type="button" class="btn btn-gray no-print" onclick="window.print()">Cetak</button>
    </div>

    <form method="get" class="row no-print" style="margin:16px 0">
        <label>Dari</label><input type="date" name="from" value="{{ $from->toDateString() }}">
        <label>Sampai</label><input type="date" name="to" value="{{ $to->toDateString() }}">
        <button class="btn">Tampilkan</button>
        <a class="btn btn-gray" href="{{ route('reports.sales', ['from' => now()->toDateString(), 'to' => now()->toDateString()]) }}">Hari ini</a>
        <a class="btn btn-gray" href="{{ route('reports.sales', ['from' => now()->subDays(6)->toDateString(), 'to' => now()->toDateString()]) }}">7 hari</a>
        <a class="btn btn-gray" href="{{ route('reports.sales', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()]) }}">Bulan ini</a>
    </form>

    <div class="rp-muted">Periode {{ $from->format('d/m/Y') }} sampai {{ $to->format('d/m/Y') }}. Hanya transaksi yang selesai yang dihitung.</div>
</div>

<div class="rp-grid">
    <div class="rp-box"><small>Total omzet</small><b>{{ $rp($revenue) }}</b></div>
    <div class="rp-box"><small>Jumlah transaksi</small><b>{{ $count }}</b></div>
    <div class="rp-box"><small>Item terjual</small><b>{{ $itemsSold }}</b></div>
    <div class="rp-box"><small>Rata-rata per transaksi</small><b>{{ $rp($average) }}</b></div>
    <div class="rp-box"><small>Total pembelian (periode)</small><b>{{ $rp($purchaseTotal) }}</b></div>
</div>

<div class="rp-two">
    <div class="card">
        <h2 style="margin-top:0">Penjualan per Hari</h2>
        <table>
            <tr><th>Tanggal</th><th>Transaksi</th><th>Omzet</th></tr>
            @forelse($daily as $date => $row)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}</td>
                    <td>{{ $row['count'] }}</td>
                    <td>{{ $rp($row['total']) }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Tidak ada penjualan pada periode ini.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="card">
        <h2 style="margin-top:0">Produk Terjual</h2>
        <table>
            <tr><th>Produk</th><th>Jumlah</th><th>Omzet</th></tr>
            @forelse($products as $p)
                <tr>
                    <td><b>{{ $p->name }}</b><br><span class="rp-muted">{{ $p->sku }}</span></td>
                    <td>{{ (int) $p->qty }}</td>
                    <td>{{ $rp($p->revenue) }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Belum ada produk terjual pada periode ini.</td></tr>
            @endforelse
        </table>
    </div>
</div>
@endsection
