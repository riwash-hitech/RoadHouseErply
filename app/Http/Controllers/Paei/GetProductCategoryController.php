<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetProductCategoryService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;

class GetProductCategoryController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetProductCategoryService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getProductCategory(){
        // $param = array(
        //     "take" => "30",
        //     "sort" => json_encode([
        //         "selector" => "changed",
        //         "desc" => false
        //     ]),
        //     "match" => ">=",
        //     "changed" => $this->service->getLastUpdateDate(),
        //     "orderBy" => 'changed',
        //     "orderByDirection" => 'ASC'
        //  ); 
        //  $res = $this->api->sendRequestBySwagger("https://api-pim-au.erply.com/v1/product/category", $param);
        //  dd($res);
        //  if(count($res) > 0){
        //     return $this->service->saveUpdate($res);
        //  }

        $param = array(
            "orderBy" => "changed",
            "orderByDir" => "asc",
            "recordsOnPage" => "200",
            "active" => 1,
            // "pageNo" => $this->page,
            "changedSince" => $this->service->getLastUpdateDate(), 
         );
 
         $res = $this->api->sendRequest("getProductCategories", $param);
        // dd($res);
         if($res['status']['errorCode'] == 0 && !empty($res['records'])){
             info("category ".count($res['records']));
            return $this->service->saveUpdateOldAPI($res['records']);
         }
    }
}
