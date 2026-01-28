<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\MatrixDimension;
use App\Models\PAEI\MatrixDimensionVariation;
use App\Models\PAEI\MatrixProduct;
use App\Models\PAEI\VariationProduct;

class GetDimensionService{

    protected $dimension;
    protected $variation;
    protected $api;

    public function __construct(MatrixDimension $c, MatrixDimensionVariation $variation, EAPIService $api){
        $this->dimension = $c;
        $this->variation = $variation;
        $this->api = $api;
    }

    public function saveUpdate($dimensions){

        foreach($dimensions as $c){
            $this->saveUpdateDimension($c);
            //saving variation value
            if(@$c['variations']){
                $this->saveUpdateDimensionValue(@$c['variations'], $c['dimensionID']);
            }
            
        }

        return response()->json(['status'=>200, 'message'=>"Dimension fetched Successfully."]);
    }

    protected function saveUpdateDimension($product){

        $this->dimension->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "dimensionID"  =>  $product['dimensionID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "dimensionID" => $product['dimensionID'],
                    "name" => $product['name'],
                    "active" => $product['active'],
                    "lastModified"  => isset($product['lastModified']) == 1 ? date('Y-m-d H:i:s',$product['lastModified']) : '0000-00-00 00:00',
                    "added"  => isset($product['added']) == 1 ? date('Y-m-d H:i:s',$product['added']) : '0000-00-00 00:00',
                     
                ]
            );
    }

    protected function saveUpdateDimensionValue($variation, $pid){
        foreach($variation as $dimVal){


            //first checking dimension value changed
            //if changed
            //first get variation product having particular dimension id color or size 
            //group by parent product id
            //and update shopify pending to all matrix product
            
            $this->handleVariationDetailsUpdate($pid, $dimVal);


            $this->variation->where('clientCode', $this->api->client->clientCode)->where('dimensionID', $pid)->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "variationID"  =>  $dimVal['variationID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "variationID" => $dimVal['variationID'],
                    "dimensionID" => $pid,
                    "name" => $dimVal['name'],
                    "code" => $dimVal['code'],
                    "order" => $dimVal['order'],
                    "active" => $dimVal['active'],
                    "lastModified"  => isset($dimVal['lastModified']) == 1 ? date('Y-m-d H:i:s',$dimVal['lastModified']) : '0000-00-00 00:00',
                    "added"  => isset($dimVal['added']) == 1 ? date('Y-m-d H:i:s',$dimVal['added']) : '0000-00-00 00:00',
                     
                ]
            );
        }
    }

    private function handleVariationDetailsUpdate($pid, $dimVal){
        $check = $this->variation->where('clientCode', $this->api->client->clientCode)->where('dimensionID', $pid)->where("variationID", $dimVal['variationID'])->first();
            if($check){
                
                //now comparing filed values
                $oldVal = $check->name."_".$check->order."_".$check->active;
                $newVal = $dimVal['name']."_".$dimVal['order']."_".$dimVal['active'];
                if($oldVal != $newVal){
                    //now getting variation product related to dim variation id
                    if($pid == 1){
                        $products = VariationProduct::where("clientCode", $this->api->client->clientCode)->where("variationDimID1", $dimVal['variationID'])->groupBy("parentProductID")->get();
                        foreach($products as $pro){
                            MatrixProduct::where("clientCode", $this->api->client->cientCode)->where("productID", $pro->parentProductID)
                            ->update(
                                [
                                    "shopifyPendingProcess" => 1
                                ]
                            );
                        }
                    }else{
                        $products = VariationProduct::where("clientCode", $this->api->client->clientCode)->where("variationDimID2", $dimVal['variationID'])->groupBy("parentProductID")->get();
                        foreach($products as $pro){
                            MatrixProduct::where("clientCode", $this->api->client->cientCode)->where("productID", $pro->parentProductID)
                            ->update(
                                [
                                    "shopifyPendingProcess" => 1
                                ]
                            );
                        }
                    }
                }
                
            }
    }


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->dimension->orderBy('lastModified', 'desc')->first();
        if($latest){
            return strtotime($latest->lastModified);
        }
        return 0;// strtotime($latest);
    }
}
