<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\ProductAPIService;
use Illuminate\Http\Request;

class ProductAPIController extends Controller
{
    //
    protected $service;
    protected $variation;

    public function __construct(ProductAPIService $service){
        $this->service = $service;
        // $this->variation = $vp;
    }

    public function getProduct(Request $req){

        // if($req->productID != ''){
        //     return $this->service->getByProductID($req->productID);
        // }

        if($req->productIDs){
            return $this->service->getByProductIDs($req);
        }
        // if($req->productCode){
        //     return $this->service->getByProductCode($req);
        // }

        return $this->service->getProductShort($req);

    }

    public function getInventory(Request  $req){
        if($req->productID){
            return $this->service->getInventoryRegistration($req);
        }
        return response()->json(['status'=>400, "records"=>"Invalid Product ID!"]);
    }
    
    public function exportCSV(Request $req){
        
        return $this->service->exportCSV($req);
        
    }
}
