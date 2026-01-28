<?php
namespace App\Http\Controllers\Services;

use App\Models\Category;
use App\Models\Client;
use App\Models\StockCategory;
use App\Models\StockColor;
use App\Models\StockColorSize;
use App\Models\StockDetail;
use Illuminate\Support\Facades\Log;

class ProductStockService
{
    protected $stockdetails;
    protected $stockcategory;
    protected $stockvariation;
    protected $api;
    protected $stockcolor;
    protected $category; 
    protected $group;
    protected $catservice;
    protected $picture; 
    protected $inventoryReg;
    protected $assortmentProduct;

    public function __construct(StockDetail $sd, StockCategory $sc, StockColorSize $scs, EAPIService $api, StockColor $stockcolor, Category $cat , GroupService $group, CategoryService $catservice, ProductPictureService $picture, InventoryRegistrationService $irs, AssortmentProductService $assortProduct)
    {
        $this->stockdetails = $sd;
        $this->stockcategory = $sc;
        $this->stockvariation = $scs;
        $this->api = $api;
        $this->stockcolor = $stockcolor;
        $this->category = $cat;
 
        // $this->api->client->sessionKey = $api->verifySessionByKey($client->sessionkey);
        $this->group = $group;
        $this->catservice = $catservice;
        $this->picture = $picture;
        // $this->session = $session;
        $this->inventoryReg = $irs;
        $this->assortmentProduct = $assortProduct;

    }


    public function toErply($req){
        
        $mlimit = $req->limit == '' ? 1 : $req->limit;
        $websku = $req->sku;
        Log::info("toErply Function called"); 
        // die;
        if($websku != ''){
            $stocks = $this->stockdetails->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
                ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID') 
                ->select(['newsystem_stockdetail.*', 'newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyCatID','newsystem_internet_category.erplyGroupPending','newsystem_stock_internet_category.*'])
                // ->where('newsystem_stockdetail.matrixFlag', 1)
                // ->where('newsystem_stockdetail.erplyPending', 1)  //correct it to 1
                ->where('newsystem_internet_category.erplyGroupPending', '0')
                ->where('newsystem_internet_category.erplyCatPending', '0') 
                ->where('newsystem_stockdetail.newSystemInternetActive', 1)
                ->where('newsystem_stockdetail.web_sku', $websku)
                ->limit($mlimit)
                ->get(); 
        }else{
            $stocks = $this->stockdetails->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
                ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID') 
                ->select(['newsystem_stockdetail.*', 'newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyCatID','newsystem_internet_category.erplyGroupPending','newsystem_stock_internet_category.*'])
                // ->where('newsystem_stockdetail.matrixFlag', 1)
                ->where('newsystem_stockdetail.erplyPending', 0 )//correct it to 1
                ->where('newsystem_stockdetail.erplyUpdate', 1 )
                ->where('newsystem_internet_category.erplyGroupPending', '0')
                ->where('newsystem_internet_category.erplyCatPending', '0')  
                ->where('newsystem_stockdetail.newSystemInternetActive', 1)
                ->limit($mlimit)
                ->get(); 
        }
        
        // dd($stocks);        
        // $stocks = $this->stockdetails->where('web_sku', '10001_1103001_0')->get();
        foreach($stocks as $product){
            //THIS FUNCTION IS RESPONSIBLE FOR CHECKING MATRIX PRODUCT AND IF NOT THAN CREATE MATRIX PRODUCT AND RETURN PRODUCT ID
            $matrixProductID = $this->handleMatrixProduct($product); 
            // echo $matrixProductID;
            // die;

            // if($product->mainImageName)$this->picture->SaveProductPictureByImageName($product->mainImageName, $matrixProductID); //FOR MATRIX PRODUCT PICTURE

            $variation = $this->stockvariation->join('newsystem_stock_internet_category', 'newsystem_stock_colour_size.web_sku', 'newsystem_stock_internet_category.web_sku')
                        ->join('current_stock_colour', 'current_stock_colour.ciColourCode','newsystem_stock_colour_size.ciColorCode')
                        ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
                        ->select(['newsystem_stock_colour_size.*', 'newsystem_stock_internet_category.*', 'current_stock_colour.ciColourDescription','current_stock_colour.erplyDimID','newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyGroupPending','newsystem_internet_category.erplyCatPending','newsystem_internet_category.erplyCatID'])
                        ->where('newsystem_stock_colour_size.web_sku', $product->web_sku) 
                        // ->where('newsystem_stock_colour_size.erplyPending', 1)
                        ->where('newsystem_internet_category.erplyGroupPending', 0)
                        ->where('newsystem_internet_category.erplyCatPending', 0)
                        ->where('newsystem_stock_colour_size.newSystemInternetActive', 1)
                        ->groupBy('newsystem_stock_colour_size.product_sku')
                        // ->limit(2)
                        ->get();
            // info($variation);
            // dd($variation);
            $variationBulk = array();
            // $newvariationBulk = $this->newHandleVariation($variation,$matrixProductID);
            if($variation){
                foreach($variation as $vp){
                    //THIS FUNCTION IS RESPONSIBLE FOR CHECKING VARIANT PRODUCT IN ERPLY DB IF EXIST THAN RETURN UPDATE PARAMETER WITH PRODUCT ID ELSE RETURN PARAM WITHOUT PRODUCT ID 
                    $vbparam = $this->handleVariationProduct($vp, $matrixProductID, $product); 
                    //ADDING VARIANTS PRODUCTS TO ARRAY FOR BULK ENTRY
                    array_push($variationBulk, $vbparam); 
                } 
                // info($variationBulk);
                //ENCODING ARRAY TO JSON 
                $variationBulk = json_encode($variationBulk, true);
                //CALLING API FOR SENDING BULK SAVE PRODUCTS
                $bulkparam = array(
                    "lang" => 'eng',
                    "responseType" => "json", 
                    "sessionKey" => $this->api->client->sessionKey,
                );
                info("variation product bulk save calling...");
                // print_r($variationBulk);
                // die;
                $bulkRes = $this->api->sendRequest($variationBulk, $bulkparam, 1,0,0);
                // info($bulkRes);
                info("variation product bulk response received ");
                // info($bulkRes);
                //UPDATING VARIATION ID
                if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests']) ){
                    $assortmentProductIDs = "";
                    $barcodeArray = array();
                    $skuArray = array();
                    foreach($variation as $key => $vp){
                        // array_push($barcodeArray, $vp->barcode);
                        // array_push($skuArray, $vp->product_sku);
                        if($bulkRes['requests'][$key]['status']['errorCode'] == 0){
                            $id = $bulkRes['requests'][$key]['records'][0]['productID'];
                            Log::info("Variation product created or updated ID : ".$id);
                            $vp->erplyProductID = $id;
                            $vp->erplyPending = 0;
                            $vp->matrixAttributeFlag = 0;
                            $vp->error = '';
                            $vp->save();
                        }else{
                            $vp->error = $bulkRes['requests'][$key]['status']['errorCode']; 
                            $vp->save();
                            info("Error Code".$bulkRes['requests'][$key]['status']['errorCode'].' '.$bulkRes['requests'][$key]['status']['errorField']);
                            info("Barcode and SKU". $vp->barcode.' '.$vp->product_sku);
                        }

                        // $assortmentProductIDs .= "$id".",";
                        //FOR PRODUCT PICTURE

                        // $this->picture->SaveProductPictureByIDMaster($vp->stockIDMaster, $bulkRes['requests'][$key]['records'][0]['productID']); 

                    }
                    
                    //FOR INVENTORY REGISTRATION, WAREHOUSE LOCATION, ASSORTMENT ADD PRODUCT ASSORTMENT
                    // $this->inventoryReg->saveInventoryRegistrationByProductBarcode($barcodeArray, $skuArray);
                    // $this->assortmentProduct->addAssortmentProducts($assortmentProductIDs, "ID OF ASSORTMENT", "ACTIVE");
                }
                return response()->json(['status' => 200, 'response' => $bulkRes]);
                // info($bulkRes);
                // return response()->json(['status'=>401,"data"=>$bulkRes]);
                // dd($bulkRes); 
            }else{
                info("No Active Status Variation");
                return response()->json(['status' => 401, 'msg' => "No Active Status Variation Products."]);
            }
        }
        

    }

    protected function handleMatrixProduct($param){
        Log::info("handle matrix product Function called"); 
        //THIS FUNCTION WILL RESPONSIBLE FOR CHECKING PRODUCT IN ERPLY DB AND IF EXIST RETURN PRODUCT ID 
        $productID = $this->productCheck($param->web_sku);
        return $this->saveMatrixProduct($param, $productID);
        
    }

    protected function handleVariationProduct($param, $ppid,  $parent){
         
        //first checking product exist or not
        $chk =  $this->productCheck($param->product_sku); 
        return $this->saveVariantProduct($param, $chk, $ppid, $parent); 


    }

    protected function saveMatrixProduct($product, $pid){
        info("preparing to save matrix product");
        $v = $this->stockvariation->where('web_sku', $product->web_sku)->where('newSystemInternetActive', 1)->first();
        $netp = 0;
        if($v){
            $netp = $v->retailPrice1;
        }
        //request array
        $param = array(
            "type" => "MATRIX", 
            // "groupID" => $this->getGroupID($product->web_sku),
            "code" => $product->web_sku,
            "code2" => $product->web_sku, 
            // "active" => ,
            // "status" => ,
            "displayedInWebshop" => $product->newSystemInternetActive,
            // "displayedInWebshop" => $product->newSystemInternetActive,
            "active" => $product->newSystemInternetActive,
            "status" => $product->newSystemInternetActive == 1 ? "ACTIVE" : 'ARCHIVED',
            "name" => $product->newSystemStockDescription,
            "description" => $product->newSystemShortDescription != '' ? $product->newSystemShortDescription : $product->newSystemStockDescription,
            "longdesc" => $product->newSystemLongDescription,
            "groupID" => $product->erplyGroupID,
            "categoryID" => $product->erplyCatID,
            "netPrice" => $netp,
            // "length" => ,
            // "width" => ,
            // "height" => ,
            "netWeight" => $product->weight,
            "sessionKey" => $this->api->client->sessionKey,
            "dimensionID1" => 1,
            "dimensionID2" => 8,

            // "netWeight"
        );

        if($pid != ''){
            //IF PRODUCT ID IS NOT EMPTY THAN ADD PRODUCT ID TO REQUEST PARAMETER AND IT WILL UPDATE PRODUCT
            $param['productID'] = $pid;
        }
 
        //THIS FUNCTION IS RESPONSIBLE FOR CHECK GROUP ID IN ERPLY DB AND IF NOT THAN CREATE AND RETURN GROUP ID
        // $gid = $this->getGroupID($product->ciCategoryID);
        // $param['groupID'] = $gid;

        //THIS FUNCTION WILL CHECK CATEGORY IN ERPLY DB AND IF NOT THAN CREATE AND RETURN CATEGORY ID
        // $cid = $this->getCategoryID($product->ciCategoryID);
        // $param['categoryID'] = $cid;

        //adding attributes\
        $index = 1;
        foreach($product->toArray() as $key => $val){
            if($key == 'displayPosition' || $key == "hideAddToCart" || $key == "gradwear" || $key == "isWholesale" || $key == "ciCustomerID" || $key == "productType" || $key == "productSubType"){
                $param["attributeName".$index] = $key;
                $param["attributeType".$index] =  $key == 'gradwear' || $key == "ciCustomerID" || $key == "productType" || $key == "productSubType" ? "varchar(300)" : ($key == 'displayPosition' ? 'float' : 'int') ;
                $param["attributeValue".$index] = $val;
                $index++;
            }
        }

        //now calling saveproduct api
        info("save product matrix calling"); 

        $res = $this->api->sendRequest("saveProduct", $param,0,0,0);
        info("save product matrix response received");
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            //NOW SAVING PRODUCT ID 
            $product->erplyPending = 0;
            $product->erplyProductID = $res['records'][0]['productID'];
            $product->erpPushDate = date('Y-m-d H:i:s');
            $product->matrixFlag = 0;
            $product->erplyUpdate = 0;
            $product->save();
            Log::info("matrix product create or updated ".$res['records'][0]['productID']);
            return $res['records'][0]['productID'];
        }
    }

    // protected function newHandleVariation($variation,$mid){
    //     $variationBulk = array();
    //     foreach($variation as $vp){
    //         //THIS FUNCTION IS RESPONSIBLE FOR CHECKING VARIANT PRODUCT IN ERPLY DB IF EXIST THAN RETURN UPDATE PARAMETER WITH PRODUCT ID ELSE RETURN PARAM WITHOUT PRODUCT ID 
    //         $vbparam = $this->handleVariationProduct($vp, $mid); 
    //         //ADDING VARIANTS PRODUCTS TO ARRAY FOR BULK ENTRY
    //         array_push($variationBulk, $vbparam); 
    //     } 
    // }

    protected function saveVariantProduct($product, $pid,$ppid, $parent){
       
        //REQUEST ARRAY
        $param = array(
            "requestName" => "saveProduct",
            "sessionKey" => $this->api->client->sessionKey,//$this->api->verifySessionByKey($this->api->client->sessionKey),
            "clientCode" => $this->api->client->clientCode,
            "type" => 'PRODUCT',
            "parentProductID" => $ppid,
            "code" => $product->product_sku,
            "code2" => $product->barcode == '' ?  $product->product_sku : $product->barcode,
            "code3" => '',
            // "active" => ,
            // "status" => ,
            "displayedInWebshop" => $product->newSystemInternetActive,
            "active" => $product->newSystemInternetActive,
            "status" => $product->newSystemInternetActive == 1 ? "ACTIVE" : 'ARCHIVED',
            "name" => $parent->newSystemStockDescription.' '.$product->ciColourDescription.' '.$product->ciSizeCode,
            "description" => $parent->newSystemShortDescription != '' ? $parent->newSystemShortDescription : $parent->newSystemStockDescription,
            "longdesc" => $parent->newSystemLongDescription,
            "netPrice" => $product->retailPrice1,
            "groupID" => $product->erplyGroupID,
            "categoryID" => $product->erplyCatID,
            "dimValueID1" => $product->erplyDimID,
            "dimValueID2" => $product->sizeDIMID,
            // "description" => ,
            // "longdesc" => ,
            // "length" => ,
            // "width" => ,
            // "height" => ,
            "netWeight" => $product->weight,
            // "netWeight"
        );
        if($pid != ''){
            $param['productID'] = $pid;
        }
        
        // Log::info("im inside group pending");
        //THIS FUNCTION WILL CHECK GROUP ID IN ERPLY DB AND IF NOT THAN CREATE GROUP AND RETURN GROUP ID
        // $gid = $this->getGroupID($product->ciCategoryID);
        // $param['groupID'] = $gid; 
        // Log::info("im inside category pending");
        //THIS FUNCTION WILL CHECK GROUP ID IN ERPLY DB AND IF NOT THAN CREATE CATEGORY AND RETURN CATEGORY ID
        // $cid = $this->getCategoryID($product->ciCategoryID);
        // $param['categoryID'] = $cid;
        


        //ADDING EXTRA ATTRIBUTE NAME TYPE VALUE TO REQUEST PARAMETER
        $index = 1;
        foreach($product->toArray() as $key => $val){
            if($key == "softStatus" || $key == "sortOrder" || $key == "wholesalePrice1" || $key == "configName" || $key == "hide_addtocart" || $key == "gradwear" || $key == "isWholesale" || $key == "MOQ" || $key == "SLA"){
                $param["attributeName".$index] = $key;
                $param["attributeType".$index] =  $key == 'softStatus' || $key == 'configName' || $key == 'gradwear' ? "varchar(200)" : ($key == 'currentSOH' ||  $key == 'wholesalePrice1' ? 'float' : 'int') ;
                $param["attributeValue".$index] = $val;
                $index++;
            }
        }
        return $param;
        
    }

    public function productCheck($sku){
        // info("check in product". $sku);
        // Log::info("Check product exist functin called");
        //THIS FUNCTION IS RESPONSIBLE FOR CHECKING PRODUCT EXISTENSE IN ERPLY DB USING CODE, CODE2 AND CODE3 FIELD AND IF FOUND THAN RETURN PRODUCT ID
        $count = 1; 
        
        
                $checkParam = array(
                    "code" => "$sku", 
                    // "code2" => $sku, 
                    // "code3" => $sku, 
                    "sessionKey" => $this->api->client->sessionKey,
                );
           
            // print_r($checkParam);
            // die;
            $checkRes = $this->api->sendRequest("getProducts", $checkParam,0,0,0);
            //  dd($checkRes);
            if($checkRes['status']['errorCode'] == 0 && !empty($checkRes['records'])){
                 
                Log::info("Product exist in erply db ".$checkRes['records'][0]['productID']);
                //create variation products
                return $checkRes['records'][0]['productID'];
            }
         
        return '';
        
         
         
    }

    protected function getGroupID($cicatid){
        Log::info("get group function called");
        return $this->group->getGroupByCatID($cicatid);
    }
    
    protected function getCategoryID($id){
        Log::info("get category function called");
        return $this->catservice->getCategoryByID($id);
    }

    
 
    public function uploadImages($req){ 
        

        
        // dd($variation);     
        
    }

    //working through its own controller
    public function inventoryReg($req){
        $mlimit = $req->limit == '' ? 1 : $req->limit;
        // $stocks = $this->stockdetails->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
        //         ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID') 
        //         ->select(['newsystem_stockdetail.*', 'newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyCatID','newsystem_internet_category.erplyGroupPending','newsystem_stock_internet_category.*'])
        //         ->where('newsystem_internet_category.erplyGroupPending', '0')
        //         ->where('newsystem_internet_category.erplyCatPending', '0')
        //         ->where('newsystem_stockdetail.erplyPending', 0)
        //         ->where('newsystem_stockdetail.inventoryFlag', 1)
        //         // ->where('newsystem_stockdetail.web_sku', '10001_1110410_0')
        //         ->limit($mlimit)
        //         ->get(); 
         
        // if($stocks){
            // foreach($stocks as $product){ 

                $variation = $this->stockvariation
                            // ->select(['newsystem_stock_colour_size.*', 'newsystem_stock_internet_category.*', 'current_stock_colour.ciColourDescription','newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyGroupPending','newsystem_internet_category.erplyCatPending','newsystem_internet_category.erplyCatID'])
                            // ->where('newsystem_stock_colour_size.web_sku', $product->web_sku)
                            ->join('current_customer_product_relation', 'current_customer_product_relation.barcode', 'newsystem_stock_colour_size.barcode')
                            ->join('current_locations', 'current_locations.locationid', 'current_customer_product_relation.locationCode')
                            ->where('current_locations.erplyPending', 0)
                            ->where('newsystem_stock_colour_size.inventoryFlag', 1)
                            ->where('newsystem_stock_colour_size.erplyPending', 0)
                            ->where('newsystem_stock_colour_size.matrixAttributeFlag', 0)
                            ->where('newsystem_stock_colour_size.newSystemInternetActive', 1)
                            ->select(['newsystem_stock_colour_size.*'])
                            ->limit($mlimit)
                            ->get();
                dd($variation);
                // print_r($variation);
                $barcodeArray = array();
                $skuArray = array();
                foreach($variation as $key => $vp){
                    array_push($barcodeArray, $vp->barcode);
                    array_push($skuArray, $vp->product_sku);
                }
                    //FOR INVENTORY REGISTRATION, WAREHOUSE LOCATION, ASSORTMENT ADD PRODUCT ASSORTMENT
                if($barcodeArray){
                    // $this->inventoryReg->saveInventoryRegistrationByProductBarcode($barcodeArray, $skuArray);
                }
                    // $this->assortmentProduct->addAssortmentProducts($assortmentProductIDs, "ID OF ASSORTMENT", "ACTIVE");
            // } 
        // }
        // else{
        //     info("Product not found for Inventory Registration");
        // }
    }


    // public function productAssortment($req){
    //     $mlimit = $req->limit == '' ? 1 : $req->limit;
         
    //         $variation = $this->stockvariation 
    //                     ->join('current_customer_product_relation','current_customer_product_relation.barcode', 'newsystem_stock_colour_size.barcode') 
    //                     ->join('current_locations','current_locations.locationid','current_customer_product_relation.locationCode')
    //                     ->select(['newsystem_stock_colour_size.*'])
    //                     // ->where('newsystem_stock_colour_size.web_sku', $product->web_sku)
    //                     ->where('newsystem_stock_colour_size.productAssortmentFlag', 1)
    //                     ->where('current_locations.erplyPending', 0)
    //                     ->where('newsystem_stock_colour_size.erplyPending', 0)
    //                     ->where('newsystem_stock_colour_size.productAssortmentFlag', 1) 
    //                     ->where('newsystem_stock_colour_size.matrixAttributeFlag', 0)
    //                     ->where('newsystem_stock_colour_size.newSystemInternetActive', 1)
    //                     // ->where('newsystem_internet_category.erplyCatPending', 0)
    //                     // ->where('')
    //                     ->limit($mlimit)
    //                     ->get(); 

    //         //  dd($variation);
    //         $barcodeArray = array();
    //         // $skuArray = array();
    //         foreach($variation as $key => $vp){ 
    //             array_push($barcodeArray, $vp->barcode);
    //             // array_push($skuArray, $vp->product_sku);
    //         }
                
    //         //FOR INVENTORY REGISTRATION, WAREHOUSE LOCATION, ASSORTMENT ADD PRODUCT ASSORTMENT
    //         if($barcodeArray){
    //             $this->inventoryReg->saveProductAssortmentByProductBarcode($barcodeArray); 
    //         }
        
        
    // }
 



     



}