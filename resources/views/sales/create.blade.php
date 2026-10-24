@extends('layouts')
@section('content')
<div class="card">
    <h1>Penjualan Baru</h1>
    <form method="post" action="{{ route('sales.store') }}">
        @csrf
        <h3>Item penjualan</h3>
        <table>
            <thead><tr><th>Produk</th><th>Jumlah</th><th>Harga jual</th><th>Stok tersedia</th><th>Subtotal</th><th></th></tr></thead>
            <tbody id="rows"></tbody>
        </table>
        <p><button type="button" class="btn btn-gray" onclick="addRow()">+ Tambah baris</button></p>
        <h3>Total: Rp <span id="total">0</span></h3>
        <small>Harga diambil otomatis dari data produk. Total akhir dihitung ulang oleh server.</small><br><br>
        <button class="btn">Simpan Penjualan</button>
        <a class="btn btn-gray" href="{{ route('sales.index') }}">Batal</a>
    </form>
</div>

<script>
    const products = @json($productData);
    const initialItems = @json($initialItems);
    const tbody = document.getElementById('rows');
    let idx = 0;

    const fmt = n => new Intl.NumberFormat('id-ID').format(n);

    function recalc() {
        let total = 0;
        tbody.querySelectorAll('tr').forEach(tr => {
            const price = parseFloat(tr.dataset.price) || 0;
            const sub = (parseFloat(tr.querySelector('.qty').value) || 0) * price;
            tr.querySelector('.sub').textContent = fmt(sub);
            total += sub;
        });
        document.getElementById('total').textContent = fmt(total);
    }

    function cell(child) {
        const td = document.createElement('td');
        if (child) td.appendChild(child);
        return td;
    }

    function addRow(item = {}) {
        const i = idx++;
        const tr = document.createElement('tr');
        tr.dataset.price = 0;

        const sel = document.createElement('select');
        sel.name = `items[${i}][product_id]`;
        sel.required = true;
        sel.add(new Option('-- pilih produk --', ''));
        products.forEach(p => {
            const o = new Option(p.name, p.id);
            o.dataset.price = p.price;
            o.dataset.stock = p.stock;
            if (String(item.product_id) === String(p.id)) o.selected = true;
            sel.add(o);
        });

        const qty = document.createElement('input');
        qty.type = 'number'; qty.name = `items[${i}][quantity]`; qty.className = 'qty';
        qty.min = 1; qty.step = 1; qty.required = true; qty.value = item.quantity ?? 1;

        const price = document.createElement('span');
        const stock = document.createElement('span');
        const sub = document.createElement('span');
        sub.className = 'sub'; sub.textContent = '0';

        function refresh() {
            const opt = sel.options[sel.selectedIndex];
            tr.dataset.price = opt.dataset.price ?? 0;
            price.textContent = opt.dataset.price ? 'Rp ' + fmt(opt.dataset.price) : '-';
            stock.textContent = opt.dataset.stock ?? '-';
            if (opt.dataset.stock) qty.max = opt.dataset.stock; else qty.removeAttribute('max');
            recalc();
        }
        sel.addEventListener('change', refresh);
        qty.addEventListener('input', recalc);

        const del = document.createElement('button');
        del.type = 'button'; del.className = 'btn btn-danger'; del.textContent = 'Hapus';
        del.addEventListener('click', () => {
            if (tbody.querySelectorAll('tr').length > 1) { tr.remove(); recalc(); }
        });

        [cell(sel), cell(qty), cell(price), cell(stock), cell(sub), cell(del)].forEach(td => tr.appendChild(td));
        tbody.appendChild(tr);
        refresh();
    }

    initialItems.forEach(item => addRow(item));
</script>
@endsection
