<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetCustomerService;
use App\Http\Controllers\Paei\Services\GetGiftCardService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;

class GetGiftCardController extends Controller
{
    //
    use ResponseTrait;
    protected $service;
    protected $api;

    public function __construct(GetGiftCardService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getGiftCards(Request $req)
    {
        $isDebug = 0;
        if($req->debug){
            $isDebug = $req->debug;
        }
        $page = $req->page ? $req->page : 1;
        $added = $this->service->getLastUpdateDate();

        $param = array(
            // "orderBy" => "changedSince",
            // "orderByDir" => "asc",
            "recordsOnPage" => "200",
            // "active" => 1,
            "pageNo" => $page,
            "changedSince" => $added, 
        );

        $res = $this->api->sendRequest("getGiftCards", $param);
        
        if($isDebug == 1){
            dd($this->service->getLastUpdateDate(), $res);
        }
         if($res['status']['errorCode'] == 0){
            return $this->service->saveUpdate($res['records']);
         }

    }
}

 