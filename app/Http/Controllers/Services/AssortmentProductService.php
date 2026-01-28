<?php
namespace App\Http\Controllers\Services;

use App\Models\Client;
use App\Models\ProductVariant;
use App\Models\StockColorSize;
use App\Models\StockDetail;

class AssortmentProductService{
    protected $api;
    protected $variation;
 
    protected $matrix;
    public function __construct(EAPIService $api, StockColorSize $pv,  StockDetail $sd)
    {
        $this->api = $api;
        $this->variation = $pv;
 
        $this->matrix = $sd;
        // $this->api->client->sessionKey = $this->api->verifySessionByKey($client->sessionKey);
    }

    public function addExtraBulkAssortmentProducts($warehouses, $status, $skuArray){ 
        // dd($warehouses);
        
        info("product assortment called");
        // $product = $this->variation->where('product_sku', $sku)->first();

        $bulkParam = array();
        $bulkP = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $this->api->client->sessionKey
        );
        // print_r($warehouses);
        // die;
        foreach($warehouses as $w){
            // info(' LID '.$w->locationid.' PID '.$w->erplyProductID.' assortment ID '.$w->erplyAssortmentID);
            $param = array(
                "sessionKey" => $this->api->client->sessionKey,
                "clientCode" => $this->api->client->clientCode,
                "requestName" => "addAssortmentProducts",
                "productIDs" => $w->erplyProductID,
                "assortmentID" => $w->erplyAssortmentID,
                "status" => $status
            );
            array_push($bulkParam, $param);
        }
        // print_r($bulkParam);
        // die;
         
        $bulkParam = json_encode($bulkParam, true);
        

        $res = $this->api->sendRequest($bulkParam, $bulkP,1,1,0);
        if($res['status']['errorCode'] == 1006){
            info("Assortments module has not been enabled on your account.");   
        }
        if($res['status']['errorCode'] == 0 && !empty($res['requests'])){
            
            info("Product Assortment Added Successfully.");
            // echo "<pre>";
            // info($res);
            foreach($warehouses as $key => $www){
                if($res['requests'][$key]['status']['errorCode'] == 0){
                    info("Already Assortment : ".$res['requests'][$key]['records'][0]['productsAlreadyInAssortment']);
                }
            }
            foreach($skuArray as $w){
                $this->matrix->where('web_sku', $w)->update(['productAssortmentFlag' => 0]);
                info("Add Product Assortment Success SKU ".$w);
            }

        }else{
            info($res);
        }
        // print_r($res);
        return response()->json(['status' => 200, 'response'=>$res]);
        // info("Assortment Product Added Successfully. productsAlreadyInAssortment ". $res['records'][0]['productsAlreadyInAssortment'] . " nonExistingIDs ". $res['records'][0]['nonExistingIDs']);

    }
    
    public function addBulkAssortmentProducts($sku, $warehouses, $status){ 
        info("product assortment called");
        $product = $this->variation->where('product_sku', $sku)->first();

        $bulkParam = array();
        $bulkP = array(
            "lang" => 'eng',
            "responseType" => "json", 
        );
        foreach($warehouses as $w){
            $param = array(
                "sessionKey" => $this->api->client->sessionKey,
                "clientCode" => $this->api->client->clientCode,
                "requestName" => "addAssortmentProducts",
                "productIDs" => $product->erplyProductID,
                "assortmentID" => $w->erplyAssortmentID,
                "status" => $status
            );
            array_push($bulkParam, $param);
        }
        $bulkParam = json_encode($bulkParam, true);
        

        $res = $this->api->sendRequest($bulkParam, $bulkP,1,1);
        if($res['status']['errorCode'] == 1006){
            info("Assortments module has not been enabled on your account.");   
        }
        if($res['status']['errorCode'] == 0 && !empty($res['requests'])){
            info("Product Assortment Added Successfully.");

        }else{
            info($res);
        }
        

        // info("Assortment Product Added Successfully. productsAlreadyInAssortment ". $res['records'][0]['productsAlreadyInAssortment'] . " nonExistingIDs ". $res['records'][0]['nonExistingIDs']);

    }

    

    public function addAssortmentProducts($productIDS, $assortmentID, $status){ 
        $param = array(
            "productIDs" => $productIDS,
            "assortmentID" => $assortmentID,
            "status" => $status
        );

        $res = $this->api->sendRequest("addAssortmentProducts", $param,0,1);
        if($res['status']['errorCode'] == 1006){
            info("Assortments module has not been enabled on your account.");   
        }


        info("Assortment Product Added Successfully. productsAlreadyInAssortment ". $res['records'][0]['productsAlreadyInAssortment'] . " nonExistingIDs ". $res['records'][0]['nonExistingIDs']);

    }

    public function getAssortmentProducts(){

    }

    public function editAssortmentProducts($productIDS, $assortmentID, $status){
        $param = array(
            "productIDs" => $productIDS,
            "assortmentID" => $assortmentID,
            "status" => $status
        );
        $res = $this->api->sendRequest("editAssortmentProducts", $param,0,1);
        if($res['status']['errorCode'] == 1006){
            info("Assortments module has not been enabled on your account.");   
        } 
        info("Assortment Product Edited, productsNotInAssortment ". $res['records'][0]['productsNotInAssortment']);
    }

    public function removeAssortmentProducts($pids, $assortmentID){
        $param = array(
            "productIDs" => $pids,
            "assortmentID" => $assortmentID
        );
        $res = $this->api->sendRequest("removeAssortmentProducts", $param);
        
        info("Deleted Product Assortment IDs ".$res['records'][0]['deletedIDs']." productsNotInAssortment ". $res['records'][0]['productsNotInAssortment']);

    }
}