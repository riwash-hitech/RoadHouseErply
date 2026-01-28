<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockDetail extends Model
{
    use HasFactory;
    protected $connection = "mysql2";
    protected $table = 'newsystem_stockdetail';
    protected $primaryKey = 'newSystemStyleID'; 
    public $timestamps = false;


}
