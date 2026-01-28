<?php

namespace App\Models\Kudos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockColorSize extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv';
    protected $table = 'Style Colour Size';
}
