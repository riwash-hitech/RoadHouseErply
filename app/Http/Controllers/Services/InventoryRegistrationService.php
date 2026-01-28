<?php
namespace App\Http\Controllers\Services;

use App\Models\Client;
use App\Models\CurrentCustomerProductRelation;
use App\Models\InventoryRegistration;
use App\Models\StockColorSize;
use App\Models\StockDetail;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Log;


class InventoryRegistrationService
{
    protected $api; 
    protected $variation;
    protected $warehouse;
    protected $inventoryRegistration;
    // protected $productRelation;
    protected $warehouseService;
    protected $productAssortment;
    protected $matrix;

    public function __construct(  EAPIService $api, StockColorSize $pv, Warehouse $warehouse , InventoryRegistration $irt, StockDetail $sd )
    {
        // $this->productAssortment = $aps;
        $this->api = $api;
 
        // $this->session = $ss;
        $this->matrix = $sd;
        // $this->warehouseService = $ws;
        $this->variation = $pv;
        $this->warehouse = $warehouse;
        $this->inventoryRegistration = $irt;
        // $this->productRelation = $prorelation;
        // $this->api->client->sessionKey = $this->api->verifySessionByKey($this->api->client->sessionKey);
    }


    protected function getWarehousesWithRelationByBarcodeArrayInv($psku){

        // print_r($psku);
        $data = $this->warehouse->join('current_customer_product_relation', 'current_locations.locationid', 'current_customer_product_relation.locationCode')
                // ->join('newsystem_stockdetail', 'newsystem_stockdetail.web_sku','current_customer_product_relation.web_sku')
                ->join('newsystem_stock_colour_size', 'newsystem_stock_colour_size.product_sku_2','current_customer_product_relation.product_sku')
                // ->whereIn('current_customer_product_relation.barcode', $barcode)
                ->whereIN('current_customer_product_relation.web_sku', $psku)
                ->where('current_customer_product_relation.locationCode', '<>','')
                ->whereIN('newsystem_stock_colour_size.web_sku', $psku)
                ->where('current_locations.erplyPending', 0)
                // ->where('newsystem_stock_colour_size.erplyPending', 0)
                // ->where('newsystem_stock_colour_size.matrixAttributeFlag', 0)
                // ->where('newsystem_stock_colour_size.inventoryFlag', 1)
                // ->where('newsystem_stock_colour_size.noRelationFlag', 0)
                ->select('current_locations.*' , 'newsystem_stock_colour_size.erplyProductID','newsystem_stock_colour_size.actualSOH','newsystem_stock_colour_size.web_sku','newsystem_stock_colour_size.retailPrice1','newsystem_stock_colour_size.product_sku')
                ->groupBy('current_customer_product_relation.locationCode', 'current_customer_product_relation.product_sku')
                ->get();
                // ->toSql();
                // print_r($data);
        // dd($data);
                // die;
                return $data;
    }

    protected function getWarehousesWithRelationBySkuArrayInv($psku){

        // dd($psku);
        $data = $this->warehouse->join('current_customer_product_relation', 'current_locations.locationid', 'current_customer_product_relation.locationCode')
                ->join('newsystem_stock_colour_size', 'newsystem_stock_colour_size.product_sku_2','current_customer_product_relation.product_sku')
                // ->join('inventory_registration', 'inventory_registration.productSKU','newsystem_stock_colour_size.product_sku')
                // ->whereIn('current_customer_product_relation.barcode', $barcode)
                ->whereIN('current_customer_product_relation.web_sku', $psku)
                ->where('current_customer_product_relation.locationCode', '<>','')
                ->whereIN('newsystem_stock_colour_size.web_sku', $psku)
                ->where('current_locations.erplyPending', 0)
                ->where('newsystem_stock_colour_size.erplyPending', 0)
                // ->where('newsystem_stock_colour_size.matrixAttributeFlag', 0)
                // ->where('newsystem_stock_colour_size.inventoryFlag', 1)
                ->where('newsystem_stock_colour_size.noRelationFlag', 0)
                ->select('current_locations.*' , 'newsystem_stock_colour_size.erplyProductID','newsystem_stock_colour_size.actualSOH','newsystem_stock_colour_size.currentSOH','newsystem_stock_colour_size.web_sku','newsystem_stock_colour_size.retailPrice1','newsystem_stock_colour_size.product_sku')
                ->groupBy('current_customer_product_relation.locationCode', 'current_customer_product_relation.product_sku')
                ->get();
                // ->toSql();
                // print_r($data);

                // die;
                return $data;
    }



    public function saveInventoryRegistrationBulk($psku){
        // $verifiedSessionKey =
         
        $bulkParam = array();
        $bulkp = array(
            "lang" => 'eng',
            "responseType" => "json",
            "sessionKey" => $this->api->client->sessionKey,
        );
        // dd($psku);
        $warehouses = $this->getWarehousesWithRelationByBarcodeArrayInv($psku);
        // dd($warehouses);
        if(count($warehouses) == 0){
            //now these is no relation found for all matrix sku
            foreach($psku as $msku){
                $this->matrix->where('web_sku', $msku)->update(['noInventoryRelation'=>1]);
            }
            info("No Product Relation Found or Deactive Warehouse");
            return response()->json(['status'=>401,"msg"=>"No Product Relation Found or Deactive Warehouse"]);
        }
        info("> 0 Product Inventory Found");

        if(count($warehouses) > 100){
            info('Max Bulk Request Limit Crossed.');
            return response()->json(['status'=> 401,"msg"=>"Max Bulk Request Limit Crossed."]);
        }
        info("< 100 Product Inventory Found");
        info("Bulk Request Count ".count($warehouses));
        $matrixConfirmed = array();
        foreach($psku as $m){
            foreach($warehouses as $p){
                $sku = $p->web_sku;
                if("$m" == "$sku"){
                    if(in_array($m, $matrixConfirmed)){

                    }else{
                        array_push($matrixConfirmed, $m);
                    }
                }
            }
        }

        // print_r($psku);
        // print_r($matrixConfirmed);
        // die;


        foreach($warehouses as $w){
            // echo $w->barcode.' LID '.$w->locationid.' PID '.$w->erplyProductID.' QTY '.$w->currentSOH.' SKU '. $w->product_sku ."<br>";
            $param = array(
                "sessionKey" => $this->api->client->sessionKey,
                "clientCode" => $this->api->client->clientCode,
                "requestName" => "saveInventoryRegistration",
                "warehouseID" => $w->erplyWarehouseID,
                "productID1" => $w->erplyProductID,
                "amount1" => $w->actualSOH,
                "price1" => $w->retailPrice1
            );
            $this->checkErplyInventoryReg($w->erplyWarehouseID, $w->erplyProductID, $w->product_sku);

            // if($rid != ''){
            //     $param['inventoryRegistrationID'] = $rid;
            //     info("Inventory Exist ". $rid);
            // }

            // checking inventory data locally and erply
            // $check = $this->inventoryRegistration->where('warehouseID', $w->erplyWarehouseID)->where('productSKU', $w->product_sku)->first();
            // if($check){
            //     //exist
            //     // $this->deleteInventoryRegistration($check->inventoryRegistrationID);
            // }
            array_push($bulkParam, $param);
        }
        // echo "deleted";
        // die;
        if(count($bulkParam) == 0){
            info('No Product Relation Found.');
            foreach($psku as $psk){
                $this->matrix->where('web_sku', $psk)->update(['noInventoryRelation' => 1]);
                info("No Relation ". $psk);
            }
            return response()->json(['status'=>401,"msg"=>"No Product Found."]);
        }

        if(count($bulkParam) > 100){
            info('Bulk Request Limit Crossed.');
            return response()->json(['status'=>401,"msg"=>"Bulk Request Limit Crossed."]);
        }

        info("Bulk Request Count ".count($bulkParam));
        // info($bulkParam);
        $bulkParam = json_encode($bulkParam, true);

        //now sending bulk requests
        $bulkRes = $this->api->sendRequest($bulkParam,$bulkp,1,0,0);

        // info($bulkRes);
        if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests'])){
            foreach($warehouses as $key => $w){
                //check same WID and PSKU exist
                if($bulkRes['requests'][$key]['status']['errorCode'] == 0){

                    $chk = $this->inventoryRegistration->where('warehouseID', $w->erplyWarehouseID)->where('productSKU', $w->product_sku)->first();
                    if($chk){
                        //than update only updatedIRID
                        InventoryRegistration::where('warehouseID', $w->erplyWarehouseID)->where('productSKU', $w->product_sku)->update(['inventoryRegistrationID'=>$bulkRes['requests'][$key]['records'][0]['inventoryRegistrationID'] ]);
                        // $this->inventoryRegistration->where('warehouseID', $w->erplyWarehouseID)->where('productSKU', $w->product_sku)->delete();
                    }else{
                        InventoryRegistration::create(['warehouseID'=>$w->erplyWarehouseID,'productSKU'=>$w->product_sku, 'inventoryRegistrationID'=>$bulkRes['requests'][$key]['records'][0]['inventoryRegistrationID']]);
                    }
                    info("IRID : ".$bulkRes['requests'][$key]['records'][0]['inventoryRegistrationID'].' PID '.$w->erplyProductID );
                }else{
                    info("Error While Saving Update Inventory Registration ".$w->product_sku ." code ". $bulkRes['requests'][$key]['status']['errorCode']);
                    $this->variation->where('product_sku', $w->product_sku)->update(['error' => $bulkRes['requests'][$key]['status']['errorCode']]);
                }
            }
            // $vv = $this->variation->where('erplyProductID', $warehouses[0]['erplyProductID'])->first();
            // print_r($vv);
            // $this->matrix->where('web_sku', $vv->web_sku)->update(['inventoryFlag' => 0]);
            foreach($matrixConfirmed as $psk){
                $this->matrix->where('web_sku', $psk)->update(['inventoryFlag' => 0]);
                info("Inventory Flag updated ".$psk);
            }
            info("Inventory Registration Save/Updated Successfully.");
            return response()->json(['status' => 200, 'response'=>"Inventory Registration Save/Updated Successfully."]);
        }

        info("Error While Saving or Updating Inventory ".$bulkRes['status']['errorCode']);
        info($bulkRes);
        return response()->json(['status' => 200, 'response'=> $bulkRes]);


        //

    }


    public function updateInventoryPrice($psku){
        $bulkParam = array();
        $bulkp = array(
            "lang" => 'eng',
            "responseType" => "json",
            "sessionKey" => $this->api->client->sessionKey,
        );
        // dd($psku);
        $warehouses = $this->getWarehousesWithRelationBySkuArrayInv($psku);
        // dd($warehouses);
        if(count($warehouses) == 0){
            //now these is no relation found for all matrix sku
            foreach($psku as $msku){
                $this->matrix->where('web_sku', $msku)->update(['noInventoryRelation'=>1]);
            }
            info("No Product Relation Found or Deactive Warehouse");
            return response()->json(['status'=>401,"msg"=>"No Product Relation Found or Deactive Warehouse"]);
        }
        info("> 0 Product Inventory Found");

        if(count($warehouses) > 100){
            info('Max Bulk Request Limit Crossed.');
            return response()->json(['status'=> 401,"msg"=>"Max Bulk Request Limit Crossed."]);
        }
        info("< 100 Product Inventory Found");
        $matrixConfirmed = array();
        foreach($psku as $m){
            foreach($warehouses as $p){
                $sku = $p->web_sku;
                if("$m" == "$sku"){
                    if(in_array($m, $matrixConfirmed)){

                    }else{
                        array_push($matrixConfirmed, $m);
                    }
                }
            }
        }

        // print_r($psku);
        // print_r($matrixConfirmed);
        // die;


        foreach($warehouses as $w){
            // echo $w->barcode.' LID '.$w->locationid.' PID '.$w->erplyProductID.' QTY '.$w->currentSOH.' SKU '. $w->product_sku ."<br>";
            $param = array(
                "sessionKey" => $this->api->client->sessionKey,
                "clientCode" => $this->api->client->clientCode,
                "requestName" => "saveInventoryRegistration",
                "warehouseID" => $w->erplyWarehouseID,
                "productID1" => $w->erplyProductID,
                "amount1" => $w->currentSOH,// $w->actualSOH,
                "price1" => 25.25//$w->retailPrice1
            );
            $rid = $this->checkErplyInventoryReg($w->erplyWarehouseID, $w->erplyProductID, $w->product_sku);

            if($rid != ''){
                $param['inventoryRegistrationID'] = $rid;
                info("Inventory Exist ". $rid);
            }

            //checking inventory data locally
            $check = $this->inventoryRegistration->where('warehouseID', $w->erplyWarehouseID)->where('productSKU', $w->product_sku)->first();
            if($check){
                //exist
                $this->deleteInventoryRegistration($check->inventoryRegistrationID);
            }
            array_push($bulkParam, $param);
        }

        if(count($bulkParam) == 0){
            info('No Product Relation Found.');
            foreach($psku as $psk){
                $this->matrix->where('web_sku', $psk)->update(['noInventoryRelation' => 1]);
                info("No Relation ". $psk);
            }
            return response()->json(['status'=>401,"msg"=>"No Product Found."]);
        }

        if(count($bulkParam) > 100){
            info('Bulk Request Limit Crossed.');
            return response()->json(['status'=>401,"msg"=>"Bulk Request Limit Crossed."]);
        }

        info("Bulk Request Cound ".count($bulkParam));
        info($bulkParam);
        $bulkParam = json_encode($bulkParam, true);

        //now sending bulk requests
        $bulkRes = $this->api->sendRequest($bulkParam,$bulkp,1,0,0);

        info($bulkRes);
        if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests'])){
            foreach($warehouses as $key => $w){
                //check same WID and PSKU exist
                if($bulkRes['requests'][$key]['status']['errorCode'] == 0){

                    $chk = $this->inventoryRegistration->where('warehouseID', $w->erplyWarehouseID)->where('productSKU', $w->product_sku)->first();
                    if($chk){
                        $this->inventoryRegistration->where('warehouseID', $w->erplyWarehouseID)->where('productSKU', $w->product_sku)->delete();
                    }

                    InventoryRegistration::create(['warehouseID'=>$w->erplyWarehouseID,'productSKU'=>$w->product_sku, 'inventoryRegistrationID'=>$bulkRes['requests'][$key]['records'][0]['inventoryRegistrationID']]);

                    info("IRID : ".$bulkRes['requests'][$key]['records'][0]['inventoryRegistrationID'].' PID '.$w->erplyProductID );
                }
            }
            $vv = $this->variation->where('erplyProductID', $warehouses[0]['erplyProductID'])->first();
            // print_r($vv);
            // $this->matrix->where('web_sku', $vv->web_sku)->update(['inventoryFlag' => 0]);
            foreach($matrixConfirmed as $psk){
                $this->matrix->where('web_sku', $psk)->update(['inventoryFlag' => 0]);
                info("Inventory Flag updated ".$psk);
            }
        }else{
            info("Error While Saving or Updating Inventory ".$bulkRes['status']['errorCode']);
            info($bulkRes);
        }

        info("Inventory Registration Save/Updated Successfully.");
        return response()->json(['status' => 200, 'response'=> $bulkRes]);
    }

    public function updateNetPrice($psku){
        $bulkParam = array();
        $bulkp = array(
            "lang" => 'eng',
            "responseType" => "json",
            "sessionKey" => $this->api->client->sessionKey,
        );
        // dd($psku);
        $warehouses = $this->getWarehousesWithRelationBySkuArrayInv($psku);
        // dd($warehouses);
        if(count($warehouses) == 0){
            //now these is no relation found for all matrix sku
            foreach($psku as $msku){
                $this->matrix->where('web_sku', $msku)->update(['noInventoryRelation'=>1]);
            }
            info("No Product Relation Found or Deactive Warehouse");
            return response()->json(['status'=>401,"msg"=>"No Product Relation Found or Deactive Warehouse"]);
        }
        info("> 0 Product Inventory Found");

        if(count($warehouses) > 100){
            info('Max Bulk Request Limit Crossed.');
            return response()->json(['status'=> 401,"msg"=>"Max Bulk Request Limit Crossed."]);
        }
        info("< 100 Product Inventory Found");
        $matrixConfirmed = array();
        foreach($psku as $m){
            foreach($warehouses as $p){
                $sku = $p->web_sku;
                if("$m" == "$sku"){
                    if(in_array($m, $matrixConfirmed)){

                    }else{
                        array_push($matrixConfirmed, $m);
                    }
                }
            }
        }

        // print_r($psku);
        // print_r($matrixConfirmed);
        // die;


        foreach($warehouses as $w){
            // echo $w->barcode.' LID '.$w->locationid.' PID '.$w->erplyProductID.' QTY '.$w->currentSOH.' SKU '. $w->product_sku ."<br>";
            $param = array(
                "sessionKey" => $this->api->client->sessionKey,
                "clientCode" => $this->api->client->clientCode,
                "requestName" => "saveProduct",
                "productID" => $w->erplyProductID,
                "netPrice" => $w->retailPrice1,
            );

            array_push($bulkParam, $param);
        }

        if(count($bulkParam) == 0){
            info('No Product Relation Found.');
            foreach($psku as $psk){
                $this->matrix->where('web_sku', $psk)->update(['noInventoryRelation' => 1]);
                info("No Relation ". $psk);
            }
            return response()->json(['status'=>401,"msg"=>"No Product Found."]);
        }

        if(count($bulkParam) > 100){
            info('Bulk Request Limit Crossed.');
            return response()->json(['status'=>401,"msg"=>"Bulk Request Limit Crossed."]);
        }

        info("Bulk Request Cound ".count($bulkParam));
        // info($bulkParam);
        $bulkParam = json_encode($bulkParam, true);

        //now sending bulk requests
        $bulkRes = $this->api->sendRequest($bulkParam,$bulkp,1,0,0);

        // info($bulkRes);
        if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests'])){
            foreach($warehouses as $key => $w){
                //check same WID and PSKU exist
                if($bulkRes['requests'][$key]['status']['errorCode'] == 0){

                }
            }
            $vv = $this->variation->where('erplyProductID', $warehouses[0]['erplyProductID'])->first();
            // print_r($vv);
            // $this->matrix->where('web_sku', $vv->web_sku)->update(['inventoryFlag' => 0]);
            // foreach($matrixConfirmed as $psk){
            //     $this->matrix->where('web_sku', $psk)->update(['inventoryFlag' => 0]);
            //     info("Inventory Flag updated ".$psk);
            // }
        }else{
            info("Error While Saving or Updating Product Net Price ".$bulkRes['status']['errorCode']);
            info($bulkRes);
        }

        info("Product Net Price Save/Updated Successfully.");
        return response()->json(['status' => 200, 'response'=> $bulkRes]);
    }



    public function checkErplyInventoryReg($wid,$pid,$psku){
        info("im called");
        //first checking from direct Inventory Registration ID if exist
        $inventory_id = InventoryRegistration::where('warehouseID', $wid)->where('productSKU', $psku)->first();
        if($inventory_id){
            info("im exist in local db");
            $param = array(
                'inventoryRegistrationID' => $inventory_id->inventoryRegistrationID,
                'sessionKey' => $this->api->client->sessionKey,
            );

            $res = $this->api->sendRequest("getInventoryRegistrations", $param,0,0,0);
            // info($res);
            if($res['status']['errorCode'] == 0 && !empty($res['records'])){
                // dd($res['records']);
                foreach($res['records'] as $val){
                    // echo $val['warehouseID'];
                    foreach($val['rows'] as $pro){
                        if($pro['productID'] == $pid){
                            // echo "Product Inventory Registered";
                            info("Inventory Exist direct RID ". $val['inventoryRegistrationID']);
                            //now deleting from erply
                            $this->deleteInventoryRegistration($val['inventoryRegistrationID']);
                            // return $val['inventoryRegistrationID'];
                        }
                    }
                }

                return '';
            }
        }

        info("im not exist in local db");
        $param = array(
            'warehouseID' => $wid,
            'sessionKey' => $this->api->client->sessionKey,
        );
        $res = $this->api->sendRequest("getInventoryRegistrations", $param,0,0,0);
        // info($res);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            // info($res['records']);
            foreach($res['records'] as $val){
                // echo $val['warehouseID'];
                foreach($val['rows'] as $pro){
                    if($pro['productID'] == $pid){
                        // echo "Product Inventory Registered";
                        info("Inventory Exist". $val['inventoryRegistrationID']);
                        $this->deleteInventoryRegistration($val['inventoryRegistrationID']);
                        return $val['inventoryRegistrationID'];
                    }
                }
            }

            return '';
        }
    }


    public function deleteInventoryRegistration($irID){

        $param = array(
            'inventoryRegistrationID' => $irID,
            'sessionKey' => $this->api->client->sessionKey,
        );
        info("Deleting Inventory...");
        $res = $this->api->sendRequest("deleteInventoryRegistration", $param,0,0,0);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            Log::info("Delete Inventory Registration Successfully.".$irID);
        }
        //now deleting from locally
        $this->inventoryRegistration->where('inventoryRegistrationID', $irID)->delete();


    }



}
