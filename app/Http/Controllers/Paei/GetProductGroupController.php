<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetProductGroupService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;
use App\Models\PAEI\Brand;

class GetProductGroupController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetProductGroupService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
        $this->api->verifySession();
    }

    public function getProductGroup(){
        // echo "hello sir";
        // die;
        $param = array(
            "orderBy" => "changed",
            "orderByDir" => "asc",
            "recordsOnPage" => "200",
            "active" => 1,
            // "pageNo" => $this->page,
            "changedSince" => $this->service->getLastUpdateDate(), 
         );
        //  print_r($param);
        //  die;
         $res = $this->api->sendRequest("getProductGroups", $param);
        // dd($res);
         if($res['status']['errorCode'] == 0 && !empty($res['records'])){
             info("tot groups : ".count($res['records']));
            return $this->service->saveUpdateOldAPI($res['records']);
         }
        //  $param = [
        //     "take" => "3000",
        //     "sort" => json_encode([
        //         "selector" => "added",
        //         "desc" => false
        //     ]),
        //     "match" => ">=",
        //     "added" => $this->service->getLastUpdateDate(),
        //     "orderBy" => 'added',
        //     "orderByDirection" => 'ASC'
        //  ];

        //  $p2 =[
        //        'take' => '100000',
        //        'sort' => json_encode([
        //           "selector" => "added",
        //           "desc" => false,
        //         ]),
        //        'filter' => '[["added", ">=", ' . $this->service->getLastUpdateDate() . ']]',
        //     ];
        // // echo $this->service->getLastUpdateDate();

        // // $p2 = json_encode($p2, true);
        // //  print_r($p2);
        // //  die;
        //  $res = $this->api->sendRequestBySwagger("https://api-pim-au.erply.com/v1/product/group", $param);
        // //  dd($res);
        //  if(count($res) > 0){
        //     return $this->service->saveUpdate($res);
        //  }

        //  return response()->json(['status'=>200, 'message'=>"Product Group Data Not Found!"]);
    }
    
    public function getBrand(){
        info("brand api called");
        $param = array(
            "orderBy" => "changed",
            "orderByDir" => "asc",
            "recordsOnPage" => "200", 
            // "pageNo" => $this->page,
            "changedSince" => $this->getBrandLastUpdateDate(), 
         );
        //  print_r($param);
        //  die;
         $res = $this->api->sendRequest("getBrands", $param);
        // dd($res);
         if($res['status']['errorCode'] == 0 && !empty($res['records'])){
             info("tot brands update ".count($res['records']));
            foreach($res['records'] as $brand){
                Brand::updateOrcreate(
                    [
                        "clientCode" => $this->api->client->clientCode,
                        "brandID" => $brand['brandID']
                    ],
                    [
                        "clientCode" => $this->api->client->clientCode,
                        "brandID" => $brand['brandID'],
                        "name" => $brand['name'],
                        "added" => date('Y-m-d H:i:s',$brand['added']),
                        "lastModified" => date('Y-m-d H:i:s',$brand['lastModified']),
                    ]
                );
            }
         }
    }
    
    public function getBrandLastUpdateDate(){
        // echo "im call";
        $latest = Brand::where('clientCode', $this->api->client->clientCode)->orderBy('added', 'desc')->first();
        if($latest){

            return strtotime($latest->added);
        }
        return 0;// strtotime($latest);
    }
    
    
    
    
}
