<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\AssortmentProductService;
use App\Models\StockColorSize;
use App\Models\StockDetail;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ProductAssortmentController extends Controller
{
    //
    // protected $stockvariation;
    protected $warehouse;
    protected $service;

    protected $stockdetails;

    public function __construct(StockDetail $sd, Warehouse $w, AssortmentProductService $as)
    {
        $this->stockdetails = $sd;
        $this->warehouse = $w;
        $this->service = $as;
    }


    public function productAssortment(Request $req){
        // echo "hello assortmnet";
        // die;
        $limit = $req->limit == '' ? 1 : $req->limit;
        $sku = $req->sku;
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
                ->where('newsystem_stockdetail.noInventoryRelation', 0)
                ->where('newsystem_stockdetail.web_sku', $sku)
                ->limit(1)
                ->get();
                // dd($stocks);

        }else{
            $stocks = $this->stockdetails
                // ->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
                // ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
                // ->select(['newsystem_stockdetail.*', 'newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyCatID','newsystem_internet_category.erplyGroupPending','newsystem_stock_internet_category.*'])
                // ->where('newsystem_stockdetail.matrixFlag', 1)
                // ->select([])
                ->where('newsystem_stockdetail.productAssortmentFlag', 1 )//correct it to 1
                ->where('newsystem_stockdetail.erplyPending', 0 )
                // ->where('newsystem_internet_category.erplyCatPending', '0')
                ->where('newsystem_stockdetail.noInventoryRelation', 0)
                ->where('newsystem_stockdetail.newSystemInternetActive', 1)
                ->limit($limit)
                ->get();

            $stocksWithoutRelation = $this->stockdetails
                ->join('newsystem_stock_internet_category', 'newsystem_stockdetail.web_sku', 'newsystem_stock_internet_category.web_sku')
                ->join('newsystem_internet_category', 'newsystem_internet_category.ciCategoryID', 'newsystem_stock_internet_category.ciCategoryID')
                ->select(['newsystem_stockdetail.*', 'newsystem_internet_category.erplyGroupID','newsystem_internet_category.erplyCatID','newsystem_internet_category.erplyGroupPending','newsystem_stock_internet_category.*'])
                // ->where('newsystem_stockdetail.matrixFlag', 1)
                ->where('newsystem_stockdetail.productAssortmentFlag', 1 )//correct it to 1
                ->where('newsystem_stockdetail.erplyPending', 0 )
                // ->where('newsystem_internet_category.erplyCatPending', '0')
                ->where('newsystem_stockdetail.noInventoryRelation', 0)
                ->where('newsystem_stockdetail.newSystemInternetActive', 1)
                ->limit($limit)
                ->get();
        }
        info($stocks);
        $skuwebArray = array();
        foreach($stocks as $s){
            array_push($skuwebArray, $s->web_sku);
        }
        $data = $this->getWarehousesWithRelationByBarcodeArrayInv($skuwebArray);
        // dd($data);
        if(count($data) > 0){
            return $this->service->addExtraBulkAssortmentProducts($data, "ACTIVE",$skuwebArray);
        }else{
            info("assortment no relation found");
            //updating
            foreach($stocks as $sku){
                info("No Relation to Warehouse ". $sku->web_sku);
                $this->stockdetails->where('web_sku', $sku->web_sku)->update(['noInventoryRelation'=> 1]);
            }
            return response()->json(['status' => 401, 'response'=>"Relation Not found or No Active Warehouse found for this Product"]);
        }
        // dd($skuwebArray);



    }

    protected function getWarehousesWithRelationByBarcodeArrayInv($psku){

        // dd($barcode);
        return $this->warehouse->join('current_customer_product_relation', 'current_locations.locationid', 'current_customer_product_relation.locationCode')
                // ->join('newsystem_stockdetail', 'newsystem_stockdetail.web_sku','current_customer_product_relation.web_sku')
                ->join('newsystem_stock_colour_size', 'newsystem_stock_colour_size.product_sku_2','current_customer_product_relation.product_sku')
                // ->whereIn('current_customer_product_relation.barcode', $barcode)
                ->whereIN('current_customer_product_relation.web_sku', $psku)
                ->whereIN('newsystem_stock_colour_size.web_sku', $psku)
                ->where('current_customer_product_relation.locationCode', '<>','')
                ->where('current_locations.erplyPending', 0)
                ->where('newsystem_stock_colour_size.erplyPending', 0)
                // ->where('newsystem_stock_colour_size.matrixAttributeFlag', 0)
                // ->where('newsystem_stock_colour_size.inventoryFlag', 1)
                ->where('newsystem_stock_colour_size.noRelationFlag', 0)
                ->select('current_locations.*','current_customer_product_relation.barcode', 'newsystem_stock_colour_size.erplyProductID','newsystem_stock_colour_size.currentSOH','newsystem_stock_colour_size.web_sku','newsystem_stock_colour_size.retailPrice1','newsystem_stock_colour_size.product_sku')
                ->groupBy('current_customer_product_relation.locationCode', 'current_customer_product_relation.product_sku')
                ->get();
    }


    public function getWarehousesWithRelationByBarcodeArray($barcode){
        // dd($barcode);
        return $this->warehouse->join('current_customer_product_relation', 'current_locations.locationid', 'current_customer_product_relation.locationCode')
                ->join('newsystem_stock_colour_size', 'newsystem_stock_colour_size.barcode','current_customer_product_relation.barcode')
                ->whereIn('current_customer_product_relation.barcode', $barcode)
                ->where('current_customer_product_relation.locationCode', '<>','')
                ->where('current_locations.erplyPending', 0)
                ->where('newsystem_stock_colour_size.erplyPending', 0)
                ->where('newsystem_stock_colour_size.productAssortmentFlag', 1)
                ->select('current_locations.*','current_customer_product_relation.barcode', 'newsystem_stock_colour_size.erplyProductID','newsystem_stock_colour_size.currentSOH','newsystem_stock_colour_size.retailPrice1','newsystem_stock_colour_size.product_sku')
                ->groupBy('current_locations.locationid', 'current_customer_product_relation.barcode')
                ->get();
    }
}
