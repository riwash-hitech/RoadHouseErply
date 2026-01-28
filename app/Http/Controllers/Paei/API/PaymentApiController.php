<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller; 
use App\Http\Controllers\Paei\API\APIServices\PaymentApiService;
use Illuminate\Http\Request;

class PaymentApiController extends Controller
{
    //
    protected $currency; 

    public function __construct(PaymentApiService $mp ){
        $this->currency = $mp;
       
    }

    public function getPayments(Request $req){
         
        return $this->currency->getPayments($req);

    }
}
