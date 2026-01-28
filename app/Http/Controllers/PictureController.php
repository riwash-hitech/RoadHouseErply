<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\EAPIService;
use App\Http\Controllers\Services\ProductPictureService;
use App\Models\Client;
use App\Models\ProductPicture;
use App\Models\StockColorSize;
use App\Models\StockDetail;
use Illuminate\Http\Request;

class PictureController extends Controller
{
    //
    protected $service;
    protected $stockdetails;
    protected $variation;
    protected $api;
    protected $picture;

    public function __construct(ProductPictureService $ps, StockDetail $sd, StockColorSize $vp, ProductPicture $pp,EAPIService $api){
        $this->service = $ps;
        $this->stockdetails = $sd;
        $this->variation = $vp;
       

        $this->api = $api;
        $this->picture = $pp;
        // $this->client->sessionKey =  $this->api->verifySessionByKey($client->sessionKey);
    }

    public function productPicture(Request $req){
            $limit = $req->limit == '' ? 1 : $req->limit;
            $stocks = $this->stockdetails
                    ->where('newsystem_stockdetail.erplyPicturePending', 1)
                    ->where('newsystem_stockdetail.erplyPending', 0)
                    ->where('newsystem_stockdetail.mainImageName', '<>', '')
                    ->where('newsystem_stockdetail.newSystemInternetActive', 1)
                    ->select(['newsystem_stockdetail.*'])
                    // ->where('newsystem_stockdetail.web_sku', '10001_1110410_0')
                    ->limit($limit)
                    ->get();
            // dd($stocks);
            if($stocks){
                return $this->service->handleMatrixImages($stocks); //FOR MATRIX PRODUCT PICTURE
            }
    }
    public function productPictureVariation(Request $req){
        $limit = $req->limit == '' ? 1 : $req->limit;
        $sku = $req->sku;
        if($sku != ''){
            $stocks = $this->stockdetails->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
                ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
                ->select(['newsystem_stockdetail.*', 'newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyCatID','newsystem_internet_category.erplyGroupPending','newsystem_stock_internet_category.*'])
                // ->where('newsystem_stockdetail.matrixFlag', 1)
                // ->where('newsystem_stockdetail.erplyPending', 0 )//correct it to 1
                ->where('newsystem_stockdetail.erplyUpdate', 0)
                // ->where('newsystem_internet_category.erplyGroupPending', '0')
                // ->where('newsystem_internet_category.erplyCatPending', '0')
                ->where('newsystem_stockdetail.newSystemInternetActive', 1)
                ->where('newsystem_stockdetail.noInventoryRelation', 0)
                ->where('newsystem_stockdetail.web_sku', $sku)
                ->limit(1)
                ->get();
                // dd($stocks);
        }else{
            $stocks = $this->stockdetails
                // ->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
                // ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
                ->select(['newsystem_stockdetail.*'])
                // ->where('newsystem_stockdetail.matrixFlag', 1)
                ->where('newsystem_stockdetail.variationPicPending', 1)//correct it to 1
                ->where('newsystem_stockdetail.erplyPending', 0 )
                ->where('newsystem_stockdetail.mainImageName', '<>', '' )
                // ->where('newsystem_internet_category.erplyCatPending', '0')
                ->where('newsystem_stockdetail.variationMaxImage', 0)
                ->where('newsystem_stockdetail.newSystemInternetActive', 1)
                ->limit($limit)
                ->get();
                // ->random($limit);
        }

        $skuArray = array();
        foreach($stocks as $s){
            array_push($skuArray, $s->web_sku);
            info("Handling variation image of SKU ". $s->web_sku);
            // $this->service->SaveProductPictureByMatrixWebSku($s);
        }
        if(count($skuArray) == 0 ){
            info('No Matrix Product Found.');
            return response()->json(['status'=>401,"msg"=>"No Matrix Product Found."]);
        }

        //getting data from variation table
        $vp = $this->variation
                    // ->join('newsystem_stock_image_map', function($join){
                    //     $join->on('newsystem_stock_image_map.stockIDMaster','=','newsystem_stock_colour_size.stockIDMaster')
                    //         ->on('newsystem_stock_image_map.ciColorCode','=','newsystem_stock_colour_size.ciColorCode')
                    //         ->on('newsystem_stock_image_map.configID','=','newsystem_stock_colour_size.configID')
                    //         ->where('newsystem_stock_image_map.imageView','first');
                    // })
                    ->whereIn('web_sku', $skuArray)
                    ->where('newSystemInternetActive', 1)
                    // ->select(['newsystem_stock_colour_size.product_sku_2','newsystem_stock_image_map.imageName'])
                    // ->toSql();
                    ->get();

                    // dd($vp);
        if(count($vp) == 0){

            info('No Variation Product Found.');
            return response()->json(['status'=>401,"msg"=>"No Variation Product Found."]);
        }
        $matrixConfirmed = array();
        foreach($skuArray as $m){
            foreach($vp as $p){
                $sku = $p->web_sku;
                if("$m" == "$sku"){
                    if(in_array($m, $matrixConfirmed)){

                    }else{
                        array_push($matrixConfirmed, $m);
                    }
                }
            }
        }
        // print_r($skuArray);
        // print_r($matrixConfirmed);
        // die;
        $bulkReq = array();
        foreach($vp as $p){
            $this->service->deleteProductPicture($p->erplyProductID);
            $picture = $this->picture->where('stockIDMaster', $p->stockIDMaster)->where('ciColorCode', $p->ciColorCode)->where('configID', $p->configID)->where('imageView','front')->first();//->where('erplyPending', 1)->get();
            if($picture){
                $reqArray = array(
                    "requestName" => "saveProductPicture",
                    "sessionKey" => $this->api->client->sessionKey,
                    "clientCode" => $this->api->client->clientCode,
                    "productID" => $p->erplyProductID,
                    "url" => "https://pswdata.retailcare.com.au/magic/uploads/images/".$picture->imageName,
                    "hostingProvider" => "",
                );
                array_push($bulkReq, $reqArray);
            }else{

                //if not found than use parent mainImage
                $matrixPic = $this->stockdetails->where('web_sku', $p->web_sku)->first();
                if($matrixPic->mainImageName != ''){
                    $reqArray = array(
                        "requestName" => "saveProductPicture",
                        "sessionKey" => $this->api->client->sessionKey,
                        "clientCode" => $this->api->client->clientCode,
                        "productID" => $p->erplyProductID,
                        "url" => "https://pswdata.retailcare.com.au/magic/uploads/images/".$matrixPic->mainImageName,
                        "hostingProvider" => "",
                    );
                    array_push($bulkReq, $reqArray);
                    info("im from variation image used mainImage");
                }
            }
        }

        if(count($bulkReq) == 0){
            info('No Product Picture Found.');
            return response()->json(['status'=>401,"msg"=>"No Product Picture Found."]);
        }
        info("> 0 pictures");
        if(count($bulkReq) > 100){
            info('Max Bulk Request Limit Crossed.');
            return response()->json(['status'=>401,"msg"=>"Max Bulk Request Limit Crossed."]);
        }
        info("< 100 pictures");

        if(count($vp) != count($bulkReq)){
            info('Some Variation Picture Not Found.');
            return response()->json(['status'=>401,"msg"=>"Some Variation Picture Not Found."]);
        }
        info("All Picture Found".count($bulkReq));

        $bulkReq = json_encode($bulkReq, true);

        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json",
            "sessionKey" => $this->api->client->sessionKey,
        );

        $bulkRes = $this->api->sendRequest($bulkReq, $bulkparam, 1,0,0);

        if($bulkRes['status']['errorCode'] == 0){
            info("Variation Picture Update Success.");
            foreach($vp as $key => $p){
                if($bulkRes['requests'][$key]['status']['errorCode'] == 0){
                    $p->erplyPicturePending = 0;
                    $p->save();
                    info('Variation '. $p->erplyProductID.' picture updated');
                }
            }
            foreach($matrixConfirmed as $sku){
                info("Variation Pic Updated ".$sku);
                $this->stockdetails->where('web_sku', $sku)->update(['variationPicPending' => 0]);
            }
            // return response()->json(['status'=>200,"msg"=>$bulkRes]);
        }else{
            info("Variation Picture Update Failed.");
        }
        return response()->json(['status'=>200,"msg"=>$bulkRes]);


    }
}
