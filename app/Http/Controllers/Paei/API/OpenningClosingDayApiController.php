<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\CashinsApiService;
use App\Http\Controllers\Paei\API\APIServices\CurrencyApiService;
use App\Http\Controllers\Paei\API\APIServices\OpenningClosingDayApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OpenningClosingDayApiController extends Controller
{
    //
    protected $ocd; 

    public function __construct(OpenningClosingDayApiService $mp ){
        $this->ocd = $mp;
       
    }

    public function getOpeningClosingDays(Request $req){
         
        return $this->ocd->getOpenningClosingDay($req);

    }

    public function saveOpeningDay(Request $req){
        $validator = Validator::make($req->all(),  [
            'pointOfSaleID' => 'required',
            'openedSum' => 'required',
            'openedUnixTime' => 'required'
        ]); 
        if ($validator->fails()) {

                return $this->validationError($validator->errors()->messages());
            
        }
        return $this->ocd->saveOpeningDay($req);
    }

    public function saveClosingDay(Request $req){
        $validator = Validator::make($req->all(),  [
            'pointOfSaleID' => 'required',
            'closedSum' => 'required',
            'closedUnixTime' => 'required',
            'bankedSum' => 'required'
        ]); 
        if ($validator->fails()) {

                return $this->validationError($validator->errors()->messages());
            
        }
        return $this->ocd->saveClosingDay($req);
    }
}
