<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller; 
use App\Http\Controllers\Paei\API\APIServices\PaymentApiService;
use App\Http\Controllers\Paei\API\APIServices\PricelistApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PricelistApiController extends Controller
{
    //
    protected $pricelist; 

    public function __construct(PricelistApiService $mp ){
        $this->pricelist = $mp;
       
    }

    public function getPricelists(Request $req){
         
        return $this->pricelist->getPricelist($req);

    }

    public function savePricelist(Request $req){

        $validator = Validator::make($req->all(),  [
            'type1' => 'required',
            'id1' => 'required', 
            'type' => 'required',	
        ]); 
        if ($validator->fails()) {

                return $this->validationError($validator->errors()->messages());
            
        }
         
        return $this->pricelist->savePricelist($req);

    }
}
