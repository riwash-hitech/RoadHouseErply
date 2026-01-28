<?php

namespace App\Models\Kudos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockUDF extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv';
    protected $table = 'Stock UDF';
}
