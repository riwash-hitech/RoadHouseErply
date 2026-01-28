<?php
namespace App\Http\Controllers\Paei\Webhooks\Services;

use App\Http\Controllers\Paei\Services\GetCustomerService; 
use App\Traits\ResponseTrait;
use Exception;

class WHCustomerService {

    use ResponseTrait; 
    // protected $assortment;
    protected $service;

    public function __construct(GetCustomerService $service)
    {
        // $this->assortment = $assortment;
        // $this->api = $api;
        $this->service = $service;
    }


    public function updateOrCreate($req)
    {
        // info($req);
        $clientCode = @$req->clientCode;
        info("Client Code Webhooks .............. ".$clientCode . " Total Event ". @$req->eventCount);
        if(@$req["items"]){
            foreach($req["items"] as $item){
                try{
                    $this->service->saveUpdateCustomerOldApi($item["data"], true, $clientCode);
                }catch(Exception $e){
                    info($e);
                }
            }
        }
    }




  
      
}


