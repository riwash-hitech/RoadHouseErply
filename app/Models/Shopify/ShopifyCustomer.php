<?php

namespace App\Models\Shopify;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyCustomer extends Model
{
    use HasFactory;
    
    protected $table = "newsystem_customers";
    protected $primaryKey = "middleCustomerID";
    protected $fillable = [];
    protected $guarded = [];
    public $timestamps = false;

}
