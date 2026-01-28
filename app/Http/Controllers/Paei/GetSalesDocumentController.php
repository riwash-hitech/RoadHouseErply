<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller; 
use App\Http\Controllers\Paei\Services\GetSalesDocumentService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;

class GetSalesDocumentController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetSalesDocumentService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getSalesDocuments(Request $req){
        $param = array(
            "orderBy" => "lastChanged",
            "orderByDir" => "asc",
            "recordsOnPage" => "100",
            "getRowsForAllInvoices" => 1,
            'getAddedTimestamp'=>1,
            "warehouseID" => 8,
            // "pageNo" => $this->page,
            "changedSince" => $this->service->getLastUpdateDate(), 
        );

        $res = $this->api->sendRequest("getSalesDocuments", $param);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            // print_r($res['records']);
            return $this->service->saveUpdate($res['records']);
        }
        return response("All sales invoices synced.");
    }
}
