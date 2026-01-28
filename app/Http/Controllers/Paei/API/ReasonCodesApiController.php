<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\ReasonCodesApiService;
use Illuminate\Http\Request;

class ReasonCodesApiController extends Controller
{
    //
    protected $reason; 

    public function __construct(ReasonCodesApiService $mp ){
        $this->reason = $mp;
       
    }

    public function getReasonCodes(Request $req){
         
        return $this->reason->getReasonCodes($req);

    }
}
