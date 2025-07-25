<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['sku', 'name', 'price', 'stock'];

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }
}
