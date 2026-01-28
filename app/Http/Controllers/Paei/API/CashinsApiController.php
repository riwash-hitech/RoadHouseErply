<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\CashinsApiService;
use App\Http\Controllers\Paei\API\APIServices\CurrencyApiService; 
use Illuminate\Http\Request;

class CashinsApiController extends Controller
{
    //
    protected $cashin; 

    public function __construct(CashinsApiService $mp ){
        $this->cashin = $mp;
       
    }

    public function getCashins(Request $req){
         
        return $this->cashin->getCashins($req);

    }
}
