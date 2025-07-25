<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Harian - {{ $date }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        
        .summary {
            margin-bottom: 30px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-card {
            display: table-cell;
            border: 1px solid #ddd;
            padding: 15px;
            width: 50%;
            vertical-align: top;
            background-color: #f9f9f9;
        }
        
        .summary-card h3 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 14px;
        }
        
        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section h2 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 18px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PENJUALAN HARIAN</h1>
        <p>Mini POS System</p>
        <p><strong>Tanggal: {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</strong></p>
        <p>Digenerate pada: {{ now()->format('d F Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-card">
                    <h3>Total Transaksi</h3>
                    <div class="value">{{ $report['summary']['total_transactions'] }}</div>
                </div>
                <div class="summary-card">
                    <h3>Total Omzet</h3>
                    <div class="value">Rp {{ number_format($report['summary']['total_revenue'], 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="summary-row">
                <div class="summary-card">
                    <h3>Total Item Terjual</h3>
                    <div class="value">{{ $report['summary']['total_items_sold'] }}</div>
                </div>
                <div class="summary-card">
                    <h3>Rata-rata Transaksi</h3>
                    <div class="value">Rp {{ number_format($report['summary']['average_transaction'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Transaksi per Jam</h2>
        <table>
            <thead>
                <tr>
                    <th>Jam</th>
                    <th class="text-center">Jumlah Transaksi</th>
                    <th class="text-right">Omzet</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['hourly_transactions'] as $hourly)
                <tr>
                    <td>{{ $hourly->hour }}:00 - {{ $hourly->hour + 1 }}:00</td>
                    <td class="text-center">{{ $hourly->transaction_count }}</td>
                    <td class="text-right">Rp {{ number_format($hourly->revenue, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">Tidak ada data transaksi</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Produk Terlaris</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>SKU</th>
                    <th>Nama Produk</th>
                    <th class="text-center">Qty Terjual</th>
                    <th class="text-right">Total Omzet</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['top_products'] as $index => $product)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $product->product->sku ?? '-' }}</td>
                    <td>{{ $product->product->name ?? 'Produk Tidak Ditemukan' }}</td>
                    <td class="text-center">{{ $product->total_qty }}</td>
                    <td class="text-right">Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data produk</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section page-break">
        <h2>Detail Transaksi</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Waktu</th>
                    <th>Customer</th>
                    <th class="text-center">Jumlah Item</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->id }}</td>
                    <td>{{ $transaction->created_at->format('H:i:s') }}</td>
                    <td>{{ $transaction->customer_name }}</td>
                    <td class="text-center">{{ $transaction->sale_details_sum_qty }}</td>
                    <td class="text-right">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada transaksi</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</body>
</html>
