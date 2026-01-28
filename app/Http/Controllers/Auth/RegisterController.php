<?php

namespace App\Http\Controllers\Auth;

 
use App\Http\Controllers\Controller;
use App\Http\Controllers\Services\RegisterService;
use App\Http\Requests\RegisterValidation;
use Illuminate\Support\Facades\Request;

class RegisterController extends Controller
{
    //
    protected $service;

    public function __construct(RegisterService $rs)
    {
        $this->service = $rs;
    }
    public function register(RegisterValidation $req){
        // return response()->json(['status'=>200]);
        return $this->service->register($req);
    }
}
