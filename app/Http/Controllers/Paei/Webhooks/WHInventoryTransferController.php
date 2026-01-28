<?php

namespace App\Http\Controllers\Paei\Webhooks;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Webhooks\Services\WHInventoryTransferService; 
use Illuminate\Http\Request;

class WHInventoryTransferController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(WHInventoryTransferService $service){
        $this->service = $service;
        // $this->api = $api;
    }

    public function createUpdate(Request $req){
        
        info($req);
        info("*********************************************** Inventory Transfer Insert Update Webhook Called ********************************************************");
        $this->service->updateOrCreate($req);

        http_response_code(200);

    }

    // public function create(Request $req){
    //     $this->service->updateOrCreate($req);
    //     http_response_code(200);
    // }
   

}
