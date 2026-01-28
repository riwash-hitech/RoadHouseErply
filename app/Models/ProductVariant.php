<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $connection = "mysql2";
    protected $table = 'sync_source_erply_product_colour_size';
    protected $primaryKey = 'variationID'; 
    protected $fillable = ['product_sku'];
    protected $guarded = [];
    public $timestamps = false;
}
