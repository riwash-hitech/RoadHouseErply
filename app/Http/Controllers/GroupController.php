<?php

namespace App\Http\Controllers;

use App\Http\Controllers\GetServices\GetGroupService;
use App\Http\Controllers\Services\GroupService; 
use Illuminate\Http\Request;

class GroupController extends Controller
{
    //
    protected $service; 
    // protected $getservice;
    public function __construct(GroupService $gs,)//  GetGroupService $gets)
    {
        $this->service = $gs; 
        // $this->getservice = $gets;
    }

    public function pushGroup(Request $req){
     
        return $this->service->updateMatrixGroup($req);
    }

    public function getGroups(Request $req){
        // return $this->getservice->getGroups($req);
    }
}
