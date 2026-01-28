<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller; 
use App\Http\Controllers\Paei\API\APIServices\InventoryWriteOffApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryWriteOffApiController extends Controller
{
    //
    protected $service;


    public function __construct(InventoryWriteOffApiService $service){
        $this->service = $service;
        // $this->variation = $vp;
    }

    public function getInventories(Request $req){

        if($req->inventoryRegistrationID){
            return $this->service->getByID($req->inventoryRegistrationID);
        }

        if($req->productID){
            return $this->service->getInventoryWriteOff($req);
        }
        if($req->productIDs){
            return $this->service->getInventoryWriteOffsByIds($req);
        }

        if($req->warehouseID){
            return $this->service->getByWarehouseID($req);
        }

        return $this->service->getInventories($req);

    }

    public function getByID(Request $req){
       
        // return response()->json(["status" => 400, "message" => "ID Field is Required!"]);

    }


    public function saveUpdate(Request  $req){
        $validator = Validator::make($req->all(), [
            // 'title' => 'required|unique:posts|max:255',
            'reasonID' => 'required',
            'warehouseID' => 'required',
            'productID1' => 'required',
            'amount1' => 'required',
        ]); 

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()//->first()
            ], 400);
        }

        return $this->service->saveInventoryWriteOff($req);
    }
}
