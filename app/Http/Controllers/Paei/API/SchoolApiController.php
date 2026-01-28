<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\SchoolApiService;
use Illuminate\Http\Request;

class SchoolApiController extends Controller
{
    //
    protected $service; 

    public function __construct(SchoolApiService $service ){
        $this->service = $service;
       
    }

    public function getSchool(Request $req){
         
        return $this->service->getSchool($req);

    }

    public function getAll(Request $req){
         
        return $this->service->getAll($req);

    }
}
