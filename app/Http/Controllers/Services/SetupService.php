<?php
namespace App\Http\Controllers\Services;

use App\Models\Setup;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SetupService
{
    protected $setup;

    public function __construct(Setup $setup)
    {
        $this->setup = $setup;
    }

    public function getdetails(){
        $setup = $this->setup->get();
        return response()->json(['status'=>200,'setup'=>$setup]);
    }

    public function update($req){
        $checkKey = $this->setup->where('key', $req->key)->get();
        if($checkKey->isNotEmpty()){
            $this->setup->where('key', $req->key)->update(['value'=>$req->value]);
            return response()->json(['status'=>200, 'message'=>'Setup Updated Successfully']);
        }
        $this->setup->insert(['key'=>$req->key,'value'=>$req->value]);
        return response()->json(['status'=>200, 'message'=>'Setup Saved Successfully.']);
    }

    public function getByKey($key){
        $result = $this->setup->where('key', $key)->first();
        if(!$result){
            return response()->json(['status'=>401,'message'=> "Key not found."]);
        }
        return response()->json(['status'=>200,'setup'=>$result]);
    }

    public function deleteByKey($key){
        $result = $this->setup->where('key', $key)->first();
        if(!$result){
            return response()->json(['status'=>401,'message'=> "Key not found."]);
        }
        $this->setup->where('key', $key)->delete();
        return response()->json(['status'=>200,'message'=>"Setup Key Value Deleted Successfully."]);
    }
}