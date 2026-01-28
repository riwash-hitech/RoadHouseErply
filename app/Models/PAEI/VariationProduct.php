<?php

namespace App\Models\PAEI;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; 

class VariationProduct extends Model
{
    use HasFactory;
 
    protected $table = 'newsystem_product_variations';
    // protected $fillable = [
    //     "clientCode",
    //             "productID",
    //             "type",
    //             "active",
    //             "status",
    //             "name",
    //             "code",
    //             "code2",
    //             "code3",
    //             "supplierCode",
    //             "code5",
    //             "code6",
    //             "code7",
    //             "code8",
    //             "groupID",
    //             "groupName",
    //             "price",
    //             "priceWithVat",
    //             "displayedInWebshop",
    //             "categoryID",
    //             "categoryName",
    //             "supplierID",
    //             "supplierName",
    //             "unitID",
    //             "unitName",
    //             "taxFree",
    //             "deliveryTime",
    //             "vatrateID",
    //             "vatrate",
    //             "hasQuickSelectButton",
    //             "isGiftCard",
    //             "isRegularGiftCard",
    //             "nonDiscountable",
    //             "nonRefundable",
    //             "manufacturerName",
    //             "priorityGroupID",
    //             "countryOfOriginID",
    //             "brandID",
    //             "brandName",
    //             "width",
    //             "height",
    //             "length",
    //             "lengthInMinutes",
    //             "setupTimeInMinutes",
    //             "cleanupTimeInMinutes",
    //             "walkInService",
    //             "rewardPointsNotAllowed",
    //             "nonStockProduct",
    //             "hasSerialNumbers",
    //             "soldInPackages",
    //             "cashierMustEnterPrice",
    //             "netWeight",
    //             "grossWeight",
    //             "volume",
    //             "description",
    //             "longdesc",
    //             "descriptionENG",
    //             "longdescENG",
    //             "descriptionRUS",
    //             "longdescRUS",
    //             "descriptionFIN",
    //             "longdescFIN",
    //             "cost",
    //             "FIFOCost",
    //             "purchasePrice",
    //             "backbarCharges",
    //             "added",
    //             "addedByUsername",
    //             "lastModified",
    //             "lastModifiedByUsername",
    //             "images",
    //             "warehouses",
    //             "variationDescription",
    //             "productVariations",
    //             "variationList",
    //             "parentProductID",
    //             "containerID",
    //             "containerName",
    //             "containerCode",
    //             "containerAmount",
    //             "packagingType",
    //             "packages",
    //             "productPackages",
    //             "replacementProducts",
    //             "relatedProducts",
    //             "relatedFiles",
    //             "productComponents",
    //             "priceListPrice",
    //             "priceListPriceWithVat",
    //             "priceCalculationSteps",
    //             "locationInWarehouse",
    //             "locationInWarehouseID",
    //             "locationInWarehouseName",
    //             "locationInWarehouseText",
    //             "reorderMultiple",
    //             "extraField1Title",
    //             "extraField1ID",
    //             "extraField1Code",
    //             "extraField1Name",
    //             "extraField2Title",
    //             "extraField2ID",
    //             "extraField2Code",
    //             "extraField2Name",
    //             "extraField3Title",
    //             "extraField3ID",
    //             "extraField3Code",
    //             "extraField3Name",
    //             "extraField4Title",
    //             "extraField4ID",
    //             "extraField4Code",
    //             "extraField4Name",
    //             "salesPackageClearBrownGlass",
    //             "salesPackageGreenOtherGlass",
    //             "salesPackagePlasticPpPe",
    //             "salesPackagePlasticPet",
    //             "salesPackageMetalFe",
    //             "salesPackageMetalAl",
    //             "salesPackageOtherMetal",
    //             "salesPackageCardboard",
    //             "salesPackageWood",
    //             "groupPackagePaper",
    //             "groupPackagePlastic",
    //             "groupPackageMetal",
    //             "groupPackageWood",
    //             "transportPackageWood",
    //             "transportPackagePlastic",
    //             "transportPackageCardboard",
    //             "registryNumber",
    //             "alcoholPercentage",
    //             "batches",
    //             "exciseDeclaration",
    //             "exciseFermentedProductUnder6",
    //             "exciseWineOver6",
    //             "exciseFermentedProductOver6",
    //             "exciseIntermediateProduct",
    //             "exciseOtherAlcohol",
    //             "excisePackaging",
    //             "attributes",
    //             "longAttributes",
    //             "parameters",
    //             "productReplacementHistory",
    // ];
    protected $fillable = [];
    protected $guarded = [];
    
 

    protected function getCreatedAtAttribute($val)
    {
        return Carbon::parse($val)->setTimezone('Australia/Sydney')->toDateTimeString();
         
    }
    protected function getUpdatedAtAttribute($val)
    {
        return Carbon::parse($val)->setTimezone('Australia/Sydney')->toDateTimeString();
         
    }


    public function scopeFilter($query, $params){
        info($params['productCode']);
        if ( isset($params['productCode']) && trim($params['productCode']) !== '' )
        {
            $query->where('code', '=', $params['productCode']);//->orWhere('code2', '=', $params['productCode']);
        }

        if ( isset($params['productCode']) && trim($params['productCode']) !== '' )
        {
            $query->orWhere('code2', '=', $params['productCode']);//->orWhere('code2', '=', $params['productCode']);
        }

        if ( isset($params['productName']) && trim($params['productName'] !== '') ) {
            $query->where('description', 'LIKE', trim($params['productName']) . '%');
        }
 
        if (isset($params['status']) && trim($params['status'] !== '') ) {
            info("my status ". $params['status']);
            $query->where('status', '=',  trim($params['status']));
        }else{
            $query->where('status',  "ACTIVE");
        }
        // info($query());
        if ( isset($params['groupID']) && trim($params['groupID']) !== '' )
        {
            if($params['groupID'] > 0){
                $query->where('groupID', '=', $params['groupID']);//->orWhere('code2', '=', $params['productCode']);
            }
        }

        if ( isset($params['priceRange']) && trim($params['priceRange']) !== '' )
        {
            $range = explode(",",$params['priceRange']);
            $query->where('price', '>', $range[0])->where('price', '<', $range[1]);//->orWhere('code2', '=', $params['productCode']);
            
        }

        if ( isset($params['type']) && trim($params['type']) !== '' )
        {
            // $isVariation = $params['includeMatrixVariations'] == 1 ? "PRODUCT" : "MATRIX";
            $query->where('type', '=', $params['type']);//->orWhere('code2', '=', $params['productCode']);
            
        } 

        
        

        return $query;
    }
}
