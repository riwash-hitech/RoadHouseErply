<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller; 
use App\Http\Controllers\Paei\Services\GetSupplierService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;

class GetSupplierController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetSupplierService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getSuppliers(){
         
        $param = array(
            "orderBy" => "added",
            "orderByDir" => "asc",
            "recordsOnPage" => "200",
            // "active" => 1,
            // "pageNo" => $this->page,
            "addedSince" => $this->service->getLastUpdateDate(), 
        );

        $res = $this->api->sendRequest("getSuppliers", $param);
        // dd($res);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            // print_r($res['records']);
            return $this->service->saveUpdate($res['records']);
        }
    }
}
