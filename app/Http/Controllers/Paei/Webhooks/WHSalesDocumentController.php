<?php

namespace App\Http\Controllers\Paei\Webhooks;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Webhooks\Services\WHSalesDocumentService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;

class WHSalesDocumentController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(WHSalesDocumentService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function update(Request $req){
        
        // info($req);
        info("*********************************************** Sales Document Insert Update Webhook Called ********************************************************");
        $this->service->updateOrCreate($req);

        http_response_code(200);

    }
    
    public function delete(Request $req){
        
        info($req);
        // $this->service->updateOrCreate($req);

        http_response_code(200);

    }

    // public function create(Request $req){
    //     $this->service->updateOrCreate($req);
    //     http_response_code(200);
    // }
   

}
