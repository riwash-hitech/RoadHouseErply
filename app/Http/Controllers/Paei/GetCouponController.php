<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetCashinService;
use App\Http\Controllers\Paei\Services\GetCouponService;
use App\Http\Controllers\Paei\Services\GetPaymentService;
use App\Http\Controllers\Paei\Services\GetPaymentTypeService;
use App\Http\Controllers\Services\EAPIService; 

class GetCouponController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetCouponService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getCoupons(){
        
         $param = array(
            "orderBy" => "changed",
            "orderByDir" => "asc",
            "recordsOnPage" => "200", 
            "changedSince" => $this->service->getLastUpdateDate(), 
         );
         $res = $this->api->sendRequest("getCoupons", $param);
         dd($res);
         if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            return $this->service->saveUpdate($res['records']);
         }
    }
}
