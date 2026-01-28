<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\CashinsApiService;
use App\Http\Controllers\Paei\API\APIServices\CurrencyApiService;
use App\Http\Controllers\Paei\API\APIServices\PosCashInOutApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PosCashInOutController extends Controller
{
    //
    protected $cashin; 

    public function __construct(PosCashInOutApiService $mp ){
        $this->cashin = $mp;
       
    }

    public function saveCashIn(Request $req){

        $customRules = array( 
            'pointOfSaleID' => 'required', 
            'sum' => 'required',
             
        ); 

        $validator = Validator::make($req->all(),  $customRules); 
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()//->first()
            ], 400);
        }
         
        return $this->cashin->saveCashIn($req);

    }

    public function saveCashOut(Request $req){
         
        $customRules = array( 
            'pointOfSaleID' => 'required', 
            'sum' => 'required',
             
        ); 

        $validator = Validator::make($req->all(),  $customRules); 
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()//->first()
            ], 400);
        }
        
        return $this->cashin->saveCashOut($req);

    }
}
