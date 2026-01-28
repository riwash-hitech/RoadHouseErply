<?php

namespace App\Models\PswClientLive\Local;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveProductSizeSortOrder extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'newsystem_product_size_sort_order_live';
    protected $fillable = [];
    protected $guarded = [];
}

 