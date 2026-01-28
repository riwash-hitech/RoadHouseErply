<?php
namespace App\Http\Controllers\Services;

use App\Classes\UserLogger;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\ResponseTrait;

class UserService
{

    public $user;
    protected $letsLog;
    use ResponseTrait;

    public function __construct(User $user, UserLogger $letsLog)
    {
        $this->user = $user;
        $this->letsLog = $letsLog;
        // $this->user->changeConnection('mysql2');

    }

    public function getUserByID($id){
        $user = $this->user->findOrfail($id);
        if($user){
            return response()->json([
                'status' => 200,
                'records' => $user
            ]);
        }
        return response()->json([
            'status' => 200,
            'message' => "Invalid User ID."
        ]);
    }

    public function getUsers($req){
        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'first_name';
        }

        $paginate = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        // $user = $this->user->paginate($paginate);
        $requestData = $req->except('sort_by', 'direction', 'pagination', 'page','recordsOnPage','strictFilter');

        $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        // $customers = $this->customer->filter($req)->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        $user = $this->user->where(function ($q) use ($requestData, $req) {
            foreach ($requestData as $keys => $value) {
                if ($value != null) { 
                    if((bool)$req->strictFilter == true){
                        $q->Where($keys, $value);
                    }else{
                        $q->Where($keys, 'LIKE', '%'.$value.'%');
                    }
                }
            }
        })->orderBy($req->sort_by, $req->direction)->paginate($pagination);

        return response()->json([
            'status' => 200,
            'records' => $user
        ]);


    }

    public function getUser($id){
        $user = $this->user->findOrfail($id);
        if($user){
            return $this->successWithData($user); 
        }

        $this->failWithMessage("User Not Found!");
          
    }

    public function register($req){

        // $user = new User();
        // $user->changeConnection('mysql2');
        if($this->checkUser($req->email) == true){
            return response()->json([
                'status' => 401,
                // 'token' => $token,
                'validation_errors' =>  array("email" => "Duplicate Email Address.")
            ]);
        }
        $this->user->first_name = $req->first_name;
        $this->user->last_name = $req->last_name;
        $this->user->email = $req->email;
        if($req->type){
            $this->user->type = $req->type;
        }
        $this->user->password = Hash::make($req->password);
        $this->user->status = isset($req->status) == 1 ? $req->status : 1;
        $this->user->save();
        if(auth('sanctum')->user()){
            $this->letsLog->setLog('',json_encode($this->user),'Register User by Authenticated User');
        }else{
            $this->letsLog->setLogin($this->user->id,'Login');
        }
        $token = $this->user->createToken($this->user->email)->plainTextToken;
        return response()->json([
            'status' => 200,
            'token' => $token,
            'message' => "User Registered Successfully."
        ]);

    }

    public function updateUser($req,$id){

        $user = $this->user->findOrfail($id);
       
        if(!$user){
            return $this->failWithMessage("Invalid User ID!");
            
        }
        $old_user = $user;
        $user->first_name = $req->first_name;
        $user->last_name = $req->last_name;
        $user->email = $req->email;
        $user->status = $req->status;
        if($req->type){
            $user->type = $req->type;
        }
        $user->save();

        $this->letsLog->setLog(json_encode($old_user, true),json_encode($user, true),'User Updated');

        // $token = $user->createToken($user->email)->plainTextToken;
        return $this->successWithMessage("User Updated Successfully.");
        

    }

    public function updateStatus($req,$id){

        $user = $this->user->findOrfail($id);
        
        $old_user = $user; 
        $user->status = $user->status == 1 ? 0 : 1;
        $user->save();

        $this->letsLog->setLog(json_encode($old_user, true),json_encode($user, true),'User Status Updated');

        // $token = $user->createToken($user->email)->plainTextToken;
        return $this->successWithMessage("User Status Updated Successfully.");
        // return response()->json([
        //     'status' => 200,
        //     // 'token' => $token,
        //     'message' => "User Status Updated Successfully."
        // ]);

    }

    public function updatePassword($req,$id){

        $user = $this->user->findOrfail($req->id);
        $old_user = $user;
        
        if($req->oldPassword != ''){
            if(!$user || !Hash::check($req->oldPassword, $user->password)){
                
                return $this->failWithMessage("Invalid Old Password!");
                
            }
        }
        $this->user->where('id', $user->id)->update(
                [
                    "password" => Hash::make($req->password)
                ]
            );
        $new_user = $this->user->findOrfail($req->id);
        $this->letsLog->setLog(json_encode($old_user, true),json_encode($new_user, true),'Pasword Updated');

        // $token = $user->createToken($user->email)->plainTextToken;
        return $this->successWithMessage("Password Updated Successfully.");
        

    }

    public function login($req){
        $user = $this->user->where('email', $req->email)->first();
        if(!$user || !Hash::check($req->password, $user->password) || $user->status == 0){
            if($user && $user->status == 0){
                // return response()->json(['status'=>400,'message'=>'Account Deactivated, Please contact your administrator.']);
                return $this->failWithMessage("Account Deactivated, Please contact your administrator.");
            }
            // return $this->validationError($validator->errors()->messages());
            return $this->failWithMessage("Invalid Credentials.");
            // return response()->json(['status'=>401,'message'=>'Invalid Credentials.']);
        }
        $user = $this->user->where('id', $user->id)->select(['id','first_name','last_name','type','email','status'])->first();
        $token = $user->createToken($user->email)->plainTextToken;

        //lets log
        $this->letsLog->setLogin($user->id,'Login');

         
        return response()->json(
            [
                'status' => 200,
                'token' => $token,
                'user' => $user,
                "message" => "User Logged In Successfully."
            ]
        );
    }

    public function deleteUser($req){


        // $user = $this->user->where('id', auth()->id)->first();
        $user = auth()->user();
        if(!$user){
            return response()->json(['status'=>401,'message'=>'Invalid User ID']);
        }

        if(!$user || $user->type != 'superadmin'){
            return $this->failWithMessage("Authorization Failed.");
        }
        // print_r(auth('sanctum')->user());
        // die;
        
        $this->letsLog->setLog(json_encode($user),'','Delete User');
        // $token = $user->createToken($user->email)->plainTextToken;
        // if($user->)
        $selfFlag = false;
        if(auth()->user()->id == $req->id){
            $selfFlag = true;
        }
        $this->user->where('id', $req->id)->delete();
        return $this->successDelUser("User Deleted Successfully.", $selfFlag);
        // return $this->successWithMessage("User Deleted Successfully.");
        
    }

    protected function checkUser($email){
        $user = $this->user->where('email', $email)->first();
        if($user){
            return true;
        }
        return false;
    }

}
