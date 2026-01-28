<?php
namespace App\Http\Controllers\Services;

use App\Models\Campaign;
use App\Models\Warehouse;

class CampaignService{
    protected $api;
    protected $campaign;
    // protected $location;
    public function __construct(EAPIService $api, Campaign $camp)
    {
        $this->api = $api;
        $this->campaign = $camp;
    }

    public function getCampaign(){
        $param = array(
            "activeToday" => 1 
        );
        $res = $this->api->sendRequest("getCampaigns", $param);
        echo "<pre>";
        print_r($res);
    }

    public function saveCampaign($req){
        $param = array(
            "startDate" => $req->startDate,
            "endDate" => $req->endDate,
            "startTime" => $req->startTime,
            "endTime" => $req->endTime,
            "enabled" => $req->enable,
            "name" => $req->name,
            "type" => $req->type,
            "warehouseID" => $req->warehouseID,
            "warehouseIDs" => $req->warehouseIDs,
            "tierID" => $req->tierID,
            "storeGroup" => $req->storeGroup,
            "storeRegionIDs" => $req->storeRegionIDs,
            "customerGroupIDs" => $req->customerGroupIDs,
            "subsidy" => $req->subsidy,
            "subsidyValue" => $req->subsidyValue,
            "subsidyTypeID" => $req->subsidyTypeID,
            "page" => $req->page,
            "positionOnPage" => $req->positionOnPage,
            "forecastUnits" => $req->forecastUnits,
            "purchaseTotalValue" => $req->purchaseTotalValue,
            "purchaseTotalValueMax" => $req->purchaseTotalValueMax,
            "purchasedProductGroupID" => $req->purchasedProductGroupID,
            "purchasedProductCategoryID" => $req->purchasedProductCategoryID,
            "purchasedProductGroupIDs" => $req->purchasedProductGroupIDs,
            "purchasedProductCategoryIDs" => $req->purchasedProductCategoryIDs,
            "purchasedBrandIDs" => $req->purchasedBrandIDs,
            "purchasedProducts" => $req->purchasedProducts,
            "purchasedProductSubsidies" => $req->purchasedProductSubsidies,
            "purchasedAmount" => $req->purchasedAmount,
            "rewardPoints" => $req->rewardPoints,
            "priceAtLeast" => $req->priceAtLeast,
            "priceAtMost" => $req->priceAtMost,
            "percentageOffEntirePurchase" => $req->percentageOffEntirePurchase,
            "excludeDiscountedFromPercentageOffEntirePurchase" => $req->excludeDiscountedFromPercentageOffEntirePurchase,
            "excludePromotionItemsFromPercentageOffEntirePurchase" => $req->excludePromotionItemsFromPercentageOffEntirePurchase,
            "percentageOffExcludedProducts" => $req->percentageOffExcludedProducts,
            "percentageOffIncludedProducts" => $req->percentageOffIncludedProducts,
            "sumOffEntirePurchase" => $req->sumOffEntirePurchase,
            "sumOffExcludedProducts" => $req->sumOffExcludedProducts,
            "sumOffIncludedProducts" => $req->sumOffIncludedProducts,
            "specialPrice" => $req->specialPrice,
            "awardedAmount" => $req->awardedAmount,
            "awardedProductGroupID" => $req->awardedProductGroupID,
            "awardedProductCategoryID" => $req->awardedProductCategoryID,
            "awardedProductGroupIDs" => $req->awardedProductGroupIDs,
            "awardedProductCategoryIDs" => $req->awardedProductCategoryIDs,
            "awardedBrandIDs" => $req->awardedBrandIDs,
            "awardedProducts" => $req->awardedProducts,
            "awardedProductSubsidies" => $req->awardedProductSubsidies,
            "excludedProducts" => $req->excludedProducts,
            "lowestPriceItemIsAwarded" => $req->lowestPriceItemIsAwarded,
            "percentageOFF" => $req->percentageOFF,
            "discountForOneLine" => $req->discountForOneLine,
            "sumOFF" => $req->sumOFF,
            "percentageOffMatchingItems" => $req->percentageOffMatchingItems,
            "sumOffMatchingItems" => $req->sumOffMatchingItems,
            "maximumNumberOfMatchingItems" => $req->maximumNumberOfMatchingItems,
            "maximumPointsDiscount" => $req->maximumPointsDiscount,
            "customerCanUseOnlyOnce" => $req->customerCanUseOnlyOnce,
            "oncePerDay" => $req->oncePerDay,
            "isBirthdayPromotion" => $req->isBirthdayPromotion,
            "oncePerBirthday" => $req->oncePerBirthday,
            "onlyForDiscountedItems" => $req->onlyForDiscountedItems,
            "restrictOnNoWarehouses" => $req->restrictOnNoWarehouses,
            "requiresManagerOverride" => $req->requiresManagerOverride,
            "reasonID" => $req->reasonID,
            "specialUnitPrice" => $req->specialUnitPrice,
            "maxItemsWithSpecialUnitPrice" => $req->maxItemsWithSpecialUnitPrice,
            "redemptionLimit" => $req->redemptionLimit,
            "isStackable" => $req->isStackable,
        );

        //CHECKING CAMPAING BY NAME AND ACTIVE TODAY
        $chk = $this->checkByNameActiveToday($req->name);
        if($chk != ''){
            $param['campaignID'] = $chk;
        }
        $res = $this->api->sendRequest("saveCampaign",$param);
        //saving campaign to local DB
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            $campaign = $this->campaign->name = $req->name;
            $campaign = $this->campaign->erplyCampaignID = $res['records'][0]['campaignID'];
            $campaign->save();
            return response()->json(['status'=>200, "data"=>$res]);
        }
        return response()->json(['status'=>401, "data"=>$res]);
    }

    protected function checkByNameActiveToday($name){
        $param = array(
            "name" => $name,
            "activeToday" => 1,
        );
        $res = $this->api->sendRequest("getCampaigns", $param);
        // $eFlag = 0;
        $campID = '';
        foreach($res['records'] as $camp){
            if($camp->name == "$name"){
                // $eFlag = 1;
                $campID = $camp->campaignID;
            }
        }
        
        return $campID;
    }

    protected function checkByCampaignID($id){
        $param = array(
            "campaignID" => $id,
        );
        $res = $this->api->sendRequest("getCampaigns", $param);
        // $eFlag = 0;
        $campID = '';
        if($res['status']['errorCode'] == 0){
            $campID = $res['records'][0]['campaignID'];
        }
        
        return $campID;
    }

    
}