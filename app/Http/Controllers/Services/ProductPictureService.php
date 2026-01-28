<?php
namespace App\Http\Controllers\Services;

use App\Http\Controllers\EAPI;
use App\Models\Client;
use App\Models\ProductPicture;
use App\Models\StockColorSize;
use App\Models\StockDetail;
use Illuminate\Support\Facades\Log;
 

class ProductPictureService
{
    protected $api;
    protected $images; 
    protected $stockdetails;
    protected $productvariation;

    public function __construct(EAPIService $api, ProductPicture $pp, StockDetail $sd, StockColorSize $variant)
    {
        $this->api = $api;
        $this->images = $pp;
 
        // $this->api->client->sessionKey = $this->api->verifySessionByKey($client->sessionKey);
        $this->stockdetails = $sd;
        $this->productvariation = $variant;

    }

    protected function getSplit($pictures){

    }

    protected function sendSplit($pic, $id){
        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $this->api->client->sessionKey,
        );
        info("variation picture save api calling...");
        $bulkRes = $this->api->sendRequest($pic, $bulkparam, 1,0,0); 
        // Log::info($bulkRes);
        if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests'])){
            $this->productvariation->where('erplyProductID', $id)->update(['erplyPicturePending' => 0]); 
            info("variation picture saved successfully.");
            // return response()->json(['status' => 200, 'data' => $bulkRes]);
        }
    }

    public function SaveProductPictureByMatrixWebSku($matrix){
        $variation = $this->productvariation 
                        ->select(['newsystem_stock_colour_size.*']) 
                        // ->where('newsystem_stock_colour_size.erplyPicturePending', 1) 
                        // ->where('newsystem_stock_colour_size.erplyPending', 0)
                        ->where('newsystem_stock_colour_size.web_sku', $matrix->web_sku)
                        ->where('newsystem_stock_colour_size.newSystemInternetActive', 1)
                        // ->where('newsystem_stock_colour_size.maxImagesFlag', 0)
                        // ->limit($mlimit)
                        ->get(); 
        // dd($variation);
        $bulkProductImage = array();
        foreach($variation as $vp){
            $this->deleteProductPicture($vp->erplyProductID);
            $pictures = $this->images->where('stockIDMaster', $vp->stockIDMaster)->where('ciColorCode', $vp->ciColorCode)->where('configID', $vp->configID)->where('imageView','front')->get();
            foreach($pictures as $p){
                $reqArray = array(
                    "requestName" => "saveProductPicture",
                    "sessionKey" => $this->api->client->sessionKey,
                    "clientCode" => $this->api->client->clientCode,
                    "productID" => $vp->erplyProductID,
                    "url" => "https://pswdata.retailcare.com.au/magic/uploads/images/".$p->imageName,
                    "hostingProvider" => "", 
                );
                array_push($bulkProductImage,$reqArray );
            } 
        }
        info("TOT IMG REQ ". count($bulkProductImage));
        $count = count($bulkProductImage);

        $bulkProductImage = json_encode($bulkProductImage, true);
        // echo "<pre>";
        // print_r($bulkProductImage);
        // die;
        
        if($count > 0 && $count <= 100 ){
            $bulkparam = array(
                "lang" => 'eng',
                "responseType" => "json", 
                "sessionKey" => $this->api->client->sessionKey,
            );
            info("variation picture save api calling...");
            $bulkRes = $this->api->sendRequest($bulkProductImage, $bulkparam, 1,0,0);

            if($bulkRes['status']['errorCode'] == 0){
                // info($bulkRes);
                $this->stockdetails->where('erplyProductID', $matrix->erplyProductID)->update(['variationPicPending' => 0]); 
                info("Variation Picture Updated ". $matrix->erplyProductID);
            }else{
                info("Variation Picture Error ". $matrix->erplyProductID);
            }
            return response()->json(['status'=>200, 'response'=>$bulkRes]);
        }else{
            
            $this->stockdetails->where('erplyProductID', $matrix->erplyProductID)->update(['variationMaxImage' => 1]); 
            if($count == 0){
                info("No Images ".$matrix->erplyProductID);
                return response()->json(['status'=>401, 'response'=> "No Images, Product ID : ".$matrix->erplyProductID ]);
            }
            if($count > 100){
                info("Max Images ".$matrix->erplyProductID);
                return response()->json(['status'=>401, 'response'=> "More than 100 Images, Product ID : ".$matrix->erplyProductID ]);
            } 
        }
        
    }

    public function SaveProductPictureByIDMaster($idmaster,$code,$config, $PID, $PPID){
        info("variation picture save called");
        //FISRT DELETEING PICTURES ASSOCIATED WITH THIS PRODUCT ID
        $this->deleteProductPicture($PID);
        $pictures = $this->images->where('stockIDMaster', $idmaster)->where('ciColorCode', $code)->where('configID', $config)->get();//->where('erplyPending', 1)->get();
         

            if(count($pictures) < 100){
                $bulkPictures = $this->makeBundleJSON($pictures, $PID);
                $bulkparam = array(
                    "lang" => 'eng',
                    "responseType" => "json", 
                    "sessionKey" => $this->api->client->sessionKey,
                );
                info("variation picture save api calling...");
                $bulkRes = $this->api->sendRequest($bulkPictures, $bulkparam, 1,0,0); 
                // Log::info($bulkRes);
                if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests'])){
                    $this->stockdetails->where('erplyProductID', $PPID)->update(['variationPicPending' => 0]); 
                    $this->productvariation->where('erplyProductID', $PID)->update(['erplyPicturePending' => 0]); 
                    info("variation picture saved successfully.". $PID);
                    // return response()->json(['status' => 200, 'data' => $bulkRes]);
                }
            }else{
                info("More than 100 images". $PID);
                $this->productvariation->where('erplyProductID', $PID)->update(['maxImagesFlag' => 1]); 
            }
        
        // return response()->json(['status' => 401, 'data' => $bulkRes]);

    }

    public function handleMatrixImages($products){
        $bulkJson = $this->handleMatrixBundl($products);

        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $this->api->client->sessionKey,
        );
        // info("variation picture save api calling...");
        $bulkRes = $this->api->sendRequest($bulkJson, $bulkparam, 1,0,0);
        if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests'])){
            foreach($products as $key => $p){
                if(0 == $bulkRes['requests'][$key]['status']['errorCode']){
                    $p->erplyPicturePending = 0;
                    $p->erplyProductPictureID = $bulkRes['requests'][$key]['records'][0]['productPictureID'];
                    $p->save();
                    info("Saved Matrix Image". $bulkRes['requests'][$key]['records'][0]['productPictureID']);
                }else{
                    info("Failed Save Matrix Image". $bulkRes['requests'][$key]['status']['errorCode']);
                }
            }
            // info("matrix bulk images saved");
        }
        return response()->json(['status'=>200,'response'=>$bulkRes]);
    }

    public function handleMatrixBundl($pro){
        $verifiedSessionKey = $this->api->client->sessionKey;//$this->api->verifySessionByKey($this->api->client->sessionKey);
        $BundleArray = array();
        foreach($pro as $pic){ 
                $reqArray = array(
                    "requestName" => "saveProductPicture",
                    "sessionKey" => $verifiedSessionKey,
                    "clientCode" => $this->api->client->clientCode,
                    "productID" => $pic->erplyProductID,
                    "url" => "https://pswdata.retailcare.com.au/magic/uploads/images/".$pic->mainImageName,
                    "hostingProvider" => "", 
                ); 
                array_push($BundleArray,$reqArray ); 
        }
        $BundleArray = json_encode($BundleArray, true);
        return $BundleArray; 
    }

    public function saveExtraBulkProductPicture($products){
        
        $bulkParam = array();
        $idmArray = array();
        foreach($products as $p){
            array_push($idmArray, $p->stockIDMaster);
        }
        print_r($idmArray);
        echo "<br>";
        $pictures = $this->getQuery($idmArray);
        echo count($pictures);
        die; 
        foreach($pictures as $p){
            echo " ID ". $p->erplyProductID.' img '.$p->imageName."<br>";
        }
        die;
    }

    protected function getQuery($idm){
        return $this->images->join('newsystem_stock_colour_size', 'newsystem_stock_colour_size.stockIDMaster', 'newsystem_stock_image_map.stockIDMaster')
                ->whereIn('newsystem_stock_image_map.stockIDMaster', $idm)
                ->groupBy('newsystem_stock_image_map.imageName','newsystem_stock_colour_size.erplyProductID')
                ->select('newsystem_stock_image_map.*', 'newsystem_stock_colour_size.erplyProductID')
                ->get();
    }

    protected function makeBundleJSON($pictures,$PID){
        $verifiedSessionKey = $this->api->client->sessionKey;//$this->api->verifySessionByKey($this->api->client->sessionKey);
        $BundleArray = array();
        foreach($pictures as $pic){
            $reqArray = array(
                "requestName" => "saveProductPicture",
                "sessionKey" => $verifiedSessionKey,
                "clientCode" => $this->api->client->clientCode,
                "productID" => $PID,
                "url" => "https://pswdata.retailcare.com.au/magic/uploads/images/".$pic->imageName,
                "hostingProvider" => "", 
            );
 
   
            array_push($BundleArray,$reqArray );
        }
        $BundleArray = json_encode($BundleArray, true);
        return $BundleArray; 
    }

    public function SaveProductPictureByImageName($name, $pid){
        info("Save image by Image name Called");
        $this->deleteProductPicture($pid);
        $reqArray = array( 
            "productID" => $pid,
            "url" => "https://pswdata.retailcare.com.au/magic/uploads/images/".$name,
            "hostingProvider" => "", 
            "sessionKey" => $this->api->client->sessionKey
        );
        $res = $this->api->sendRequest("saveProductPicture", $reqArray, 0,0,0);
        //productPictureID
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){ 
            // if(){
                $this->stockdetails->where('erplyProductID', $pid)->update(['erplyProductPictureID' => $res['records'][0]['productPictureID'], "erplyPicturePending" => 0]);
            // }
        }

    }

    public function deleteProductPicture($pid){
        $param = array(
            "productID" => $pid,
            "sessionKey" => $this->api->client->sessionKey
        );
        $res = $this->api->sendRequest("deleteProductPicture", $param,0,0,0);
        // return response()->json($res);
    }

    public function deleteBulkProductPicture($products){
        $bulkParam = array();
        foreach($products as $p){
            $param = array(
                "productID" => $p->erplyProductID,
                'clientCode' => $this->api->client->clientCode,
                'sessionKey' => $this->api->client->sessionKey,
                "requestName" => "deleteProductPicture"
            );
            array_push($bulkParam, $param);
        }
        $bulkParam = json_encode($bulkParam);
        $bulkP = array(
            "lang" => 'eng',
            "responseType" => "json", 
        );
        
        $res = $this->api->sendRequest($bulkParam, $bulkP, 1);

        return response()->json($res);
    }



}