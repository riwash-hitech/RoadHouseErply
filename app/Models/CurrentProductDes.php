<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrentProductDes extends Model
{
    use HasFactory;

    // protected $connection = "mysql2";
    protected $table = 'current_product_des';
    // protected $primaryKey = 'customerID';
    // public $timestamps = false;
     protected $fillable = [];
    protected $guarded = [];
}