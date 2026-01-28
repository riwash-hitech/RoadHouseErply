<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\ProductStockService;
use App\Http\Controllers\GetServices\GetProductService;
use App\Http\Controllers\Services\DimensionService;
use App\Http\Controllers\Services\EAPIService;
use App\Models\Client;
use App\Models\StockColorSize;
use App\Models\StockDetail;
use Illuminate\Http\Request;

class ProductStockController extends Controller
{
    //
    protected $service;
    protected $api;
    protected $dimservice;
    protected $stockdetails;
    protected $variation;
 

    public function __construct(ProductStockService $pss, DimensionService $ds, StockDetail $sd, StockColorSize $vp , EAPIService $api)//, GetProductService $api)
    {
        $this->service = $pss;
        $this->dimservice = $ds;
        $this->stockdetails = $sd;
        $this->variation = $vp;
        
        $this->api = $api;
    }

    public function toErply(Request $req){
       return $this->toErply($req);
        // $this->service->productCheck("10001_1110410_0");
    }

    public function fixMatrixVariationIssue(Request  $req){
        $limit = $req->limit == '' ? 3 : $req->limit;

        $stocks = $this->stockdetails
        ->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
        ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
        // ->join('newsystem_stock_colour_size', function($join){
        //     $join->on('newsystem_stock_colour_size.web_sku','=', 'newsystem_stockdetail.web_sku')
        //             ->where('newsystem_stock_colour_size.newSystemInternetActive', 1);
        // })
        ->select(['newsystem_stockdetail.*', 'newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyCatID','newsystem_internet_category.erplyGroupPending','newsystem_stock_internet_category.*'])
        // ->select(['newsystem_stockdetail.*'])
        // ->where('newsystem_stockdetail.matrixFlag', 1)
        ->where('newsystem_stockdetail.erplyPending', 0 )//correct it to 1
        // ->where('newsystem_stockdetail.variationPending', 1)
        ->where('newsystem_stockdetail.updateStatus', 1 )
        ->where('newsystem_internet_category.erplyGroupPending', '0')
        ->where('newsystem_internet_category.erplyCatPending', '0')
        ->where('newsystem_stockdetail.newSystemInternetActive', 1)
        ->limit($limit)
        ->get();
        
        



    }

    public function bulkProductPush(Request $req){
        //first get matrix product
        //get variation product
        //check all matrix sku exist
        //make confirm array matrix
        //make bulk request of matrix
        //make bulk request of variation including parent id
        //send request and update

        $limit = $req->limit == '' ? 3 : $req->limit;

        $stocks = $this->stockdetails
        ->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
        ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
        // ->join('newsystem_stock_colour_size', function($join){
        //     $join->on('newsystem_stock_colour_size.web_sku','=', 'newsystem_stockdetail.web_sku')
        //             ->where('newsystem_stock_colour_size.newSystemInternetActive', 1);
        // })
        ->select(['newsystem_stockdetail.*', 'newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyCatID','newsystem_internet_category.erplyGroupPending','newsystem_stock_internet_category.*'])
        // ->select(['newsystem_stockdetail.*'])
        // ->where('newsystem_stockdetail.matrixFlag', 1)
        ->where('newsystem_stockdetail.erplyPending', 0 )//correct it to 1
        // ->where('newsystem_stockdetail.variationPending', 1)
        ->where('newsystem_stockdetail.updateStatus', 1 )
        ->where('newsystem_internet_category.erplyGroupPending', '0')
        ->where('newsystem_internet_category.erplyCatPending', '0')
        ->where('newsystem_stockdetail.newSystemInternetActive', 1)
        ->limit($limit)
        ->get();
        // ->random($limit);
        //  dd($stocks);
        if(count($stocks) < 1){
            info('No Matrix product found');
            return response()->json(['status'=>401,"msg"=>"No Matrix product found"]);
        }

        $skuAarray = array();
        foreach($stocks as $s){
            array_push($skuAarray, $s->web_sku);
        }

        $variation = $this->variation->join('newsystem_stock_internet_category', 'newsystem_stock_colour_size.web_sku', 'newsystem_stock_internet_category.web_sku')
                        ->join('current_stock_colour', 'current_stock_colour.ciColourCode','newsystem_stock_colour_size.ciColorCode')
                        ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
                        ->select(['newsystem_stock_colour_size.*', 'newsystem_stock_internet_category.*', 'current_stock_colour.ciColourDescription','current_stock_colour.erplyDimID','newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyGroupPending','newsystem_internet_category.erplyCatPending','newsystem_internet_category.erplyCatID'])
                        ->whereIn('newsystem_stock_colour_size.web_sku', $skuAarray)
                        // ->where('newsystem_stock_colour_size.erplyPending', 1)
                        ->where('newsystem_internet_category.erplyGroupPending', 0)
                        ->where('newsystem_internet_category.erplyCatPending', 0)
                        ->where('newsystem_stock_colour_size.pos_sku', '<>', '')
                        ->where('newsystem_stock_colour_size.newSystemInternetActive', 1)
                        ->groupBy('newsystem_stock_colour_size.product_sku')
                        ->orderBy('newsystem_stock_colour_size.sortOrder','asc')
                        // ->limit(2)
                        ->get();
        
        if(count($variation) < 1){
            info('No Variation product found');
            return response()->json(['status'=>401,"msg"=>"No Variation product found"]);
        }

        $confirmMatrixSKU = array();
        foreach($skuAarray as $m){
            foreach($variation as $p){
                $sku = $p->web_sku;
                if("$m" == "$sku"){
                    if(in_array($m, $confirmMatrixSKU)){

                    }else{
                        array_push($confirmMatrixSKU, $m);
                    }
                }
            }
        }

        if(count($confirmMatrixSKU) > 100){
            info('Matrix Product Bulk Request Limit Crossed.');
            return response()->json(['status'=>401,"msg"=>"Matrix Product Bulk Request Limit Crossed."]);
        }

        info('Matrix Product Bulk Request Count '.count($confirmMatrixSKU));

        if(count($variation) > 100){
            info('Variation Bulk Request Limit Crossed.');
            return response()->json(['status'=>401,"msg"=>"Variation Product Bulk Request Limit Crossed."]);
        }

        info('Variation Product Bulk Request Count '.count($variation));

        $confirmMatrixProduct = array();
        foreach($confirmMatrixSKU as $s){
            foreach($stocks as $mp){
                if($s == $mp->web_sku){
                    array_push($confirmMatrixProduct, $mp);
                }
            }
        }


        // print_r($skuAarray);
        // print_r($confirmMatrixSKU);
        // print_r($confirmMatrixProduct);

        // $matrixBulkRes = $this->makeMatrixBundleAndSend($confirmMatrixProduct);
        // if($matrixBulkRes['status']['errorCode'] != 0){
        //     info("Error While saving Matrix Products");
        //     return response()->json(['error'=> $matrixBulkRes]);
        // }
        return $this->makeVariationBundleAndSend($variation, $confirmMatrixProduct, $confirmMatrixSKU);

    }

    protected function makeMatrixBundleAndSend($matrix){

        $bundleReq = array();
        foreach($matrix as $mp){
            $v = $this->variation->where('web_sku', $mp->web_sku)->where('newSystemInternetActive', 1)->first();
            $netp = 0;
            if($v){
                $netp = $v->retailPrice1;
            }
            $pid =  $this->service->productCheck($mp->web_sku);
            $param = array(
                "requestName" => "saveProduct",
                "sessionKey" => $this->api->client->sessionKey,//$this->api->verifySessionByKey($this->api->client->sessionKey),
                "clientCode" => $this->api->client->clientCode,
                "type" => "MATRIX",
                // "groupID" => $this->getGroupID($product->web_sku),
                "code" => $mp->web_sku,
                "code2" => $mp->web_sku,
                "displayedInWebshop" => $mp->newSystemInternetActive,
                "active" => $mp->newSystemInternetActive,
                "status" => $mp->newSystemInternetActive == 1 ? "ACTIVE" : 'ARCHIVED',
                "name" => $mp->newSystemStockDescription,
                "description" => $mp->newSystemShortDescription != '' ? $mp->newSystemShortDescription : $mp->newSystemStockDescription,
                "longdesc" => $mp->newSystemLongDescription,
                "groupID" => $mp->erplyGroupID,
                "categoryID" => $mp->erplyCatID,
                "netPrice" => $netp,
                // "length" => ,
                // "width" => ,
                // "height" => ,
                "netWeight" => $mp->weight,
                "sessionKey" => $this->api->client->sessionKey,
                "dimensionID1" => 1,
                "dimensionID2" => 8,

                // "netWeight"
            );

            if($pid != ''){
                //IF PRODUCT ID IS NOT EMPTY THAN ADD PRODUCT ID TO REQUEST PARAMETER AND IT WILL UPDATE PRODUCT
                $param['productID'] = $pid;
            }
            //adding attributes\
            $index = 1;
            foreach($mp->toArray() as $key => $val){
                if($key == 'displayPosition' || $key == "hideAddToCart" || $key == "gradwear" || $key == "isWholesale" || $key == "ciCustomerID" || $key == "productType" || $key == "productSubType"){
                    $param["attributeName".$index] = $key;
                    $param["attributeType".$index] =  $key == 'gradwear' || $key == "ciCustomerID" || $key == "productType" || $key == "productSubType" ? "varchar(300)" : ($key == 'displayPosition' ? 'float' : 'int') ;
                    $param["attributeValue".$index] = $val;
                    $index++;
                }
            }
            array_push($bundleReq, $param);
        }
        // info($bundleReq);
        $bundleReq = json_encode($bundleReq,true);
        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json",
            "sessionKey" => $this->api->client->sessionKey,
        );

        info("Matrix Bulk Save Request Calling...");
        $bulkRes = $this->api->sendRequest($bundleReq, $bulkparam,1,0,0);

        if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests'])){
            //first update to db
            foreach($matrix as $key => $mp){
                if($bulkRes['requests'][$key]['status']['errorCode'] == 0){
                    $mp->erplyPending = 0;
                    $mp->erplyProductID = $bulkRes['requests'][$key]['records'][0]['productID'];
                    $mp->erpPushDate = date('Y-m-d H:i:s');
                    
                    // $mp->matrixFlag = 0;
                    // $mp->erplyUpdate = 0;
                    $mp->save();
                    info("matrix product create or updated ".$bulkRes['requests'][$key]['records'][0]['productID']." sku ".$mp->web_sku);
                }else{
                    info("Error matrix product create or updated ".$bulkRes['requests'][$key]['status']['errorCode'].' SKU '. $mp->web_sku);
                }
            }
        }
        return $bulkRes;
    }

    protected function makeVariationBundleAndSend($variation, $matrix, $confirmMatrixSku){
        $variationColl = array();
        $variationBulkRequest = array();
        // foreach($matrix as $mkey => $mp){
        //     if($matrixRes['requests'][$mkey]['status']['errorCode'] == 0){
                // $variation = $this->variation->join('newsystem_stock_internet_category', 'newsystem_stock_colour_size.web_sku', 'newsystem_stock_internet_category.web_sku')
                //             ->join('current_stock_colour', 'current_stock_colour.ciColourCode','newsystem_stock_colour_size.ciColorCode')
                //             ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
                //             ->select(['newsystem_stock_colour_size.*', 'newsystem_stock_internet_category.*', 'current_stock_colour.ciColourDescription','current_stock_colour.erplyDimID','newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyGroupPending','newsystem_internet_category.erplyCatPending','newsystem_internet_category.erplyCatID'])
                //             ->where('newsystem_stock_colour_size.web_sku', $mp->web_sku)
                //             ->where('newsystem_internet_category.erplyGroupPending', 0)
                //             ->where('newsystem_internet_category.erplyCatPending', 0)
                //             ->where('newsystem_stock_colour_size.newSystemInternetActive', 1)
                //             ->groupBy('newsystem_stock_colour_size.product_sku')
                //             ->get();
                // array_push($variationColl, $variation);
                foreach($variation as $vp){
                    $mp = $this->stockdetails->where('web_sku', $vp->web_sku)->first();
                    $pid =  $this->service->productCheck($vp->product_sku); 
                    // info("Product sku ".$vp->product_sku);
                    $param = array(
                        "requestName" => "saveProduct",
                        "sessionKey" => $this->api->client->sessionKey,//$this->api->verifySessionByKey($this->api->client->sessionKey),
                        "clientCode" => $this->api->client->clientCode,
                        "type" => 'PRODUCT',
                        "parentProductID" => $mp->erplyProductID,
                        "code" => $vp->product_sku,
                        "code2" => $vp->barcode == '' ?  $vp->product_sku : $vp->barcode,
                        "code3" => '',
                        "displayedInWebshop" => $vp->newSystemInternetActive,
                        "active" => 0,//$vp->newSystemInternetActive,
                        "status" => $vp->newSystemInternetActive == 1 ? "ACTIVE" : 'ARCHIVED',
                        "name" => $mp->newSystemStockDescription.' '.$vp->ciColourDescription.' '.$vp->ciSizeCode,
                        "description" => $mp->newSystemShortDescription != '' ? $mp->newSystemShortDescription : $mp->newSystemStockDescription,
                        "longdesc" => $mp->newSystemLongDescription,
                        "netPrice" => $vp->retailPrice1,
                        "groupID" => $vp->erplyGroupID,
                        "categoryID" => $vp->erplyCatID,
                        "dimValueID1" => $vp->erplyDimID,
                        "dimValueID2" => $vp->sizeDIMID,
                        // "description" => ,
                        // "longdesc" => ,
                        // "length" => ,
                        // "width" => ,
                        // "height" => ,
                        "netWeight" => $vp->weight,
                        // "netWeight"
                    );
                    if($pid != ''){
                        $param['productID'] = $pid;
                    }
                    //ADDING EXTRA ATTRIBUTE NAME TYPE VALUE TO REQUEST PARAMETER
                    $index = 1;
                    foreach($vp->toArray() as $key => $val){
                        if($key == "softStatus" || $key == "sortOrder" || $key == "wholesalePrice1" || $key == "configName" || $key == "hide_addtocart" || $key == "gradwear" || $key == "isWholesale" || $key == "MOQ" || $key == "SLA"){
                            $param["attributeName".$index] = $key;
                            $param["attributeType".$index] =  $key == 'softStatus' || $key == 'configName' || $key == 'gradwear' ? "varchar(200)" : ($key == 'currentSOH' ||  $key == 'wholesalePrice1' ? 'float' : 'int') ;
                            $param["attributeValue".$index] = $val;
                            $index++;
                        }
                    }
                    if($pid != ''){
                        array_push($variationBulkRequest, $param);
                    }
                }
        //     }
        // }

                
        // info($variationBulkRequest);
        if(count($variationBulkRequest) == 0){
            foreach($confirmMatrixSku as $sku){
                $this->stockdetails->where('web_sku', $sku)->update(['updateStatus'=> 0]);
            }
            info('No Variation Bulk Request Found.');
            return response()->json(['status'=>401,"msg"=>"No Variation Product Bulk Request Found."]);
        }
        $variationBulkRequest = json_encode($variationBulkRequest, true);
        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json",
            "sessionKey" => $this->api->client->sessionKey,
        );

        info("Variation Bulk Save Request Calling...");

        $variationRes = $this->api->sendRequest($variationBulkRequest,$bulkparam, 1, 0, 0);

        if($variationRes['status']['errorCode'] != 0){
            info("Error Variation Bulk Save Update Success.");
            return response()->json(['status' => 200, 'response' => $variationRes]);
        }


        // foreach($variation as $key => $vp){
        //     if($variationRes['requests'][$key]['status']['errorCode'] == 0){
        //         $id = $variationRes['requests'][$key]['records'][0]['productID'];
        //         info("Variation product created or updated ID : ".$id.'  product sku '.$vp->product_sku);
        //         // $this->variation->where('newSystemColourSizeID', $vp[$key]['newSystemColourSizeID'])->update(['erplyProductID'=>$id,'erplyPending'=>0, 'matrixAttributeFlag'=>0,'error'=>'']);
        //         $vp->erplyProductID = $id;
        //         $vp->erplyPending = 0;
        //         // $vp->matrixAttributeFlag = 0;
        //         $vp->error = '';
        //         $vp->save();
        //     }else{
        //         // $this->variation->where('newSystemColourSizeID', $vp[$key]['newSystemColourSizeID'])->update(['error'=>'']);
        //         $vp->error = $variationRes['requests'][$key]['status']['errorCode'];
        //         $vp->save();
        //         info("Error Code".$variationRes['requests'][$key]['status']['errorCode'].' '.$variationRes['requests'][$key]['status']['errorField']);
        //         info("Barcode and SKU". $vp->barcode.' '.$vp->product_sku);
        //     }
        // }

        // foreach($confirmMatrixSku as $sku){
        //     $this->stockdetails->where('web_sku', $sku)->update(['updateStatus'=> 0]);
        // }

        info("Matrix and Variation Bulk Save Update Success.");
        return response()->json(['status' => 200, 'response' => $variationRes]);



    }

    public function getProduct(Request $req){
         
        // return $this->api->getProducts($req);
        // $this->service->productCheck("10001_1110410_0");
    }

  


    public function inventoryReg(Request $req){
         
        $this->service->inventoryReg($req);
    }

    // public function addAssortment(Request $req){
    //     $this->service->productAssortment($req);
    // }

    public function colorDimension(Request $req){
        $this->dimservice->saveColorDimension($req);
    }

    public function sizeDimension(Request $req){
        $this->dimservice->saveSizeDimension($req);
    }

    public function createDimension(Request $req){
        return $this->dimservice->saveDimension($req);
    }

    public function getProductSwaggerPost(){
        
        $url = 'https://api-pim-au.erply.com/v1/product/bulk/get?field=id';

        $clientCode = '603303';

        $sessionKey = '7af2d849cb13e98c72dfeacd6c9e0be4212a8e661bac';

        $productCodes = ["7197", "6582"];

 
        $farray = array();
        foreach($productCodes as $key => $pc){ 
            $farray[] = $key < 1 ? ["fieldName" => "id", "operator"=>"=", "value" => $pc] : ["fieldName" => "id", "operandBefore"=>"or", "operator"=>"=", "value" => $pc];
        }

        $data = array("requests" => [array('filters'=>$farray)]);//array(array("filters" => $filter)));

        // print_r(json_encode($data));

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

            'accept: application/json',

            'clientCode: '.$clientCode,

            'sessionKey: '.$sessionKey,

            'Content-Type: application/json'

        ));

        

        $response = curl_exec($ch);

        curl_close($ch);

        $resutls = json_decode($response,true);
        // echo "<pre>";
        // print_r($resutls);
        $fr = array();
        foreach($resutls['results'][0]['products'] as $key => $pro){
            
            $fr[$pro['code']] = $pro['id'];
        }

        print_r($fr);
    }


}
 