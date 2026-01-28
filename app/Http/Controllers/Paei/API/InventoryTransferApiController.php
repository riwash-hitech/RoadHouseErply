<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\InventoryTransferApiService; 
use Illuminate\Http\Request;

class InventoryTransferApiController extends Controller
{
    //
    protected $service;


    public function __construct(InventoryTransferApiService $service){
        $this->service = $service;
        // $this->variation = $vp;
    }

    public function getInventories(Request $req){

        if($req->inventoryTransferID){
            return $this->service->getByID($req->inventoryTransferID);
        }
        // if($req->productID){
        //     return $this->service->getInventoryWriteOff($req);
        // }
        // if($req->productIDs){
        //     return $this->service->getInventoryWriteOffsByIds($req);
        // }

        // if($req->warehouseID){
        //     return $this->service->getByWarehouseID($req);
        // }

        return $this->service->getInventories($req);

    }

    public function getByID(Request $req){
       
        // return response()->json(["status" => 400, "message" => "ID Field is Required!"]);

    }

    public function saveUpdate(Request $req	){
        
    }
}
