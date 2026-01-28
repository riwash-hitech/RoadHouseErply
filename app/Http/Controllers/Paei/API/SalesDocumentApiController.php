<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\SalesDocumentApiService; 
use Illuminate\Http\Request;

class SalesDocumentApiController extends Controller
{
    //
    protected $service;


    public function __construct(SalesDocumentApiService $service){
        $this->service = $service;
        // $this->variation = $vp;
    }

    public function getSalesDocuments(Request $req){
        return $this->service->getSalesDocuments($req); 
    }
 
}
