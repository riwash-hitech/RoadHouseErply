<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\WarehouseApiService;
use App\Models\PAEI\Warehouse;
use Illuminate\Http\Request;

class WarehouseApiController extends Controller
{
    //
    protected $service;


    public function __construct(WarehouseApiService $service){
        $this->service = $service;
        // $this->variation = $vp;
    }

    public function getWarehouses(Request $req){

        return $this->service->getWarehouses($req);

    }

    public function getWarehouseByID(Request $req){
        if($req->id){
            return $this->service->getByWarehouseID($req->id);
        }
        return response()->json(["status" => 400, "message" => "ID Field is Required!"]);

    }
}
