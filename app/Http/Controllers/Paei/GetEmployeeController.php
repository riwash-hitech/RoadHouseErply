<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetCashinService;
use App\Http\Controllers\Paei\Services\GetEmployeeService;
use App\Http\Controllers\Paei\Services\GetPaymentService;
use App\Http\Controllers\Paei\Services\GetPaymentTypeService;
use App\Http\Controllers\Services\EAPIService; 

class GetEmployeeController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetEmployeeService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getEmployees(){
        
         $param = array(
            "orderBy" => "changed",
            "orderByDir" => "asc",
            "recordsOnPage" => "200", 
            "changedSince" => $this->service->getLastUpdateDate(), 
         );
         $res = $this->api->sendRequest("getEmployees", $param);
        //  dd($res);
         if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            return $this->service->saveUpdate($res['records']);
         }
    }
}
