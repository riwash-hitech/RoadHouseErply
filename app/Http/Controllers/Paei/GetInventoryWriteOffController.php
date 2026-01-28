<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetCustomerService;
use App\Http\Controllers\Paei\Services\GetGiftCardService;
use App\Http\Controllers\Paei\Services\GetInventoryRegistrationService;
use App\Http\Controllers\Paei\Services\GetInventoryWriteOffService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;

class GetInventoryWriteOffController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetInventoryWriteOffService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getInventoryWriteOffs(){ 

        $param = array(
            "orderBy" => "changedSince",
            "orderByDir" => "asc",
            "recordsOnPage" => "200",
            // "active" => 1,
            // "pageNo" => $this->page,
            "changedSince" => $this->service->getLastUpdateDate(), 
         );
         $res = $this->api->sendRequest("getInventoryWriteOffs", $param);
        //  dd($res);
         if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            return $this->service->saveUpdate($res['records']);
         }
    }
}
