<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\InventoryTransfer; 

class GetInventoryTransferService{

    protected $inventory;
    protected $api;

    public function __construct(InventoryTransfer $c, EAPIService $api){
        $this->inventory = $c;
        $this->api = $api;
    }

    public function saveUpdate($inventories){

        foreach($inventories as $c){
            $this->saveUpdateInventoryTransfer($c);
        }

        return response()->json(['status'=>200, 'message'=>"Inventory Write Offs fetched Successfully."]);
    }

    protected function saveUpdateInventoryTransfer($product){

        $this->inventory->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "inventoryTransferID"  =>  $product['inventoryTransferID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "inventoryTransferID" => $product['inventoryTransferID'],
                    "inventoryTransferNo" => $product['inventoryTransferNo'],
                    "creatorID" => @$product['creatorID'],
                    "warehouseFromID" => @$product['warehouseFromID'],
                    "warehouseToID" => @$product['warehouseToID'],
                    "deliveryAddressID"  => @$product['deliveryAddressID'],
                    "currencyCode"  => @$product['currencyCode'],
                    "currencyRate"  =>  @$product['currencyRate'],
                    "type"  => $product['type'],
                    "inventoryTransferOrderID"  => @$product['inventoryTransferOrderID'],
                    "followupInventoryTransferID"  => @$product['followupInventoryTransferID'],
                    "date"  =>  @$product['date'],
                    "shippingDate"  =>  @$product['shippingDate'],
                    "shippingDateActual"  =>  @$product['shippingDateActual'],
                    "inventoryTransactionDate"  =>  @$product['inventoryTransactionDate'],
                    "status"  =>  @$product['status'],
                    "notes"  =>  @$product['notes'],
                    "added"  => date('Y-m-d H:i:s',$product['added']),
                    "confirmed"  => @$product['confirmed'],
                    "lastModified"  => isset($product['lastModified']) == 1 && isset($product['lastModified']) != null ? date('Y-m-d H:i:s',$product['added']) : "0000-00-00 00:00:00",
                    "rows"  => !empty($product['rows']) ? json_encode($product['rows'],1) : '', 
                    "attributes"  => !empty($product['attributes']) ? json_encode($product['attributes'],1) : '', 
                    
                     
                ]
            );
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
