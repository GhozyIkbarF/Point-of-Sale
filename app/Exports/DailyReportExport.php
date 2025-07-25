<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DailyReportExport implements WithMultipleSheets
{
    protected $date;
    protected $cashierId;
    
    public function __construct($date, $cashierId = null)
    {
        $this->date = Carbon::parse($date);
        $this->cashierId = $cashierId;
    }
    
    public function sheets(): array
    {
        return [
            new SummarySheet($this->date, $this->cashierId),
            new TransactionsSheet($this->date, $this->cashierId),
            new HourlyBreakdownSheet($this->date, $this->cashierId),
            new TopProductsSheet($this->date, $this->cashierId),
        ];
    }
}

// Sheet Ringkasan
class SummarySheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $date;
    protected $cashierId;
    
    public function __construct($date, $cashierId = null)
    {
        $this->date = $date;
        $this->cashierId = $cashierId;
    }
    
    public function collection()
    {
        $query = Sale::whereDate('created_at', $this->date);
        if ($this->cashierId) {
            $query->where('cashier_id', $this->cashierId);
        }
        
        $totalTransactions = $query->count();
        $totalRevenue = $query->sum('total_price');
        
        $totalItemsSold = SaleDetail::whereHas('sale', function($query) {
            $query->whereDate('created_at', $this->date);
            if ($this->cashierId) {
                $query->where('cashier_id', $this->cashierId);
            }
        })->sum('qty');
        
        $averageTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
        
        $data = [
            [
                'metric' => 'LAPORAN PENJUALAN HARIAN',
                'value' => ''
            ],
            [
                'metric' => 'Tanggal Laporan',
                'value' => $this->date->format('d F Y')
            ],
            [
                'metric' => 'Digenerate pada',
                'value' => now()->format('d F Y H:i:s')
            ]
        ];
        
        // Add cashier info if specified
        if ($this->cashierId) {
            $cashier = \App\Models\User::find($this->cashierId);
            $data[] = [
                'metric' => 'Kasir',
                'value' => $cashier ? $cashier->name : 'Unknown'
            ];
        }
        
        $data = array_merge($data, [
            [
                'metric' => '',
                'value' => ''
            ],
            [
                'metric' => 'Metrik',
                'value' => 'Nilai'
            ],
            [
                'metric' => 'Total Transaksi',
                'value' => $totalTransactions
            ],
            [
                'metric' => 'Total Omzet',
                'value' => 'Rp ' . number_format($totalRevenue, 0, ',', '.')
            ],
            [
                'metric' => 'Total Item Terjual',
                'value' => $totalItemsSold
            ],
            [
                'metric' => 'Rata-rata Transaksi',
                'value' => 'Rp ' . number_format($averageTransaction, 0, ',', '.')
            ]
        ]);
        
        return collect($data);
    }
    
    public function headings(): array
    {
        return ['Metrik', 'Nilai'];
    }
    
    public function title(): string
    {
        return 'Ringkasan - ' . $this->date->format('d/m/Y');
    }
}

// Sheet Detail Transaksi
class TransactionsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $date;
    protected $cashierId;
    
    public function __construct($date, $cashierId = null)
    {
        $this->date = $date;
        $this->cashierId = $cashierId;
    }
    
    public function collection()
    {
        $query = Sale::whereDate('created_at', $this->date);
        if ($this->cashierId) {
            $query->where('cashier_id', $this->cashierId);
        }
        
        return $query->with(['saleDetails.product'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    public function map($sale): array
    {
        return [
            $sale->id,
            $sale->created_at->format('H:i:s'),
            $sale->customer_name,
            $sale->total_price,
            'Rp ' . number_format($sale->total_price, 0, ',', '.'),
            $sale->saleDetails->sum('qty')
        ];
    }
    
    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Waktu',
            'Nama Customer',
            'Total (Angka)',
            'Total (Format)',
            'Jumlah Item'
        ];
    }
    
    public function title(): string
    {
        return 'Transaksi - ' . $this->date->format('d/m/Y');
    }
}

// Sheet Breakdown Per Jam
class HourlyBreakdownSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $date;
    protected $cashierId;
    
    public function __construct($date, $cashierId = null)
    {
        $this->date = $date;
        $this->cashierId = $cashierId;
    }
    
    public function collection()
    {
        $query = Sale::whereDate('created_at', $this->date);
        if ($this->cashierId) {
            $query->where('cashier_id', $this->cashierId);
        }
        
        $hourlyData = $query->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
            
        return $hourlyData->map(function($item) {
            return [
                'hour' => $item->hour . ':00 - ' . ($item->hour + 1) . ':00',
                'transaction_count' => $item->transaction_count,
                'revenue' => 'Rp ' . number_format($item->revenue, 0, ',', '.')
            ];
        });
    }
    
    public function headings(): array
    {
        return ['Jam', 'Jumlah Transaksi', 'Omzet'];
    }
    
    public function title(): string
    {
        return 'Per Jam - ' . $this->date->format('d/m/Y');
    }
}

// Sheet Top Produk
class TopProductsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $date;
    protected $cashierId;
    
    public function __construct($date, $cashierId = null)
    {
        $this->date = $date;
        $this->cashierId = $cashierId;
    }
    
    public function collection()
    {
        return SaleDetail::whereHas('sale', function($query) {
                $query->whereDate('created_at', $this->date);
                if ($this->cashierId) {
                    $query->where('cashier_id', $this->cashierId);
                }
            })
            ->select(
                'product_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(qty * price_at_time) as total_revenue')
            )
            ->with('product:id,name,sku')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(20)
            ->get();
    }
    
    public function map($item): array
    {
        return [
            $item->product->sku ?? '-',
            $item->product->name ?? 'Produk Tidak Ditemukan',
            $item->total_qty,
            'Rp ' . number_format($item->total_revenue, 0, ',', '.')
        ];
    }
    
    public function headings(): array
    {
        return ['SKU', 'Nama Produk', 'Qty Terjual', 'Total Omzet'];
    }
    
    public function title(): string
    {
        return 'Top Produk - ' . $this->date->format('d/m/Y');
    }
}
