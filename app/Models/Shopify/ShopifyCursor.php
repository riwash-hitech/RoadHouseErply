<?php

namespace App\Models\Shopify;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyCursor extends Model
{
    use HasFactory;
    
    protected $table = "shopify_cursors";
    // protected $primaryKey = "middleCustomerID";
    protected $fillable = [];
    protected $guarded = [];
    // public $timestamps = false;

}
