<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockCategory extends Model
{
    use HasFactory;
    protected $connection = "mysql2";
    protected $table = 'newsystem_stock_internet_category';
    protected $primaryKey = 'newsystem_StockCategoryID'; 
}
