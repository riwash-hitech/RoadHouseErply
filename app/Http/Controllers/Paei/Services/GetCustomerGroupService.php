<?php
namespace App\Http\Controllers\Paei\Services;

use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Customer;
use App\Models\PAEI\CustomerGroup;

class GetCustomerGroupService{

    protected $group;
    protected $letsLog;
    protected $api;

    public function __construct(CustomerGroup $c,UserLogger $logger, EAPIService $api){
        $this->group = $c;
        $this->letsLog = $logger;
        $this->api = $api;
    }

    public function saveUpdate($groups){

        foreach($groups as $c){
            $this->saveUpdateGroup($c);
        }

        return response()->json(['status'=>200, 'message'=>"Customer Group fetched Successfully."]);
    }

    protected function saveUpdateGroup($product){
        // $old = $this->group->where('clientCode', $this->api->client->clientCode)->where('customerGroupID', $product['customerGroupID'])->first();
        $change = $this->group->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "customerGroupID"  =>  $product['customerGroupID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "customerGroupID" => $product["customerGroupID"],
                    "parentID" => @$product["parentID"],
                    "name" => $product["name"],
                    "pricelistID" => @$product["pricelistID"],
                    "pricelistID2" => @$product["pricelistID2"],
                    "pricelistID3" => @$product["pricelistID3"],
                    "pricelistID4" => @$product["pricelistID4"],
                    "pricelistID5" => @$product["pricelistID5"],
                    "added" =>  date('Y-m-d H:i:s',$product['added']),
                    "lastModified" => isset($product['lastModified']) == 1 ? date('Y-m-d H:i:s',$product['lastModified']) : '0000-00-00 00:00',
                    "attributes" => !empty($product['attributes']) ? json_encode($product['attributes'],1) : ''
                ]
            );
        // $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($change, true), $old  ? "Customer Group Updated" : "Customer Group Created");
    }


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->group->orderBy('lastModified', 'desc')->first();
        if($latest){
            return strtotime($latest->lastModified);
        }
        return 0;// strtotime($latest);
    }
}
