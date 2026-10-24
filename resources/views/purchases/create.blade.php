@extends('layouts')
@section('content')
<div class="card">
    <h1>Pembelian Baru</h1>
    <form method="post" action="{{ route('purchases.store') }}">
        @csrf
        <label>Supplier</label>
        <select name="supplier_id" required>
            <option value="">-- pilih supplier --</option>
            @foreach($suppliers as $s)
                <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
        <label>Tanggal pembelian</label>
        <input type="date" name="purchased_at" value="{{ old('purchased_at', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required>

        <h3>Item pembelian</h3>
        <table>
            <thead><tr><th>Produk</th><th>Jumlah</th><th>Harga beli (satuan)</th><th>Subtotal</th><th></th></tr></thead>
            <tbody id="rows"></tbody>
        </table>
        <p><button type="button" class="btn btn-gray" onclick="addRow()">+ Tambah baris</button></p>
        <h3>Total: Rp <span id="total">0</span></h3>
        <button class="btn">Simpan Pembelian</button>
        <a class="btn btn-gray" href="{{ route('purchases.index') }}">Batal</a>
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
            const sub = (parseFloat(tr.querySelector('.qty').value) || 0) * (parseFloat(tr.querySelector('.cost').value) || 0);
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

        const sel = document.createElement('select');
        sel.name = `items[${i}][product_id]`;
        sel.required = true;
        sel.add(new Option('-- pilih produk --', ''));
        products.forEach(p => {
            const o = new Option(p.name, p.id);
            o.dataset.price = p.price;
            if (String(item.product_id) === String(p.id)) o.selected = true;
            sel.add(o);
        });

        const qty = document.createElement('input');
        qty.type = 'number'; qty.name = `items[${i}][quantity]`; qty.className = 'qty';
        qty.min = 1; qty.step = 1; qty.required = true; qty.value = item.quantity ?? 1;

        const cost = document.createElement('input');
        cost.type = 'number'; cost.name = `items[${i}][unit_cost]`; cost.className = 'cost';
        cost.min = 0; cost.step = '0.01'; cost.required = true; cost.value = item.unit_cost ?? '';

        sel.addEventListener('change', () => {
            const opt = sel.options[sel.selectedIndex];
            cost.value = opt.dataset.price ?? '';
            recalc();
        });
        qty.addEventListener('input', recalc);
        cost.addEventListener('input', recalc);

        const sub = document.createElement('span');
        sub.className = 'sub'; sub.textContent = '0';

        const del = document.createElement('button');
        del.type = 'button'; del.className = 'btn btn-danger'; del.textContent = 'Hapus';
        del.addEventListener('click', () => {
            if (tbody.querySelectorAll('tr').length > 1) { tr.remove(); recalc(); }
        });

        [cell(sel), cell(qty), cell(cost), cell(sub), cell(del)].forEach(td => tr.appendChild(td));
        tbody.appendChild(tr);
        recalc();
    }

    initialItems.forEach(item => addRow(item));
</script>
@endsection
