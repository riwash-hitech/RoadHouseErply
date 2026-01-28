<?php
namespace App\Http\Controllers\Paei\Services;

use App\Models\PAEI\Customer;
use App\Models\PAEI\MatrixProduct;
use App\Models\PAEI\ProductPictureCDN;
use App\Models\PAEI\TempProductUpdate;
use App\Models\PAEI\VariationProduct;
use Exception;

class GetProductPictureV2Service{

    protected $picture;

    public function __construct(ProductPictureCDN $c){
        $this->picture = $c;
    }

    public function saveUpdate($pictures, $isNew = 0, $page = 0){
        // dd($pictures);
        $lastModified = 0;
        foreach(@$pictures["images"] as $c){
            $this->saveUpdatePicture($c);
            if(@$c["updatedTimestamp"] > $lastModified ){
                $lastModified = @$c["updatedTimestamp"];
            }
        }
        
        if($page > 0){
            if(count(@$pictures["images"]) >= 100){
                TempProductUpdate::where("id", 1)->update(["cdn_image_page" => $page + 1 ]);    
            }else{
                if($isNew == 1){
                    TempProductUpdate::where("id", 1)->update(["cdn_image_lastmodified" => date('Y-m-d H:i:s', $lastModified), "cdn_image_page" => 1]);
                }        
            }
        }

        info("Product Picture fetched  CDN Successfully.");
        dd($pictures);
        return response()->json(['status'=>200, 'message'=>"Product Picture fetched  CDN Successfully."]);
    }

    public function saveUpdateNew ($pictures, $pageNo)
    {
        $lastModified = 0;
        foreach ($pictures as $c) {
            $this->saveUpdatePictureNew(product: $c);
            if (@$c['updatedTimestamp'] > $lastModified) {
                $lastModified = @$c['updatedTimestamp'];
            }
        }
        $lastModified = \Carbon\Carbon::createFromTimestamp($lastModified)->format('Y-m-d H:i:s');
        print_r($lastModified);

        $tempProductUpdate = TempProductUpdate::where("id", 1)->first();
        if (count($pictures) >= 100) {
            $tempProductUpdate->cdn_image_page = $pageNo + 1;
            $tempProductUpdate->cdn_next_request = 0;
        } else {
            $tempProductUpdate->cdn_image_page = 1;
            $tempProductUpdate->cdn_next_request = 1;
        }
        $tempProductUpdate->cdn_image_lastmodified = $lastModified;
        $tempProductUpdate->save();

        //info("Product Picture fetched  CDN Successfully.");
        return response()->json(['status' => 200, 'message' => 'Product Picture CDN fetched Successfully.']);
    }

    protected function saveUpdatePictureNew ($product)
    {
        $image = ProductPictureCDN::where("productPictureID", $product['id'])->first();
        if (
            $image &&
            @$image->width == $product['width'] &&
            @$image->height == $product['height'] &&
            @$image->size == $product['size'] &&
            @$image->key == $product['key'] &&
            @$image->isDeleted == $product['isDeleted']
        ) {
        } else {
            $isMatrix = 0;
            $chkMatrix = MatrixProduct::where('clientCode', $product['tenant'])->where("productID", $product['productId'])->first();
            if ($chkMatrix) $isMatrix = 1;

            $isVariation = 0;
            $chkVar = VariationProduct::where('clientCode', $product['tenant'])->where("parentProductID", '>', 0)->where('productID', $product['productId'])->first();
            if ($chkVar) $isVariation = 1;
            try {
                $this->picture->updateOrCreate(
                    [
                        "productPictureID"  =>  $product['id'],
                        "clientCode"  =>  $product['tenant'],
                    ],
                    [
                        "clientCode"  =>  $product['tenant'],
                        "productPictureID" => $product['id'],
                        "productID" => $product['productId'],
                        "parentProductID" => $isMatrix == 1 ? $product['productId'] : $chkVar->parentProductID,
                        "colourID" => $isVariation == 1 ? $chkVar->variationDimID1 : 0,
                        "sequenceNr" => @$product['sequenceNr'],
                        "SKU" => @$product['SKU'],
                        "description"  => @$product['description'],
                        "width"  => $product['width'],
                        "height"  => @$product['height'],
                        "size"  => @$product['size'],
                        "key"  => @$product['key'],
                        "isDeleted"  => @$product['isDeleted'],
                        "order"  => @$product['order'],
                        "lastModified"  => isset($product['updatedTimestamp']) == 1 ? date('Y-m-d H:i:s', $product['updatedTimestamp']) : '0000-00-00 00:00',
                        "added"  => isset($product['createdTimestamp']) == 1 ? date('Y-m-d H:i:s', $product['createdTimestamp']) : '0000-00-00 00:00',
                    ]
                );
                $productID = $product['productId'];
                if ($isVariation == 1) {
                    $productID = $chkVar->parentProductID;
                }

                MatrixProduct::where("productID", $productID)->update(
                    [
                        "shopifyImagePending" => 1,
                        "roadhouseImageStatus" => 1
                    ]
                );
            } catch (Exception $e) {
                dump($product, $chkMatrix, $chkVar);
            }
        }
    }

    protected function saveUpdatePicture($product){

        //now updating matrix image flags 
        $old = ProductPictureCDN::where("productPictureID", $product['id'])->first();
        $lmp = isset($product['updatedTimestamp']) == 1 ? date('Y-m-d H:i:s',$product['updatedTimestamp']) : '0000-00-00 00:00';

        $isMatrix = 0;
        $chkMatrix = MatrixProduct::where("productID", $product['productId'])->first();
        if($chkMatrix){
            $isMatrix = 1; 
            if($old){
                if($old->lastModified != $lmp){
                    MatrixProduct::where("productID", $product['productId'])->update(
                        [
                            "shopifyImagePending" => 1,
                            "roadhouseImageStatus" => 1
                        ]
                    );
                }
            }else{
                MatrixProduct::where("productID", $product['productId'])->update(
                    [
                        "shopifyImagePending" => 1,
                        "roadhouseImageStatus" => 1
                    ]
                );
            }
        }

        $chkVar = VariationProduct::where("parentProductID", '>', 0)->where("productID", $product['productId'])->first();
        $isVariation = 0;
        if($chkVar){
            $isVariation = 1;  
            if($old){
                if($old->lastModified != $lmp){
                    MatrixProduct::where("productID", $chkVar->parentProductID)->update(
                        [
                            "shopifyImagePending" => 1,
                            "roadhouseImageStatus" => 1
                        ]
                    );
                }
            }else{
                MatrixProduct::where("productID", $chkVar->parentProductID)->update(
                    [
                        "shopifyImagePending" => 1,
                        "roadhouseImageStatus" => 1
                    ]
                );
            }
            if(is_null(@$old->shopifyMediaID)){
                MatrixProduct::where("productID", $chkVar->parentProductID)->update(
                    [
                        "shopifyImagePending" => 1,
                        "roadhouseImageStatus" => 1
                    ]
                );
            }

        }
        try{
        $this->picture->updateOrCreate(
                [
                    "productPictureID"  =>  $product['id'],
                    "clientCode"  =>  $product['tenant'],
                ],
                [
                    "clientCode"  =>  $product['tenant'],
                    "productPictureID" => $product['id'],
                    "productID" => $product['productId'],
                    "parentProductID" => $isMatrix == 1 ? $product['productId'] : $chkVar->parentProductID,
                    "colourID" => $isVariation == 1 ? $chkVar->variationDimID1 : 0,
                    "sequenceNr" => @$product['sequenceNr'],
                    "SKU" => @$product['SKU'],
                    "description"  => @$product['description'],
                    "width"  => $product['width'],
                    "height"  => @$product['height'],
                    "size"  => @$product['size'],
                    "key"  => @$product['key'],
                    "isDeleted"  => @$product['isDeleted'],
                    "order"  => @$product['order'], 
                    "lastModified"  => isset($product['updatedTimestamp']) == 1 ? date('Y-m-d H:i:s',$product['updatedTimestamp']) : '0000-00-00 00:00', 
                    "added"  => isset($product['createdTimestamp']) == 1 ? date('Y-m-d H:i:s',$product['createdTimestamp']) : '0000-00-00 00:00', 

                ]
            );
        }catch(Exception $e){
            dd($product, $chkMatrix, $e);
        }
        
    }


    public function getLastUpdateDate($isNew = 0 ){
        if($isNew == 1){
            $latest = TempProductUpdate::where("id", 1)->first()->cdn_image_lastmodified;
            return strtotime($latest); 
        }
        // echo "im call";
         $latest = $this->picture->orderBy('added', 'desc')->first();
        if($latest){
            return strtotime($latest->added);
        }
        return 0; // strtotime($latest);
    }
    
    public function getCdnPage(){
        
        return $latest = TempProductUpdate::where("id", 1)->first()->cdn_image_page;
        
    }
}
