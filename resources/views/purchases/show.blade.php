@extends('layouts')
@section('content')
<div class="card">
    <div class="row" style="justify-content:space-between">
        <h1 style="margin:0">Pembelian {{ $purchase->purchase_number }}</h1>
        <span class="badge {{ $purchase->status === 'completed' ? 'badge-ok' : 'badge-cancel' }}">{{ $purchase->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}</span>
    </div>
    <p>Tanggal: {{ $purchase->purchased_at?->format('d/m/Y') }}<br>
       Supplier: {{ $purchase->supplier->name }}<br>
       Dibuat oleh: {{ $purchase->user->name }}</p>
    <table>
        <tr><th>Produk</th><th>Jumlah</th><th>Harga beli</th><th>Subtotal</th></tr>
        @foreach($purchase->items as $item)
            <tr>
                <td>{{ $item->product->sku }} - {{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        <tr><th colspan="3">Total</th><th>Rp {{ number_format($purchase->total, 0, ',', '.') }}</th></tr>
    </table>
    <p class="row" style="margin-top:16px">
        <a class="btn btn-gray" href="{{ route('purchases.index') }}">Kembali</a>
        @if(auth()->user()->hasRole('admin') && $purchase->status === 'completed')
            <form method="post" action="{{ route('purchases.cancel', $purchase) }}" onsubmit="return confirm('Batalkan pembelian ini? Stok akan dikurangi.')">
                @csrf
                <button class="btn btn-danger">Batalkan Pembelian</button>
            </form>
        @endif
    </p>
</div>
@endsection
