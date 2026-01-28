<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $connection = "mysql2";
    
    protected $table = 'sync_source_erply_products';
    protected $primaryKey = 'productID'; 
    protected $fillable = [];
    protected $guarded = [];
    public $timestamps = false;
}
