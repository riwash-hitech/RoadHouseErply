<?php

namespace App\Models\PswClientLive\Local;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveWarehouseLocation extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'newstystem_store_location_live';
}
