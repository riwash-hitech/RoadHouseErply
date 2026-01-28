<?php

namespace App\Models\Shopify;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifySalesOrderDelivery extends Model
{
    use HasFactory;

    protected $table = "newsystem_order_delivery";
    protected $fillable = [];
    protected $guarded = [];
    
    public $timestamps = false;

}
