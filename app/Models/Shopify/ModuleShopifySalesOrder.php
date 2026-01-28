<?php

namespace App\Models\Shopify;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleShopifySalesOrder extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = "orders";
    protected $fillable = [];
    protected $guarded = [];
}
