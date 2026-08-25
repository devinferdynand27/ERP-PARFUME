<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $header->nomor_po }} - Pesanan Pembelian</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            padding: 32px;
            font-size: 13px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #111827;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .brand-name { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        .brand-sub { font-size: 11px; color: #6b7280; letter-spacing: 2px; margin-top: 2px; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 16px; margin: 0 0 4px; }
        .doc-title .nomor { font-family: monospace; font-size: 13px; font-weight: bold; }
        .meta-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .meta-table td { padding: 3px 0; vertical-align: top; }
        .meta-table td.label { width: 140px; color: #6b7280; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        table.items th, table.items td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            font-size: 12px;
            text-align: left;
        }
        table.items th {
            background: #111827;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }
        table.items td.num { text-align: right; }
        table.items td.center { text-align: center; }
        table.items tfoot td {
            font-weight: bold;
            background: #f9fafb;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 56px;
        }
        .signature-box { width: 220px; text-align: center; font-size: 12px; }
        .signature-line { margin-top: 64px; border-top: 1px solid #111827; padding-top: 4px; }
        .print-bar { text-align: right; margin-bottom: 16px; }
        .print-bar button {
            padding: 8px 16px;
            background: #111827;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
        }
        @media print {
            .print-bar { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="print-bar">
        <button onclick="window.print()">Cetak</button>
    </div>

    <div class="header">
        <div>
            <div class="brand-name">ERP PARFUME</div>
            <div class="brand-sub">LUXURY FRAGRANCES</div>
        </div>
        <div class="doc-title">
            <h1>Pesanan Pembelian</h1>
            <div class="nomor">{{ $header->nomor_po }}</div>
        </div>
    </div>

    <table class="meta-table">
        <tr>
            <td class="label">Tanggal</td>
            <td>{{ \Illuminate\Support\Carbon::parse($header->tanggal)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Supplier</td>
            <td>{{ $header->nama_supplier }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Permintaan</td>
            <td>{{ $header->nomor_permintaan ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $header->status) }}</td>
        </tr>
        <tr>
            <td class="label">Catatan</td>
            <td>{{ $header->catatan ?: '-' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 32px;">No</th>
                <th>Barang</th>
                <th style="width: 90px;">Satuan</th>
                <th style="width: 90px;" class="num">Qty Dipesan</th>
                <th style="width: 120px;" class="num">Harga Satuan</th>
                <th style="width: 130px;" class="num">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->nama_satuan }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format($item->qty_dipesan, 2, '.', ''), '0'), '.') }}</td>
                    <td class="num">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="num">Total</td>
                <td class="num">{{ number_format(collect($items)->sum('subtotal'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="signatures">
        <div class="signature-box">
            <div>Dipesan oleh</div>
            <div class="signature-line">&nbsp;</div>
        </div>
        <div class="signature-box">
            <div>Disetujui oleh</div>
            <div class="signature-line">&nbsp;</div>
        </div>
    </div>
</body>
</html>
