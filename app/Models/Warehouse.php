<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    protected $connection = "mysql2";
    protected $table = 'current_locations';
    protected $primaryKey = 'id';
    protected $fillable = ['erplyWarehouseID', 'erplyPending','erplyAssortmentID'];
    public $timestamps = false;
}
