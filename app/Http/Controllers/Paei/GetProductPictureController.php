<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller; 
use App\Http\Controllers\Paei\Services\GetProductPictureService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;

class GetProductPictureController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetProductPictureService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getProductPictures(){
         
         $param = array(
            "orderBy" => "added",
            "orderByDir" => "asc",
            "recordsOnPage" => "200",
            // "active" => 1,
            // "pageNo" => $this->page,
            "added" => $this->service->getLastUpdateDate(), 
         );

         
         $res = $this->api->sendRequest("getProductPictures", $param);
        //  dd($res);
         if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            return $this->service->saveUpdate($res['records']);
         }

         return response()->json($res);
    }
}

// [{"pictureID":"440","name":"","thumbURL":"https:\/\/pswdata.retailcare.com.au\/magic\/uploads\/images\/1100105_6_10024_11_F_conImg.jpg","smallURL":"https:\/\/pswdata.retailcare.com.au\/magic\/uploads\/images\/1100105_6_10024_11_F_conImg.jpg","largeURL":"https:\/\/pswdata.retailcare.com.au\/magic\/uploads\/images\/1100105_6_10024_11_F_conImg.jpg","fullURL":"https:\/\/pswdata.retailcare.com.au\/magic\/uploads\/images\/1100105_6_10024_11_F_conImg.jpg","external":1,"hostingProvider":"","hash":null,"tenant":null}]