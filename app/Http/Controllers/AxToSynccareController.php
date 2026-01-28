<?php

namespace App\Http\Controllers;
 
use App\Http\Controllers\Services\EAPIService;
use App\Models\Client; 
use Illuminate\Http\Request;
use App\Models\Kudos\StockStyle;
use App\Models\CurrentProductDes;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\PAEI\MatrixProduct;
use App\Models\PAEI\VariationProduct;

class AxToSynccareController extends Controller
{
    
    protected $api;
    
    
    public function __construct(EAPIService $api){
        $this->api = $api;
    }
    
    function escapeFunc($val){
        
        $val = str_replace("'","\'",$val);
        $val = str_replace('"','\"',$val);
        // $val = trim($val);
        return $val;
    }
    
    public function getProductAX(){
        
        $products = StockStyle::get();
        // echo count($products);
        // die;
        $count = 1;
        $array = array();
        foreach($products as $key => $p){
            
            $des = str_replace("UK sizing,","UK sizing.",$p["Internet Description"]);
            $des = str_replace('<a href="javascript:;" target="" data-target="#myModalSize" data-toggle="modal">our size guide</a>',"our size guide",$des);
            $des = str_replace('<a href="javascript:;" target="" data-toggle="modal" data-target="#myModalSize">our size guide</a>',"our size guide",$des);
            
            $array[] = array("code" => $p->Code,"shortDes" => trim($p["Internet Short Description"]), "des" => trim($des));
            
            
            
            if($key == 20000 || $key == 40000){
                CurrentProductDes::insert($array);    
                $array = [];
            }
            if($key > 40000){
                $des = str_replace("UK sizing,","UK sizing.",$p["Internet Description"]);
                $des = str_replace('<a href="javascript:;" target="" data-target="#myModalSize" data-toggle="modal">our size guide</a>',"our size guide",$des);
                $des = str_replace('<a href="javascript:;" target="" data-toggle="modal" data-target="#myModalSize">our size guide</a>',"our size guide",$des);
                
                $single = array("code" => $p->Code,"shortDes" => trim($p["Internet Short Description"]), "des" => trim($des));
                CurrentProductDes::insert($single);    
            }
            
            
        }
        
        echo "Success";
        die;
        // dd($products);
        
        
        // $path = public_path('RoadhouseTemp');

        // File::delete($path . '/productDes.txt');

        // if (!File::exists($path)) {

        //     File::makeDirectory($path);

        // }

        // // $products = $this->psw_live_product->get();
        // // dd($products);
        // $chunkProduct = $products->chunk(500);

        // foreach ($chunkProduct as $cpro) {

        //     $content = 'Insert into `temp_product_des`(`Code`,
        //             `des`
        //             ) VALUES ';
            
        //     $q = null;

        //     foreach ($cpro as $key => $value) {

        //         //$compareField = $value->STOCKCODE . '-' . (int)$value->LOCNO . '-' . (int)$value->INSTOCKQTY . '-' . (int)$value->SALESORDQTY . '-' . (int)$value->VIRTSTOCK;

        //         $key = $cpro->last() == $value ? ';' : ',';

        //         $q .= '( "'. $value['Code'] . '",
        //                 "' . $this->escapeFunc($value['Internet Description']) . '")'
        //                 . $key;

        //     }

        //     $content = $content . '' . $q . '' . "\n";

        //     File::append($path . '/productDes.txt', $content);

        // }

        // return $this->successWithMessage("Product File Generated Successfully.");
        
        
        //now syncing to temp
        // $path = public_path('RoadhouseTemp/productDes.txt');

        // if (File::exists($path)) {  
        //     // $tempProDes = TempProductDescription::where('pendingProcess', 0)->count(); 
        //     // TempProductDescription::turncate();
            
        //     $file = File::get($path);
         
        //     $sqls = explode(";\n", $file); 
            
        //     foreach ($sqls as $sql) { 
        //         if ($sql != '') { 
        //             DB::select($sql); 
        //         } 
        //     }   

        //     return response("Product Description File Executed Successfully.");
            
        // } else{
        //     echo "no file";
        //     die;
        // }
        
         
    }
    
    
    //updating des to Erply
    
    public function updateDesErply(){
         
        
        $datas = CurrentProductDes::where("pendingProcess", 1)->where("des","<>",'')->where("pushCount", 0)->limit(99)->get();
        if($datas->isEmpty()){
            $datas = CurrentProductDes::where("pendingProcess", 1)->where("des","<>",'')->where("pushCount", 1)->limit(99)->get();
        }
        // dd($datas);
        $bulkReq = array();
        $productInErply = array();
        foreach($datas as $data){
            CurrentProductDes::where("id", $data->id)->update(["pushCount" => $data->pushCount + 1]);
            $pid = $this->checkProductErply($data->code);
            
            if($pid != ''){
                
                $param = array(
                    "requestName" => "saveProduct",
                    "sessionKey" => $this->api->client->sessionKey,//$this->api->verifySessionByKey($this->api->client->sessionKey),
                    "clientCode" => $this->api->client->clientCode, 
                    "productID" => $pid,
                    "longdesc" => $data->des,
                    
                );
                $productInErply[] = $data;
                $bulkReq[] = $param;
                
            }
        }
        
        if(count($bulkReq) < 1){
            info("All Product Long Description Synced.");
            return response("All Product Long Description Synced.");
        }
        // dd($bulkReq);
        $bulkReq = json_encode($bulkReq, true);
        //CALLING API FOR SENDING BULK SAVE PRODUCTS
        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $this->api->client->sessionKey,
        );
     
        $bulkRes = $this->api->sendRequest($bulkReq, $bulkparam, 1,0,0);
        
        if($bulkRes["status"]["errorCode"] == 0 && !empty($bulkRes["requests"])){
            foreach($productInErply as $key => $ep){
                if($bulkRes['requests'][$key]['status']['errorCode'] == 0){
                    CurrentProductDes::where("id", $ep["id"])->update(["pendingProcess" => 0, "erplyID" => $bulkRes['requests'][$key]['records'][0]['productID'] ]);
                }else{
                    info("Getting error while updang des on ".$ep["code"]);
                }
            }
        }
        
        return response("Product Long Description Updated Successfully.");
        
        
        
    }
    
 
    
    public function updateAgainDes(){
        
        $datas = CurrentProductDes::where("pendingProcess", 0)->where("variationCount",'>', 100)->limit(1)->get();
        // dd($datas);
        $matrixCount = 0;
        $vCount = 0;
        $variationInErply = array();
        $bulkReq = array();
        foreach($datas as $data){
            
            //now updating variations through 
            $m = MatrixProduct::where("productID", $data->erplyID)->first();
            // dd($m);
            if($m){
                
                $v = VariationProduct::where("parentProductID", $data->erplyID)->where("productID",'>',1)->where("active",1)->orderBy("productID", 'asc')->skip(200)->limit(100)->get();
                // dd($v);
                // if(count($v) > 99){
                //     CurrentProductDes::where("id", $data->id)->update(["crossCheckPending" => 0]);
                // }
                $vCount = $vCount + count($v);
                
                // if($vCount > 99){
                //     // 
                //     break;
                // }else{
                    // CurrentProductDes::where("id", $data->id)->update(["crossCheckPending" => 0]);
                    foreach($v as $vData){
                        // $pid = $this->checkProductErply($data->code);
                
                        // if($pid != ''){
                            
                            $param = array(
                                "requestName" => "saveProduct",
                                "sessionKey" => $this->api->client->sessionKey,//$this->api->verifySessionByKey($this->api->client->sessionKey),
                                "clientCode" => $this->api->client->clientCode, 
                                "productID" => $vData->productID,
                                "longdesc" => $data->des,
                                
                            );
                            $productInErply[] = $data;
                            $bulkReq[] = $param;
                            
                        // }
                    }
                    
                // }    
            } 
        }
        // dd($bulkReq);
        if(count($bulkReq) < 1){
            return response("All Description Synced.");
        }
        
        $bulkReq = json_encode($bulkReq);
        
        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $this->api->client->sessionKey,
        );
     
        $bulkRes = $this->api->sendRequest($bulkReq, $bulkparam, 1,0,0);
        
        return response()->json($bulkRes);
        
        
        
    }
    
    public function setFlagToMaxVariation(){
        $datas = CurrentProductDes::where("pendingProcess", 0)->where("crossCheckPending", 0)->get();
        // dd($datas);
        $matrixCount = 0;
        $vCount = 0;
        $variationInErply = array();
        $bulkReq = array();
        foreach($datas as $data){
            
            //now updating variations through 
            $m = MatrixProduct::where("productID", $data->erplyID)->first();
            // dd($m);
            if($m){
                
                $v = VariationProduct::where("parentProductID", $data->erplyID)->where("productID",'>',1)->where("displayedInWebshop",1)->get();
                //  dd($v);
                if(count($v) > 99){
                    CurrentProductDes::where("id", $data->id)->update(["variationCount" => count($v)]);
                }
                   
            } 
        }
        
        echo "count done";
        
    }
    
    protected function checkProductErply($sku){ 
        $checkParam = array(
            "code" => "$sku", 
            // "code2" => $sku, 
            // "code3" => $sku, 
            "sessionKey" => $this->api->client->sessionKey,
        ); 
        
        $checkRes = $this->api->sendRequest("getProducts", $checkParam,0,0,0);
        //  dd($checkRes);
        if($checkRes['status']['errorCode'] == 0 && !empty($checkRes['records'])){
             
            // Log::info("Product exist in erply db ".$checkRes['records'][0]['productID']);
            //create variation products
            return $checkRes['records'][0]['productID'];
        }
         
        return '';
         
    }
    
    
}