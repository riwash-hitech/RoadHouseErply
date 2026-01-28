<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetWarehouseService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;

class GetWarehouseController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetWarehouseService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getWarehouse(){
        // $param = array(
        //     "take" => "100",
        //     "sort" => json_encode([
        //         "selector" => "changed",
        //         "desc" => false
        //     ]),
        //     "match" => ">=",
        //     "changed" => 0,//$this->service->getLastUpdateDate(),
        //     "orderBy" => 'changed',
        //     "orderByDirection" => 'ASC'
        //  );

        //  $res = $this->api->sendRequestBySwagger("https://api-am-au.erply.com/v1/warehouse", $param);
        //  dd($res);
        
        $param = array(
                "take" => 200,
            );
            
        $res = $this->api->sendRequest("getWarehouses", $param);
        // dd($res);
        
         if($res['status']['errorCode'] == 0){
           return $this->service->saveUpdate($res['records']);
         }
    }
}
