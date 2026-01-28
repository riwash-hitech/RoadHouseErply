<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\RegisterService;
use App\Http\Controllers\Services\UserService;
use App\Http\Requests\RegisterValidation;
use App\Http\Requests\UserValidation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    //
    protected $service;
    use ResponseTrait;


    public function __construct(UserService $rs)
    {
        $this->service = $rs;

    }

    public function register(Request $req){
        $validator = Validator::make($req->all(), [
            'email' => 'required|email|unique:users',
            // 'username' => 'required|string|max:50',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required|min:8',
        ]);
        // $validated = $req->validated();
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->messages());
            // return $this->validationError($validator->errors()->messages());
            // return response()->json(['status'=>401,'validation_errors'=>$validator->messages()]);
        }
        return $this->service->register($req);
    }

    public function getUsers(Request $req){
        // echo "hello";
        // die;
        return $this->service->getUsers($req);


    }
    public function getUser(Request $req, $id){

            return $this->service->getUser($id);

    }

    public function updatePassword(Request $req, $id){

        $fields = array( 
            // 'username' => 'required|string|max:50',
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required|min:8',

        );
        $user = User::findOrfail($id);
        if($user->type != 'superadmin'){
            $fields['oldPassword'] = 'required|min:8';
        }
        // return $this->successWithData($user);
        $validator = Validator::make($req->all(), $fields);
        // $validated = $req->validated();
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->messages()); 
            // return response()->json(['status'=>401,'validation_errors'=>$validator->messages()]);
        }
        
        return $this->service->updatePassword($req, $id);
    }

    public function updateUser(Request $req , $id){
        $validator = Validator::make($req->all(), [
            // 'id' => 'required',
            'email' => 'required|email',
            // 'username' => 'required|string|max:50',
            'first_name' => 'required|string',
            'last_name' => 'required|string',

        ]);
        // $validated = $req->validated();
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->messages());
            // return response()->json(['status'=>401,'validation_errors'=>$validator->messages()]);
        }
        return $this->service->updateUser($req,$id);
    }

    public function updateUserStatus(Request $req , $id){
        $user = $this->service->user->findOrfail($id);
       
        if(!$user){
            return $this->successWithMessage("Invalid User ID!");
            // return response()->json([
            //     'status' => 401,
            //     'message' => "Invalid User ID!"
            // ]);
        }
        // $validated = $req->validated();
        
        return $this->service->updateStatus($req,$id);
    }

    public function deleteUser(Request $req){
        $validator = Validator::make($req->all(), [
            'id' => 'required',
        ]);
        // $validated = $req->validated();
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->messages());
            // return response()->json(['status'=>401,'validation_errors'=>$validator->messages()]);
        }
        return $this->service->deleteUser($req);
    }

    public function login(Request $req){
        $validator = Validator::make($req->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);
        // $validated = $req->validated();
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->messages());
            // return response()->json(['status'=>401,'message'=>'Invalid Credentials.']);
        }

        return $this->service->login($req);

    }





}
