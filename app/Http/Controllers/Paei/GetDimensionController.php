<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetCustomerService;
use App\Http\Controllers\Paei\Services\GetDimensionService;
use App\Http\Controllers\Services\EAPIService;
use App\Models\StockColor;
use App\Models\StockColorSize;
use Illuminate\Http\Request;

class GetDimensionController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetDimensionService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getMatrixDimension(){
        
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

        //  print_r($param);
        //  die;
        $param = array(
            //  "dimensionID" => 1
        );
         $res = $this->api->sendRequest("getMatrixDimensions", $param,);
        //  dd($res);
        //temp code
        // foreach($res['records'][0]['variations'] as $v){
        //     // StockColorSize::where('ciSizeCode', $v['name'])->update(['sizeDIMID'=>$v['variationID']]);
        //     StockColor::where('ciColourDescription', $v['name'])->update(['erplyDimID'=> $v['variationID']]);
        // }
        // echo "Colour updated successfully";
        // die;

         //real code
         if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            // print_r($res['records']);
            return $this->service->saveUpdate($res['records']);
         }
    }
}
