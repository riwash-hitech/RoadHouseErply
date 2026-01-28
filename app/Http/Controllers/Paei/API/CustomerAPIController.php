<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\CustomerAPIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerAPIController extends Controller
{
    //
    protected $service;


    public function __construct(CustomerAPIService $service){
        $this->service = $service;
        // $this->variation = $vp;
    }

    public function getCustomers(Request $req){
        if(isset($req->direction) == 0){
            $req['direction'] = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req['sort_by'] = 'fullName';
        }
        if(isset($req->deleted) == 0){
            $req['deleted'] = 0;
        }

        if($req->id){
            return $this->service->getByCustomerID($req->id);
        }

        return $this->service->getCustomer($req);

    }

    public function getCustomersByID(Request $req){
        if($req->id){
            return $this->service->getByCustomerID($req->id);
        }
        return response()->json(["status" => 400, "message" => "ID Field is Required!"]);

    }

    public function saveCustomer(Request $req){

        $customRules = array(
            // 'firstName' => 'required',
            // 'lastName' => 'required',
            'groupID' => 'required',
            // 'countryID' => 'required',
            // 'groupName' => 'required',
            // 'phone' => 'required',
            'mobile' => 'required',
            'email' => 'required',
            'customerType' => 'required',
            // 'code' => 'required',
            'euCustomerType' => 'required',
        );
        if($req->customerType == 'PERSON'){
            $customRules['firstName'] = 'required';
            $customRules['lastName'] ='required';
        }else{
            if(isset($req['companyName2']) == 0 && $req['companyName2'] == ''){
                $customRules['companyName'] = 'required';
            }
            
        }


        $validator = Validator::make($req->all(),  $customRules); 
        if ($validator->fails()) {

                return $this->validationError($validator->errors()->messages());
            
        }
        // if ($validator->fails()) {
        //     return response()->json([
        //         'error' => $validator->messages()//->first()
        //     ], 400);
        // }

        return $this->service->saveCustomer($req);
        
    }

    public function deleteCustomer(Request $req){
        // echo "hello"
        $validator = Validator::make($req->all(), [ 
            'id' => 'required', 
        ]); 

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()//->first()
            ], 400);
        }

        return $this->service->deleteCustomer($req);
        
    }
}
