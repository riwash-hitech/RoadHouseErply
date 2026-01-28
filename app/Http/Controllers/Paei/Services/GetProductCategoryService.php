<?php
namespace App\Http\Controllers\Paei\Services;

use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\ProductCategory;


class GetProductCategoryService{

    protected $category;
    protected $api;
    protected $letsLog;

    public function __construct(ProductCategory $pg, EAPIService $api,UserLogger $logger){
        $this->category = $pg;
        $this->api = $api;
        $this->letsLog = $logger;
    }

    public function saveUpdate($products){

        foreach($products as $p){
            $this->categorySaveUpdate($p);
        }

        return response()->json(['status'=>200, 'message'=>"Product Category fetched Successfully."]);
    }

    protected function categorySaveUpdate($product){

        $this->category->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "productCategoryID"  =>  $product['id']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "productCategoryID" => $product['id'],
                    "parentCategoryID" => $product['parent_id'],
                    "productCategoryName" => @$product['name']['en'],
                    "attributes" => '',//$product['non_discountable'],
                    "order_sw"  => @$product['order'],
                    "added"  =>  date('Y-m-d H:i:s',$product['added']),
                    "addedBy" => @$product['addedby'],
                    "changed" => date('Y-m-d H:i:s',$product['changed']),
                    "changedBy" => @$product['changedby'],

                ]
            );
    }

    public function saveUpdateOldAPI($products){
       
        // dd($products);
        foreach($products as $p){
            $this->categorySaveUpdateOldAPI($p);
            // if(!empty($p["subGroups"])){
            //     $this->subCategorySaveUpdateOldAPI($p["subGroups"]);
            // }
        }

        return response()->json(['status'=>200, 'message'=>"Product Category fetched Successfully."]);
    }

    protected function categorySaveUpdateOldAPI($product){

         //for log
        //  $old = $this->category->where('clientCode',  $this->api->client->clientCode)->where('productCategoryID', $product['productCategoryID'])->first();

        $change = $this->category->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "productCategoryID"  =>  $product['productCategoryID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "productCategoryID" => $product['productCategoryID'],
                    "parentCategoryID" => $product['parentCategoryID'],
                    "productCategoryName" => @$product['productCategoryName'],
                    "attributes" => !empty(@$product['attributes']) ? json_encode(@$product['attributes'],1) : '', //$product['non_discountable'],
                    // "order_sw"  => @$product['order'],
                    "added"  =>  date('Y-m-d H:i:s',$product['added']),
                    // "addedBy" => @$product['addedby'],
                    "changed" => date('Y-m-d H:i:s',$product['lastModified']),
                    // "changedBy" => @$product['changedby'],

                ]
            );
            // $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($change, true), $old  ? "Product Category Updated" : "Product Category Created");        
    }
    
   


    public function getLastUpdateDate(){
        // echo "im call";
        $latest = $this->category->where('clientCode', $this->api->client->clientCode)->orderBy('added', 'desc')->first();
        if($latest){

            return strtotime($latest->added);
        }
        return 0;// strtotime($latest);
    }
}
