<?php

namespace App\Models\PAEI;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryWriteOffs extends Model
{
    use HasFactory;
    protected $table = 'newsystem_inventory_write_offs';
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
