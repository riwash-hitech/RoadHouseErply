<?php

namespace App\Models\PAEI;
 
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;
    protected $table = 'newsystem_employees';
    protected $fillable = [];
    protected $guarded = [];
    protected $casts = [
        "warehouses" => 'array'
    ];

    protected function getCreatedAtAttribute($val)
    {
        return Carbon::parse($val)->setTimezone('Australia/Sydney')->toDateTimeString();
         
    }

     
    
    public function warehousesList(){
        // return $this->belongsTo('Warehouse', 'warehouses->id');
        return $this->hasMany(Warehouse::class,'warehouseID', 'warehouses', "warehouses" );
    }

    protected function getUpdatedAtAttribute($val)
    {
        return Carbon::parse($val)->setTimezone('Australia/Sydney')->toDateTimeString();
         
    }
}
