<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetCashinService;
use App\Http\Controllers\Paei\Services\GetOpenningClosingService;
use App\Http\Controllers\Paei\Services\GetPaymentService;
use App\Http\Controllers\Paei\Services\GetPaymentTypeService;
use App\Http\Controllers\Services\EAPIService; 

class GetOpenningClosingController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetOpenningClosingService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getOpenningClosing(){
        
         $param = array(
            "orderBy" => "changed",
            "orderByDir" => "asc",
            "recordsOnPage" => "200", 
            "changedSince" => 0,//$this->service->getLastUpdateDate(), 
         );
         $res = $this->api->sendRequest("getDayClosings", $param);
        //  dd($res);
         if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            return $this->service->saveUpdate($res['records']);
         }
    }
}
