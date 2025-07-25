<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use App\Exports\DailyReportExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role === 'cashier') {
            return $this->cashierReport($request);
        } else {
            return $this->adminReport($request);
        }
    }
    
    public function cashierReport(Request $request)
    {
        $user = Auth::user();
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);
        
        $dailyReport = $this->getDailyReport($selectedDate, $user->id);
        
        return Inertia::render('Report/CashierIndex', [
            'selectedDate' => $selectedDate->format('Y-m-d'),
            'report' => $dailyReport,
            'cashier' => $user
        ]);
    }
    
    public function adminReport(Request $request)
    {
        $period = $request->get('period', 'day');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $cashierId = $request->get('cashier_id');
        
        $users = User::where('role', 'cashier')->get();
        
        $report = $this->getAdminReport($period, $startDate, $endDate, $cashierId);
        
        return Inertia::render('Report/AdminIndex', [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'cashier_id' => $cashierId,
            'report' => $report,
            'users' => $users
        ]);
    }

    public function exportCashierReport(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $format = $request->get('format', 'excel');
        
        $user = Auth::user();
        $selectedDate = Carbon::parse($date);
        $report = $this->getDailyReport($selectedDate, $user->id);
        
        if ($format === 'pdf') {
            $data = [
                'title' => 'Daily Report - ' . $user->name,
                'period' => $selectedDate->format('Y-m-d'),
                'report' => $report,
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'cashier' => $user
            ];
            
            $pdf = Pdf::loadView('reports.comprehensive-pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            
            $filename = 'daily-report-' . $user->name . '-' . $selectedDate->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
        }
        
        // Excel export
        $filename = 'daily-report-' . $user->name . '-' . $selectedDate->format('Y-m-d') . '.xlsx';
        return Excel::download(new DailyReportExport($date, $user->id), $filename);
    }

    public function exportAdminReport(Request $request)
    {
        $period = $request->get('period', 'day');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $cashierId = $request->get('cashier_id');
        $format = $request->get('format', 'excel');
        
        $report = $this->getAdminReport($period, $startDate, $endDate, $cashierId);
        
        $title = 'Admin Report - ' . ucfirst($period);
        if ($cashierId) {
            $cashier = User::find($cashierId);
            $title .= ' - ' . $cashier->name;
        }
        
        if ($format === 'pdf') {
            $data = [
                'title' => $title,
                'period' => $period,
                'report' => $report,
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'cashier' => $cashierId ? User::find($cashierId) : null
            ];
            
            $pdf = Pdf::loadView('reports.comprehensive-pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            
            $filename = strtolower(str_replace(' ', '-', $title)) . '-' . $period . '.pdf';
            
            return $pdf->download($filename);
        }
        
        // Excel export - untuk admin report menggunakan periode hari ini jika day
        if ($period === 'day') {
            $exportDate = $startDate ?: Carbon::today()->format('Y-m-d');
            $filename = strtolower(str_replace(' ', '-', $title)) . '-' . $exportDate . '.xlsx';
            return Excel::download(new DailyReportExport($exportDate, $cashierId), $filename);
        }
        
        // Untuk periode lain, gunakan tanggal hari ini sebagai default
        $exportDate = Carbon::today()->format('Y-m-d');
        $filename = strtolower(str_replace(' ', '-', $title)) . '-' . $exportDate . '.xlsx';
        return Excel::download(new DailyReportExport($exportDate, $cashierId), $filename);
    }
    
    private function getDailyReport($date, $cashierId = null)
    {
        $query = Sale::whereDate('created_at', $date);
        
        if ($cashierId) {
            $query->where('cashier_id', $cashierId);
        }
        
        $totalTransactions = $query->count();
        $totalRevenue = $query->sum('total_price');
        
        $totalItemsSold = SaleDetail::whereHas('sale', function($query) use ($date, $cashierId) {
            $query->whereDate('created_at', $date);
            if ($cashierId) {
                $query->where('cashier_id', $cashierId);
            }
        })->sum('qty');
        
        $hourlyTransactions = Sale::whereDate('created_at', $date)
            ->when($cashierId, function($query) use ($cashierId) {
                return $query->where('cashier_id', $cashierId);
            })
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        
        // Top produk terjual
        $topProducts = SaleDetail::whereHas('sale', function($query) use ($date, $cashierId) {
                $query->whereDate('created_at', $date);
                if ($cashierId) {
                    $query->where('cashier_id', $cashierId);
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
            ->limit(10)
            ->get();
        
        return [
            'summary' => [
                'total_transactions' => $totalTransactions,
                'total_revenue' => $totalRevenue,
                'total_items_sold' => $totalItemsSold,
                'average_transaction' => $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0
            ],
            'hourly_transactions' => $hourlyTransactions,
            'top_products' => $topProducts
        ];
    }
    
    private function getAdminReport($period, $startDate = null, $endDate = null, $cashierId = null)
    {
        if (!$startDate || !$endDate) {
            $today = Carbon::today();
            [$startDate, $endDate] = $this->getDateRange($today, $period);
        } else {
            $startDate = Carbon::parse($startDate);
            $endDate = Carbon::parse($endDate);
        }
        
        $query = Sale::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($cashierId) {
            $query->where('cashier_id', $cashierId);
        }
        
        $totalTransactions = $query->count();
        $totalRevenue = $query->sum('total_price');
        
        $totalItemsSold = SaleDetail::whereHas('sale', function($query) use ($startDate, $endDate, $cashierId) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
            if ($cashierId) {
                $query->where('cashier_id', $cashierId);
            }
        })->sum('qty');
        
        // Sales by cashier
        $salesByCashier = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->when($cashierId, function($query) use ($cashierId) {
                return $query->where('cashier_id', $cashierId);
            })
            ->select(
                'cashier_id',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->with('cashier:id,name')
            ->groupBy('cashier_id')
            ->orderByDesc('revenue')
            ->get()
            ->map(function($sale) use ($startDate, $endDate) {
                // Get total items for each cashier
                $totalItems = SaleDetail::whereHas('sale', function($query) use ($startDate, $endDate, $sale) {
                    $query->whereBetween('created_at', [$startDate, $endDate])
                          ->where('cashier_id', $sale->cashier_id);
                })->sum('qty');
                
                $sale->total_items = $totalItems;
                return $sale;
            });
        
        // Daily/Periodic trends
        $periodGroup = $this->getPeriodGroupBy($period);
        $periodTrends = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->when($cashierId, function($query) use ($cashierId) {
                return $query->where('cashier_id', $cashierId);
            })
            ->select(
                DB::raw("{$periodGroup} as period"),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        
        // Top products
        $topProducts = SaleDetail::whereHas('sale', function($query) use ($startDate, $endDate, $cashierId) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
                if ($cashierId) {
                    $query->where('cashier_id', $cashierId);
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
            ->limit(10)
            ->get();
        
        // Recent sales
        $recentSales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->when($cashierId, function($query) use ($cashierId) {
                return $query->where('cashier_id', $cashierId);
            })
            ->with('cashier:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return [
            'summary' => [
                'total_transactions' => $totalTransactions,
                'total_revenue' => $totalRevenue,
                'total_items_sold' => $totalItemsSold,
                'average_transaction' => $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ],
            'sales_by_cashier' => $salesByCashier,
            'period_trends' => $periodTrends,
            'top_products' => $topProducts,
            'recent_sales' => $recentSales
        ];
    }
    
    private function getDateRange($date, $period)
    {
        $selectedDate = Carbon::parse($date);
        
        switch ($period) {
            case 'week':
                return [$selectedDate->copy()->startOfWeek(), $selectedDate->copy()->endOfWeek()];
            case 'month':
                return [$selectedDate->copy()->startOfMonth(), $selectedDate->copy()->endOfMonth()];
            case 'year':
                return [$selectedDate->copy()->startOfYear(), $selectedDate->copy()->endOfYear()];
            default: // day
                return [$selectedDate->copy()->startOfDay(), $selectedDate->copy()->endOfDay()];
        }
    }
    
    private function getPeriodGroupBy($period)
    {
        switch ($period) {
            case 'week':
                return 'DATE(created_at)';
            case 'month':
                return 'DATE(created_at)';
            case 'year':
                return 'DATE_FORMAT(created_at, "%Y-%m")';
            default: // day
                return 'HOUR(created_at)';
        }
    }
}
