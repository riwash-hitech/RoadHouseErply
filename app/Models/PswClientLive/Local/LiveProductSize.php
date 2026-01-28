<?php

namespace App\Models\PswClientLive\Local;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveProductSize extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'newsystem_product_size_live';
    protected $fillable = [];
    protected $guarded = [];
}

 