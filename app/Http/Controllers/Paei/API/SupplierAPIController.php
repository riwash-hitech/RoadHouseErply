<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller; 
use App\Http\Controllers\Paei\API\APIServices\SupplierApiService;
use Illuminate\Http\Request;

class SupplierAPIController extends Controller
{
    //
    protected $service;


    public function __construct(SupplierApiService $service){
        $this->service = $service;
        // $this->variation = $vp;
    }

    public function getSuppliers(Request $req){
        return $this->service->getSuppliers($req);

    }

    public function getCustomersByID(Request $req){
        if($req->id){
            return $this->service->getBySupplierID($req->id);
        }
        return response()->json(["status" => 400, "message" => "ID Field is Required!"]);

    }
}
