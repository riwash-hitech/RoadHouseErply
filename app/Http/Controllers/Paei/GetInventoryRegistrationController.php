<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetCustomerService;
use App\Http\Controllers\Paei\Services\GetGiftCardService;
use App\Http\Controllers\Paei\Services\GetInventoryRegistrationService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;
use App\Models\PAEI\Warehouse;
use App\Models\PAEI\ProductSoh;
use App\Models\PAEI\VariationProduct;
use App\Models\PAEI\MatrixProduct;
use Illuminate\Support\Facades\Http;


class GetInventoryRegistrationController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetInventoryRegistrationService $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }
    
    
    public function getSohOldApi(){
        info("Get Inventory SOH API Called OLD");
        $param = array(
            "orderBy" => "changedSince",
            "orderByDir" => "asc",
            "recordsOnPage" => "100",
            // "active" => 1,
            // "pageNo" => $this->page,
            "changedSince" => $this->service->getLastUpdateDate(), 
         );
         $res = $this->api->sendRequest("getInventoryRegistrations", $param);
        //  dd($res);
         if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            return $this->service->saveUpdate($res['records']);
         }
    }

    public function getInventoryRegistration(){
        ini_set('max_execution_time', 10000);
        ini_set('memory_limit', -1);
         
        info("SOH Cron Triggered");
        // where("warehouseID", 3)->get();//
        $warehouses = Warehouse::all();
        // $auth = $this->auth->earplyLogin();
        foreach ($warehouses as $warehouse) {
            $stockInHand = ProductSoh::where('erplyWarehouseID', $warehouse->warehouseID)->orderBy('lastModifiedDateTime', 'desc')->first();
            //now truncating data related to warehouse
            ProductSoh::where("erplyWarehouseID", $warehouse->warehouseID)->where("pendingProcess", 1)->update(["pendingProcess" => 0 ]);
            // dd($stockInHand);
            // if ($stockInHand) {
            //     $lastModifiedDateTime = strtotime($stockInHand->lastModifiedDateTime);
            // } else {
                $lastModifiedDateTime = 0;
            // }
            $datas = Http::asForm()->post('https://' . $this->api->client->clientCode . '.erply.com/api/', [
                'sessionKey' => $this->api->client->sessionKey,
                'clientCode' => $this->api->client->clientCode,
                'request' => 'getProductStock',
                'warehouseID' => $warehouse->warehouseID,
                'getAmountReserved' => 1,
                'getLastSoldDate' => 1,
                'changedSince' => $lastModifiedDateTime

            ]);
            info("soh update ".count($datas['records']));
             
            foreach ($datas['records'] as $data) {
                // dd($data);
                
                //first getting soh from local db
                $chk = ProductSoh::where('erplyWarehouseID', $warehouse->warehouseID)->where("erplyProductID", $data['productID'])->first();
                $isStockZero = 0;
                if($chk){

                    if($chk->erplyCurrentStockValue == 0){
                        $isStockZero = 1;
                    }

                    if($chk->erplyCurrentStockValue != ($data['amountInStock'] - $data['amountReserved']) ){
                        
                        $isMatrixPending = 0;
                        if($chk->erplyCurrentStockValue <= 0 && $data['amountInStock'] > 0){
                            $isMatrixPending = 1;
                        }
                        
                        ProductSoh::updateOrCreate(
                            [
                                'erplyWarehouseID' => $warehouse->warehouseID, 'erplyProductID' => $data['productID']
                            ],
                            [
                                'syncCompanyID' => 1,
                                'erplyClientCode' =>$this->api->client->clientCode,
                                'erplyWarehouseID' => $warehouse->warehouseID,
                                'erplyProductID' => $data['productID'],
                                'erplyCurrentStockValue' => $data['amountInStock'] - $data['amountReserved'],
                                'reservedStock' => $data['amountReserved'],
                                'totalStock' => $data['amountInStock'],
                                'lastModifiedDateTime' => $data['lastSoldDate'],
                                'pendingProcess' => 1,
                            ]
                        );
                        $vProduct = VariationProduct::where("productID", $data['productID'])->first();
                        
                       
                        
                        // echo $data['productID'];
                        // die;
                        // dd($vProduct);
                        if($vProduct){ 
                            $vProduct->shopifySohVarPending = 1;
                            $vProduct->save();

                            $payload = array(
                                "shopifySohPending" => 1,
                                "roadhouseSohStatus" => 1
                                );
                            if($isMatrixPending == 1){
                                $payload["shopifyPendingProcess"] = 1;
                                $payload["roadhouseStatus"] = 1;
                            }
                            
                            MatrixProduct::where("productID", $vProduct->parentProductID)->update($payload);
                            
                            // dd($vProduct->parentProductID);
                        }  
                        
                    } else{
                        ProductSoh::updateOrCreate(
                            [
                                'erplyWarehouseID' => $warehouse->warehouseID, 'erplyProductID' => $data['productID']
                            ],
                            [
                                'syncCompanyID' => 1,
                                'erplyClientCode' =>$this->api->client->clientCode,
                                'erplyWarehouseID' => $warehouse->warehouseID,
                                'erplyProductID' => $data['productID'],
                                'erplyCurrentStockValue' => $data['amountInStock'] - $data['amountReserved'],
                                'reservedStock' => $data['amountReserved'],
                                'totalStock' => $data['amountInStock'],
                                'lastModifiedDateTime' => $data['lastSoldDate'],
                                'pendingProcess' => 1,
                            ]
                        );
                        
                        // $vProduct = VariationProduct::where("productID", $data['productID'])->first(); 
                        // if($vProduct){ 
                        //     MatrixProduct::where("productID", $vProduct->parentProductID)->update(["shopifySohPending" => 1]); 
                        // } 
                    }   
                }else{ 
                        ProductSoh::updateOrCreate(
                        [
                            'erplyWarehouseID' => $warehouse->warehouseID, 'erplyProductID' => $data['productID']
                        ],
                        [
                            'syncCompanyID' => 1,
                            'erplyClientCode' =>$this->api->client->clientCode,
                            'erplyWarehouseID' => $warehouse->warehouseID,
                            'erplyProductID' => $data['productID'],
                            'erplyCurrentStockValue' => $data['amountInStock'] - $data['amountReserved'],
                            'reservedStock' => $data['amountReserved'],
                            'totalStock' => $data['amountInStock'],
                            'lastModifiedDateTime' => $data['lastSoldDate'],
                            'pendingProcess' => 1,
                        ]
                    );
                    
                    $vProduct = VariationProduct::where("productID", $data['productID'])->first();
                    
                    // echo $data['productID'];
                    // die;
                    // dd($vProduct);
                    if($vProduct){
                        $vProduct->shopifySohVarPending = 1;
                        $vProduct->save();
                        MatrixProduct::where("productID", $vProduct->parentProductID)->update(["shopifySohPending" => 1, "roadhouseSohStatus" => 1]);
                        // dd($vProduct->parentProductID);
                    }    
                }
                
                
                
                
            }
            
            //now handle those pending process 0 products
            $emptySOH = ProductSoh::where("erplyWarehouseID", $warehouse->warehouseID)->where("pendingProcess", 0)->get();
            foreach($emptySOH as $esoh){
                $esoh->erplyCurrentStockValue = 0;
                $esoh->pendingProcess = 2;
                $esoh->save();
                
                $vProduct = VariationProduct::where("productID", $esoh->erplyProductID)->first();
                
                // echo $data['productID'];
                // die;
                // dd($vProduct);
                if($vProduct){
                    $vProduct->shopifySohVarPending = 1;
                    $vProduct->save();
                    MatrixProduct::where("productID", $vProduct->parentProductID)->update(["shopifySohPending" => 1, "roadhouseSohStatus" => 1]);
                    // dd($vProduct->parentProductID);
                }    
                
            }
        }
        
        return response()->json(["status" => "success"]);
         
         
         
    }
}
