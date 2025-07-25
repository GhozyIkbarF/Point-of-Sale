<?php

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/products', function (Request $request) {
    $search = $request->get('search');
    $perPage = $request->get('per_page', 15);
    
    $query = Product::query();
    
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('sku', 'like', '%' . $search . '%')
              ->orWhere('name', 'like', '%' . $search . '%');
        });
    }
    
    $products = $query->where('stock', '>', 0)
                     ->orderBy('name')
                     ->paginate($perPage);
    
    return response()->json([
        'success' => true,
        'data' => $products,
        'message' => 'Products retrieved successfully'
    ]);
});

Route::get('/sales', function (Request $request) {
    $search = $request->get('search');
    $perPage = $request->get('per_page', 15);
    $startDate = $request->get('start_date');
    $endDate = $request->get('end_date');
    
    $query = Sale::with(['cashier:id,name', 'details.product:id,name,sku']);
    
    // Apply search filter
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('invoice_number', 'like', '%' . $search . '%')
              ->orWhere('customer_name', 'like', '%' . $search . '%');
        });
    }
    
    // Apply date range filter
    if ($startDate) {
        $query->whereDate('created_at', '>=', $startDate);
    }
    if ($endDate) {
        $query->whereDate('created_at', '<=', $endDate);
    }
    
    $sales = $query->orderByDesc('id')->paginate($perPage);
    
    return response()->json([
        'success' => true,
        'data' => $sales,
        'message' => 'Sales retrieved successfully'
    ]);
});

// API untuk detail sale
Route::get('/sales/{id}', function ($id) {
    $sale = Sale::with(['cashier:id,name', 'details.product:id,name,sku,price'])
                ->find($id);
    
    if (!$sale) {
        return response()->json([
            'success' => false,
            'message' => 'Sale not found'
        ], 404);
    }
    
    return response()->json([
        'success' => true,
        'data' => $sale,
        'message' => 'Sale detail retrieved successfully'
    ]);
});