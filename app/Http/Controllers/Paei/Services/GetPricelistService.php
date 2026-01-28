<?php

namespace App\Http\Controllers\Paei\Services;

use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\BrandDiscountFromPricelist;
use App\Models\PAEI\MatrixProduct;
use App\Models\PAEI\Payment;
use App\Models\PAEI\Pricelist;
use App\Models\PAEI\PricelistRule;
use App\Models\PAEI\TempProductUpdate;
use App\Models\PAEI\VariationProduct;
use App\Models\PAEI\Warehouse;

class GetPricelistService
{

    protected $pricelist;
    protected $letsLog;
    protected $api;

    public function __construct(Pricelist $c, UserLogger $logger, EAPIService $api)
    {
        $this->pricelist = $c;
        $this->letsLog = $logger;
        $this->api = $api;
    }

    public function saveUpdate($pricelists)
    {
        // Pricelist::where("priceListID", '>', 0)->update(["isDeleted", 1]);

        $pricelistIds = '';
        $latest = 0;
        foreach ($pricelists as $key => $c) {
            if ($latest < $c["lastModified"]) {
                $latest = $c["lastModified"];
            }
            $this->saveUpdatePricelist($c);
            $pricelistIds .= $key > 0 ? ',' . $c["pricelistID"] : $c["pricelistID"];
        }

        //now updating lastmodified datetime

        $chk = TempProductUpdate::where("id", 1)->first()->pricelist;
        if ($chk < date('Y-m-d H:i:s', $latest)) {
            TempProductUpdate::where("id", 1)->update(["pricelist" => date('Y-m-d H:i:s', $latest)]);
        }

        //now calling get discount by brands
        // dd($pricelistIds);

        $param = array(
            "priceListIDs" => $pricelistIds
        );

        $res = $this->api->sendRequest("getBrandDiscountsFromPriceLists", $param);
        if ($res["status"]["errorCode"] == 0) {
            dump('Brand Discount found for the pricelist ids !!!', $pricelistIds);
            $currentDate = date('Y-m-d H:i:s');
            foreach (explode(",", $pricelistIds) as $pid) {
                // now deleting all price list
                BrandDiscountFromPricelist::where("clientCode", $this->api->client->clientCode)->where("priceListID", $pid)->where("isDeleted", 0)->update(["isDeleted" => 1]);
            }
            dump('Brand Discount : ', $res);
            foreach (@$res["records"] as $rec) {
                //check brand discount exist
                $check = BrandDiscountFromPricelist::where("clientCode", $this->api->client->clientCode)->where('priceListID', $rec["priceListID"])
                    ->first();

                BrandDiscountFromPricelist::updateOrcreate(
                    [
                        "clientCode" => $this->api->client->clientCode,
                        "priceListID" => $rec["priceListID"], 
                    ],
                    [
                        "clientCode" => $this->api->client->clientCode,
                        "priceListID" => $rec["priceListID"],
                        "brandID" => $rec["brandID"],
                        "discount" => $rec["discount"],
                        "isDeleted" => 0
                    ]
                );

                $isNew = 0;
                if ($check && ($check->isDeleted == 1 || $check->discount != $rec["discount"])) {
                    $isNew = 1;
                }
                if (!$check) {
                    $isNew = 1;
                }

                $checkPricelist = Pricelist::where("clientCode", $this->api->client->clientCode)->where('pricelistID', $rec["priceListID"])->first();
                if ($checkPricelist && $checkPricelist->synccareStatus == 'ACTIVE' && $isNew == 1) {
                    $this->setPricelistPendingBrandDiscountForShopify($check);
                }
            }
            //now getting deleted brand discount
            dump('Current Date time :', $currentDate);
            $deletedBrandDis = BrandDiscountFromPricelist::where("clientCode", $this->api->client->clientCode)->where("isDeleted", 1)->where("updated_at", '>=', $currentDate)->get();
            foreach ($deletedBrandDis as $bd) {
                MatrixProduct::where("brandID", $bd->brandID)->update(["pricePending" => 1, "roadhousePricePending" => 1]);
            }
        }

        // dd($res);
        return response()->json(['status' => 200, 'message' => "Pricelist fetched Successfully."]);
    }

    protected function saveUpdatePricelist($product)
    { 
        Pricelist::updateOrCreate(
            [
                "clientCode" => $this->api->client->clientCode,
                "pricelistID"  =>  $product['pricelistID']
            ],
            [
                "clientCode" => $this->api->client->clientCode,
                'pricelistID' => @$product['pricelistID'],
                'name' => @$product['name'],
                'startDate' => @$product['startDate'],
                'endDate' => @$product['endDate'],
                'active' => @$product['active'],
                'isDeleted' => 0,
                'type' => @$product['type'],
                'pricelistRules' => '', // !empty($product['pricelistRules']) ? json_encode($product['pricelistRules'],1) : '',  
                'addedByUserName' => @$product['addedByUserName'],
                'lastModifiedByUserName' => @$product['lastModifiedByUserName'],
                "attributes" => !empty($product['attributes']) ? json_encode($product['attributes'], 1) : '',
                "added" =>  date('Y-m-d H:i:s', $product['added']),
                "lastModified" => date('Y-m-d H:i:s', $product['lastModified']),
                "pendingProcess" => 1
            ]
        );
        $pricelistInfo = Pricelist::where('pricelistID', $product['pricelistID'])->first();
        // $currentDate = date('Y-m-d H:i:s');

        // Disabled by Suraj
        // pending flag 10 means temporary deleted
        // PricelistRule::where("clientCode", $this->api->client->clientCode)->where("pricelistID", @$product['pricelistID'])->where("isDeleted", 0)->update(["pendingProcess" => 10]);

        if (@$product["pricelistRules"]) {
            foreach (@$product['pricelistRules'] as $rule) {
                $isNew = 0;
                //first getting old data if exist
                $oldPriceRule = PricelistRule::where("clientCode", $this->api->client->clientCode)
                    ->where("pricelistID", $product['pricelistID'])
                    ->where("productID", $rule["id"])
                    ->first();
                $details = [
                    "clientCode" => $this->api->client->clientCode,
                    "pricelistID"  =>  $product['pricelistID'],
                    "ruleID" => $rule["ruleID"],
                    "productID" => $rule["id"], // Product ID or Product Group ID
                    "type" => $rule["type"], 
                    "discountPercent" => @$rule["discountPercent"],
                    "price" => @$rule["price"],
                    "priceWithVat" => @$rule["priceWithVat"], 
                ];

                if ($oldPriceRule) {
                    // Price list rule exist 
                    // If price/discountPercent is same then not to update pending to 1
                    $details["isDeleted"] = 0;

                    $isPriceChanged = $oldPriceRule->price != @$rule["price"];
                    $isDiscountChanged = $oldPriceRule->discountPercent != @$rule["discountPercent"];
                    if ($oldPriceRule->type == "PRODUCT" && ($oldPriceRule->isDeleted == 1 || $isPriceChanged)) {
                        // if price changed then update pending process
                        $isNew = 1;
                        $details["pendingProcess"] = 1;
                    } else if ($oldPriceRule->type == "PRODGROUP" && ($oldPriceRule->isDeleted == 1 || $isDiscountChanged)) {
                        // If discount changed then update pending process
                        $isNew = 1;
                        $details["pendingProcess"] = 1;
                    } else {
                         if ($oldPriceRule->isDeleted == 1) {
                            // if price rule deleted previously the update pending process 1
                            $details["pendingProcess"] = 1;
                        }
                    }

                    PricelistRule::updateOrcreate(
                        [
                            "clientCode" => $this->api->client->clientCode,
                            "pricelistID"  =>  $product['pricelistID'],
                            "productID" => $rule["id"]
                        ],
                        $details
                    );
                } else {
                    PricelistRule::updateOrcreate(
                        [
                            "clientCode" => $this->api->client->clientCode,
                            "pricelistID"  =>  $product['pricelistID'],
                            "productID" => $rule["id"]
                        ],
                        $details
                    );
                    $isNew = 1;
                }
                if ($pricelistInfo->synccareStatus == 'ACTIVE' && $isNew == 1) {
                    $this->updateMatrixProductPricePending($rule["id"], 1, $rule["type"]);
                }
            }
        }

        // Disabled by Suraj since pendingProcess doesn't become 10 at all
        // $checkDeletedProductFromPriceList = PricelistRule::where("clientCode", $this->api->client->clientCode)
        //     ->where("pricelistID", @$product['pricelistID'])
        //     ->where("pendingProcess", 10)
        //     // ->where("updated_at", '>=', $currentDate)
        //     ->first();
        // if ($checkDeletedProductFromPriceList) {
        //     //now check pricelist active 
        //     $deletedPricelistRuleProductIDs = PricelistRule::where("clientCode", $this->api->client->clientCode)
        //         ->where("pricelistID", @$product['pricelistID'])
        //         ->where("pendingProcess", 10)
        //         ->pluck('productID')
        //         ->toArray();
        //     PricelistRule::where('pricelistID', $product['pricelistID'])->whereIn('productID', $deletedPricelistRuleProductIDs)->update(['isDeleted' => 1, 'pendingProcess' => 0]);
        //     if ($pricelistInfo && $pricelistInfo->synccareStatus == 'ACTIVE') {
        //         MatrixProduct::whereIn('productID', $deletedPricelistRuleProductIDs)->update(['roadhousePricePending' => 1, 'pricePending' => 1]);
        //         $varParentIDs = VariationProduct::whereIn('productID', $deletedPricelistRuleProductIDs)->pluck('parentProductID')->toArray();
        //         MatrixProduct::whereIn('productID', $varParentIDs)->update(['roadhousePricePending' => 1, 'pricePending' => 1]);
        //     }
        // }

        // foreach ($deletedProductFromPriceList as $delProduct) {
        //     $this->updateMatrixProductPricePending($delProduct->productID, 1);
        // }

        //if pricelist is deactivated make all matrix product pending process 1
        //first getting all product related to this pricelist
        // if(@$product['active'] == 0){
        //     $relatedProductIDs = PricelistRule::where("pricelistID", @$product['pricelistID'])->pluck("productID")->toArray();
        //     $parentIds = VariationProduct::whereIn("productID", $relatedProductIDs)->where("parentProductID", '>', 0)->pluck("parentProductID")->toArray();
        //     MatrixProduct::whereIn("productID", $parentIds)->update(["pricePending" => 1]);
        // }


    }

    private function updateMatrixProductPricePending($productID, $pendingFlag, $type)
    {
        //first getting variation product 
        $sql = VariationProduct::query();
        if ($type == "PRODGROUP") {
            $sql->where("groupID", $productID); // i.e For Product Group
        } else {
            $sql->where("productID", $productID); // i.e For Product 
        }

        $vp = $sql->first();
        if (@$vp->parentProductID > 0) {
            //now updting price pending in matrix product
            MatrixProduct::where("productID", $vp->parentProductID)->update(["pricePending" => $pendingFlag, "roadhousePricePending" => $pendingFlag]);
        }
    }


    public function getLastUpdateDate()
    {
        $latest = TempProductUpdate::where("id", 1)->first()->pricelist;
        if ($latest) {
            return strtotime($latest);
        }
        return 0;
        // echo "im call";
        $latest = $this->pricelist->orderBy('lastModified', 'desc')->first();
        if ($latest) {
            return strtotime($latest->lastModified);
        }
        return 0; // strtotime($latest);
    }

    //check price list active or inactive or deleted
    public function checkPricePolicyStartAndExpiry($req)
    {
        $debug = $req->debug ?? 0;
        //first check if price policy is expired and synced into MarketPlace
        $expiredPricePolicy = Pricelist::where('synccareStatus', 'ACTIVE')
            ->where(function ($q) {
                $q->where('active', 0)
                    ->orWhere(function ($q) {
                        $q->where('endDate', '<', date('Y-m-d'))
                            ->whereNotNull('endDate')
                            ->where('endDate', '<>', '')
                            ->where(function ($q) {
                                $q->whereNull('endTime')
                                    ->orWhere('endTime', '');
                            })
                            ->orWhere(function ($q) {
                                $q->where('endDate', '<=', date('Y-m-d'))
                                    ->whereNotNull('endDate')
                                    ->where('endDate', '<>', '')
                                    ->whereNotNull('endTime')
                                    ->where('endTime', '<>', '')
                                    ->where('endTime', '<=', date('H:i'));
                            });
                    })
                    ->orWhere(function ($q) {
                        $q->where('startDate', '>', date('Y-m-d'))
                            ->whereNotNull('startDate')
                            ->where('startDate', '<>', '')
                            ->where(function ($q) {
                                $q->whereNull('startTime')
                                    ->orWhere('startTime', '');
                            })
                            ->orWhere(function ($q) {
                                $q->where('startDate', '>=', date('Y-m-d'))
                                    ->whereNotNull('startDate')
                                    ->where('startDate', '<>', '')
                                    ->whereNotNull('startTime')
                                    ->where('startTime', '<>', '')
                                    ->where('startTime', '>', date('H:i'));
                            });
                    })
                    // ->orWhere('endDate', '<', date('Y-m-d'))
                    // ->orWhere('startDate', '>', date('Y-m-d'))
                    ->orWhere('isDeleted', 1);
            })
            ->pluck('pricelistID')
            ->toArray();

        // now set price pending to activate sale price
        foreach ($expiredPricePolicy as $expiredPricelist) { 
            Pricelist::where('pricelistID', $expiredPricelist)->update(['synccareStatus' => null]);
            $this->setPricelistPendingForShopify($expiredPricelist);

            // now check if it has brand discount
            $hasBrandDiscount = BrandDiscountFromPricelist::where('priceListID', $expiredPricelist)->first();
            if ($hasBrandDiscount) {
                $this->setPricelistPendingBrandDiscountForShopify($hasBrandDiscount);
            }
        }

        $newActivePricePolicy = Pricelist::where(function ($q) {
            $q->where('synccareStatus', '')
                ->orWhereNull('synccareStatus');
        })
            // ->where('startDate', '<=', date('Y-m-d'))
            ->Where(function ($q) {
                $q->whereNotNull('startDate')
                    ->where('startDate', '<>', '')
                    ->where('startDate', '<=', date('Y-m-d'))
                    ->where(function ($q) {
                        $q->whereNull('startTime')
                            ->orWhere('startTime', '');
                    })
                    ->orWhere(function ($q) {
                        $q->whereNotNull('startDate')
                            ->where('startDate', '<>', '')
                            ->where('startDate', '<=', date('Y-m-d'))
                            ->whereNotNull('startTime')
                            ->where('startTime', '<>', '')
                            ->where('startTime', '<=', date('H:i'));
                    })
                    ->orWhere('startDate', '0000-00-00');
            })
            // ->where('endDate', '>', date('Y-m-d'))
            ->Where(function ($q) {
                $q->where('endDate', '>=', date('Y-m-d'))
                    ->whereNotNull('endDate')
                    ->where('endDate', '<>', '')
                    ->where(function ($q) {
                        $q->whereNull('endTime')
                            ->orWhere('endTime', '');
                    })
                    ->orWhere(function ($q) {
                        $q->whereNotNull('endDate')
                            ->where('endDate', '<>', '')
                            ->where('endDate', '>=', date('Y-m-d'))
                            ->whereNotNull('endTime')
                            ->where('endTime', '<>', '')
                            ->where('endTime', '>', date('H:i'));
                    })
                    ->orWhere('endDate', '0000-00-00');
            })
            ->where('active', 1)
            ->where('isDeleted', 0)
            // ->whereIn('pricelistID', [60])
            ->pluck('pricelistID')
            ->toArray();
        if ($debug == 1) {
            dd($expiredPricePolicy, $newActivePricePolicy);
        }

        foreach ($newActivePricePolicy as $activePricelist) {
            //now check price list attached with warehouse location 8
            $checkPricelistExistInWarehouse = Warehouse::where('warehouseID', 8)
                ->where(function ($q) use ($activePricelist) {
                    $q->where('priceListID', $activePricelist)
                        ->orWhere('priceListID2', $activePricelist)
                        ->orWhere('priceListID3', $activePricelist)
                        ->orWhere('priceListID4', $activePricelist)
                        ->orWhere('priceListID5', $activePricelist);
                })
                ->first();
            if ($checkPricelistExistInWarehouse) {
                Pricelist::where('pricelistID', $activePricelist)->update(['synccareStatus' => 'ACTIVE']);
                $this->setPricelistPendingForShopify($activePricelist, ['roadhousePricePending' => 1, 'pricePending' => 1]);
                //now check if it has brand discount
                $hasBrandDiscount = BrandDiscountFromPricelist::where('priceListID', $activePricelist)->first();
                if ($hasBrandDiscount) {
                    $this->setPricelistPendingBrandDiscountForShopify($hasBrandDiscount);
                }
            }
        }

        //checking active pricelist but removed from warehouse
        $activePriceListToCheckWarehouseExist = Pricelist::where('synccareStatus', 'ACTIVE')->get();
        foreach ($activePriceListToCheckWarehouseExist as $currentlyActive) {
            $checkPricelistExistInWarehouse = Warehouse::where('warehouseID', 8)
                ->where(function ($q) use ($currentlyActive) {
                    $q->where('priceListID', $currentlyActive->pricelistID)
                        ->orWhere('priceListID2', $currentlyActive->pricelistID)
                        ->orWhere('priceListID3', $currentlyActive->pricelistID)
                        ->orWhere('priceListID4', $currentlyActive->pricelistID)
                        ->orWhere('priceListID5', $currentlyActive->pricelistID);
                })
                ->first();
            if (!$checkPricelistExistInWarehouse) {
                $currentlyActive->synccareStatus = null;
                $currentlyActive->save();
                $this->setPricelistPendingForShopify($currentlyActive->pricelistID);
                //now check if it has brand discount
                $hasBrandDiscount = BrandDiscountFromPricelist::where('priceListID', $currentlyActive->pricelistID)->first();
                if ($hasBrandDiscount) {
                    $this->setPricelistPendingBrandDiscountForShopify($hasBrandDiscount);
                }
            }
        }
        $payload = ['expiredPricePolicy' => $expiredPricePolicy, 'newActivePricePolicy' => $newActivePricePolicy, 'activeAndCheckForWarehouse' => $activePriceListToCheckWarehouseExist];
        return response()->json($payload);
    }

    private function setPricelistPendingBrandDiscountForShopify( $brandDisocunt)
    {
        MatrixProduct::where('brandID', $brandDisocunt->brandID)->update(['roadhousePricePending' => 1, 'pricePending' => 1]);
        $varParentIDs = VariationProduct::where('brandID', $brandDisocunt->brandID)->pluck('parentProductID')->toArray();
        MatrixProduct::whereIn('productID', $varParentIDs)->update(['roadhousePricePending' => 1, 'pricePending' => 1]);
    }

    private function setPricelistPendingForShopifyBackup($expiredPricelist)
    {
        $expiredPricelistProduct = PricelistRule::where('pricelistID', $expiredPricelist)
            ->pluck('productID')
            ->toArray();
            
        MatrixProduct::whereIn('productID', $expiredPricelistProduct)->update(['roadhousePricePending' => 1, 'pricePending' => 1]);
        $varParentIDs = VariationProduct::whereIn('productID', $expiredPricelistProduct)->pluck('parentProductID')->toArray();
        MatrixProduct::whereIn('productID', $varParentIDs)->update(['roadhousePricePending' => 1, 'pricePending' => 1]);
    }

    private function setPricelistPendingForShopify($expiredPricelist)
    {
        $expiredPricelistProduct = PricelistRule::select('type', 'productID')->where('pricelistID', $expiredPricelist)
            ->get();

        if ($expiredPricelistProduct->isNotEmpty()) {
            $productIds = [];
            $productGroupIds = [];

            if (count($expiredPricelistProduct) > 0) {
                foreach ($expiredPricelistProduct as $rule) {
                    if ($rule->type === 'PRODUCT') {
                        $productIds[] = $rule->productID;
                    }

                    if ($rule->type === 'PRODGROUP') {
                        $productGroupIds[] = $rule->productID;
                    }
                }
            }

            // Now process product only 
            if (count($productIds) > 0) $this->setPricelistPendingForProductShopify($productIds);
            
            // Now process product group discount
            if (count($productGroupIds) > 0) $this->setPricelistPendingForProductGroupShopify($productGroupIds);
        }
    }

    private function setPricelistPendingForProductShopify($productIds)
    {
        MatrixProduct::whereIn('productID', $productIds)->update(['roadhousePricePending' => 1, 'pricePending' => 1]);
        $varParentIDs = VariationProduct::whereIn('productID', $productIds)->pluck('parentProductID')->toArray();
        if (count($varParentIDs) > 0) {
            MatrixProduct::whereIn('productID', $varParentIDs)->update(['roadhousePricePending' => 1, 'pricePending' => 1]);
        }
    }

    private function setPricelistPendingForProductGroupShopify($productGroupIds)
    {
        MatrixProduct::whereIn('groupID', $productGroupIds)->update(['roadhousePricePending' => 1, 'pricePending' => 1]);
        $varParentIDs = VariationProduct::whereIn('groupID', $productGroupIds)->pluck('parentProductID')->toArray();
        if (count($varParentIDs) > 0) {
            MatrixProduct::whereIn('productID', $varParentIDs)->update(['roadhousePricePending' => 1, 'pricePending' => 1]);
        }
    }
}