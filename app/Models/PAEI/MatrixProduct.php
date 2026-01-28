<?php

namespace App\Models\PAEI;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatrixProduct extends Model
{
    use HasFactory;
 
    protected $table = 'newsystem_product_matrix';
    protected $fillable = [];  
    protected $guarded = [];

    public function variations() {
        return $this->hasMany(VariationProduct::class,'parentProductID', 'productID');
    }

    protected function getCreatedAtAttribute($val)
    {
        return Carbon::parse($val)->setTimezone('Australia/Sydney')->toDateTimeString();
         
    }
    protected function getUpdatedAtAttribute($val)
    {
        return Carbon::parse($val)->setTimezone('Australia/Sydney')->toDateTimeString();
         
    }


    public function scopeFilter($query, $params){

        if ( isset($params['productCode']) && trim($params['productCode']) !== '' )
        {
            $query->where('code', '=', $params['productCode']);//->orWhere('code2', '=', $params['productCode']);
        }

        if ( isset($params['productCode']) && trim($params['productCode']) !== '' )
        {
            $query->where('code2', '=', $params['productCode']);//->orWhere('code2', '=', $params['productCode']);
        }

        if ( isset($params['productName']) && trim($params['productName'] !== '') ) {
            $query->where('description', 'LIKE', trim($params['productName']) . '%');
        }
 
        if (isset($params['status']) && trim($params['status'] !== '') ) {
            info("my status ". $params['status']);
            $query->where('status', '=',  trim($params['status']));
        }else{
            $query->where('status',  "ACTIVE");
        }
        // info($query());
        if ( isset($params['groupID']) && trim($params['groupID']) !== '' )
        {
            if($params['groupID'] > 0){
                $query->where('groupID', '=', $params['groupID']);//->orWhere('code2', '=', $params['productCode']);
            }
        }

        if ( isset($params['priceRange']) && trim($params['priceRange']) !== '' )
        {
            $range = explode(",",$params['priceRange']);
            $query->where('price', '>', $range[0])->where('price', '<', $range[1]);//->orWhere('code2', '=', $params['productCode']);
            
        }

        if ( isset($params['type']) && trim($params['type']) !== '' )
        {
            
            $query->where('type', '=', $params['type']);//->orWhere('code2', '=', $params['productCode']);
            
        } 

        
        

        return $query;
        
        
    }
}
