@extends('layouts')
@section('content')
<div class="card">
    <div class="row" style="justify-content:space-between">
        <h1 style="margin:0">Penjualan {{ $sale->sale_number }}</h1>
        <span class="badge {{ $sale->status === 'completed' ? 'badge-ok' : 'badge-cancel' }}">{{ $sale->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}</span>
    </div>
    <p>Tanggal: {{ $sale->sold_at?->format('d/m/Y H:i') }}<br>
       Kasir: {{ $sale->user->name }}</p>
    <table>
        <tr><th>Produk</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr>
        @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->product->sku }} - {{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        <tr><th colspan="3">Total</th><th>Rp {{ number_format($sale->total, 0, ',', '.') }}</th></tr>
    </table>
    <p class="row" style="margin-top:16px">
        <a class="btn btn-gray" href="{{ route('sales.index') }}">Kembali</a>
        @if(auth()->user()->hasRole('admin') && $sale->status === 'completed')
            <form method="post" action="{{ route('sales.cancel', $sale) }}" onsubmit="return confirm('Batalkan penjualan ini? Stok akan dikembalikan.')">
                @csrf
                <button class="btn btn-danger">Batalkan Penjualan</button>
            </form>
        @endif
    </p>
</div>
@endsection
