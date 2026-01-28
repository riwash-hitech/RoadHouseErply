<?php

namespace App\Models\PswClientLive\Local;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempProductSizeSortOrder extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'temp_product_size_sort_order';

    protected $fillable = [];
    protected $guarded = [];
}

 