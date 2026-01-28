<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetAssortmentService;
use App\Http\Controllers\Paei\Services\GetUserOperationService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;

class GetAssortmentController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetAssortmentService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getAssortment(Request $req){
         
        $param = array(
            "orderBy" => "added",
            "orderByDir" => "asc",
            "recordsOnPage" => "200",
            // "tableNames" => "customers"
            // "getRowsForAllInvoices" => 1,
            // "active" => 1,
            // "pageNo" => $this->page,
            "addedSince" => $this->service->getLastUpdateDate(), 
        );
        
        $res = $this->api->sendRequest("getAssortments", $param);
        // dd($res);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            $this->service->saveUpdate($res['records']);
        }

        return response()->json(['status'=>200, 'message'=>"All Assortment Fetched Successfully."]);
    }

    

}
