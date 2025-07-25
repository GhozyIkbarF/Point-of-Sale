<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            return redirect()->route('sales.all');
        } else {
            return redirect()->route('sales.my');
        }
    }

    public function allSales(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('search');
        
        $query = Sale::with('cashier:id,name');
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhereHas('cashier', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        
        $sales = $query->orderByDesc('id')->paginate(10)->withQueryString();
        
        return inertia('Sale/AllSales', [
            'sales' => $sales,
            'filters' => [
                'search' => $search
            ]
        ]);
    }

    public function mySales(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('search');
        
        $query = Sale::with('cashier:id,name')->where('cashier_id', $user->id);
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%');
            });
        }
        
        $sales = $query->orderByDesc('id')->paginate(10)->withQueryString();
        
        return inertia('Sale/MySales', [
            'sales' => $sales,
            'filters' => [
                'search' => $search
            ],
            'cashier' => $user
        ]);
    }

    public function create()
    {
        $products = \App\Models\Product::all();
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            $cashiers = \App\Models\User::all();
        } else {
            $cashiers = collect([$user]); 
        }
        
        return inertia('Sale/Create', [
            'products' => $products,
            'cashiers' => $cashiers,
            'currentUser' => $user
        ]);
    }

    public function show(Sale $sale)
    {
        $user = Auth::user();
        
        if ($user->role === 'cashier' && $sale->cashier_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk melihat transaksi ini.');
        }
        
        $sale->load(['details.product', 'cashier']);
        return inertia('Sale/Show', [
            'sale' => $sale
        ]);
    }

    public function edit(Sale $sale)
    {
        $user = Auth::user();
        
        // Kasir hanya bisa edit sales mereka sendiri
        if ($user->role === 'cashier' && $sale->cashier_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit transaksi ini.');
        }
        
        $sale->load(['details.product', 'cashier']);
        $products = \App\Models\Product::all();
        $cashiers = \App\Models\User::all();
        return inertia('Sale/Edit', [
            'sale' => $sale,
            'products' => $products,
            'cashiers' => $cashiers
        ]);
    }
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'cashier_id' => 'required|exists:users,id',
            'paid_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        if ($user->role === 'cashier') {
            $validated['cashier_id'] = $user->id;
        }

        try {
            DB::beginTransaction();

            $total_price = 0;
            $itemDetails = [];

            // Validasi stok & hitung total
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($product->stock < $item['qty']) {
                    $products = \App\Models\Product::all();
                    $cashiers = \App\Models\User::all();
                    return Inertia::render('Sale/Create', [
                        'products' => $products,
                        'cashiers' => $cashiers,
                        'flash' => [
                            'error' => "Stok produk {$product->name} tidak mencukupi"
                        ]
                    ]);
                }

                $subtotal = $product->price * $item['qty'];
                $total_price += $subtotal;

                $itemDetails[] = [
                    'product' => $product,
                    'qty' => $item['qty'],
                    'price_at_time' => $product->price,
                ];
            }

            // Validasi pembayaran
            if ($validated['paid_amount'] < $total_price) {
                $products = \App\Models\Product::all();
                $cashiers = \App\Models\User::all();
                return Inertia::render('Sale/Create', [
                    'products' => $products,
                    'cashiers' => $cashiers,
                    'flash' => [
                        'error' => 'Jumlah pembayaran tidak mencukupi'
                    ]
                ]);
            }

            $sale = Sale::create([
                'invoice_number' => $this->generateInvoice(),
                'customer_name' => $validated['customer_name'],
                'cashier_id' => $validated['cashier_id'],
                'total_price' => $total_price,
                'paid_amount' => $validated['paid_amount'],
                'change' => $validated['paid_amount'] - $total_price,
            ]);

            foreach ($itemDetails as $item) {
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product']->id,
                    'qty' => $item['qty'],
                    'price_at_time' => $item['price_at_time'],
                ]);

                $item['product']->decrement('stock', $item['qty']);
            }

            DB::commit();

            $message = 'Transaksi berhasil disimpan dengan invoice: ' . $sale->invoice_number;
            
            $redirectRoute = $user->role === 'admin' ? 'sales.all' : 'sales.my';
            return redirect()->route($redirectRoute)->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            $products = \App\Models\Product::all();
            $cashiers = \App\Models\User::all();
            return Inertia::render('Sale/Create', [
                'products' => $products,
                'cashiers' => $cashiers,
                'flash' => [
                    'error' => 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage()
                ]
            ]);
        }
    }

    protected function generateInvoice(): string
    {
        $today = Carbon::now()->format('ymd');
        $count = Sale::whereDate('created_at', today())->count() + 1;
        return "INV-{$today}-" . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
