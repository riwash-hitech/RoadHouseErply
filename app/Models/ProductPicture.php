<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPicture extends Model
{
    use HasFactory;

    protected $connection = "mysql2";
    protected $table = 'newsystem_stock_image_map';
    protected $primaryKey = 'newSystemMapID'; 
    public $timestamps = false;
}
