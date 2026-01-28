<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\CurrencyApiService; 
use Illuminate\Http\Request;

class CurrencyApiController extends Controller
{
    //
    protected $currency; 

    public function __construct(CurrencyApiService $mp ){
        $this->currency = $mp;
       
    }

    public function getCurrency(Request $req){
        
         
        return $this->currency->getCurrency($req);

    }
}
