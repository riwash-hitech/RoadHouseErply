<?php

namespace App\Models\Shopify;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifySalesReturn extends Model
{
    use HasFactory;

    protected $table = "newsystem_refunds";
    protected $primaryKey = "refund_id";
    protected $fillable = [];
    protected $guarded = [];
    public $timestamps = false;

}