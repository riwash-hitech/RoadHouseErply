<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetCustomerGroupService;
use App\Http\Controllers\Paei\Services\GetCustomerService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;

class GetCustomerGroupController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetCustomerGroupService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getCustomerGroups(){
        //   [
                //   'take' => '200',
                //   'sort' => json_encode([
                //     "selector" => "changed",
                //     "desc" => false,
                //     ]),
                //   'match' => '>=',
                //     'changed' => $lastModified,
                //     'orderBy' => 'changed',
                //     'orderByDirection' => 'ASC',
                //    ]
        // $param = array(
        //     "take" => "100",
        //     "sort" => json_encode([
        //         "selector" => "changed",
        //         "desc" => false
        //     ]),
        //     "match" => ">=",
        //     "changed" => $this->service->getLastUpdateDate(),
        //     "orderBy" => 'changed',
        //     "orderByDirection" => 'ASC'
        //  );

        //  $res = $this->api->sendRequestBySwagger("https://api-crm-au.erply.com/v1/customers", $param);
        //  if(count($res) > 0){
        //    return $this->service->saveUpdate($res);
        //  }
        info("Cron Customer Group Sync");
         $param = array(
            "orderBy" => "changed",
            "orderByDir" => "asc",
            "recordsOnPage" => "200", 
            "changedSince" => $this->service->getLastUpdateDate(), 
         );
         $res = $this->api->sendRequest("getCustomerGroups", $param);
        //  dd($res);
         if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            return $this->service->saveUpdate($res['records']);
         }
    }
}
