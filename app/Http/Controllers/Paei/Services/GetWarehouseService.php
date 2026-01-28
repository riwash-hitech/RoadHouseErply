<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\BrandDiscountFromPricelist;
use App\Models\PAEI\MatrixProduct;
use App\Models\PAEI\PricelistRule;
use App\Models\PAEI\ProductGroup;
use App\Models\PAEI\VariationProduct;
use App\Models\PAEI\Warehouse;

class GetWarehouseService{

    protected $warehouse;
    protected $api;

    public function __construct(Warehouse $w, EAPIService $api){
        $this->warehouse = $w;
        $this->api = $api;
    }

    public function saveUpdate($warehouses){

        foreach($warehouses as $p){
            $this->warehouseSaveUpdateOleApi($p);
        }
        return response()->json(['status'=>200, 'message'=>"Warehouse data fetched Successfully.", "data" => $warehouses]);
        // echo "Warehouse Fetched Successfully.";
    }
    
    protected function warehouseSaveUpdateOleApi($product){
        // dd($product);
        $pricelistIds = '';
        for($i=1;$i<=5;$i++){
            $ii = $i == 1 ? '' : $i;
            // dd(@$product["pricelistID"],$product);
            if((int)@$product["pricelistID".$ii] > 0){
                $pricelistIds .= @$product["pricelistID".$ii].',';
            }
        }

        if($pricelistIds != ''){
            $pricelistIds = substr($pricelistIds, 0, -1);
        }
        // dd($pricelistIds, $product);
        $previousPricelist = [];
        if($product["warehouseID"] == 8){
            //if warehouse id is 8 then check pricelist deleted or not
            // $oldWarehouse = Warehouse::where("warehouseID", 8)->first();
            // if($oldWarehouse){
            //     if($oldWarehouse->priceListIDs != ''){
            $newPricelist = explode(",", $pricelistIds);
            //     }
            // }


             
            //if warehouse 8 then now values are updated
            //if pricelist is removed then set price pending for all matrix product associated with that pricelist
            // dump($newPricelist);
            foreach($newPricelist as $pl){
                //checking
                $chk = Warehouse::where(function($q)use($pl){
                    $q->where("priceListID", $pl)
                        ->orWhere("priceListID2", $pl)
                        ->orWhere("priceListID3", $pl)
                        ->orWhere("priceListID4", $pl)
                        ->orWhere("priceListID5", $pl);
                    })
                    ->where("warehouseID", 8)
                    ->first();
                if(!$chk){
                    //if empty then update price pending for all matrix product associated with that pricelist
                    $this->setMatrixPricePending($pl);
                } 
            }
             
        }

        Warehouse::updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "warehouseID"  =>  $product['warehouseID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "warehouseID" => $product['warehouseID'],
                    "name" => $product['name'],
                    "code" => $product['code'],
                    "storeRegionID" => $product['storeRegionID'],
                    "assortmentID"  => @$product['assortmentID'],
                    "priceListID"  => @$product['pricelistID'],
                    "priceListID2"  => @$product['pricelistID2'],
                    "priceListID3"  => @$product['pricelistID3'],
                    "priceListID4"  => @$product['pricelistID4'],
                    "priceListID5"  => @$product['pricelistID5'],
                    "priceListIDs"  => $pricelistIds,
                    "address"  => @$product['address'],
                    "address2"  => @$product['address2'],
                    "street"  => @$product['street'],
                    "city"  => @$product['city'],
                    "state"  => @$product['state'],
                    "country"  => @$product['country'],
                    "ZIPcode"  => @$product['ZIPcode'],
                    "phone"  => @$product['phone'],
                    "fax"  => @$product['fax'],
                    "email"  => @$product['email'],
                    "website"  => @$product['website'],
                    "bankName"  => @$product['bankName'],
                    "bankAccountNumber"  => @$product['bankAccountNumber'],
                    "iban"  => @$product['iban'],
                    "swift"  => @$product['swift'],
                    "onlineAppointmentsEnabled"  => @$product['onlineAppointmentsEnabled'] == true ? 1 : 0,
                    "timeZone"  => @$product['timeZone'],
                    "storeGroups"  => @$product['storeGroups'],
                    // "priceListID4"  => @$product['priceListID4'],
                    // "priceListID5"  => @$product['priceListID5'],
                    "defaultCustomerGroupID"  => @$product['defaultCustomerGroupID'],
                    "receiptAddressID"  => @$product['receiptAddressID'],
                    "attributes"  => !empty($product['attributes']) ? json_encode($product['attributes'], true) : '',
                    // "added"  =>  date('Y-m-d H:i:s',$product['added']),
                    // "addedBy" => $product['addedBy'],
                    // "changed" => date('Y-m-d H:i:s',$product['changed']),
                    // "changedBy" => $product['changedBy'],

                ]
            );
        
        
    }


    private function setMatrixPricePending($pricelistID){

        //first getting all productID from this pricelist
        $specialPrice = PricelistRule::where("pricelistID", $pricelistID)->pluck("productID")->toArray();
        $variationParent = VariationProduct::whereIn("productID", $specialPrice)->where("parentProductID", '>', 0)->pluck("parentProductID")->toArray();

        //now updating price list
        MatrixProduct::whereIn("productID", $specialPrice)->update(["pricePending" => 1, "roadhousePricePending" => 1]);
        MatrixProduct::whereIn("productID", $variationParent)->update(["pricePending" => 1 , "roadhousePricePending" => 1]);

        //for brand discount product
        $brand = BrandDiscountFromPricelist::where("priceListID", $pricelistID)->first();
        if($brand){
            MatrixProduct::where("brandID", $brand->brandID)->update(["pricePending" => 1 , "roadhousePricePending" => 1]);
        }

    }

    protected function warehouseSaveUpdate($product){

        $this->warehouse->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "warehouseID"  =>  $product['id']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "warehouseID" => $product['id'],
                    "name" => $product['name']['en'],
                    "code" => $product['code'],
                    "storeRegionID" => $product['storeRegionId'],
                    "assortmentID"  => @$product['assortmentID'],
                    "priceListID"  => @$product['priceListID'],
                    "priceListID2"  => @$product['priceListID2'],
                    "priceListID3"  => @$product['priceListID3'],
                    "order_sw"  => @$product['order'],
                    "phone"  => @$product['phone'],
                    "fax"  => @$product['fax'],
                    "email"  => @$product['email'],
                    "website"  => @$product['website'],
                    "bankName"  => @$product['bankName'],
                    "bankAccountNumber"  => @$product['bankAccountNumber'],
                    "iban"  => @$product['iban'],
                    "swift"  => @$product['swift'],
                    "onlineAppointmentsEnabled"  => @$product['onlineAppointmentsEnabled'] == true ? 1 : 0,
                    "timeZone"  => @$product['timeZone'],
                    "storeGroups"  => @$product['storeGroups'],
                    "priceListID4"  => @$product['priceListID4'],
                    "priceListID5"  => @$product['priceListID5'],
                    "defaultCustomerGroupID"  => @$product['defaultCustomerGroupID'],
                    "receiptAddressID"  => @$product['receiptAddressID'],
                    "added"  =>  date('Y-m-d H:i:s',$product['added']),
                    "addedBy" => $product['addedBy'],
                    "changed" => date('Y-m-d H:i:s',$product['changed']),
                    "changedBy" => $product['changedBy'],

                ]
            );
    }


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->warehouse->where('clientCode', $this->api->client->clientCode)->orderBy('added', 'desc')->first();
        if($latest){
            return strtotime($latest->added);
        }
        return 0;// strtotime($latest);
    }
}
