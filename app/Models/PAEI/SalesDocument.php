<?php

namespace App\Models\PAEI;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDocument extends Model
{
    use HasFactory; 
    protected $table = 'newsystem_sales_documents';
    protected $fillable = [];
    protected $guarded = [];

    public function SalesDetails(){
        return $this->hasMany(SalesDocumentDetail::class, 'salesDocumentID', 'salesDocumentID');
    }


    protected function getCreatedAtAttribute($val)
    {
        return Carbon::parse($val)->setTimezone('Australia/Sydney')->toDateTimeString();
         
    }
    protected function getUpdatedAtAttribute($val)
    {
        return Carbon::parse($val)->setTimezone('Australia/Sydney')->toDateTimeString();
         
    }
}
