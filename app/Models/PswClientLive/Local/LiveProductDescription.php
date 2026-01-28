<?php

namespace App\Models\PswClientLive\Local;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveProductDescription extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'newsystem_product_description_live';

    protected $fillable = [];
    protected $guarded = [];
}
