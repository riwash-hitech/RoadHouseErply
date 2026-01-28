<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\InventoryRegistrationApiService;
use App\Http\Controllers\Paei\API\APIServices\WarehouseApiService;
use App\Models\PAEI\Warehouse;
use Illuminate\Http\Request;

class InventoryRegistrationApiController extends Controller
{
    //
    protected $service;


    public function __construct(InventoryRegistrationApiService $service){
        $this->service = $service;
        // $this->variation = $vp;
    }

    public function getInventories(Request $req){

        // if($req->inventoryRegistrationID){
        //     return $this->service->getByID($req->inventoryRegistrationID);
        // }

        if($req->productID){
            return $this->service->getInventoryRegistration($req);
        }
        if($req->productIDs){
            return $this->service->getInventoryRegistrationByIds($req);
        }

        // if($req->warehouseID){
        //     return $this->service->getByWarehouseID($req);
        // }

        return $this->service->getInventories($req);

    }

    public function getByID(Request $req){
       
        // return response()->json(["status" => 400, "message" => "ID Field is Required!"]);

    }
    
    public function getSOH(Request $req){
        return $this->service->getSOH($req);
    }
}
