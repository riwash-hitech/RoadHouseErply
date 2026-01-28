<?php

namespace App\Models\PAEI;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

class ProductSoh extends Model
{
    
    use HasFactory;
    
    protected $primaryKey = "sohID";
    protected $table = 'newsystem_product_soh';
    protected $fillable = [];
    protected $guarded = [];
    
    public $timestamps = false;

    


}