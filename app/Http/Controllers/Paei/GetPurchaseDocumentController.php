<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller; 
use App\Http\Controllers\Paei\Services\GetPurchaseDocumentService;
use App\Http\Controllers\Services\EAPIService; 

class GetPurchaseDocumentController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetPurchaseDocumentService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getPurchaseDocument(){
        $param = array(
            "orderBy" => "added",
            "orderByDir" => "asc",
            "recordsOnPage" => "200",
            // "active" => 1,
            // "pageNo" => $this->page,
            "addedSince" => $this->service->getLastUpdateDate(), 
        );

        $res = $this->api->sendRequest("getPurchaseDocuments", $param);
        // dd($res);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            // print_r($res['records']);
            return $this->service->saveUpdate($res['records']);
        }
        return response()->json(['status'=>200, 'message'=>"No Purchase Document fetched Successfully."]);

    }
}
