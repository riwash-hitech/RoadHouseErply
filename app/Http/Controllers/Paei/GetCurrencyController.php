<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetCurrencyService; 
use App\Http\Controllers\Services\EAPIService;  

class GetCurrencyController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetCurrencyService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getCurrencies(){
        
         
        $param = array(
            "orderBy" => "added",
            "orderByDir" => "asc",
            "recordsOnPage" => "200", 
            // "pageNo" => $this->page,
            "addedSince" => $this->service->getLastUpdateDate(), 
        );

        $res = $this->api->sendRequest("getCurrencies", $param);
         
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            // print_r($res['records']);
            return $this->service->saveUpdate($res['records']);
        }
    }
}
