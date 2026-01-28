<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\CustomerAPIService;
use App\Http\Controllers\Paei\API\APIServices\CustomerGroupAPIService;
use App\Http\Requests\Newsystem\CustomerGroupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerGroupApiController extends Controller
{
    //
    protected $service;


    public function __construct(CustomerGroupAPIService $service){
        $this->service = $service;
        // $this->variation = $vp;
    }

    public function getCustomerGroups(Request $req){

        if($req->id){
            return $this->service->getByID($req->id);
        }

        return $this->service->getCustomerGroup($req);

    }

    public function saveCustomerGroup(Request  $req){
        $validator = Validator::make($req->all(), [
            // 'title' => 'required|unique:posts|max:255',
            'name' => 'required',
        ]); 

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->messages());
            // return response()->json([
            //     'error' => $validator->messages()//->first()
            // ], 400);
        }
         
        return $this->service->saveCustomerGroup($req);
    }

    public function deleteCustomerGroup(Request  $req){
        if($req->ids){
            return $this->service->deleteCustomerGroup($req);
        }
        $validator = Validator::make($req->all(), [
            // 'title' => 'required|unique:posts|max:255',
            'id' => 'required',
        ]); 
        
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()->first()
            ], 400);
        }
         
        return $this->service->deleteCustomerGroup($req);
    }
 

    public function updateCustomer(Request $req){
        
    }
}
