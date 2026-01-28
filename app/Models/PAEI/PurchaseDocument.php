<?php

namespace App\Models\PAEI;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDocument extends Model
{
    use HasFactory; 
    protected $table = 'newsystem_purchase_documents';
    protected $fillable = [];
    protected $guarded = [];

    public function purchaseDetails(){
        return $this->hasMany(PurchaseDocumentDetail::class,'purchaseDocumentID', 'purchaseDocumentID');
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
