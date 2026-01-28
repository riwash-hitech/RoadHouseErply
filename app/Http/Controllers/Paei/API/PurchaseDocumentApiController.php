<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\PurchaseDocumentApiService;
use App\Http\Controllers\Paei\API\APIServices\SupplierApiService;
use Illuminate\Http\Request;

class PurchaseDocumentApiController extends Controller
{
    //
    protected $service;


    public function __construct(PurchaseDocumentApiService $service){
        $this->service = $service;
        // $this->variation = $vp;
    }

    public function getPurchaseDocuments(Request $req){
        return $this->service->getPurchaseDocuments($req); 
    }

     
}
