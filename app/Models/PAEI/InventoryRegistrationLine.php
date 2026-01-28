<?php

namespace App\Models\PAEI;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

class InventoryRegistrationLine extends Model
{
    
    use HasFactory;
    
    protected $table = 'newsystem_inventory_registration_lines';
    protected $fillable = [];
    protected $guarded = [];

    

    // public function warehouse() {
    //     return $this->hasOne(Warehouse::class,'warehouseID', 'warehouseID');
    // }
    

    protected function getCreatedAtAttribute($val)
    {
        return Carbon::parse($val)->setTimezone('Australia/Sydney')->toDateTimeString();
         
    }
    protected function getUpdatedAtAttribute($val)
    {
        return Carbon::parse($val)->setTimezone('Australia/Sydney')->toDateTimeString();
         
    }

}
