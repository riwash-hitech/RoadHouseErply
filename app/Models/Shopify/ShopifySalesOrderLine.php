<?php

namespace App\Models\Shopify;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifySalesOrderLine extends Model
{
    use HasFactory;

    protected $table = "newsystem_orders_product";
    protected $primaryKey = "orderproductID";
    protected $fillable = [];
    protected $guarded = [];
    public $timestamps = false;

}
