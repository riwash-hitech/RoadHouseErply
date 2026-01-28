<?php

namespace App\Models\Shopify;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifySalesOrder extends Model
{
    use HasFactory;

    protected $table = "newsystem_orders";
    protected $primaryKey = "orderID";
    protected $fillable = [];
    protected $guarded = [];
    public $timestamps = false;

}
