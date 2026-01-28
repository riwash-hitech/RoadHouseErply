<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\ProductCategoryApiService;
use Illuminate\Http\Request;

class ProductCategoryApiController extends Controller
{
    //
    protected $service;


    public function __construct(ProductCategoryApiService $service){
        $this->service = $service;
        // $this->variation = $vp;
    }

    public function getCategories(Request $req){
        

        return $this->service->getCategories($req);

    }

    public function getCategoryByID(Request $req){
        if($req->id){
            return $this->service->getByCategoryID($req->id);
        }
        return response()->json(["status" => 400, "message" => "ID Field is Required!"]);

    }
}
