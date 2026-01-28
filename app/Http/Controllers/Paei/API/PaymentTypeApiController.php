<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;  
use App\Http\Controllers\Paei\API\APIServices\PaymentTypeApiService;
use Illuminate\Http\Request;

class PaymentTypeApiController extends Controller
{
    //
    protected $type; 

    public function __construct(PaymentTypeApiService $mp ){
        $this->type = $mp;
       
    }

    public function getTypes(Request $req){
         
        return $this->type->getTypes($req);

    }

    public function saveUpdate(Request $req){
        return $this->type->saveUpdate($req);
    }
}
