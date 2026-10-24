@extends('layouts')
@section('content')
@php
    $user = auth()->user();
    $isStaff = $user->hasRole('admin', 'staff');
    $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');
    $short = fn ($n) => $n >= 1000000
        ? number_format($n / 1000000, 1, ',', '.').' jt'
        : ($n >= 1000 ? number_format($n / 1000, 0, ',', '.').' rb' : ($n > 0 ? number_format($n, 0, ',', '.') : ''));
    $icons = [
        'box' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
        'cart' => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
        'trend' => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
        'money' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'alert' => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'layers' => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
    ];

    // Ringkasan grafik penjualan
    $chartTotal = $chart->sum('total');
    $chartAvg = $chart->count() ? $chartTotal / $chart->count() : 0;

    // Titik garis mini (sparkline) dari penjualan 7 hari terakhir
    $spark = '';
    if ($isStaff && $chartMax > 0) {
        $vals = $chart->pluck('total')->values()->all();
        $n = count($vals);
        $pts = [];
        foreach ($vals as $i => $v) {
            $x = $n > 1 ? ($i / ($n - 1)) * 100 : 0;
            $y = 30 - ($v / $chartMax) * 26;
            $pts[] = round($x, 1).','.round($y, 1);
        }
        $spark = implode(' ', $pts);
    }

    // Donat kondisi stok
    $ok = $stockStatus['ok']; $lowS = $stockStatus['low']; $emp = $stockStatus['empty'];
    $stockTotal = $ok + $lowS + $emp;
    $degA = $stockTotal > 0 ? round($ok / $stockTotal * 100, 2) : 0;
    $degB = $stockTotal > 0 ? round(($ok + $lowS) / $stockTotal * 100, 2) : 0;

    $topMax = (int) $topProducts->max('qty');
@endphp

<style>
    .dash{--teal:#4a7c74;--teal-d:#3d6862;--teal-dd:#2f4f4f;--sage:#6b8f5e;--sand:#c9974a;--mauve:#9a82ad;--terra:#c2574a;--ink:#26323a;--muted:#6d7b7e;--line:#e6e9e3}
    .dash *{box-sizing:border-box}
    @keyframes dFadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
    @keyframes dRise{from{transform:scaleY(0)}to{transform:scaleY(1)}}
    @keyframes dGrow{from{width:0}}

    /* ===== Hero ===== */
    .dash-hero{position:relative;overflow:hidden;background:linear-gradient(135deg,#2f4f4f 0%,#5b8a80 100%);color:#fff;border-radius:18px;padding:28px 30px 78px;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px}
    .dash-hero::before,.dash-hero::after{content:"";position:absolute;border-radius:50%;background:#ffffff12;pointer-events:none}
    .dash-hero::before{width:260px;height:260px;right:-60px;top:-110px}
    .dash-hero::after{width:180px;height:180px;right:140px;bottom:-110px}
    .dash-hero>*{position:relative;z-index:1}
    .dash-hello{font-size:13px;color:#d5e6e1;letter-spacing:.04em;text-transform:uppercase}
    .dash-hero h1{margin:4px 0 0;font-size:28px;font-weight:700}
    .dash-role{display:inline-block;background:#ffffff26;border:1px solid #ffffff40;border-radius:99px;padding:2px 10px;font-size:12px;margin-left:8px;text-transform:capitalize;vertical-align:middle;font-weight:normal}
    .dash-date{margin-top:6px;color:#d5e6e1;font-size:14px}
    .dash-chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}
    .dash-chip{background:#ffffff1f;border:1px solid #ffffff33;border-radius:99px;padding:5px 12px;font-size:13px}
    .dash-hero-actions{display:flex;gap:8px;flex-wrap:wrap}
    .hero-btn{background:#fff;color:#2f4f4f;padding:10px 15px;border-radius:10px;font-size:14px;font-weight:bold;text-decoration:none;display:inline-block;transition:transform .15s,background .15s}
    .hero-btn:hover{background:#eef5f3;transform:translateY(-1px)}
    .hero-btn.ghost{background:transparent;color:#fff;border:1px solid #ffffff80}
    .hero-btn.ghost:hover{background:#ffffff1f}

    /* ===== KPI ===== */
    .dash-grid{display:grid;gap:16px;margin:-52px 18px 0;position:relative;z-index:2}
    .dash-grid.g3{grid-template-columns:repeat(3,1fr)}
    .dash-grid.g4{grid-template-columns:repeat(4,1fr)}
    .kpi{position:relative;overflow:hidden;background:#fff;border-radius:16px;padding:18px 20px;box-shadow:0 6px 18px #26323a14;border:1px solid var(--line);transition:transform .2s,box-shadow .2s;animation:dFadeUp .5s ease both}
    .kpi:hover{transform:translateY(-3px);box-shadow:0 12px 26px #26323a22}
    .kpi::before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;background:var(--c,#d5d9d4)}
    .kpi:nth-child(2){animation-delay:.05s}.kpi:nth-child(3){animation-delay:.1s}.kpi:nth-child(4){animation-delay:.15s}.kpi:nth-child(5){animation-delay:.2s}.kpi:nth-child(6){animation-delay:.25s}
    .kpi-top{display:flex;justify-content:space-between;align-items:center}
    .kpi-label{font-size:12px;font-weight:bold;letter-spacing:.05em;text-transform:uppercase;color:var(--muted)}
    .kpi-ico{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:var(--bg);color:var(--fg)}
    .kpi-ico svg{width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
    .kpi-value{font-size:27px;font-weight:700;margin:10px 0 4px;color:var(--ink)}
    .kpi-note{font-size:13px;color:var(--muted);display:flex;align-items:center;gap:6px;flex-wrap:wrap}
    .k-teal{--c:#4a7c74;--bg:#e3efec;--fg:#3d6862}
    .k-green{--c:#6b8f5e;--bg:#e8f0e2;--fg:#4d6b41}
    .k-red{--c:#c2574a;--bg:#f8e3df;--fg:#a8402f}.k-red .kpi-value{color:#a8402f}
    .k-amber{--c:#c9974a;--bg:#f6ecd9;--fg:#8a5f1f}
    .k-purple{--c:#9a82ad;--bg:#efe7f4;--fg:#6f5584}
    .spark{width:100%;height:34px;margin-top:8px;display:block}
    .delta{font-weight:bold;font-size:12px;padding:2px 8px;border-radius:99px}
    .delta.up{background:#e8f0e2;color:#4d6b41}
    .delta.down{background:#f8e3df;color:#a8402f}

    /* ===== Panel ===== */
    .dash-panel{background:#fff;border-radius:16px;padding:22px;box-shadow:0 6px 18px #26323a14;border:1px solid var(--line);animation:dFadeUp .6s ease both}
    .dash-panel-head{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap;margin-bottom:16px}
    .dash-panel-head h2{margin:0;font-size:18px;color:var(--ink)}
    .dash-panel-head small{display:block;color:var(--muted);font-size:13px;margin-top:3px}
    .dash-panel-head a{font-size:13px;color:var(--teal-d);text-decoration:none;font-weight:bold}
    .dash-panel-head a:hover{text-decoration:underline}
    .dash-section{margin-top:18px}
    .dash-row{display:grid;gap:18px;margin-top:18px}
    .dash-row.r21{grid-template-columns:2fr 1fr}
    .dash-row.r11{grid-template-columns:1fr 1fr}
    .dash-empty{color:var(--muted);font-size:14px;padding:22px 0;text-align:center}
    .dash-muted{color:var(--muted);font-size:12px}

    /* ===== Grafik batang ===== */
    .dash-chart{display:flex}
    .dash-col{flex:1;padding:0 6px;text-align:center}
    .dash-cell{height:170px;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;border-bottom:1px solid #d5d9d4;background-image:linear-gradient(#eef0ec 1px,transparent 1px);background-size:100% 34px}
    .dash-bar{width:100%;max-width:44px;border-radius:8px 8px 0 0;background:linear-gradient(180deg,#9fc3ba,#4a7c74);transform-origin:bottom;animation:dRise .8s cubic-bezier(.2,.8,.2,1) both}
    .dash-bar.today{background:linear-gradient(180deg,#3d6862,#2f4f4f)}
    .dash-bar-val{font-size:11px;color:#475569;height:16px;margin-bottom:4px;white-space:nowrap}
    .dash-bar-label{font-size:12px;color:var(--muted);margin-top:8px}
    .dash-bar-label.today{color:var(--teal-dd);font-weight:bold}
    .dash-chart-sum{display:flex;gap:22px;flex-wrap:wrap}
    .dash-chart-sum div{font-size:12px;color:var(--muted)}
    .dash-chart-sum b{display:block;font-size:16px;color:var(--ink);margin-top:2px}

    /* ===== Donat ===== */
    .donut-wrap{display:flex;flex-direction:column;align-items:center;gap:18px}
    .donut{width:170px;height:170px;border-radius:50%;position:relative;transition:transform .3s}
    .donut:hover{transform:scale(1.03)}
    .donut::after{content:"";position:absolute;inset:26px;background:#fff;border-radius:50%}
    .donut-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;z-index:1}
    .donut-center b{font-size:28px;color:var(--ink)}
    .donut-center span{font-size:12px;color:var(--muted)}
    .legend{list-style:none;margin:0;padding:0;width:100%}
    .legend li{display:flex;align-items:center;gap:10px;padding:7px 0;font-size:14px;border-bottom:1px solid #f0f2ee}
    .legend li:last-child{border-bottom:0}
    .legend i{width:12px;height:12px;border-radius:4px;display:inline-block}
    .legend b{margin-left:auto}

    /* ===== Produk terlaris ===== */
    .top-list{list-style:none;margin:0;padding:0}
    .top-item{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f0f2ee}
    .top-item:last-child{border-bottom:0}
    .top-rank{width:30px;height:30px;border-radius:10px;background:#e3efec;color:var(--teal-d);font-weight:bold;display:flex;align-items:center;justify-content:center;flex:none}
    .top-item:first-child .top-rank{background:var(--teal);color:#fff}
    .top-body{flex:1;min-width:0}
    .top-name{display:flex;justify-content:space-between;gap:8px;font-size:14px}
    .top-name b{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .bar-track{height:8px;border-radius:99px;background:#e9ece7;margin-top:6px;overflow:hidden}
    .bar-track span{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,#9fc3ba,#4a7c74);animation:dGrow .9s ease both}
    .top-rev{font-size:13px;font-weight:bold;color:var(--ink);white-space:nowrap}

    /* ===== Aktivitas ===== */
    .tl{list-style:none;margin:0;padding:0;position:relative}
    .tl::before{content:"";position:absolute;left:7px;top:8px;bottom:8px;width:2px;background:#e6e9e3}
    .tl-item{position:relative;display:flex;gap:14px;align-items:flex-start;padding:9px 0 9px 28px}
    .tl-dot{position:absolute;left:0;top:14px;width:16px;height:16px;border-radius:50%;background:var(--c);border:3px solid #fff;box-shadow:0 0 0 2px var(--c)}
    .tl-sale{--c:#6b8f5e}.tl-buy{--c:#c9974a}
    .tl-main{flex:1;min-width:0;font-size:14px}
    .tl-main a{color:var(--teal-d);font-weight:bold;text-decoration:none}
    .tl-main a:hover{text-decoration:underline}
    .tl-amt{text-align:right;font-size:14px;font-weight:bold;white-space:nowrap}

    /* ===== Tabel stok rendah ===== */
    .dash-table{width:100%;border-collapse:collapse}
    .dash-table th{font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:#f7f8f5;padding:10px;text-align:left;border-bottom:1px solid #e6e9e3}
    .dash-table td{padding:12px 10px;border-bottom:1px solid #eef0ec;font-size:14px;vertical-align:middle}
    .dash-table tr:hover td{background:#f7f8f5}
    .dash-pill{display:inline-block;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:bold}
    .p-red{background:#f8e3df;color:#a8402f}
    .p-amber{background:#f6ecd9;color:#8a5f1f}
    .p-green{background:#e8f0e2;color:#4d6b41}
    .dash-level{width:110px;height:8px;border-radius:99px;background:#e2e6e0;overflow:hidden}
    .dash-level span{display:block;height:100%;border-radius:99px}
    .lv-red{background:#c2574a}.lv-amber{background:#c9974a}

    @media (max-width:900px){
        .dash-grid.g3,.dash-grid.g4{grid-template-columns:repeat(2,1fr)}
        .dash-row.r21,.dash-row.r11{grid-template-columns:1fr}
    }
    @media (max-width:520px){
        .dash-grid.g3,.dash-grid.g4{grid-template-columns:1fr;margin:-52px 8px 0}
        .dash-hero{padding:22px 18px 72px}
    }
    @media (prefers-reduced-motion:reduce){
        .dash *,.dash *::before{animation:none!important;transition:none!important}
    }
</style>

<div class="dash">

{{-- ===== Hero ===== --}}
<div class="dash-hero">
    <div>
        <div class="dash-hello" id="greet">Selamat datang</div>
        <h1>{{ $user->name }}<span class="dash-role">{{ $user->role }}</span></h1>
        <div class="dash-date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
        <div class="dash-chips">
            @if($isStaff)
                @if($revenueChange !== null)
                    <span class="dash-chip">Penjualan hari ini {{ $revenueChange >= 0 ? 'naik' : 'turun' }} {{ abs($revenueChange) }}% dari kemarin</span>
                @endif
            @endif
            <span class="dash-chip">{{ $lowCount > 0 ? $lowCount.' produk stok rendah' : 'Semua stok aman' }}</span>
        </div>
    </div>
    <div class="dash-hero-actions">
        @if($isStaff)
            <a class="hero-btn" href="{{ route('sales.create') }}">+ Penjualan Baru</a>
            <a class="hero-btn ghost" href="{{ route('purchases.create') }}">+ Pembelian Baru</a>
        @endif
        @if($user->hasRole('admin'))
            <a class="hero-btn ghost" href="{{ route('products.create') }}">+ Tambah Produk</a>
        @endif
        @unless($isStaff)
            <a class="hero-btn" href="{{ route('products.index') }}">Lihat Produk</a>
        @endunless
    </div>
</div>

{{-- ===== KPI ===== --}}
<div class="dash-grid {{ $isStaff ? 'g3' : 'g4' }}">
    @if($isStaff)
        <div class="kpi k-green">
            <div class="kpi-top"><span class="kpi-label">Penjualan Hari Ini</span><span class="kpi-ico"><svg viewBox="0 0 24 24">{!! $icons['trend'] !!}</svg></span></div>
            <div class="kpi-value countup" data-value="{{ (float) $todayRevenue }}" data-prefix="Rp ">{{ $rp($todayRevenue) }}</div>
            <div class="kpi-note">
                @if($revenueChange !== null)
                    <span class="delta {{ $revenueChange >= 0 ? 'up' : 'down' }}">{{ $revenueChange >= 0 ? '▲' : '▼' }} {{ abs($revenueChange) }}%</span> dibanding kemarin
                @else
                    belum ada pembanding dari kemarin
                @endif
            </div>
            @if($spark !== '')
                <svg class="spark" viewBox="0 0 100 32" preserveAspectRatio="none">
                    <polygon points="0,32 {{ $spark }} 100,32" fill="#6b8f5e22"/>
                    <polyline points="{{ $spark }}" fill="none" stroke="#6b8f5e" stroke-width="2" vector-effect="non-scaling-stroke" stroke-linejoin="round" stroke-linecap="round"/>
                </svg>
            @endif
        </div>
        <div class="kpi k-teal">
            <div class="kpi-top"><span class="kpi-label">Total Omzet</span><span class="kpi-ico"><svg viewBox="0 0 24 24">{!! $icons['money'] !!}</svg></span></div>
            <div class="kpi-value countup" data-value="{{ (float) $revenue }}" data-prefix="Rp ">{{ $rp($revenue) }}</div>
            <div class="kpi-note">{{ $salesCount }} transaksi &middot; bulan ini {{ $rp($monthRevenue) }}</div>
        </div>
        <div class="kpi k-amber">
            <div class="kpi-top"><span class="kpi-label">Total Pembelian</span><span class="kpi-ico"><svg viewBox="0 0 24 24">{!! $icons['cart'] !!}</svg></span></div>
            <div class="kpi-value countup" data-value="{{ (float) $spending }}" data-prefix="Rp ">{{ $rp($spending) }}</div>
            <div class="kpi-note">{{ $purchasesCount }} transaksi pembelian</div>
        </div>
        <div class="kpi k-purple">
            <div class="kpi-top"><span class="kpi-label">Nilai Persediaan</span><span class="kpi-ico"><svg viewBox="0 0 24 24">{!! $icons['layers'] !!}</svg></span></div>
            <div class="kpi-value countup" data-value="{{ (float) $stockValue }}" data-prefix="Rp ">{{ $rp($stockValue) }}</div>
            <div class="kpi-note">stok x harga beli</div>
        </div>
    @endif

    <div class="kpi k-teal">
        <div class="kpi-top"><span class="kpi-label">Total Produk</span><span class="kpi-ico"><svg viewBox="0 0 24 24">{!! $icons['box'] !!}</svg></span></div>
        <div class="kpi-value countup" data-value="{{ $productCount }}" data-prefix="">{{ number_format($productCount, 0, ',', '.') }}</div>
        <div class="kpi-note">item terdaftar</div>
    </div>

    <div class="kpi {{ $lowCount > 0 ? 'k-red' : 'k-green' }}">
        <div class="kpi-top"><span class="kpi-label">Stok Rendah</span><span class="kpi-ico"><svg viewBox="0 0 24 24">{!! $icons['alert'] !!}</svg></span></div>
        <div class="kpi-value">{{ $lowCount }}</div>
        <div class="kpi-note">{{ $lowCount > 0 ? 'produk perlu segera restok' : 'semua stok dalam kondisi aman' }}</div>
    </div>

    @unless($isStaff)
        <div class="kpi k-green">
            <div class="kpi-top"><span class="kpi-label">Transaksi Penjualan</span><span class="kpi-ico"><svg viewBox="0 0 24 24">{!! $icons['trend'] !!}</svg></span></div>
            <div class="kpi-value">{{ $salesCount }}</div>
            <div class="kpi-note">transaksi selesai</div>
        </div>
        <div class="kpi k-amber">
            <div class="kpi-top"><span class="kpi-label">Transaksi Pembelian</span><span class="kpi-ico"><svg viewBox="0 0 24 24">{!! $icons['cart'] !!}</svg></span></div>
            <div class="kpi-value">{{ $purchasesCount }}</div>
            <div class="kpi-note">transaksi selesai</div>
        </div>
    @endunless
</div>

{{-- ===== Grafik penjualan + donat stok ===== --}}
<div class="dash-row {{ $isStaff ? 'r21' : '' }}">
    @if($isStaff)
        <div class="dash-panel">
            <div class="dash-panel-head">
                <div>
                    <h2>Penjualan 7 Hari Terakhir</h2>
                    <small>Total per hari, hanya transaksi yang selesai</small>
                </div>
                <div class="dash-chart-sum">
                    <div>Total 7 hari<b>{{ $rp($chartTotal) }}</b></div>
                    <div>Rata-rata per hari<b>{{ $rp($chartAvg) }}</b></div>
                </div>
            </div>
            @if($chartMax > 0)
                <div class="dash-chart">
                    @foreach($chart as $bar)
                        @php $barHeight = max((int) round(($bar['total'] / $chartMax) * 148), $bar['total'] > 0 ? 6 : 2); @endphp
                        <div class="dash-col">
                            <div class="dash-cell">
                                <div class="dash-bar-val">{{ $short($bar['total']) }}</div>
                                <div class="dash-bar {{ $loop->last ? 'today' : '' }}" style="height:{{ $barHeight }}px;animation-delay:{{ $loop->index * 0.06 }}s" title="{{ $bar['label'] }}: {{ $rp($bar['total']) }}"></div>
                            </div>
                            <div class="dash-bar-label {{ $loop->last ? 'today' : '' }}">{{ $loop->last ? 'Hari ini' : $bar['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="dash-empty">Belum ada penjualan dalam 7 hari terakhir.</div>
            @endif
        </div>
    @endif

    <div class="dash-panel">
        <div class="dash-panel-head">
            <div>
                <h2>Kondisi Stok</h2>
                <small>Perbandingan stok semua produk</small>
            </div>
        </div>
        @if($stockTotal > 0)
            <div class="donut-wrap">
                <div class="donut" style="background:conic-gradient(#6b8f5e 0 {{ $degA }}%, #c9974a {{ $degA }}% {{ $degB }}%, #c2574a {{ $degB }}% 100%)">
                    <div class="donut-center"><b>{{ $stockTotal }}</b><span>produk</span></div>
                </div>
                <ul class="legend">
                    <li><i style="background:#6b8f5e"></i>Aman<b>{{ $ok }}</b></li>
                    <li><i style="background:#c9974a"></i>Menipis<b>{{ $lowS }}</b></li>
                    <li><i style="background:#c2574a"></i>Habis<b>{{ $emp }}</b></li>
                </ul>
            </div>
        @else
            <div class="dash-empty">Belum ada produk.</div>
        @endif
    </div>
</div>

@if($isStaff)
    {{-- ===== Produk terlaris + aktivitas ===== --}}
    <div class="dash-row r11">
        <div class="dash-panel">
            <div class="dash-panel-head">
                <div>
                    <h2>Produk Terlaris</h2>
                    <small>Berdasarkan jumlah terjual</small>
                </div>
                <a href="{{ route('products.index') }}">Semua produk &rarr;</a>
            </div>
            @if($topProducts->isEmpty())
                <div class="dash-empty">Belum ada data penjualan.</div>
            @else
                <ul class="top-list">
                    @foreach($topProducts as $i => $t)
                        <li class="top-item">
                            <span class="top-rank">{{ $i + 1 }}</span>
                            <div class="top-body">
                                <div class="top-name"><b>{{ $t->name }}</b><span class="dash-muted">{{ (int) $t->qty }} terjual</span></div>
                                <div class="bar-track"><span style="width:{{ $topMax > 0 ? round($t->qty / $topMax * 100) : 0 }}%"></span></div>
                            </div>
                            <div class="top-rev">{{ $rp($t->revenue) }}</div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="dash-panel">
            <div class="dash-panel-head">
                <div>
                    <h2>Aktivitas Terbaru</h2>
                    <small>Penjualan dan pembelian terakhir</small>
                </div>
            </div>
            @if($activity->isEmpty())
                <div class="dash-empty">Belum ada aktivitas transaksi.</div>
            @else
                <ul class="tl">
                    @foreach($activity as $a)
                        <li class="tl-item {{ $a['type'] === 'sale' ? 'tl-sale' : 'tl-buy' }}">
                            <span class="tl-dot"></span>
                            <div class="tl-main">
                                <a href="{{ $a['url'] }}">{{ $a['type'] === 'sale' ? 'Penjualan' : 'Pembelian' }} {{ $a['number'] }}</a>
                                <div class="dash-muted">{{ $a['who'] }} &middot; {{ $a['at']?->format($a['type'] === 'sale' ? 'd/m/Y H:i' : 'd/m/Y') }}</div>
                            </div>
                            <div class="tl-amt">
                                {{ $rp($a['amount']) }}<br>
                                <span class="dash-pill {{ $a['status'] === 'completed' ? 'p-green' : 'p-red' }}">{{ $a['status'] === 'completed' ? 'Selesai' : 'Dibatalkan' }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif

{{-- ===== Stok rendah ===== --}}
<div class="dash-panel dash-section">
    <div class="dash-panel-head">
        <div>
            <h2>Peringatan Stok Rendah</h2>
            <small>Produk yang stoknya sudah sama dengan atau di bawah stok minimum</small>
        </div>
        <a href="{{ route('products.low-stock') }}">Lihat daftar lengkap &rarr;</a>
    </div>
    @if($low->isEmpty())
        <div class="dash-empty">Semua stok produk dalam kondisi aman.</div>
    @else
        <div style="overflow-x:auto">
        <table class="dash-table">
            <tr><th>Produk</th><th>Kategori</th><th>Stok</th><th>Minimum</th><th>Level</th><th>Status</th></tr>
            @foreach($low as $p)
                @php $pct = $p->minimum_stock > 0 ? min(100, (int) round($p->stock / $p->minimum_stock * 100)) : 0; @endphp
                <tr>
                    <td><b>{{ $p->name }}</b><br><span class="dash-muted">{{ $p->sku }}</span></td>
                    <td>{{ $p->category->name }}</td>
                    <td class="low">{{ $p->stock }}</td>
                    <td>{{ $p->minimum_stock }}</td>
                    <td><div class="dash-level"><span class="{{ $p->stock == 0 || $pct < 50 ? 'lv-red' : 'lv-amber' }}" style="width:{{ max($pct, 4) }}%"></span></div></td>
                    <td><span class="dash-pill {{ $p->stock == 0 ? 'p-red' : 'p-amber' }}">{{ $p->stock == 0 ? 'Habis' : 'Menipis' }}</span></td>
                </tr>
            @endforeach
        </table>
        </div>
        {{ $low->links() }}
    @endif
</div>

</div>

<script>
    // Sapaan sesuai jam di komputer pengguna
    (function () {
        var h = new Date().getHours();
        var t = h < 11 ? 'Selamat pagi' : (h < 15 ? 'Selamat siang' : (h < 18 ? 'Selamat sore' : 'Selamat malam'));
        var el = document.getElementById('greet');
        if (el) el.textContent = t;
    })();

    // Angka naik perlahan dari 0 sampai nilai sebenarnya
    (function () {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        document.querySelectorAll('.countup').forEach(function (el) {
            var target = parseFloat(el.dataset.value) || 0;
            var prefix = el.dataset.prefix || '';
            if (target === 0) return;
            var start = null, dur = 900;
            function tick(now) {
                if (start === null) start = now;
                var p = Math.min((now - start) / dur, 1);
                var e = 1 - Math.pow(1 - p, 3);
                el.textContent = prefix + new Intl.NumberFormat('id-ID').format(Math.round(target * e));
                if (p < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        });
    })();
</script>
@endsection
