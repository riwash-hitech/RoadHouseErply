<?php
namespace App\Http\Controllers\Paei\Services;

use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\MatrixProduct;
use App\Models\PAEI\AttributeCategory;
use App\Models\PAEI\VariationProduct;
use App\Models\PAEI\TempProductUpdate;

class GetProductService{

    protected $matrix;
    protected $variation;
    protected $letsLog;
    protected $api;

    public function __construct(MatrixProduct $mp, VariationProduct $vp, UserLogger $logger, EAPIService $api){
        $this->matrix = $mp;
        $this->variation = $vp;
        $this->letsLog = $logger;
        $this->api = $api;
    }

    public function saveUpdate($products){
        //   dd($products);
        info("Updated product : ".count($products));
        if(count($products) >= 1){
            info("PID : ". $products[0]["productID"]);
        }
        
        $newLastModified = 0;
        
        foreach($products as $key => $p){     
            $this->seperateCategoryBySKU($p);
            if(@$p['parentProductID'] > 0){ 

                $this->variationSaveUpdate($p, $this->api->client->clientCode);
                    
                $vProduct = VariationProduct::where("productID", $p['productID'])->first();
                if($vProduct){
                    MatrixProduct::where("productID", $vProduct->parentProductID)->update(["shopifyPendingProcess" => 1, "roadhouseStatus" => 1]);
                }

            }else{
 
                $this->matrixSaveUpdate($p, $this->api->client->clientCode);
                MatrixProduct::where("productID", $p["productID"])->update(["shopifyPendingProcess" => 1, "roadhouseStatus" => 1]);    

            }
            
            if($newLastModified < @$p["lastModified"]){  
                $newLastModified = $p["lastModified"];
            }
            
               
        }
        
        $pp = collect($products);
        $forUpdate = $pp->last();
        TempProductUpdate::where("id", 1)->update(["product" => date('Y-m-d H:i:s', $newLastModified)]);
        
        // $pp = collect($products);
        // $forUpdate = $pp->last();
        // TempProductUpdate::where("id", 1)->update(["datetime" => date('Y-m-d H:i:s', $forUpdate['added'])]);
        
        

        return response()->json(['status'=>200, 'message'=>"Product fetched Successfully."]);
    }
    
     public function saveUpdateV2($products){
        //   dd($products);
        info("Updated product : ".count($products));
        if(count($products) >= 1){
            info("PID : ". $products[0]["productID"]);
        }
        foreach($products as $key => $p){     
            $this->seperateCategoryBySKU($p);
            if($p['type'] == "MATRIX"){ 
                
                $this->matrixSaveUpdate($p, $this->api->client->clientCode);
                MatrixProduct::where("productID", $p["productID"])->update(["shopifyPendingProcess" => 1]);

            }else{
 
                    $this->variationSaveUpdate($p, $this->api->client->clientCode);
                    
                    $vProduct = VariationProduct::where("productID", $p['productID'])->first();
                    if($vProduct){
                        MatrixProduct::where("productID", $vProduct->parentProductID)->update(["shopifyPendingProcess" => 1]);
                    }

            }
            
            
        }
        
        $pp = collect($products);
        $forUpdate = $pp->last();
        TempProductUpdate::where("id", 1)->update(["datetime" => date('Y-m-d H:i:s', $forUpdate['added'])]);
        
        

        return response()->json(['status'=>200, 'message'=>"Product fetched Successfully."]);
    }
    
    //for category seperation
    public function seperateCategoryBySKU($product){
        
        $category = "";
        
        foreach($product["longAttributes"] as $att){
            if($att["attributeName"] == "category"){
                $category = $att["attributeValue"];
            }
        }
        
        
        AttributeCategory::updateOrcreate(
            [
                "erplyID" => $product["productID"]    
            ],
            [
                "erplyID" => $product["productID"],
                "sku" => $product["code"],
                "category" => trim($category) 
            ]
            
        );
        
    }

    public function saveUpdatePIM($products){

        foreach($products as $p){     
            if($p['type'] == "MATRIX"){ 
                
                $this->matrixSaveUpdatePIM($p);

            }else{

                if(!str_contains($p["code"], "PSW")){
                    $this->variationSaveUpdatePIM($p);
                }

            }
        }

        return response()->json(['status'=>200, 'message'=>"Product fetched Successfully."]);
    }

    public function saveUpdateByWebhook($product, $clientCode){
       
        // foreach($products as $p){     
        if($product['parentProductID'] > 0){ 
            
            // if(!str_contains($p["code"], "PSW") && $this->api->client->clientCode == 603303){
            //     $this->variationSaveUpdate($p);
            // }else{
                $this->variationSaveUpdate($product, $clientCode);

                $vProduct = VariationProduct::where("productID", $product['productID'])->first();
                if($vProduct){
                    MatrixProduct::where("productID", $vProduct->parentProductID)->update(["shopifyPendingProcess" => 1, "roadhouseStatus" => 1]);
                }
            // }
            

        }else{

            $this->matrixSaveUpdate($product, $clientCode);

            // $vProduct = VariationProduct::where("productID", $product['productID'])->first();
            // if($vProduct){
            MatrixProduct::where("productID", $product["productID"])->update(["shopifyPendingProcess" => 1, "roadhouseStatus" => 1]);
            // }

            

        }
        // }

        // return response()->json(['status'=>200, 'message'=>"Product fetched Successfully."]);
    }


    protected function matrixSaveUpdate($product, $clientCode){
        // info(" hello sir ". $this->api->client->clientCode);
        $old = MatrixProduct::where('clientCode', $this->api->client->clientCode)->where('productID', $product['productID'])->first();
        
        $longDesEng = trim($product['longdescENG']);
        if(trim($longDesEng) == ''){
            $longDesEng = $product['longdesc'];
        }
        
        if(trim($longDesEng) == ''){
            $longDesEng = trim($product['description']);
        }
        if(trim($longDesEng) == ''){
            $longDesEng = trim($product['descriptionENG']);
        }
        if(trim($longDesEng) == ''){
            $longDesEng =  trim($product['name']);
        }   
        
        $matrixPayload = [
            "clientCode" => $clientCode,
            "productID" => $product['productID'],
            "type" => $product['type'],
            "active" => $product['active'],
            "status" => $product['status'], 
            "name"  => trim($product['name']),
            "code"  => $product['code'],
            "code2"  => @$product['code2'],
            "code3"  => @$product['code3'],
            "supplierCode"  => @$product['supplierCode'],
            "code5"  =>  @$product['code5'],
            "code6"  =>  @$product['code6'],
            "code7"  =>  @$product['code7'],
            "code8"  =>  @$product['code8'],
            "groupID"  => $product['groupID'],
            "groupName"  => $product['groupName'],
            "price"  => @$product['price'],
            "priceWithVat"  => @$product['priceWithVat'], 
            "displayedInWebshop"  => @$product['displayedInWebshop'],
            "categoryID"  => @$product['categoryID'], 
            "categoryName"  => @$product['categoryName'], 
            "supplierID"  => @$product['supplierID'], 
            "supplierName"  => @$product['supplierName'],
            "unitID"  => @$product['unitID'],
            "unitName"  => @$product['unitName'],
            "taxFree"  => @$product['taxFree'],
            "deliveryTime"  => @$product['deliveryTime'], 
            "vatrateID"  => @$product['vatrateID'], 
            "vatrate"  => @$product['vatrate'], 
            "hasQuickSelectButton"  => @$product['hasQuickSelectButton'], 
            "isGiftCard"  => @$product['isGiftCard'], 
            "isRegularGiftCard"  => @$product['isRegularGiftCard'], 
            "nonDiscountable"  => @$product['nonDiscountable'], 
            "nonRefundable"  => @$product['nonRefundable'], 
            "manufacturerName"  => @$product['manufacturerName'], 
            "priorityGroupID"  => @$product['priorityGroupID'], 
            "countryOfOriginID"  => @$product['countryOfOriginID'], 
            "brandID"  => @$product['brandID'], 
            "brandName"  => @$product['brandName'],//today date time 
            "width"  => @$product['width'], 
            "height"  => @$product['height'], 
            "length"  => @$product['length'],// today date  
            "lengthInMinutes"  => @$product['lengthInMinutes'], 
            "setupTimeInMinutes"  => @$product['setupTimeInMinutes'], 
            "cleanupTimeInMinutes"  => @$product['cleanupTimeInMinutes'], 
            "walkInService"  => @$product['walkInService'], 
            "rewardPointsNotAllowed"  => @$product['rewardPointsNotAllowed'], 
            "nonStockProduct"  => @$product['nonStockProduct'], 
            "hasSerialNumbers"  => @$product['hasSerialNumbers'], 
            "soldInPackages"  => @$product['soldInPackages'], 
            "cashierMustEnterPrice"  => @$product['cashierMustEnterPrice'], 
            "netWeight"  => $product['netWeight'] == '' ? 0 : $product['netWeight'], 
            "grossWeight"  => $product['grossWeight'] == '' ? 0 : $product['grossWeight'], 
            "volume"  => @$product['volume'], 
            "description"  => $product['description'] == '' ? trim($product['name']) : $product['description'], 
            "longdesc"  => $product['longdesc'] ? $product['longdesc'] : '-', 
            "descriptionENG"  => $product['descriptionENG'] == '' ? trim($product['name']) : trim($product['descriptionENG']), 
            "longdescENG"  => $longDesEng, 
            "descriptionRUS"  => $product['descriptionRUS'], 
            "longdescRUS"  => $product['longdescRUS'], 
            "descriptionFIN"  => $product['descriptionFIN'], 
            "longdescFIN"  => $product['longdescFIN'], 
            "cost"  => $product['cost'], 
            "FIFOCost"  => @$product['FIFOCost'], 
            "purchasePrice"  => @$product['purchasePrice'], 
            "backbarCharges"  => @$product['backbarCharges'], 
            "added"  => date('Y-m-d H:i:s',$product['added']), 
            "addedByUsername"  => $product['addedByUsername'], 
            "lastModified"  => date('Y-m-d H:i:s', $product['lastModified']), 
            "lastModifiedByUsername"  => $product['lastModifiedByUsername'], 
            "images"  => !empty($product['images']) ? json_encode($product['images'],1) : '', 
            "warehouses"  => !empty($product['warehouses']) ? json_encode($product['warehouses'],1) : '', 
            "variationDescription"  => !empty($product['variationDescription']) ? json_encode($product['variationDescription'],1) : '', 
            "productVariations"  => !empty($product['productVariations']) ? json_encode($product['productVariations'],1) : '', 
            "variationList"  => !empty($product['variationList']) ? json_encode($product['variationList'],1) : '', 
            "parentProductID"  => @$product['parentProductID'], 
            "containerID"  => @$product['containerID'], 
            "containerName"  => @$product['containerName'], 
            "containerCode"  => @$product['containerCode'], 
            "containerAmount"  => @$product['containerAmount'], 
            "packagingType"  => $product['packagingType'], 
            "packages"  => !empty($product['packages']) ? json_encode($product['packages'],1) : '', 
            "productPackages"  => !empty($product['productPackages']) ? json_encode($product['productPackages'],1) : '', 
            "replacementProducts"  => !empty($product['replacementProducts']) ? json_encode($product['replacementProducts'],1) : '', 
            "relatedProducts"  => !empty($product['relatedProducts']) ? json_encode($product['relatedProducts'],1) : '', 
            "relatedFiles"  => !empty($product['relatedFiles']) ? json_encode($product['relatedFiles'],1) : '', 
            "productComponents"  => !empty($product['productComponents']) ? json_encode($product['productComponents'],1) : '', 
            "priceListPrice"  => @$product['priceListPrice'], 
            "priceListPriceWithVat"  => @$product['priceListPriceWithVat'],
            "priceCalculationSteps"  => !empty($product['priceCalculationSteps']) ? json_encode($product['priceCalculationSteps'],1) : '', 
            "locationInWarehouse"  => @$product['locationInWarehouse'], 
            "locationInWarehouseID"  => @$product['locationInWarehouseID'], 
            "locationInWarehouseName"  => @$product['locationInWarehouseName'], 
            "locationInWarehouseText"  => @$product['locationInWarehouseText'], 
            "reorderMultiple"  => @$product['reorderMultiple'], 
            "extraField1Title"  => @$product['extraField1Title'], 
            "extraField1ID"  => @$product['extraField1ID'], 
            "extraField1Code"  => @$product['extraField1Code'], 
            "extraField1Name"  => @$product['extraField1Name'], 
            "extraField2Title"  => @$product['extraField2Title'], 
            "extraField2ID"  => @$product['extraField2ID'], 
            "extraField2Code"  => @$product['extraField2Code'], 
            "extraField2Name"  => @$product['extraField2Name'], 
            "extraField3Title"  => @$product['extraField3Title'], 
            "extraField3ID"  => @$product['extraField3ID'], 
            "extraField3Code"  => @$product['extraField3Code'], 
            "extraField3Name"  => @$product['extraField3Name'], 
            "extraField4Title"  => @$product['extraField4Title'], 
            "extraField4ID"  => @$product['extraField4ID'], 
            "extraField4Code"  => @$product['extraField4Code'], 
            "extraField4Name"  => @$product['extraField4Name'], 
            "salesPackageClearBrownGlass"  => @$product['salesPackageClearBrownGlass'], 
            "salesPackageGreenOtherGlass"  => @$product['salesPackageGreenOtherGlass'], 
            "salesPackagePlasticPpPe"  => @$product['salesPackagePlasticPpPe'], 
            "salesPackagePlasticPet"  => @$product['salesPackagePlasticPet'], 
            "salesPackageMetalFe"  => @$product['salesPackageMetalFe'], 
            "salesPackageMetalAl"  => @$product['salesPackageMetalAl'], 
            "salesPackageOtherMetal"  => @$product['salesPackageOtherMetal'], 
            "salesPackageCardboard"  => @$product['salesPackageCardboard'], 
            "salesPackageWood"  => @$product['salesPackageWood'], 
            "groupPackagePaper"  => @$product['groupPackagePaper'], 
            "groupPackagePlastic"  => @$product['groupPackagePlastic'], 
            "groupPackageMetal"  => @$product['groupPackageMetal'], 
            "groupPackageWood"  => @$product['groupPackageWood'], 
            "transportPackageWood"  => @$product['transportPackageWood'], 
            "transportPackagePlastic"  => @$product['transportPackagePlastic'], 
            "transportPackageCardboard"  => @$product['transportPackageCardboard'],
            "registryNumber"  => @$product['registryNumber'], 
            "alcoholPercentage"  => isset($product['alcoholPercentage']) ? ($product['alcoholPercentage'] == '' ? 0 : $product['alcoholPercentage']) : 0, 
            "batches"  => @$product['batches'], 
            "exciseDeclaration"  => @$product['exciseDeclaration'], 
            "exciseFermentedProductUnder6"  => @$product['exciseFermentedProductUnder6'] == '' ? 0.0 : $product['exciseFermentedProductUnder6'], 
            "exciseWineOver6"  => @$product['exciseWineOver6'] == '' ? 0.0 : $product['exciseWineOver6'], 
            "exciseFermentedProductOver6"  => @$product['exciseFermentedProductOver6'] == '' ? 0.0 : $product['exciseFermentedProductOver6'], 
            "exciseIntermediateProduct"  => @$product['exciseIntermediateProduct'] == '' ? 0.0 : $product['exciseIntermediateProduct'], 
            "exciseOtherAlcohol"  => @$product['exciseOtherAlcohol'] == '' ? 0.0 : $product['exciseOtherAlcohol'], 
            "excisePackaging"  => @$product['excisePackaging'] == '' ? 0.0 : $product['excisePackaging'], 
            "attributes"  => !empty($product['attributes']) ? json_encode($product['attributes'],1) : '',
            "longAttributes"  => !empty($product['longAttributes']) ? json_encode($product['longAttributes'],1) : '', 
            "parameters"  => !empty($product['parameters']) ? json_encode($product['parameters'],1) : '',
            "productReplacementHistory"  => !empty($product['productReplacementHistory']) ? json_encode($product['productReplacementHistory'],1) : '',  
        ];


        if($old && $old->status == "ARCHIVED" && $product["status"] == "ACTIVE"){
            $matrixPayload["pricePending"] = 1;
            $matrixPayload["roadhousePricePending"] = 1;
        }

         

        $change = $this->matrix->updateOrCreate(
                [
                    "clientCode" => $clientCode,
                    "productID"  =>  $product['productID']
                ],
                $matrixPayload
            );
        // $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($change, true), $old  ? "Matrix Product Updated" : "Matrix Product Created");    
    }

    protected function variationSaveUpdate($product, $clientCode){
        // dd($product);

        $old = VariationProduct::where('clientCode',  $this->api->client->clientCode)->where('productID', $product['productID'])->first();
        $longDesEng = trim($product['longdescENG']);
        if(trim($longDesEng) == ''){
            $longDesEng = $product['longdesc'];
        }
        
        if(trim($longDesEng) == ''){
            $longDesEng = trim($product['description']);
        }
        if(trim($longDesEng) == ''){
            $longDesEng = trim($product['descriptionENG']);
        }
        if(trim($longDesEng) == ''){
            $longDesEng =  trim($product['name']);
        }
        
        $colorOrder = 0;
        $sizeOrder = 0;
        $dimID1 = 0;
        $dimID2 = 0;
        $vDimID1 = 0;
        $vDimID2 = 0;
        
        if(@$product["variationDescription"]){
            // echo $product['productID'];
            // die;
            if(count($product["variationDescription"]) > 0){
                
                 
                
                if(@$product["variationDescription"][0]["name"] == "Color"){
                    $colorOrder = $product["variationDescription"][0]["order"];
                    $dimID1 = $product["variationDescription"][0]["dimensionID"];
                    $vDimID1 = $product["variationDescription"][0]["variationID"];
                }

                if(@$product["variationDescription"][0]["name"] == "Size"){
                    $sizeOrder = $product["variationDescription"][0]["order"];
                    $dimID2 = $product["variationDescription"][0]["dimensionID"];
                    $vDimID2 = $product["variationDescription"][0]["variationID"];
                }
                

                
            }
             
            if(count($product["variationDescription"]) > 1){
                // $sizeOrder = $product["variationDescription"][1]["order"];
                // $dimID2 = $product["variationDescription"][1]["dimensionID"];
                // $vDimID2 = $product["variationDescription"][1]["variationID"];
                if(@$product["variationDescription"][1]["name"] == "Color"){
                    $colorOrder = $product["variationDescription"][1]["order"];
                    $dimID1 = $product["variationDescription"][1]["dimensionID"];
                    $vDimID1 = $product["variationDescription"][1]["variationID"];
                }

                if(@$product["variationDescription"][1]["name"] == "Size"){
                    $sizeOrder = $product["variationDescription"][1]["order"];
                    $dimID2 = $product["variationDescription"][1]["dimensionID"];
                    $vDimID2 = $product["variationDescription"][1]["variationID"];
                }
            }
        }
        
        info("product ID ". $product['productID']." variation dim val ". $vDimID2);
        
        $details = array(
            
                "clientCode" => $clientCode,
                "productID" => $product['productID'],
                "type" => $product['type'],
                "active" => $product['active'],
                "status" => $product['status'], 
                "name"  => trim($product['name']),
                "code"  => $product['code'],
                "code2"  => @$product['code2'],
                "code3"  => @$product['code3'],
                "supplierCode"  => @$product['supplierCode'],
                "code5"  =>  @$product['code5'],
                "code6"  =>  @$product['code6'],
                "code7"  =>  @$product['code7'],
                "code8"  =>  @$product['code8'],
                "groupID"  => $product['groupID'],
                "groupName"  => $product['groupName'],
                "price"  => @$product['price'],
                "priceWithVat"  => @$product['priceWithVat'], 
                "displayedInWebshop"  => @$product['displayedInWebshop'],
                "categoryID"  => @$product['categoryID'], 
                "categoryName"  => @$product['categoryName'], 
                "supplierID"  => @$product['supplierID'], 
                "supplierName"  => @$product['supplierName'],
                "unitID"  => @$product['unitID'],
                "unitName"  => @$product['unitName'],
                "taxFree"  => @$product['taxFree'],
                "deliveryTime"  => @$product['deliveryTime'], 
                "vatrateID"  => @$product['vatrateID'], 
                "vatrate"  => @$product['vatrate'], 
                "hasQuickSelectButton"  => @$product['hasQuickSelectButton'], 
                "isGiftCard"  => @$product['isGiftCard'], 
                "isRegularGiftCard"  => @$product['isRegularGiftCard'], 
                "nonDiscountable"  => @$product['nonDiscountable'], 
                "nonRefundable"  => @$product['nonRefundable'], 
                "manufacturerName"  => @$product['manufacturerName'], 
                "priorityGroupID"  => @$product['priorityGroupID'], 
                "countryOfOriginID"  => @$product['countryOfOriginID'], 
                "brandID"  => @$product['brandID'], 
                "brandName"  => @$product['brandName'],//today date time 
                "width"  => @$product['width'], 
                "height"  => @$product['height'], 
                "length"  => @$product['length'],// today date  
                "lengthInMinutes"  => @$product['lengthInMinutes'], 
                "setupTimeInMinutes"  => @$product['setupTimeInMinutes'], 
                "cleanupTimeInMinutes"  => @$product['cleanupTimeInMinutes'], 
                "walkInService"  => @$product['walkInService'], 
                "rewardPointsNotAllowed"  => @$product['rewardPointsNotAllowed'], 
                "nonStockProduct"  => @$product['nonStockProduct'], 
                "hasSerialNumbers"  => @$product['hasSerialNumbers'], 
                "soldInPackages"  => @$product['soldInPackages'], 
                "cashierMustEnterPrice"  => @$product['cashierMustEnterPrice'], 
                "netWeight"  => $product['netWeight'] == '' ? 0 : $product['netWeight'], 
                "grossWeight"  => $product['grossWeight'] == '' ? 0 : $product['grossWeight'], 
                "volume"  => @$product['volume'], 
                "description"  => $product['description'] == '' ? '-' : $product['description'], 
                "longdesc"  => trim($product['longdesc']) == '' ? "-" : trim($product['longdesc']), 
                // "descriptionENG"  => $product['descriptionENG'],
                "descriptionENG"  => $product['descriptionENG'] == '' ? trim($product['name']) : $product['descriptionENG'], 
                "longdescENG"  => trim($longDesEng), 
                "descriptionRUS"  => $product['descriptionRUS'], 
                "longdescRUS"  => $product['longdescRUS'], 
                "descriptionFIN"  => $product['descriptionFIN'], 
                "longdescFIN"  => $product['longdescFIN'], 
                "cost"  => $product['cost'], 
                "FIFOCost"  => @$product['FIFOCost'], 
                "purchasePrice"  => @$product['purchasePrice'], 
                "backbarCharges"  => @$product['backbarCharges'], 
                "added"  => date('Y-m-d H:i:s',$product['added']), 
                "addedByUsername"  => $product['addedByUsername'], 
                "lastModified"  => date('Y-m-d H:i:s', $product['lastModified']), 
                "lastModifiedByUsername"  => $product['lastModifiedByUsername'], 
                "images"  => !empty($product['images']) ? json_encode($product['images'],1) : '', 
                "warehouses"  => !empty($product['warehouses']) ? json_encode($product['warehouses'],1) : '', 
                "variationDescription"  => !empty($product['variationDescription']) ? json_encode($product['variationDescription'],1) : '', 
                "colorOrder" => $colorOrder,
                "sizeOrder" => $sizeOrder,
                "dimID1" => $dimID1,
                "dimID2" => $dimID2,
                "variationDimID1" => $vDimID1,
                "variationDimID2" => $vDimID2,
                "sizeOrder" => $sizeOrder,
                "productVariations"  => !empty($product['productVariations']) ? json_encode($product['productVariations'],1) : '', 
                "variationList"  => !empty($product['variationList']) ? json_encode($product['variationList'],1) : '', 
                "parentProductID"  => @$product['parentProductID'], 
                "containerID"  => @$product['containerID'], 
                "containerName"  => @$product['containerName'], 
                "containerCode"  => @$product['containerCode'], 
                "containerAmount"  => @$product['containerAmount'], 
                "packagingType"  => $product['packagingType'], 
                "packages"  => !empty($product['packages']) ? json_encode($product['packages'],1) : '', 
                "productPackages"  => !empty($product['productPackages']) ? json_encode($product['productPackages'],1) : '', 
                "replacementProducts"  => !empty($product['replacementProducts']) ? json_encode($product['replacementProducts'],1) : '', 
                "relatedProducts"  => !empty($product['relatedProducts']) ? json_encode($product['relatedProducts'],1) : '', 
                "relatedFiles"  => !empty($product['relatedFiles']) ? json_encode($product['relatedFiles'],1) : '', 
                "productComponents"  => !empty($product['productComponents']) ? json_encode($product['productComponents'],1) : '', 
                "priceListPrice"  => @$product['priceListPrice'], 
                "priceListPriceWithVat"  => @$product['priceListPriceWithVat'],
                "priceCalculationSteps"  => !empty($product['priceCalculationSteps']) ? json_encode($product['priceCalculationSteps'],1) : '', 
                "locationInWarehouse"  => @$product['locationInWarehouse'], 
                "locationInWarehouseID"  => @$product['locationInWarehouseID'], 
                "locationInWarehouseName"  => @$product['locationInWarehouseName'], 
                "locationInWarehouseText"  => @$product['locationInWarehouseText'], 
                "reorderMultiple"  => @$product['reorderMultiple'], 
                "extraField1Title"  => @$product['extraField1Title'], 
                "extraField1ID"  => @$product['extraField1ID'], 
                "extraField1Code"  => @$product['extraField1Code'], 
                "extraField1Name"  => @$product['extraField1Name'], 
                "extraField2Title"  => @$product['extraField2Title'], 
                "extraField2ID"  => @$product['extraField2ID'], 
                "extraField2Code"  => @$product['extraField2Code'], 
                "extraField2Name"  => @$product['extraField2Name'], 
                "extraField3Title"  => @$product['extraField3Title'], 
                "extraField3ID"  => @$product['extraField3ID'], 
                "extraField3Code"  => @$product['extraField3Code'], 
                "extraField3Name"  => @$product['extraField3Name'], 
                "extraField4Title"  => @$product['extraField4Title'], 
                "extraField4ID"  => @$product['extraField4ID'], 
                "extraField4Code"  => @$product['extraField4Code'], 
                "extraField4Name"  => @$product['extraField4Name'], 
                "salesPackageClearBrownGlass"  => @$product['salesPackageClearBrownGlass'], 
                "salesPackageGreenOtherGlass"  => @$product['salesPackageGreenOtherGlass'], 
                "salesPackagePlasticPpPe"  => @$product['salesPackagePlasticPpPe'], 
                "salesPackagePlasticPet"  => @$product['salesPackagePlasticPet'], 
                "salesPackageMetalFe"  => @$product['salesPackageMetalFe'], 
                "salesPackageMetalAl"  => @$product['salesPackageMetalAl'], 
                "salesPackageOtherMetal"  => @$product['salesPackageOtherMetal'], 
                "salesPackageCardboard"  => @$product['salesPackageCardboard'], 
                "salesPackageWood"  => @$product['salesPackageWood'], 
                "groupPackagePaper"  => @$product['groupPackagePaper'], 
                "groupPackagePlastic"  => @$product['groupPackagePlastic'], 
                "groupPackageMetal"  => @$product['groupPackageMetal'], 
                "groupPackageWood"  => @$product['groupPackageWood'], 
                "transportPackageWood"  => @$product['transportPackageWood'], 
                "transportPackagePlastic"  => @$product['transportPackagePlastic'], 
                "transportPackageCardboard"  => @$product['transportPackageCardboard'],
                "registryNumber"  => $product['registryNumber'], 
                "alcoholPercentage"  => isset($product['alcoholPercentage']) ? ($product['alcoholPercentage'] == '' ? 0 : $product['alcoholPercentage']) : 0, 
                "batches"  => @$product['batches'], 
                "exciseDeclaration"  => $product['exciseDeclaration'], 
                "exciseFermentedProductUnder6"  => $product['exciseFermentedProductUnder6'] == '' ? 0.0 : $product['exciseFermentedProductUnder6'], 
                "exciseWineOver6"  => @$product['exciseWineOver6'] == '' ? 0.0 : $product['exciseWineOver6'], 
                "exciseFermentedProductOver6"  => @$product['exciseFermentedProductOver6'] == '' ? 0.0 : $product['exciseFermentedProductOver6'], 
                "exciseIntermediateProduct"  => @$product['exciseIntermediateProduct'] == '' ? 0.0 : $product['exciseIntermediateProduct'], 
                "exciseOtherAlcohol"  => @$product['exciseOtherAlcohol'] == '' ? 0.0 : $product['exciseOtherAlcohol'], 
                "excisePackaging"  => @$product['excisePackaging'] == '' ? 0.0 : $product['excisePackaging'], 
                "attributes"  => !empty($product['attributes']) ? json_encode($product['attributes'],1) : '',
                "longAttributes"  => !empty($product['longAttributes']) ? json_encode($product['longAttributes'],1) : '', 
                "parameters"  => !empty($product['parameters']) ? json_encode($product['parameters'],1) : '',
                "productReplacementHistory"  => !empty($product['productReplacementHistory']) ? json_encode($product['productReplacementHistory'],1) : '',  
            );
         
        // dd($details);   

        
        if($old){
            if($old->priceWithVat != $product["priceWithVat"]){
                $details["pricePending"] = 1;
                $mtest = MatrixProduct::where("productID", $product["parentProductID"])->update(["pricePending" => 1, "roadhousePricePending" => 1]);
                info('Price Pending Status'. @$mtest->pricePending);
                info("Matrix Product Price Pending updated..........................". $product["parentProductID"]);
            }

            if(@$old->status == "ARCHIVED" && @$product["status"] == "ACTIVE"){
                $details["pricePending"] = 1;
                MatrixProduct::where("productID", $product["parentProductID"])->update(["pricePending" => 1, "roadhousePricePending" => 1, "roadhouseStatus" => 1]);
                info("Matrix Product Price Pending updated..........................". $product["parentProductID"]);
            }

            
            // VariationProduct::where
        }
        
        $change = $this->variation->updateOrCreate(
            [
                "clientCode" => $clientCode,
                "productID"  =>  $product['productID']
            ],
            $details
            
        );
        // $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($change, true), $old  ? "Variation Product Updated" : "Variation Product Created");    
    }

    protected function matrixSaveUpdatePIM($product){
        $old = $this->matrix->where('productID', $product['id'])->first();
         

        $change = $this->matrix->updateOrCreate(
                [
                    "productID"  =>  $product['id']
                ],
                [
                    "productID" => $product['id'],
                    "type" => $product['type'],
                    "active" => $product['active'],
                    "status" => $product['status'], 
                    "name"  => $product['name']['en'],
                    "code"  => $product['code'],
                    "code2"  => @$product['code2'],
                    "code3"  => @$product['code3'],
                    "supplierCode"  => @$product['supplierCode'],
                    "code5"  =>  @$product['code5'],
                    "code6"  =>  @$product['code6'],
                    "code7"  =>  @$product['code7'],
                    "code8"  =>  @$product['code8'],
                    "groupID"  => $product['group_id'],
                    "groupName"  => $product['groupName'],
                    "price"  => @$product['price'],
                    "priceWithVat"  => @$product['priceWithVat'], 
                    "displayedInWebshop"  => @$product['displayedInWebshop'],
                    "categoryID"  => @$product['categoryID'], 
                    "categoryName"  => @$product['categoryName'], 
                    "supplierID"  => @$product['supplierID'], 
                    "supplierName"  => @$product['supplierName'],
                    "unitID"  => @$product['unit_id'],
                    "unitName"  => @$product['unitName'],
                    "taxFree"  => @$product['taxFree'],
                    "deliveryTime"  => @$product['deliveryTime'], 
                    "vatrateID"  => @$product['vatrateID'], 
                    "vatrate"  => @$product['vatrate'], 
                    "hasQuickSelectButton"  => @$product['hasQuickSelectButton'], 
                    "isGiftCard"  => @$product['isGiftCard'], 
                    "isRegularGiftCard"  => @$product['isRegularGiftCard'], 
                    "nonDiscountable"  => @$product['nonDiscountable'], 
                    "nonRefundable"  => @$product['nonRefundable'], 
                    "manufacturerName"  => @$product['manufacturerName'], 
                    "priorityGroupID"  => @$product['priorityGroupID'], 
                    "countryOfOriginID"  => @$product['countryOfOriginID'], 
                    "brandID"  => @$product['brandID'], 
                    "brandName"  => @$product['brandName'],//today date time 
                    "width"  => @$product['width'], 
                    "height"  => @$product['height'], 
                    "length"  => @$product['length'],// today date  
                    "lengthInMinutes"  => @$product['lengthInMinutes'], 
                    "setupTimeInMinutes"  => @$product['setupTimeInMinutes'], 
                    "cleanupTimeInMinutes"  => @$product['cleanupTimeInMinutes'], 
                    "walkInService"  => @$product['walkInService'], 
                    "rewardPointsNotAllowed"  => @$product['rewardPointsNotAllowed'], 
                    "nonStockProduct"  => @$product['nonStockProduct'], 
                    "hasSerialNumbers"  => @$product['hasSerialNumbers'], 
                    "soldInPackages"  => @$product['soldInPackages'], 
                    "cashierMustEnterPrice"  => @$product['cashierMustEnterPrice'], 
                    "netWeight"  => $product['netWeight'] == '' ? 0 : $product['netWeight'], 
                    "grossWeight"  => $product['grossWeight'] == '' ? 0 : $product['grossWeight'], 
                    "volume"  => @$product['volume'], 
                    "description"  => $product['description'], 
                    "longdesc"  => $product['longdesc'], 
                    "descriptionENG"  => $product['description'], 
                    "longdescENG"  => $product['longdescENG'], 
                    "descriptionRUS"  => $product['descriptionRUS'], 
                    "longdescRUS"  => $product['longdescRUS'], 
                    "descriptionFIN"  => $product['descriptionFIN'], 
                    "longdescFIN"  => $product['longdescFIN'], 
                    "cost"  => $product['cost'], 
                    "FIFOCost"  => @$product['FIFOCost'], 
                    "purchasePrice"  => @$product['purchasePrice'], 
                    "backbarCharges"  => @$product['backbarCharges'], 
                    "added"  => date('Y-m-d H:i:s',$product['added']), 
                    "addedByUsername"  => $product['addedByUsername'], 
                    "lastModified"  => date('Y-m-d H:i:s', $product['lastModified']), 
                    "lastModifiedByUsername"  => $product['lastModifiedByUsername'], 
                    "images"  => !empty($product['images']) ? json_encode($product['images'],1) : '', 
                    "warehouses"  => !empty($product['warehouses']) ? json_encode($product['warehouses'],1) : '', 
                    "variationDescription"  => !empty($product['variationDescription']) ? json_encode($product['variationDescription'],1) : '', 
                    "productVariations"  => !empty($product['productVariations']) ? json_encode($product['productVariations'],1) : '', 
                    "variationList"  => !empty($product['variationList']) ? json_encode($product['variationList'],1) : '', 
                    "parentProductID"  => @$product['parentProductID'], 
                    "containerID"  => @$product['containerID'], 
                    "containerName"  => @$product['containerName'], 
                    "containerCode"  => @$product['containerCode'], 
                    "containerAmount"  => @$product['containerAmount'], 
                    "packagingType"  => $product['packagingType'], 
                    "packages"  => !empty($product['packages']) ? json_encode($product['packages'],1) : '', 
                    "productPackages"  => !empty($product['productPackages']) ? json_encode($product['productPackages'],1) : '', 
                    "replacementProducts"  => !empty($product['replacementProducts']) ? json_encode($product['replacementProducts'],1) : '', 
                    "relatedProducts"  => !empty($product['relatedProducts']) ? json_encode($product['relatedProducts'],1) : '', 
                    "relatedFiles"  => !empty($product['relatedFiles']) ? json_encode($product['relatedFiles'],1) : '', 
                    "productComponents"  => !empty($product['productComponents']) ? json_encode($product['productComponents'],1) : '', 
                    "priceListPrice"  => @$product['priceListPrice'], 
                    "priceListPriceWithVat"  => @$product['priceListPriceWithVat'],
                    "priceCalculationSteps"  => !empty($product['priceCalculationSteps']) ? json_encode($product['priceCalculationSteps'],1) : '', 
                    "locationInWarehouse"  => @$product['locationInWarehouse'], 
                    "locationInWarehouseID"  => @$product['locationInWarehouseID'], 
                    "locationInWarehouseName"  => @$product['locationInWarehouseName'], 
                    "locationInWarehouseText"  => @$product['locationInWarehouseText'], 
                    "reorderMultiple"  => $product['reorderMultiple'], 
                    "extraField1Title"  => @$product['extraField1Title'], 
                    "extraField1ID"  => @$product['extraField1ID'], 
                    "extraField1Code"  => @$product['extra_field1_id'], 
                    "extraField1Name"  => @$product['extraField1Name'], 
                    "extraField2Title"  => @$product['extraField2Title'], 
                    "extraField2ID"  => @$product['extra_field2_id'], 
                    "extraField2Code"  => @$product['extraField2Code'], 
                    "extraField2Name"  => @$product['extraField2Name'], 
                    "extraField3Title"  => @$product['extraField3Title'], 
                    "extraField3ID"  => @$product['extra_field3_id'], 
                    "extraField3Code"  => @$product['extraField3Code'], 
                    "extraField3Name"  => @$product['extraField3Name'], 
                    "extraField4Title"  => @$product['extraField4Title'], 
                    "extraField4ID"  => @$product['extra_field4_id'], 
                    "extraField4Code"  => @$product['extraField4Code'], 
                    "extraField4Name"  => @$product['extraField4Name'], 
                    "salesPackageClearBrownGlass"  => @$product['salesPackageClearBrownGlass'], 
                    "salesPackageGreenOtherGlass"  => @$product['salesPackageGreenOtherGlass'], 
                    "salesPackagePlasticPpPe"  => @$product['salesPackagePlasticPpPe'], 
                    "salesPackagePlasticPet"  => @$product['salesPackagePlasticPet'], 
                    "salesPackageMetalFe"  => @$product['salesPackageMetalFe'], 
                    "salesPackageMetalAl"  => @$product['salesPackageMetalAl'], 
                    "salesPackageOtherMetal"  => @$product['salesPackageOtherMetal'], 
                    "salesPackageCardboard"  => @$product['salesPackageCardboard'], 
                    "salesPackageWood"  => @$product['salesPackageWood'], 
                    "groupPackagePaper"  => @$product['groupPackagePaper'], 
                    "groupPackagePlastic"  => @$product['groupPackagePlastic'], 
                    "groupPackageMetal"  => @$product['groupPackageMetal'], 
                    "groupPackageWood"  => @$product['groupPackageWood'], 
                    "transportPackageWood"  => @$product['transportPackageWood'], 
                    "transportPackagePlastic"  => @$product['transportPackagePlastic'], 
                    "transportPackageCardboard"  => @$product['transportPackageCardboard'],
                    "registryNumber"  => $product['registryNumber'], 
                    "alcoholPercentage"  => isset($product['alcoholPercentage']) ? ($product['alcoholPercentage'] == '' ? 0 : $product['alcoholPercentage']) : 0, 
                    "batches"  => $product['batches'], 
                    "exciseDeclaration"  => $product['exciseDeclaration'], 
                    "exciseFermentedProductUnder6"  => $product['exciseFermentedProductUnder6'] == '' ? 0.0 : $product['exciseFermentedProductUnder6'], 
                    "exciseWineOver6"  => @$product['exciseWineOver6'] == '' ? 0.0 : $product['exciseWineOver6'], 
                    "exciseFermentedProductOver6"  => @$product['exciseFermentedProductOver6'] == '' ? 0.0 : $product['exciseFermentedProductOver6'], 
                    "exciseIntermediateProduct"  => @$product['exciseIntermediateProduct'] == '' ? 0.0 : $product['exciseIntermediateProduct'], 
                    "exciseOtherAlcohol"  => @$product['exciseOtherAlcohol'] == '' ? 0.0 : $product['exciseOtherAlcohol'], 
                    "excisePackaging"  => @$product['excisePackaging'] == '' ? 0.0 : $product['excisePackaging'], 
                    "attributes"  => !empty($product['attributes']) ? json_encode($product['attributes'],1) : '',
                    "longAttributes"  => !empty($product['longAttributes']) ? json_encode($product['longAttributes'],1) : '', 
                    "parameters"  => !empty($product['parameters']) ? json_encode($product['parameters'],1) : '',
                    "productReplacementHistory"  => !empty($product['productReplacementHistory']) ? json_encode($product['productReplacementHistory'],1) : '',  
                ]
            );
        $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($change, true), $old  ? "Matrix Product Updated" : "Matrix Product Created");    
    }

    protected function variationSaveUpdatePIM($product){

        $old = $this->variation->where('productID', $product['productID'])->first();

        $change = $this->variation->updateOrCreate(
            [
                "productID"  =>  $product['productID']
            ],
            [
                "productID" => $product['productID'],
                "type" => $product['type'],
                "active" => $product['active'],
                "status" => $product['status'], 
                "name"  => $product['name'],
                "code"  => $product['code'],
                "code2"  => @$product['code2'],
                "code3"  => @$product['code3'],
                "supplierCode"  => @$product['supplierCode'],
                "code5"  =>  @$product['code5'],
                "code6"  =>  @$product['code6'],
                "code7"  =>  @$product['code7'],
                "code8"  =>  @$product['code8'],
                "groupID"  => $product['groupID'],
                "groupName"  => $product['groupName'],
                "price"  => @$product['price'],
                "priceWithVat"  => @$product['priceWithVat'], 
                "displayedInWebshop"  => @$product['displayedInWebshop'],
                "categoryID"  => @$product['categoryID'], 
                "categoryName"  => @$product['categoryName'], 
                "supplierID"  => @$product['supplierID'], 
                "supplierName"  => @$product['supplierName'],
                "unitID"  => @$product['unitID'],
                "unitName"  => @$product['unitName'],
                "taxFree"  => @$product['taxFree'],
                "deliveryTime"  => @$product['deliveryTime'], 
                "vatrateID"  => @$product['vatrateID'], 
                "vatrate"  => @$product['vatrate'], 
                "hasQuickSelectButton"  => @$product['hasQuickSelectButton'], 
                "isGiftCard"  => @$product['isGiftCard'], 
                "isRegularGiftCard"  => @$product['isRegularGiftCard'], 
                "nonDiscountable"  => @$product['nonDiscountable'], 
                "nonRefundable"  => @$product['nonRefundable'], 
                "manufacturerName"  => @$product['manufacturerName'], 
                "priorityGroupID"  => @$product['priorityGroupID'], 
                "countryOfOriginID"  => @$product['countryOfOriginID'], 
                "brandID"  => @$product['brandID'], 
                "brandName"  => @$product['brandName'],//today date time 
                "width"  => @$product['width'], 
                "height"  => @$product['height'], 
                "length"  => @$product['length'],// today date  
                "lengthInMinutes"  => @$product['lengthInMinutes'], 
                "setupTimeInMinutes"  => @$product['setupTimeInMinutes'], 
                "cleanupTimeInMinutes"  => @$product['cleanupTimeInMinutes'], 
                "walkInService"  => @$product['walkInService'], 
                "rewardPointsNotAllowed"  => @$product['rewardPointsNotAllowed'], 
                "nonStockProduct"  => @$product['nonStockProduct'], 
                "hasSerialNumbers"  => @$product['hasSerialNumbers'], 
                "soldInPackages"  => @$product['soldInPackages'], 
                "cashierMustEnterPrice"  => @$product['cashierMustEnterPrice'], 
                "netWeight"  => $product['netWeight'] == '' ? 0 : $product['netWeight'], 
                "grossWeight"  => $product['grossWeight'] == '' ? 0 : $product['grossWeight'], 
                "volume"  => @$product['volume'], 
                "description"  => $product['description'], 
                "longdesc"  => $product['longdesc'], 
                "descriptionENG"  => $product['descriptionENG'], 
                "longdescENG"  => $product['longdescENG'], 
                "descriptionRUS"  => $product['descriptionRUS'], 
                "longdescRUS"  => $product['longdescRUS'], 
                "descriptionFIN"  => $product['descriptionFIN'], 
                "longdescFIN"  => $product['longdescFIN'], 
                "cost"  => $product['cost'], 
                "FIFOCost"  => @$product['FIFOCost'], 
                "purchasePrice"  => @$product['purchasePrice'], 
                "backbarCharges"  => @$product['backbarCharges'], 
                "added"  => date('Y-m-d H:i:s',$product['added']), 
                "addedByUsername"  => $product['addedByUsername'], 
                "lastModified"  => date('Y-m-d H:i:s', $product['lastModified']), 
                "lastModifiedByUsername"  => $product['lastModifiedByUsername'], 
                "images"  => !empty($product['images']) ? json_encode($product['images'],1) : '', 
                "warehouses"  => !empty($product['warehouses']) ? json_encode($product['warehouses'],1) : '', 
                "variationDescription"  => !empty($product['variationDescription']) ? json_encode($product['variationDescription'],1) : '', 
                "productVariations"  => !empty($product['productVariations']) ? json_encode($product['productVariations'],1) : '', 
                "variationList"  => !empty($product['variationList']) ? json_encode($product['variationList'],1) : '', 
                "parentProductID"  => @$product['parentProductID'], 
                "containerID"  => @$product['containerID'], 
                "containerName"  => @$product['containerName'], 
                "containerCode"  => @$product['containerCode'], 
                "containerAmount"  => @$product['containerAmount'], 
                "packagingType"  => $product['packagingType'], 
                "packages"  => !empty($product['packages']) ? json_encode($product['packages'],1) : '', 
                "productPackages"  => !empty($product['productPackages']) ? json_encode($product['productPackages'],1) : '', 
                "replacementProducts"  => !empty($product['replacementProducts']) ? json_encode($product['replacementProducts'],1) : '', 
                "relatedProducts"  => !empty($product['relatedProducts']) ? json_encode($product['relatedProducts'],1) : '', 
                "relatedFiles"  => !empty($product['relatedFiles']) ? json_encode($product['relatedFiles'],1) : '', 
                "productComponents"  => !empty($product['productComponents']) ? json_encode($product['productComponents'],1) : '', 
                "priceListPrice"  => @$product['priceListPrice'], 
                "priceListPriceWithVat"  => @$product['priceListPriceWithVat'],
                "priceCalculationSteps"  => !empty($product['priceCalculationSteps']) ? json_encode($product['priceCalculationSteps'],1) : '', 
                "locationInWarehouse"  => @$product['locationInWarehouse'], 
                "locationInWarehouseID"  => @$product['locationInWarehouseID'], 
                "locationInWarehouseName"  => @$product['locationInWarehouseName'], 
                "locationInWarehouseText"  => @$product['locationInWarehouseText'], 
                "reorderMultiple"  => $product['reorderMultiple'], 
                "extraField1Title"  => @$product['extraField1Title'], 
                "extraField1ID"  => @$product['extraField1ID'], 
                "extraField1Code"  => @$product['extraField1Code'], 
                "extraField1Name"  => @$product['extraField1Name'], 
                "extraField2Title"  => @$product['extraField2Title'], 
                "extraField2ID"  => @$product['extraField2ID'], 
                "extraField2Code"  => @$product['extraField2Code'], 
                "extraField2Name"  => @$product['extraField2Name'], 
                "extraField3Title"  => @$product['extraField3Title'], 
                "extraField3ID"  => @$product['extraField3ID'], 
                "extraField3Code"  => @$product['extraField3Code'], 
                "extraField3Name"  => @$product['extraField3Name'], 
                "extraField4Title"  => @$product['extraField4Title'], 
                "extraField4ID"  => @$product['extraField4ID'], 
                "extraField4Code"  => @$product['extraField4Code'], 
                "extraField4Name"  => @$product['extraField4Name'], 
                "salesPackageClearBrownGlass"  => @$product['salesPackageClearBrownGlass'], 
                "salesPackageGreenOtherGlass"  => @$product['salesPackageGreenOtherGlass'], 
                "salesPackagePlasticPpPe"  => @$product['salesPackagePlasticPpPe'], 
                "salesPackagePlasticPet"  => @$product['salesPackagePlasticPet'], 
                "salesPackageMetalFe"  => @$product['salesPackageMetalFe'], 
                "salesPackageMetalAl"  => @$product['salesPackageMetalAl'], 
                "salesPackageOtherMetal"  => @$product['salesPackageOtherMetal'], 
                "salesPackageCardboard"  => @$product['salesPackageCardboard'], 
                "salesPackageWood"  => @$product['salesPackageWood'], 
                "groupPackagePaper"  => @$product['groupPackagePaper'], 
                "groupPackagePlastic"  => @$product['groupPackagePlastic'], 
                "groupPackageMetal"  => @$product['groupPackageMetal'], 
                "groupPackageWood"  => @$product['groupPackageWood'], 
                "transportPackageWood"  => @$product['transportPackageWood'], 
                "transportPackagePlastic"  => @$product['transportPackagePlastic'], 
                "transportPackageCardboard"  => @$product['transportPackageCardboard'],
                "registryNumber"  => $product['registryNumber'], 
                "alcoholPercentage"  => isset($product['alcoholPercentage']) ? ($product['alcoholPercentage'] == '' ? 0 : $product['alcoholPercentage']) : 0, 
                "batches"  => $product['batches'], 
                "exciseDeclaration"  => $product['exciseDeclaration'], 
                "exciseFermentedProductUnder6"  => $product['exciseFermentedProductUnder6'] == '' ? 0.0 : $product['exciseFermentedProductUnder6'], 
                "exciseWineOver6"  => @$product['exciseWineOver6'] == '' ? 0.0 : $product['exciseWineOver6'], 
                "exciseFermentedProductOver6"  => @$product['exciseFermentedProductOver6'] == '' ? 0.0 : $product['exciseFermentedProductOver6'], 
                "exciseIntermediateProduct"  => @$product['exciseIntermediateProduct'] == '' ? 0.0 : $product['exciseIntermediateProduct'], 
                "exciseOtherAlcohol"  => @$product['exciseOtherAlcohol'] == '' ? 0.0 : $product['exciseOtherAlcohol'], 
                "excisePackaging"  => @$product['excisePackaging'] == '' ? 0.0 : $product['excisePackaging'], 
                "attributes"  => !empty($product['attributes']) ? json_encode($product['attributes'],1) : '',
                "longAttributes"  => !empty($product['longAttributes']) ? json_encode($product['longAttributes'],1) : '', 
                "parameters"  => !empty($product['parameters']) ? json_encode($product['parameters'],1) : '',
                "productReplacementHistory"  => !empty($product['productReplacementHistory']) ? json_encode($product['productReplacementHistory'],1) : '',  
            ]
        );
        $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($change, true), $old  ? "Variation Product Updated" : "Variation Product Created");    
    }

    public function getLastUpdateDate(){
        
        $date = TempProductUpdate::first();
        if($date){
            $l = $date->product;
            // dd($l);
            // info($l);
            // die;
            return strtotime($l) + 1; // To skip repeat call on same products
        }
        return 0;
        
        // echo "im call";
         $vlatest = $this->variation->where('clientCode',  $this->api->client->clientCode)->orderBy('lastModified', 'desc')->first();
         $mlatest = $this->matrix->where('clientCode',  $this->api->client->clientCode)->orderBy('lastModified', 'desc')->first();
        //  info($vlatest->lastModified."  ".$mlatest->lastModified);
        //  die;
        if($vlatest){
            $l = $mlatest->lastModified > $vlatest->lastModified ? $mlatest->lastModified : $vlatest->lastModified;
            // dd($l);
            // info($l);
            // die;
            return strtotime($l);
        }
        return 0;// strtotime($latest);
    }
    
    public function getProductUpdateDate(){
        $date = TempProductUpdate::first();
        if($date->datetime == '0000-00-00 00:00:00'){
            return 0;
        } 
        if($date){
            $l = $date->datetime;
            // dd($l);
            // info($l);
            // die;
            return strtotime($l);
        }
        return 0;// strtotime($latest);
    }
    
    
    
}