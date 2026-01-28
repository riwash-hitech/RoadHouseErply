<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\InventoryWriteOffs;

class GetInventoryWriteOffService{

    protected $inventory;
    protected $api;

    public function __construct(InventoryWriteOffs $c, EAPIService $api){
        $this->inventory = $c;
        $this->api = $api;
    }

    public function saveUpdate($inventories){

        foreach($inventories as $c){
            $this->saveUpdateInventoryWriteOffs($c);
        }

        return response()->json(['status'=>200, 'message'=>"Inventory Write Offs fetched Successfully."]);
    }

    protected function saveUpdateInventoryWriteOffs($product){

        $this->inventory->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "inventoryWriteOffID"  =>  $product['inventoryWriteOffID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "inventoryWriteOffID" => $product['inventoryWriteOffID'],
                    "inventoryWriteOffNo" => $product['inventoryWriteOffNo'],
                    "creatorID" => @$product['creatorID'],
                    "warehouseID" => @$product['warehouseID'],
                    "stocktakingID"  => @$product['stocktakingID'],
                    "inventoryID"  => $product['inventoryID'],
                    "recipientID"  => @$product['recipientID'],
                    "reasonID"  => @$product['reasonID'],
                    "currencyCode"  => @$product['currencyCode'],
                    "currencyRate"  =>  @$product['currencyRate'],
                    "date"  =>  @$product['date'],
                    "inventoryTransactionDate"  =>  @$product['inventoryTransactionDate'],
                    "comments"  =>  @$product['comments'],
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
