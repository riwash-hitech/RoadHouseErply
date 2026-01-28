<?php

namespace App\Http\Controllers\Paei\Webhooks;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Webhooks\Services\WHCustomerService; 
use Illuminate\Http\Request;

class WHCustomerController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(WHCustomerService $service){
        $this->service = $service;
        // $this->api = $api;
    }

    public function createUpdate(Request $req){
        
        // info($req);
        info("*********************************************** Customer Insert Update Webhook Called ********************************************************");
        $this->service->updateOrCreate($req);

        http_response_code(200);

    }

    // public function create(Request $req){
    //     $this->service->updateOrCreate($req);
    //     http_response_code(200);
    // }
   

}
