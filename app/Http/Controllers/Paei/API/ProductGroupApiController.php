<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\ProductGroupApiService;
use Illuminate\Http\Request;

class ProductGroupApiController extends Controller
{
    //
    protected $service;


    public function __construct(ProductGroupApiService $service){
        $this->service = $service;
        // $this->variation = $vp;
    }

    public function getGroups(Request $req){

        return $this->service->getGroups($req);

    }

    public function getGroupsByID(Request $req){
        if($req->productGroupID){
            return $this->service->getByGroupsID($req->productGroupID);
        }
        return response()->json(["status" => 400, "message" => "ID Field is Required!"]);

    }
}
