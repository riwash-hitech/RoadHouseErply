<?php

namespace App\Models\PswClientLive\Local;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempProduct extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'temp_product_dev';
    protected $fillable = [];
    protected $guarded = [];
}
