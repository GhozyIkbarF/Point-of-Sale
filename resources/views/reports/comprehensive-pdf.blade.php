<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2563eb;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            margin: 0;
        }
        .summary {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            flex: 1;
            min-width: 200px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
        }
        .summary-card h3 {
            margin: 0 0 10px 0;
            color: #374151;
            font-size: 14px;
        }
        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            text-align: left;
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }
        .currency {
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generated on {{ $generated_at }}</p>
        @if(isset($cashier))
            <p><strong>Cashier:</strong> {{ $cashier['name'] }}</p>
        @endif
        @if(isset($period) && !isset($report['summary']['start_date']))
            <p><strong>Report Date:</strong> {{ \Carbon\Carbon::parse($period)->format('F d, Y') }}</p>
        @endif
        @if(isset($report['summary']['start_date']) && isset($report['summary']['end_date']))
            <p><strong>Period:</strong> {{ \Carbon\Carbon::parse($report['summary']['start_date'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($report['summary']['end_date'])->format('M d, Y') }}</p>
        @endif
    </div>

    <div class="summary">
        <div class="summary-card">
            <h3>Total Transactions</h3>
            <div class="value">{{ number_format($report['summary']['total_transactions']) }}</div>
        </div>
        <div class="summary-card">
            <h3>Total Revenue</h3>
            <div class="value currency">Rp {{ number_format($report['summary']['total_revenue'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <h3>Items Sold</h3>
            <div class="value">{{ number_format($report['summary']['total_items_sold']) }}</div>
        </div>
        <div class="summary-card">
            <h3>Average Transaction</h3>
            <div class="value currency">Rp {{ number_format($report['summary']['average_transaction'], 0, ',', '.') }}</div>
        </div>
    </div>

    @if(isset($report['sales_by_cashier']) && count($report['sales_by_cashier']) > 0)
    <div class="section">
        <h2>Sales by Cashier</h2>
        <table>
            <thead>
                <tr>
                    <th>Cashier</th>
                    <th class="text-right">Transactions</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Items Sold</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['sales_by_cashier'] as $cashier)
                <tr>
                    <td>{{ $cashier['cashier']['name'] ?? 'Unknown' }}</td>
                    <td class="text-right">{{ number_format($cashier['transaction_count']) }}</td>
                    <td class="text-right currency">Rp {{ number_format($cashier['revenue'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($cashier['total_items'] ?? 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($report['top_products']) && count($report['top_products']) > 0)
    <div class="section">
        <h2>Top Selling Products</h2>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th class="text-right">Qty Sold</th>
                    <th class="text-right">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['top_products'] as $product)
                <tr>
                    <td>{{ $product['product']['name'] }}</td>
                    <td>{{ $product['product']['sku'] }}</td>
                    <td class="text-right">{{ number_format($product['total_qty']) }}</td>
                    <td class="text-right currency">Rp {{ number_format($product['total_revenue'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($report['period_trends']) && count($report['period_trends']) > 0)
    <div class="section">
        <h2>Period Trends</h2>
        <table>
            <thead>
                <tr>
                    <th>Period</th>
                    <th class="text-right">Transactions</th>
                    <th class="text-right">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['period_trends'] as $trend)
                <tr>
                    <td>{{ $trend['period'] }}</td>
                    <td class="text-right">{{ number_format($trend['transaction_count']) }}</td>
                    <td class="text-right currency">Rp {{ number_format($trend['revenue'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($report['hourly_transactions']) && count($report['hourly_transactions']) > 0)
    <div class="section">
        <h2>Hourly Performance</h2>
        <table>
            <thead>
                <tr>
                    <th>Hour</th>
                    <th class="text-right">Transactions</th>
                    <th class="text-right">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['hourly_transactions'] as $hour)
                <tr>
                    <td>{{ $hour['hour'] }}:00</td>
                    <td class="text-right">{{ number_format($hour['transaction_count']) }}</td>
                    <td class="text-right currency">Rp {{ number_format($hour['revenue'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Generated by Mini POS System &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
