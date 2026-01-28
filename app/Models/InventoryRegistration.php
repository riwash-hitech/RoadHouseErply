<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRegistration extends Model
{
    use HasFactory;

    protected $connection = "mysql2";
    protected $table = 'inventory_registration';
    protected $primaryKey = 'id'; 
    protected $fillable = [];
    protected $guarded = [];
}
