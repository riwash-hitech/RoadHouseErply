<?php
namespace App\Http\Controllers\Paei\Services;

use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\GiftCard;
use App\Models\PAEI\InventoryRegistration;
use App\Models\PAEI\InventoryRegistrationLine;

class GetInventoryRegistrationService{

    protected $inventory;
    protected $api;
    protected $letsLog;

    public function __construct(InventoryRegistration $c, EAPIService $api, UserLogger $logger){
        $this->inventory = $c;
        $this->api = $api;
        $this->letsLog = $logger;
    }

    public function saveUpdate($customers){
        if(count($customers) <1){
            return response("All SOH Syncced Erply to Synccare.");
        }
        // dd($customers);
        foreach($customers as $c){
            $this->saveUpdateInventoryRegistration($c);
            //now saving lines
            if(@$c["rows"]){
                if(count($c["rows"]) > 0){
                    $this->saveUpdateLines($c["rows"], $c["warehouseID"]);
                }    
            }
            
        }

        return response()->json(['status'=>200, 'message'=>"Inventory Registration fetched Successfully."]);
    }

    protected function saveUpdateInventoryRegistration($product){

        //for log
        // $old = $this->inventory->where('clientCode',  $this->api->client->clientCode)->where('inventoryRegistrationID', $product['inventoryRegistrationID'])->first();

        $change = $this->inventory->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "inventoryRegistrationID"  =>  $product['inventoryRegistrationID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "inventoryRegistrationID" => $product['inventoryRegistrationID'],
                    "inventoryRegistrationNo" => $product['inventoryRegistrationNo'],
                    "creatorID" => @$product['creatorID'],
                    "warehouseID" => @$product['warehouseID'],
                    "stocktakingID"  => @$product['stocktakingID'],
                    "inventoryID"  => $product['inventoryID'],
                    "supplierID"  => @$product['supplierID'],
                    "reasonID"  => @$product['reasonID'],
                    "currencyCode"  => @$product['currencyCode'],
                    "currencyRate"  =>  @$product['currencyRate'],
                    "date"  =>  @$product['date'],
                    "inventoryTransactionDate"  =>  @$product['inventoryTransactionDate'] ? @$product['inventoryTransactionDate'] : "0000-00-00 00:00:00",
                    "cause"  =>  @$product['cause'],
                    "added"  => date('Y-m-d H:i:s',$product['added']),
                    "confirmed"  => @$product['confirmed'],
                    "lastModified"  => isset($product['lastModified']) == 1 && isset($product['lastModified']) != null ? date('Y-m-d H:i:s',$product['added']) : "0000-00-00 00:00:00",
                    "rows"  => !empty($product['rows']) ? json_encode($product['rows'],1) : '', 
                    "attributes"  => !empty($product['attributes']) ? json_encode($product['attributes'],1) : '', 
                    
                     
                ]
            );
            // $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($change, true), $old  ? "Inventory Registration Updated" : "Inventory Registration Created");        
    }

    
    protected function saveUpdateLines($rows, $warehouse){

        foreach($rows as $row){
            $details = array(
                "clientCode" => $this->api->client->clientCode,
                "warehouse" => $warehouse,
                "productID" => $row["productID"],
                "price" => $row["price"],
                "amount" => $row["amount"],
            );

            InventoryRegistrationLine::updateOrcreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "warehouse" => $warehouse,
                    "productID" => $row["productID"],
                ],
                $details
            );
        }
    }
    

    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->inventory->orderBy('added', 'desc')->first();
        if($latest){
            return strtotime($latest->added);
        }
        return 0;// strtotime($latest);
    }
}
