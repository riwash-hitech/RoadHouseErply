<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller; 
use App\Http\Controllers\Paei\Services\GetUserOperationService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;

class GetUserOperationLogController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetUserOperationService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getOperationLogCustomer(Request $req){
         
        $param = array(
            "orderBy" => "added",
            "orderByDir" => "asc",
            "recordsOnPage" => "200",
            "tableName" => "customers",
            // "getRowsForAllInvoices" => 1,
            // "active" => 1,
            // "pageNo" => $this->page,
            "addedFrom" => $this->service->getLastUpdateDate("customers"), 
        );
        
         $res = $this->api->sendRequest("getUserOperationsLog", $param);
        //  dd($res);
         if($res['status']['errorCode'] == 0){
            return $this->service->handleCustomer($res['records']);
         }
    }

    public function getOperationLogCustomerGroup(Request $req){
         
        $param = array(
            "orderBy" => "added",
            "orderByDir" => "asc",
            "recordsOnPage" => "200",
            "tableName" => "customerGroups",
            // "getRowsForAllInvoices" => 1,
            // "active" => 1,
            // "pageNo" => $this->page,
            "addedFrom" => $this->service->getLastUpdateDate("customerGroups"), 
        );
        
         $res = $this->api->sendRequest("getUserOperationsLog", $param);
        //  dd($res);
         if($res['status']['errorCode'] == 0){
            return $this->service->handleCustomer($res['records']);
         }
    }

    

}
