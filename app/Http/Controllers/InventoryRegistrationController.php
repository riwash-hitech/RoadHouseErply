<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\InventoryRegistrationService;
use App\Http\Controllers\Services\ProductPictureService;
use App\Models\ProductVariant;
use App\Models\StockColorSize;
use App\Models\StockDetail;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;

class InventoryRegistrationController extends Controller
{
    //
    use ResponseTrait;
    protected $service;
    protected $variation;
    protected $picture;
    protected $stockdetails;

    public function __construct(InventoryRegistrationService $irs, StockColorSize $p, StockDetail $sd)
    {
        $this->service = $irs;
        $this->variation = $p;
        $this->stockdetails  = $sd;
        // $this->picture = $picture;
    }

    public function saveInventory(Request $req){
         

        $limit = $req->limit == '' ? 1 : $req->limit;
        $sku = $req->sku;
        $stocks = [];
        $psku='';
        if($sku != ''){
            $stocks = $this->stockdetails->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
                ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
                ->select(['newsystem_stockdetail.*', 'newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyCatID','newsystem_internet_category.erplyGroupPending','newsystem_stock_internet_category.*'])
                // ->where('newsystem_stockdetail.matrixFlag', 1)
                // ->where('newsystem_stockdetail.erplyPending', 0 )//correct it to 1
                // ->where('newsystem_stockdetail.erplyUpdate', 0 )
                // ->where('newsystem_internet_category.erplyGroupPending', '0')
                // ->where('newsystem_internet_category.erplyCatPending', '0')
                ->where('newsystem_stockdetail.newSystemInternetActive', 1)
                ->where('newsystem_stockdetail.web_sku', $sku)
                ->limit(1)
                ->get();
                // dd($stocks);

        }else{
            $stocks = $this->stockdetails
                // ->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
                // ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
                ->join('current_customer_product_relation', 'current_customer_product_relation.web_sku', 'newsystem_stockdetail.web_sku')
                ->join('current_locations', 'current_locations.locationid', 'current_customer_product_relation.locationCode')
                // ->select(['newsystem_stockdetail.*', 'newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyCatID','newsystem_internet_category.erplyGroupPending','newsystem_stock_internet_category.*'])
                // ->where('newsystem_stockdetail.matrixFlag', 1)
                ->select(['newSystemStyleID','erplyProductID','newsystem_stockdetail.web_sku'])
                ->distinct()
                ->where('newsystem_stockdetail.inventoryFlag', 1)//correct it to 1
                ->where('current_locations.erplyPending', 0 )
                ->where('newsystem_stockdetail.noInventoryRelation', '0')
                ->where('newsystem_stockdetail.erplyPending', 0 )
                // ->where('newsystem_internet_category.erplyCatPending', '0')
                ->where('newsystem_stockdetail.newSystemInternetActive', 1)
                ->limit($limit)
                // ->toSql();
                ->get();
                // ->random($mlimit);
        }
        // dd($stocks);

        $skuwebArray = array();
        foreach($stocks as $s){
            array_push($skuwebArray, $s->web_sku);
        }
        if(count($skuwebArray) > 0){
            return $this->service->saveInventoryRegistrationBulk($skuwebArray);
        }
        return $this->successWithMessage("All Inventory Registration Synced Successfully.");
        

    }

    public function saveInventoryV2(Request $req){
        echo "hello sir";
    }

    public function updateInventoryPrice(Request $req){
        $skuArray = $this->getSkuArray($req);

        return $this->service->updateInventoryPrice($skuArray);
    }

    public function updateNetPrice(Request $req){
        $mlimit = $req->limit == '' ? 5 : $req->limit;
        $sku = $req->sku;
        $stocks = [];
        $psku='';
        if($sku != ''){
            $stocks = $this->stockdetails->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
                ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
                ->select(['newsystem_stockdetail.*', 'newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyCatID','newsystem_internet_category.erplyGroupPending','newsystem_stock_internet_category.*'])
                // ->where('newsystem_stockdetail.matrixFlag', 1)
                // ->where('newsystem_stockdetail.erplyPending', 0 )//correct it to 1
                ->where('newsystem_stockdetail.erplyUpdate', 0 )
                // ->where('newsystem_internet_category.erplyGroupPending', '0')
                // ->where('newsystem_internet_category.erplyCatPending', '0')
                ->where('newsystem_stockdetail.newSystemInternetActive', 1)
                ->where('newsystem_stockdetail.web_sku', $sku)
                ->limit(1)
                ->get();
                // dd($stocks);

        }else{
            $stocks = $this->stockdetails->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
                ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
                ->select(['newsystem_stockdetail.*', 'newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyCatID','newsystem_internet_category.erplyGroupPending','newsystem_stock_internet_category.*'])
                // ->where('newsystem_stockdetail.matrixFlag', 1)
                // ->where('newsystem_stockdetail.inventoryFlag', 1 )//correct it to 1
                ->where('newsystem_stockdetail.erplyUpdate', 0 )
                ->where('newsystem_stockdetail.noInventoryRelation', '0')
                // ->where('newsystem_internet_category.erplyCatPending', '0')
                ->where('newsystem_stockdetail.newSystemInternetActive', 1)
                ->limit($mlimit)
                ->get();
                // ->random($mlimit);
        }
        // dd($stocks);
        $skuwebArray = array();
        foreach($stocks as $s){
            array_push($skuwebArray, $s->web_sku);
        }

        return $this->service->updateNetPrice($skuwebArray);
    }


    protected function getSkuArray($req){
        $mlimit = $req->limit == '' ? 5 : $req->limit;
        $sku = $req->sku;
        $stocks = [];
        $psku='';
        if($sku != ''){
            $stocks = $this->stockdetails->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
                ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
                ->select(['newsystem_stockdetail.*', 'newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyCatID','newsystem_internet_category.erplyGroupPending','newsystem_stock_internet_category.*'])
                // ->where('newsystem_stockdetail.matrixFlag', 1)
                // ->where('newsystem_stockdetail.erplyPending', 0 )//correct it to 1
                ->where('newsystem_stockdetail.erplyUpdate', 0 )
                // ->where('newsystem_internet_category.erplyGroupPending', '0')
                // ->where('newsystem_internet_category.erplyCatPending', '0')
                ->where('newsystem_stockdetail.newSystemInternetActive', 1)
                ->where('newsystem_stockdetail.web_sku', $sku)
                ->limit(1)
                ->get();
                // dd($stocks);

        }else{
            $stocks = $this->stockdetails->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
                ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
                ->select(['newsystem_stockdetail.*', 'newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyCatID','newsystem_internet_category.erplyGroupPending','newsystem_stock_internet_category.*'])
                // ->where('newsystem_stockdetail.matrixFlag', 1)
                ->where('newsystem_stockdetail.inventoryFlag', 1 )//correct it to 1
                ->where('newsystem_stockdetail.erplyUpdate', 0 )
                ->where('newsystem_stockdetail.noInventoryRelation', '0')
                // ->where('newsystem_internet_category.erplyCatPending', '0')
                ->where('newsystem_stockdetail.newSystemInternetActive', 1)
                ->limit($mlimit)
                ->get();
                // ->random($mlimit);
        }
        // dd($stocks);
        $skuwebArray = array();
        foreach($stocks as $s){
            array_push($skuwebArray, $s->web_sku);
        }
        return $skuwebArray;
    }
}
