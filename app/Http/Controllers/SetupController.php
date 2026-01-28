<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\SetupService;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Validator;

class SetupController extends Controller
{
    //
    protected $service;

    public function __construct(SetupService $ss)
    {
        $this->service = $ss;
    }

    public function getdetails(){
        return $this->service->getdetails();
    }

    public function saveUpdate(Request $req){
        $validator = Validator::make($req->all(), [
            'key' => 'required',
            'value' => 'required'
        ]);
         
        if ($validator->fails()) {
            return response()->json(['status'=>401,'validator_erros'=> $validator->message()]);
        }
        return $this->service->update($req);
    }

    public function getByKey(Request $req){
        $validator = Validator::make($req->all(), [
            'key' => 'required', 
        ]);
         
        if ($validator->fails()) {
            return response()->json(['status'=>401,'message'=> "Key not found."]);
        }
        
        return $this->service->getByKey($req->key);
    }

    public function deleteByKey(Request $req){
        $validator = Validator::make($req->all(), [
            'key' => 'required', 
        ]);
         
        if ($validator->fails()) {
            return response()->json(['status'=>401,'message'=> "Key not found."]);
        }

        return $this->service->deleteByKey($req->key);
    }
}
