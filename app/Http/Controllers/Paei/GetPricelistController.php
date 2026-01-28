<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;  
use App\Http\Controllers\Paei\Services\GetPricelistService;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\BrandDiscountFromPricelist;
use App\Models\PAEI\MatrixProduct;
use App\Models\PAEI\Pricelist;
use App\Models\PAEI\PricelistRule;
use App\Models\PAEI\UserOperationLog;
use App\Models\PAEI\VariationProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GetPricelistController extends Controller
{
    //
    protected $service;
    protected $api; 


    public function __construct(GetPricelistService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getPricelist(Request $req){
        if($req->type == 'checkPricelistActiveAndExpiry'){
            return $this->service->checkPricePolicyStartAndExpiry($req);
        }
        ini_set('memory_limit', -1);
        // if($req->bugFixing){
        //     return $this->getPricelistDeleteDeleteFixing($req);
        //     die;
        // }

        if ($req->type == 'getPricelistOperation'){
            return $this->getOperationLogPricelist('priceLists');
        }

        if ($req->type == 'getPricelistRulesOperation') {
            return $this->getOperationLogPricelist('pricelistProducts');
        }

        if ($req->type == 'getPricelistProductGroupOperation') {
            return $this->getOperationLogPricelist('pricelistDiscounts');
        }

        $param = array(
            "orderBy" => "changed",
            "orderByDir" => "asc",
            "recordsOnPage" => "100", 
            "getPricesWithVAT" => 1,
            // "pageNo" => $this->page,
            "changedSince" => $this->service->getLastUpdateDate(), 
        );

         $res = $this->api->sendRequest("getPriceLists", $param);
         if (request()->query('debug') == 1) dd($res);
        //  dd($res);
         if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            dump($res['records']);
            return $this->service->saveUpdate($res['records']);
         }
    }
    
    public function getPricelistDeleteDeleteFixing($req){
        
        $param = array(
            "orderBy" => "changed",
            "orderByDir" => "asc",
            "recordsOnPage" => "200", 
            "getHeadersOnly" => 1, 
            "getPricesWithVAT" => 1,
            // "pageNo" => $this->page,
            // "changedSince" => $this->service->getLastUpdateDate(), 
         );

         $res = $this->api->sendRequest("getPriceLists", $param);
        //  dd($res);
         if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            Pricelist::where("priceListID", '>', 0)->update(["isDeleted" => 1]);
            foreach($res["records"] as $data){
                $oldPricelist = Pricelist::where("clientCode", $this->api->client->clientCode)->where("pricelistID", $data['pricelistID'])->first();

                

                Pricelist::updateOrCreate(
                    [
                        "clientCode" => $this->api->client->clientCode,
                        "pricelistID"  =>  $data['pricelistID']
                    ],
                    [
                        "clientCode" => $this->api->client->clientCode,
                        'pricelistID' => @$data['pricelistID'], 
                        'isDeleted' => 0, 
                    ]
                );
                
                $isExpired = 1;
                
                //now very first checking is pricelist active
                if(@$data["startDate"] == "0000-00-00"){
                    //this means pricelist not expired
                    $isExpired = 0;
                }

                if(@$data["startDate"] != "0000-00-00"){
                    $currentDate = Carbon::now(); // Get the current date and time
                    $startDate = Carbon::parse($data["startDate"]); // Parse the start date
                    $endDate = Carbon::parse($data["endDate"]); // Parse the end date

                    if($currentDate->between($startDate, $endDate)) {
                        //if true means the current pricelist is active
                        $isExpired = 0;
                    }else{
                        $isExpired = 1;
                    } 
                }

                
                 

            }
         }

        dd("Pricelist Deleted Cron Checking...");
    }

    //getting deleted pricelists
    public function getOperationLogPricelist($table){
         
        $timestamp = $this->getLastUpdateDateDelete($table);
        $param = array(
            "orderBy" => "added",
            "orderByDir" => "asc",
            "recordsOnPage" => "100",
            "tableName" => $table, 
            "addedFrom" => strtotime($timestamp), 
            "pageNo" => $this->getPageNo($table, $timestamp)
        );
        // dd($this->api->client);
        dump($param);
        $res = $this->api->sendRequest("getUserOperationsLog", $param);
        if (\request()->query('debug') == 1) {
            dd($res);
        }
        if (empty($res['records'])) {
            info("All Product Operation Log Up-to-date");
            return response()->json(["status" => 200, "message" => "All Product Operation Log Up-to-date"]);
        }


        if ($res['status']['errorCode'] == 0) { 
            // $this->userOperationInterface->deleteRecords($res['records'], $this->api->client->clientCode);

            foreach ($res["records"] as $l) {
                if ($l['operation'] != 'delete') continue;

                $operationLog = UserOperationLog::where('clientCode', $this->api->client->clientCode)
                    ->where('logID', $l['logID'])
                    ->where('tableName', $l["tableName"])
                    ->first();

                if ($operationLog) continue;

                UserOperationLog::create(
                    [
                        "clientCode" => $this->api->client->clientCode,
                        "logID" => $l["logID"],
                        "userName" => $l["username"],
                        "tableName" => $l["tableName"],
                        "itemID" => $l['itemID'],
                        "operation" => $l['operation'],
                        "timestamp" => date('Y-m-d H:i:s', $l['timestamp']),
                    ]
                );

                // UserOperationLog::updateOrcreate(
                //     [
                //         "clientCode" => $this->api->client->clientCode,
                //         "logID" => $l["logID"],
                //         "tableName" => $l["tableName"]
                //     ],
                //     [
                //         "clientCode" => $this->api->client->clientCode,
                //         "logID" => $l["logID"],
                //         "userName" => $l["username"],
                //         "tableName" => $l["tableName"],
                //         "itemID" => $l['itemID'],
                //         "operation" => $l['operation'],
                //         "timestamp" => date('Y-m-d H:i:s', $l['timestamp']),
                //     ]
                // );

                // checking to table
                // if ($l['operation'] == 'delete') {

                //     $chk = Pricelist::where("pricelistID", $l["itemID"])->first();
                //     if($chk){ 
                //         if($chk->isDeleted == 0){ 
                //             //if deleted then re-sync pricelist
                //             Pricelist::where("pricelistID", $l["itemID"])->update(["isDeleted" => 1, "lastModified" => date('Y-m-d H:i:s')]);
                //             $specialPrice = PricelistRule::where("pricelistID", $l["itemID"])->pluck("productID")->toArray();
                //             $parentPro = VariationProduct::whereIn("productID", $specialPrice)->pluck("parentProductID")->toArray();
                //             MatrixProduct::whereIn("productID", $specialPrice)->update([ "pricePending" => 1, "roadhousePricePending" => 1]);
                //             MatrixProduct::whereIn("productID", $parentPro)->update([ "pricePending" => 1, "roadhousePricePending" => 1]);

                //             $brand = BrandDiscountFromPricelist::where("priceListID", $l["itemID"])->first();
                //             if($brand){
                //                 MatrixProduct::where("brandID", $brand->brandID)->update(["pricePending" => 1 , "roadhousePricePending" => 1]);
                //             }
                //         }
                //     }
                // }

                if ($table == 'priceLists') {
                    $chk = Pricelist::where("pricelistID", $l["itemID"])->first();
                    if ($chk) {
                        if ($chk->isDeleted == 0) {
                            //if deleted then re-sync pricelist
                            Pricelist::where("pricelistID", $l["itemID"])->update(["isDeleted" => 1, "lastModified" => date('Y-m-d H:i:s')]);
                            PricelistRule::where("pricelistID", $l["itemID"])->update(['isDeleted' => 1]);
                            $specialPrice = PricelistRule::where("pricelistID", $l["itemID"])->pluck("productID")->toArray();
                            $parentPro = VariationProduct::whereIn("productID", $specialPrice)->pluck("parentProductID")->toArray();
                            MatrixProduct::whereIn("productID", $specialPrice)->update(["pricePending" => 1, "roadhousePricePending" => 1]);
                            MatrixProduct::whereIn("productID", $parentPro)->update(["pricePending" => 1, "roadhousePricePending" => 1]);

                            $brand = BrandDiscountFromPricelist::where("priceListID", $l["itemID"])->first();
                            if ($brand) {
                                MatrixProduct::where("brandID", $brand->brandID)->update(["pricePending" => 1, "roadhousePricePending" => 1]);
                            }
                            // \DB::connection('mysql2')->table('source_variants')->whereIn('variantId', $specialPrice)->update(['compareAtPrice' => 0]);
                            // \DB::connection('mysql2')->table('source_products')->whereIn('stockId', $specialPrice)->update(['compareAtPrice' => 0]);
                        }
                    }
                }

                if ($table == 'pricelistProducts') {
                    $specialPrice = PricelistRule::where('type', 'PRODUCT')->where("ruleID", $l["itemID"])->first();

                    if ($specialPrice) {
                        if ($specialPrice->isDeleted == 0) {

                            //if deleted then re-sync pricelist
                            $specialPrice->isDeleted = 1;
                            $specialPrice->save();

                            // \DB::connection('mysql2')->table('source_variants')->where('variantId', $specialPrice->productID)->update(['compareAtPrice' => 0]);
                            // \DB::connection('mysql2')->table('source_products')->where('stockId', $specialPrice->productID)->update(['compareAtPrice' => 0]);

                            $parentPro = VariationProduct::where("productID", $specialPrice->productID)->pluck("parentProductID")->toArray();
                            MatrixProduct::whereIn("productID", $parentPro)->update(["pricePending" => 1, "roadhousePricePending" => 1]);
                            MatrixProduct::where("productID", $specialPrice->productID)->update(["pricePending" => 1, "roadhousePricePending" => 1]);
                        }
                    }
                }

                if ($table == "pricelistDiscounts") {
                    $specialPrice = PricelistRule::where('type', 'PRODGROUP')->where("ruleID", $l["itemID"])->first();

                    if ($specialPrice) {
                        if ($specialPrice->isDeleted == 0) {

                            //if deleted then re-sync pricelist
                            $specialPrice->isDeleted = 1;
                            $specialPrice->save();

                            $parentPro = VariationProduct::where("groupID", $specialPrice->productID)->pluck("parentProductID")->toArray();
                            MatrixProduct::whereIn("productID", $parentPro)->update(["pricePending" => 1, "roadhousePricePending" => 1]);
                        }
                    }
                }
            } 
        }
        info("Pricelist Operation Log Fetched Successfully.");
        return response()->json(["status" => 200, "message" => "Pricelist Operation Log Fetched Successfully."]);
    }

    public function getLastUpdateDateDelete($table)
    {
        // echo "im call";
        $latest = UserOperationLog::where('clientCode', $this->api->client->clientCode)
            ->where('tableName', $table)
            ->orderBy('timestamp', 'desc')
            ->first();


        if ($latest) {
            dump($latest->timestamp);
            return $latest->timestamp;
        }
        dump('2025-11-01 00:00:00');
        return '2025-11-01 00:00:00';
        // return 0; // strtotime($latest);
    }

    // $relatedProductIDs = PricelistRule::where("pricelistID", @$product['pricelistID'])->pluck("productID")->toArray();
    //         $parentIds = VariationProduct::whereIn("productID", $relatedProductIDs)->where("parentProductID", '>', 0)->pluck("parentProductID")->toArray();
    //         MatrixProduct::whereIn("productID", $parentIds)->update(["pricePending" => 1]);

    public function getPageNo ($table, $timestamp) 
    {
        $countTimestamp = UserOperationLog::where('clientCode', $this->api->client->clientCode)
            ->where('tableName', $table)
            ->where('timestamp', $timestamp)
            ->count();

        if ($countTimestamp >= 200) {
            $pageNo = 3;
        } else if ($countTimestamp >= 100) {
            $pageNo = 2;
        } else {
            $pageNo = 1;
        }
        return $pageNo;
    }
}
