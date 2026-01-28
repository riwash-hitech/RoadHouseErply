<?php
namespace App\Http\Controllers\Paei\Webhooks\Services;

use App\Http\Controllers\Paei\Services\GetSalesDocumentService;
use App\Http\Controllers\Services\EAPIService; 
use App\Traits\ResponseTrait;
use Exception;

class WHSalesDocumentService {

    use ResponseTrait; 
    // protected $assortment;
    protected $service;

    public function __construct(GetSalesDocumentService $service)
    {
        // $this->assortment = $assortment;
        // $this->api = $api;
        $this->service = $service;
    }


    public function updateOrCreate($req)
    {
        $clientCode = @$req->clientCode;
        info("Client Code Webhooks .............. ".$clientCode . " Total Event ". @$req->eventCount);
        if(@$req["items"]){
            foreach($req["items"] as $item){
                try{
                    $this->service->saveUpdateByWebhook($item["data"], $clientCode);
                }catch(Exception $e){
                    info($e);
                }
            }
        }
    }




  
      
}


