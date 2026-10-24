@extends('layouts')
@section('content')
<div class="card">
    <div class="row" style="justify-content:space-between">
        <h1 style="margin:0">Penjualan</h1>
        <a class="btn" href="{{ route('sales.create') }}">+ Penjualan Baru</a>
    </div>
    <form method="get" class="row" style="margin:16px 0">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor penjualan...">
        <select name="status">
            <option value="">Semua status</option>
            <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
        </select>
        <label>Dari</label><input type="date" name="from" value="{{ request('from') }}">
        <label>Sampai</label><input type="date" name="to" value="{{ request('to') }}">
        <button class="btn">Filter</button>
        @if(request()->hasAny(['q','status','from','to']))<a class="btn btn-gray" href="{{ route('sales.index') }}">Reset</a>@endif
    </form>
    <table>
        <tr><th>Nomor</th><th>Tanggal</th><th>Kasir</th><th>Total</th><th>Status</th><th></th></tr>
        @forelse($sales as $s)
            <tr>
                <td>{{ $s->sale_number }}</td>
                <td>{{ $s->sold_at?->format('d/m/Y H:i') }}</td>
                <td>{{ $s->user->name }}</td>
                <td>Rp {{ number_format($s->total, 0, ',', '.') }}</td>
                <td><span class="badge {{ $s->status === 'completed' ? 'badge-ok' : 'badge-cancel' }}">{{ $s->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}</span></td>
                <td><a class="btn" href="{{ route('sales.show', $s) }}">Detail</a></td>
            </tr>
        @empty
            <tr><td colspan="6">Belum ada data.</td></tr>
        @endforelse
    </table>
    {{ $sales->links() }}
</div>
@endsection
