<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetCashinService;
use App\Http\Controllers\Paei\Services\GetPaymentService;
use App\Http\Controllers\Paei\Services\GetPaymentTypeService;
use App\Http\Controllers\Services\EAPIService; 

class GetCashInsController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetCashinService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getCashins(){
         
         $param = array(
            "orderBy" => "changed",
            "orderByDir" => "asc",
            "recordsOnPage" => "200", 
            "changedSince" => $this->service->getLastUpdateDate(), 
         );
         $res = $this->api->sendRequest("getCashIns", $param);
        //  dd($res);
         if($res['status']['errorCode'] == 0){
            return $this->service->saveUpdate($res['records']);
         }
    }
}
