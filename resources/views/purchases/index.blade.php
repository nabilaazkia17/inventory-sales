@extends('layouts')
@section('content')
<div class="card">
    <div class="row" style="justify-content:space-between">
        <h1 style="margin:0">Pembelian</h1>
        <a class="btn" href="{{ route('purchases.create') }}">+ Pembelian Baru</a>
    </div>
    <form method="get" class="row" style="margin:16px 0">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor pembelian...">
        <select name="status">
            <option value="">Semua status</option>
            <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
        </select>
        <label>Dari</label><input type="date" name="from" value="{{ request('from') }}">
        <label>Sampai</label><input type="date" name="to" value="{{ request('to') }}">
        <button class="btn">Filter</button>
        @if(request()->hasAny(['q','status','from','to']))<a class="btn btn-gray" href="{{ route('purchases.index') }}">Reset</a>@endif
    </form>
    <table>
        <tr><th>Nomor</th><th>Tanggal</th><th>Supplier</th><th>Dibuat oleh</th><th>Total</th><th>Status</th><th></th></tr>
        @forelse($purchases as $p)
            <tr>
                <td>{{ $p->purchase_number }}</td>
                <td>{{ $p->purchased_at?->format('d/m/Y') }}</td>
                <td>{{ $p->supplier->name }}</td>
                <td>{{ $p->user->name }}</td>
                <td>Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                <td><span class="badge {{ $p->status === 'completed' ? 'badge-ok' : 'badge-cancel' }}">{{ $p->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}</span></td>
                <td><a class="btn" href="{{ route('purchases.show', $p) }}">Detail</a></td>
            </tr>
        @empty
            <tr><td colspan="7">Belum ada data.</td></tr>
        @endforelse
    </table>
    {{ $purchases->links() }}
</div>
@endsection
