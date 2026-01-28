<?php
namespace App\Http\Controllers\Paei\Webhooks\Services;
 
use App\Http\Controllers\Paei\Services\GetProductService;
use App\Models\PAEI\MatrixProduct;
use App\Models\PAEI\VariationProduct;
use App\Traits\ResponseTrait;
use Exception;

class WHProductService {

    use ResponseTrait; 
    // protected $assortment;
    protected $service;

    public function __construct(GetProductService $service)
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
                    $this->service->saveUpdateByWebhook($item["data"], $clientCode);
                }catch(Exception $e){
                    info($e);
                }
            }
        }
    }

    public function deleteProduct($req)
    {
        $clientCode = @$req->clientCode; 
        if(@$req["items"]){
            foreach($req["items"] as $item){
                try{
                    info("Erply Product Deleted ". $item["rowId"]);
                    MatrixProduct::where("productID", $item["rowId"])->update(["erplyDeleted" => 1, "roadhouseStatus" => 1]);
                    $chkVariation = VariationProduct::where("productID", $item["rowId"])->first();
                    if($chkVariation){
                        VariationProduct::where("productID", $item["rowId"])->update(["erplyDeleted" => 1]);
                        MatrixProduct::where("productID", $chkVariation->parentProductID)->update(["roadhouseStatus" => 1]);
                    } 

                }catch(Exception $e){
                    info($e);
                }
            }
        }
    }




  
      
}


