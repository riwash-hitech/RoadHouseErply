<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller; 
use App\Http\Controllers\Paei\Services\GetPaymentService;
use App\Http\Controllers\Paei\Services\GetPaymentTypeService;
use App\Http\Controllers\Services\EAPIService; 

class GetPaymentTypeController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetPaymentTypeService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getTypes(){
        
         $param = array(
            "take" => "200",
            "sort" => json_encode([
                "selector" => "added",
                "desc" => false
            ]),
            "match" => ">=",
            "added" => $this->service->getLastUpdateDate(),
            "orderBy" => 'added',
            "orderByDirection" => 'ASC'
         );
         $res = $this->api->sendRequest("getPaymentTypes", $param);
        //  dd($res);
         if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            return $this->service->saveUpdate($res['records']);
         }
    }
}
