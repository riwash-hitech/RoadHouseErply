<?php
namespace App\Http\Controllers\Paei\Services;

use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\ProductGroup;

class GetProductGroupService{

    protected $group;
    protected $api;
    protected $letsLog;

    public function __construct(ProductGroup $pg, EAPIService $api, UserLogger $logger){
        $this->group = $pg;
        $this->api = $api;
        $this->letsLog = $logger;
    }

    public function saveUpdateOldAPI($products){

        foreach($products as $p){
            $this->groupSaveUpdateOldAPI($p);
            
            if(@$p["subGroups"]){
                if(count(@$p['subGroups']) > 0){
                
                    $this->subGroupSaveUpdateOldAPI(@$p['subGroups']);
                }
            }
        }

        return response()->json(['status'=>200, 'message'=>"Product Group fetched Successfully."]);
    }

    protected function groupSaveUpdateOldAPI($product){
        //for log
        // $old = $this->group->where('clientCode',  $this->api->client->clientCode)->where('productGroupID', $product['productGroupID'])->first();
        $change = $this->group->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "productGroupID"  =>  $product['productGroupID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "productGroupID" => $product['productGroupID'],
                    "name" => @$product['name'],
                    "showInWebshop" => @$product['showInWebshop'],
                    "nonDiscountable" => @$product['nonDiscountable'],
                    "positionNo"  => @$product['positionNo'],
                    "parentGroupID"  => @$product['parentGroupID'],
                    "images"  => '',//!empty($product['images']) ? json_encode($product['images'],1) : '',
                    "subGroups"  => !empty(@$product['subGroups']) ? json_encode(@$product['subGroups'],1) : '',
                    "attributes"  => '',//!empty($product['attributes']) ? json_encode($product['attributes'],1) : '',
                    "vatrates"  =>  '',//!empty($product['vatrates']) ? json_encode($product['vatrates'],1) : '',
                    "added"  =>  date('Y-m-d H:i:s', @$product['added']),
                    // "addedBy" => $product['addedby'],
                    "changed" => date('Y-m-d H:i:s', @$product['lastModified']),
                    // "changedBy" => $product['changedby'],

                ]
            );
            // $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($change, true), $old  ? "Product Group Updated" : "Product Group Created");        
    }
    
    protected function subGroupSaveUpdateOldAPI($products){
        //for log
        // $old = $this->group->where('clientCode',  $this->api->client->clientCode)->where('productGroupID', $product['productGroupID'])->first();
        foreach($products as $product){
        $change = $this->group->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "productGroupID"  =>  $product['productGroupID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "productGroupID" => $product['productGroupID'],
                    "name" => @$product['name'],
                    "showInWebshop" => @$product['showInWebshop'],
                    "nonDiscountable" => @$product['nonDiscountable'],
                    "positionNo"  => @$product['positionNo'],
                    "parentGroupID"  => @$product['parentGroupID'],
                    "images"  => '',//!empty($product['images']) ? json_encode($product['images'],1) : '',
                    "subGroups"  => !empty(@$product['subGroups']) ? json_encode(@$product['subGroups'],1) : '',
                    "attributes"  => '',//!empty($product['attributes']) ? json_encode($product['attributes'],1) : '',
                    "vatrates"  =>  '',//!empty($product['vatrates']) ? json_encode($product['vatrates'],1) : '',
                    "added"  =>  date('Y-m-d H:i:s', @$product['added']),
                    // "addedBy" => $product['addedby'],
                    "changed" => date('Y-m-d H:i:s', @$product['lastModified']),
                    // "changedBy" => $product['changedby'],

                ]
            );
        }
            // $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($change, true), $old  ? "Product Group Updated" : "Product Group Created");        
    }

    public function saveUpdate($products){
        
        foreach($products as $p){
            $this->groupSaveUpdate($p);
        }

        return response()->json(['status'=>200, 'message'=>"Product Group fetched Successfully."]);
    }

    protected function groupSaveUpdate($product){

        $this->group->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "productGroupID"  =>  $product['id']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "productGroupID" => $product['id'],
                    "name" => $product['name']['en'],
                    "showInWebshop" => $product['show_in_webshop'],
                    "nonDiscountable" => $product['non_discountable'],
                    "positionNo"  => @$product['positionNo'],
                    "parentGroupID"  => $product['parent_id'],
                    "images"  => '',//!empty($product['images']) ? json_encode($product['images'],1) : '',
                    "subGroups"  => '',//!empty($product['subGroups']) ? json_encode($product['subGroups'],1) : ''
                    "attributes"  => '',//!empty($product['attributes']) ? json_encode($product['attributes'],1) : '',
                    "vatrates"  =>  '',//!empty($product['vatrates']) ? json_encode($product['vatrates'],1) : '',
                    "added"  =>  date('Y-m-d H:i:s',$product['added']),
                    "addedBy" => $product['addedby'],
                    "changed" => date('Y-m-d H:i:s',$product['changed']),
                    "changedBy" => $product['changedby'],

                ]
            );
    }


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->group->where('clientCode',$this->api->client->clientCode )->orderBy('added', 'desc')->first();
        if($latest){
            return strtotime($latest->added);
        }
        return 0;// strtotime($latest);
    }
}
