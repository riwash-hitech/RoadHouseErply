<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetProductService;
use App\Http\Controllers\Services\EAPIService;
use App\Models\Client;
use App\Models\StockColorSize;
use App\Models\PAEI\UserOperationLog;
use App\Models\StockDetail;
use App\Models\PAEI\MatrixProduct;
use App\Models\PAEI\ProductPictureCDN;
use App\Models\PAEI\VariationProduct;
use Exception;
use Illuminate\Http\Request;
// use App\Models\StockDetail;

class GetMatrixProductController extends Controller
{
    //
    protected $api;
    protected $service;
    protected $client;

    //for updating psw existing products
    protected $matrix;
    protected $variation;

    public function __construct(EAPIService $api, GetProductService $service, StockDetail $sd, StockColorSize $vp)
    {
        // info("const from get matrix");
        $this->api = $api;

        $this->service = $service;
        // $this->client =$client;  
        // $this->client->sessionKey = $this->api->verifySessionByKey($client->sessionKey);
        $this->matrix = $sd;
        $this->variation = $vp;
    }



    public function getProduct(Request $req)
    {
        if($req->type == "log"){
            return $this->getOperationLogProduct($req);
        }
        $debug = $req->debug ?? 0;
        $page = $req->page ?? 0;
        info("update product by last modified cron calling...");

        $param = array(
            "orderBy" => "changed",
            "orderByDir" => "asc",
            "recordsOnPage" => "1000",
            "getPriceListPrices" => 1,
            // "warehouseID" => 1,
            // "pageNo" => 2,
            // "active" => 1,
            // "pageNo" => $this->page,
            "changedSince" => $this->service->getLastUpdateDate(),
        );
        if($page > 0){
            $param["pageNo"] = $page;
        }
 
        $res = $this->api->sendRequest("getProducts", $param);
        if($debug == 1){
            dd($res);
        }
        if ($res['status']['errorCode'] == 0 && !empty($res['records'])) {
            return $this->service->saveUpdate($res['records']);
        }
    }

    public function getUpdateAllProduct(Request $req)
    {
        // die;
        // echo "hello im ok";
        // die;


        info("Re-sync product by added...");

        $param = array(
            "orderBy" => "added",
            "orderByDir" => "asc",
            "recordsOnPage" => "500",
            "getPriceListPrices" => 1,
            "warehouseID" => 1,
            "addedSince" => $this->service->getProductUpdateDate(),
        );

        if ($req->pid) {
            $param["productIDs"] = $req->pid;
        }

        // dd($param);

        $res = $this->api->sendRequest("getProducts", $param);
        // dd($res);
        if ($res['status']['errorCode'] == 0 && !empty($res['records'])) {

            //  $pp = collect($res['records']);
            // $forUpdate = $pp->last();
            // echo $forUpdate["added"];
            // die;
            return $this->service->saveUpdateV2($res['records']);
        }
    }




    public function getProductPIM()
    {
        $param = array(
            "take" => "200",
            "sort" => json_encode([
                "selector" => "changed",
                "desc" => false
            ]),
            "match" => ">=",
            "changed" => $this->service->getLastUpdateDate(),
            "orderBy" => 'changed',
            "active" => 1,
            "orderByDirection" => 'ASC'
        );

        $res = $this->api->sendRequestBySwagger("https://api-pim-au.erply.com/v1/product", $param);
        if (count($res) > 0) {
            // dd($res[0]);
            return $this->service->saveUpdatePIM($res);
        }
    }

    public function letsUpdateMatrix()
    {
        $param = array(
            // "orderBy" => "added",
            "orderByDir" => "asc",
            "recordsOnPage" => "2000",
            "includeMatrixVariations" => 0,
            "active" => 0,
            // "pageNo" => $this->page,
            // "addedSince" => $this->getLastUpdatedDate(), 
        );

        //  print_r($param);
        //  die;
        $res = $this->api->sendRequest("getProducts", $param);
        // dd($res);
        if ($res['status']['errorCode'] == 0 && !empty($res['records'])) {
            foreach ($res['records'] as $p) {
                $this->matrix->where('web_sku', $p['code'])->update(['erplyPending' => 0, 'erplyProductID' => $p['productID'], 'erplyAdded' => date('Y-m-d H:i:s', $p['added'])]);
            }
            return response()->json("Matrix Product Updated");
        }
    }

    protected function getLastUpdatedDate()
    {
        $latest = $this->matrix->where('erplyPending', 0)->orderBy('erplyAdded', 'desc')->first();
        if ($latest) {
            return strtotime($latest->erplyAdded);
        }
        return 0;
    }

    public function getOperationLogProduct($req)
    {
        $debug = $req->debug ?? 0;
        $param = array(
            "orderBy" => "added",
            "orderByDir" => "asc",
            "recordsOnPage" => "200",
            "tableName" => "products",
            "addedFrom" => $this->getLastUpdateDateDelete("products"),
        );
        if($debug == 1){
            dd($param);
        }
        $res = $this->api->sendRequest("getUserOperationsLog", $param);
        if($debug == 2){
            dd($res);
        }
        if ($res['status']['errorCode'] == 0) {

            if (empty($res['records'])) {
                info("All Product Operation Log Up-to-date");
                return response()->json(["status" => 200, "message" => "All Product Operation Log Up-to-date"]);
            }

            // $this->userOperationInterface->deleteRecords($res['records'], $this->api->client->clientCode);

            foreach ($res["records"] as $l) {
                UserOperationLog::updateOrcreate(
                    [
                        "clientCode" => $this->api->client->clientCode,
                        "logID" => $l["logID"],
                        "tableName" => $l["tableName"]
                    ],
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

                // checking to table
                if ($l['operation'] == 'delete') {
                    $variation = VariationProduct::where("productID", $l["itemID"])->first();
                    if ($variation) {
                        VariationProduct::where("productID", $l["itemID"])->update(["displayedInWebshop" => 0, "erplyDeleted" => 1]);
                        MatrixProduct::where("productID", $variation->parentProductID)->update(["shopifyPendingProcess" => 1]);
                    }

                    $mm = MatrixProduct::where("productID", $l["itemID"])->first();
                    if ($mm) {
                        MatrixProduct::where("productID", $l["itemID"])->update(["displayedInWebshop" => 0, "shopifyPendingProcess" => 1, "erplyDeleted" => 1]);
                    }
                }
            }
            info("Product Operation Log Fetched Successfully.");
        }

        return response()->json(["status" => 200, "message" => "Product Operation Log Fetched Successfully."]);
    }

    public function getLastUpdateDateDelete($table)
    {
        // echo "im call";
        $latest = UserOperationLog::where('clientCode', $this->api->client->clientCode)->where('tableName', $table)->orderBy('timestamp', 'desc')->first();
        if ($latest) {
            return strtotime($latest->timestamp);
        }
        return 0; // strtotime($latest);
    }



    //check product exist is erply

    public function checkProductExistInErply(Request $req){
        
        $datas = MatrixProduct::where('erplyDeleted', 0)->where("checkErply", 1)->limit(100)->get();
        
        $flag = true;
        if($datas->isEmpty()){
            $flag = false;
            $datas = VariationProduct::where('erplyDeleted', 0)->where("checkErply", 1)->limit(100)->get();    
        }

        if($datas->isEmpty()){
            info("All Deleted Product Checked.");
            return response("All Deleted Product Checked");
        }
        // dd($datas);

        $getBulkReq = array();
        foreach($datas as $data){
            $checkParam = array(
                "requestName" => "getProducts",
                "sessionKey" => $this->api->client->sessionKey,//$this->api->verifySessionByKey($this->api->client->sessionKey),
                "clientCode" => $this->api->client->clientCode,
                "productID" =>  $data->productID,
                "getFields" => "productID,type"
            );
            $getBulkReq[] = $checkParam;
        }
        if(count($getBulkReq) < 1){
            info("All Deleted Product Checked.");
            return response("All Deleted Product Checked");
        }
        $getBulkReq = json_encode($getBulkReq, true);
        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json",
            "sessionKey" => $this->api->client->sessionKey,
        );

        $getBulkRes = $this->api->sendRequest($getBulkReq, $bulkparam, 1);
        // dd($getBulkRes);
        if($getBulkRes['status']['errorCode'] == 0){
            foreach($datas as $key => $data){
                if($getBulkRes["requests"][$key]['status']['errorCode'] == 0){
                    if(empty($getBulkRes['requests'][$key]['records'])){
                        //product deleted in erply
                        //just update erply pending = 1 and erplyID null 
                        // info("Empty Get Records...");
                        // $data->erplyPending = 1;
                        // $data->erplyID = null;
                        $data->displayedInWebshop = 0;
                        $data->checkErply = 0;
                        if($flag == true){
                            $data->shopifyPendingProcess = 1;
                        }
                        $data->erplyDeleted = 1;
                        $data->save();

                        if($flag == false){
                            MatrixProduct::where("productID", $data->parentProductID)->update(["shopifyPendingProcess" => 1]);
                        }
 
                    }
                    else{
                        
                        $data->checkErply = 0; 
                        $data->save();
                         
                    }
                }
            }
        }

        info("Checking product exists in erply...");

        return response()->json($getBulkRes);
    }

    public function fixVariationDimensionID(Request $req)
    {

        //first getting pending variation product
        $datas = VariationProduct::where("fixVariationDimID", 1)->where("variationDescription", '<>', '')->limit(200)->get();
        if($datas->isEmpty()){
            $datas = VariationProduct::where("fixVariationDimID", 2)->where("variationDescription", '<>', '')->limit(200)->get();
        }
        // dd($datas);
        foreach($datas as $data){
            try{
                if($data->variationDescription != ''){
                    $des = json_decode($data->variationDescription, true);

                    $colorOrder = 0;
                    $sizeOrder = 0;
                    $dimID1 = 0;
                    $dimID2 = 0;
                    $vDimID1 = 0;
                    $vDimID2 = 0;

                    if(count($des) > 0){
                        // $sizeOrder = $product["variationDescription"][1]["order"];
                        // $dimID2 = $product["variationDescription"][1]["dimensionID"];
                        // $vDimID2 = $product["variationDescription"][1]["variationID"];
                        if(@$des[0]["name"] == "Color"){
                            $colorOrder = $des[0]["order"];
                            $dimID1 = $des[0]["dimensionID"];
                            $vDimID1 = $des[0]["variationID"];
                        }
        
                        if(@$des[0]["name"] == "Size"){
                            $sizeOrder = $des[0]["order"];
                            $dimID2 = $des[0]["dimensionID"];
                            $vDimID2 = $des[0]["variationID"];
                        }
                    }

                    if(count($des) > 1){
                        // $sizeOrder = $product["variationDescription"][1]["order"];
                        // $dimID2 = $product["variationDescription"][1]["dimensionID"];
                        // $vDimID2 = $product["variationDescription"][1]["variationID"];
                        if(@$des[1]["name"] == "Color"){
                            $colorOrder = $des[1]["order"];
                            $dimID1 = $des[1]["dimensionID"];
                            $vDimID1 = $des[1]["variationID"];
                        }
        
                        if(@$des[1]["name"] == "Size"){
                            $sizeOrder = $des[1]["order"];
                            $dimID2 = $des[1]["dimensionID"];
                            $vDimID2 = $des[1]["variationID"];
                        }
                    }

                    $data->colorOrder = $colorOrder;
                    $data->sizeOrder = $sizeOrder;
                    $data->dimID1 = $dimID1;
                    $data->dimID2 = $dimID2;
                    $data->variationDimID1 = $vDimID1;
                    $data->variationDimID2 = $vDimID2;
                    $data->fixVariationDimID = 0;
                    $data->save();

                    //now updating cdn table data 
                    ProductPictureCDN::where("productID", $data->productID)->update(["colourID" => $vDimID1]);
                }
            }catch(Exception $e){
                $data->fixVariationDimID = 2;
                $data->save();
            }

        }

        info("Fixing Variation Dimension ID");
        return response("Fixing Variation Dimension ID");
    }

}
