<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockColorSize extends Model
{
    use HasFactory;
    protected $connection = "mysql2";
    protected $table = 'newsystem_stock_colour_size';
    protected $primaryKey = 'newSystemColourSizeID'; 
    public $timestamps = false;
}
