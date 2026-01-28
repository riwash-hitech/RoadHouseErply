<?php

namespace App\Models\PAEI;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr; 

class TempProductUpdate extends Model
{
    
    use HasFactory;
    
    protected $table = 'temp_product_date';
    protected $fillable = [];
    protected $guarded = [];
    
    public $timestamps = false;
    

    


}