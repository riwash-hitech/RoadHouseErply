<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller; 
use App\Http\Controllers\Paei\Services\GetPriorityGroupService; 
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;

class GetPriorityGroupController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetPriorityGroupService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getPriorityGroup(Request $req){
         
        $param = array(
            "orderBy" => "added",
            "orderByDir" => "asc",
            "recordsOnPage" => "100", 
            "addedSince" => $this->service->getLastUpdateDate(), 
        );
        
        $res = $this->api->sendRequest("getProductPriorityGroups", $param);
        // dd($res);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            $this->service->saveUpdate($res['records']);
        }

        return response()->json(['status'=>200, 'message'=>"All Priority Group Fetched Successfully."]);
    }

    

}
