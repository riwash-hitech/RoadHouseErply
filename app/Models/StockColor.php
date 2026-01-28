<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockColor extends Model
{
    use HasFactory;
    protected $connection = "mysql2";
    protected $table = 'current_stock_colour';
    protected $primaryKey = 'colourID'; 
    public $timestamps = false;
}
