<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\MatrixDimensionApiService;
use App\Http\Controllers\Paei\API\APIServices\SupplierApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatrixDimensionApiController extends Controller
{
    //
    protected $service;


    public function __construct(MatrixDimensionApiService $service){
        $this->service = $service;
        // $this->variation = $vp;
    }

    public function getDimensions(Request $req){
        return $this->service->getMatrixDimension($req);

    }

    public function getVariations(Request $req){
        // if($req->parentID){
        //     return $this->service->getVariationByDimID($req);
        // }
        return $this->service->getVariations($req);
        return response()->json(["status" => 400, "message" => "ID Field is Required!"]);

    }

    public function saveMatrixDimension(Request $req){
        $validator = Validator::make($req->all(), [
            'name' => 'required', 
            'active' => 'required',

        ]);
        // $validated = $req->validated();
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->messages());
            // return response()->json(['status'=>401,'validation_errors'=>$validator->messages()]);
        }

        return $this->service->saveUpdateMatrixDimension($req);
    }

    public function saveMatrixDimensionValue(Request $req){
        $validator = Validator::make($req->all(), [ 
            'name' => 'required', 
            'active' => 'required',
            'dimensionID' => 'required',
            'code' => 'required',
            
        ]);

        // $validated = $req->validated();
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->messages());
            // return response()->json(['status'=>401,'validation_errors'=>$validator->messages()]);
        }
        return $this->service->saveUpdateMatrixDimensionValue($req);
    }
}
