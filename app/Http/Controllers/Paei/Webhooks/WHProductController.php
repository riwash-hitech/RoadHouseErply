<?php

namespace App\Http\Controllers\Paei\Webhooks;

use App\Http\Controllers\Controller; 
use App\Http\Controllers\Paei\Webhooks\Services\WHProductService;
use Illuminate\Http\Request;

class WHProductController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(WHProductService $service){
        $this->service = $service;
        // $this->api = $api;
    }

    public function createUpdate(Request $req){
        
        // info($req);
        info("*********************************************** Product Insert Update Webhook Called ********************************************************");
        $this->service->updateOrCreate($req);
        info("product created or updated by webhook");
        http_response_code(200);

    }

    public function delete(Request $req){
        
        info($req);
        $this->service->deleteProduct($req);
        http_response_code(200);

    }

    // public function create(Request $req){
    //     $this->service->updateOrCreate($req);
    //     http_response_code(200);
    // }
   

}
