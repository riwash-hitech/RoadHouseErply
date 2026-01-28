<?php

namespace App\Http\Controllers\ShopifyPull\Services;

use DateTime;
use GuzzleHttp\Client;
use App\Traits\ShopifyTrait;
use App\Models\PAEI\VariationProduct;
use App\Models\Shopify\ShopifyCursor;
use App\Models\Shopify\ShopifyCustomer;
use App\Models\Shopify\ShopifyImages;
use App\Models\Shopify\ShopifyProduct;
use App\Models\Shopify\ShopifyProductSoh;
use App\Models\Shopify\ShopifySalesOrder;
use App\Models\Shopify\ShopifySalesReturn;
use App\Models\Shopify\ShopifyProductVariant;
use App\Models\Shopify\ShopifySalesOrderLine;
use App\Models\Shopify\ShopifySalesOrderDelivery;

class ShopifyProductService
{
    use ShopifyTrait;
    protected $company = null;
    protected $clientCode;
    protected $live = 0;
    protected $url = null;
    protected $secret = null;



    public function __construct()
    {
        //   $this->clientCode = '605909';
        $this->clientCode = '602570'; 
        $this->live = 1;
    }

    private function setEnv($req){
        if(isset($req->islive) == 1){
            $this->live = $req->islive;
        }
    }


    public function getMatrixProducts($req)
    {

        $this->setEnv($req);

        $debug = 0;
        if (isset($req->debug)) {
            $debug = $req->debug;
        }
        //first getting cursor
        $query = $this->getMatrixProductQuery($this->clientCode);

        if ($debug == 1) {
            dd($query);
        }

        // $datas = $this->sendShopifyQueryRequest($this->url, "POST", $this->secret, $query);
        $datas = $this->sendShopifyQueryRequestV2("POST", $query, $this->live);

        if ($debug == 2) {
            dd($datas, $query);
        }

        foreach (@$datas->data->products->edges as $mp) {
            $details = $mp->node;
            $tags = implode(",", $details->tags);
            $newsystemProductId = str_replace('gid://shopify/Product/', '', $details->id);
            $pdata = array(
                'clientCode' => $this->clientCode, //$this->norrisClientCode,
                'newsystemProductId' => $newsystemProductId,
                'shopifyProductId' => $details->id,
                'newsystemProductTitle' => addslashes($details->title),
                // 'sku' => $mainSku,
                // 'barcode' => $mainBarCode,
                // 'totalInventory' => $value->totalInventory,
                'handle' => $details->handle,
                'description' => trim($details->description),
                'longDescription' => trim($details->descriptionHtml),
                'productType' => $details->productType,
                'totalVariants' => $details->totalVariants,
                'vendor' => $details->vendor,
                'defaultCursor' => $details->defaultCursor,
                'pendingProcess' => 1,
                'tags' => $tags,
                'status' => $details->status,
                'hasVariants' =>   $details->totalVariants > 0 ? 1 : 0,
                'lastupdateDate' => (new DateTime($details->updatedAt))->format('Y-m-d H:i:s'),
                'createdAt' => (new DateTime($details->createdAt))->format('Y-m-d H:i:s')
                // 'importPending' => 0,
                // 'variationPending' => 1
            );


            ShopifyProduct::updateOrcreate(
                [
                    'clientCode' => $this->clientCode,
                    'newsystemProductId' => $newsystemProductId,
                ],
                $pdata
            );

            if (count(@$details->images->edges) > 0) {
                $this->saveMatrixImages($newsystemProductId, @$details->images->edges);
            }
        }

        if (@$datas->data->products->pageInfo->endCursor) {
            $lastCursor = @$datas->data->products->pageInfo->endCursor;
            ShopifyCursor::updateOrcreate(
                [
                    "clientCode" => $this->clientCode,
                    "cursorName" => "matrixProduct",
                    "isLive" => $this->live
                ],
                [
                    "clientCode" => $this->clientCode,
                    "cursorName" => "matrixProduct",
                    "cursor" => $lastCursor,
                    "isLive" => $this->live
                ]
            );
        }

        return response("Matrix Product Synccing from Shopify...");
    }

    public function saveMatrixImages($matrixID, $datas)
    {

        foreach ($datas as $data) {
            $details = array(
                "clientCode" => $this->clientCode,
                "newsystemProductID" => $matrixID,
                "image" => $data->node->url,
                "live" => $this->live,
            );

            ShopifyImages::updateOrcreate(
                [
                    "clientCode" => $this->clientCode,
                    // "newsystemProductID" => $matrixID,
                    "live" => $this->live,
                    "image" => $data->node->url,
                ],
                $details
            );
        }
    }

    public function getVariationProducts($req)
    {
        $this->setEnv($req);

        $debug = 0;
        if (isset($req->debug)) {
            $debug = $req->debug;
        }
        //first getting cursor
        $query = $this->getVariationProductQuery($this->clientCode);

        if ($debug == 1) {
            dd($query);
        }

        // $datas = $this->sendShopifyQueryRequest($this->url, "POST", $this->secret, $query);
        $datas = $this->sendShopifyQueryRequestV2("POST", $query, $this->live);

        if ($debug == 2) {
            dd($datas, $query);
        }

        foreach (@$datas->data->productVariants->edges as $mp) {
            $details = $mp->node;
            $newSystemVariantId = str_replace('gid://shopify/ProductVariant/', '', $details->id);
            $newsystemProductId = str_replace('gid://shopify/Product/', '', $details->product->id);
            $inventoryItemID = str_replace('gid://shopify/InventoryItem/', '', $details->inventoryItem->id);
            $otherVariants  = array();
            foreach ($details->selectedOptions as $optVal) {
                $otherVariants[] = array('name' => $optVal->name, 'value' => $optVal->value);
            }
            if ($debug == 3) {
                dd($details, $newSystemVariantId, $newsystemProductId, $details->inventoryItem->id, $inventoryItemID, $otherVariants);
            }
            $pdata = array(
                'clientCode' => $this->clientCode, //$this->norrisClientCode,
                'newsystemProductId' => $newsystemProductId,
                'newSystemVariantId' => $newSystemVariantId,
                'shopifyVariantId' => $details->id,
                'variantTitle' => addslashes($details->title),
                'variantPrice' => $details->price,
                'inventoryQuantity' => $details->inventoryQuantity,
                'availableForSale' => intval($details->availableForSale),
                'displayName' => $details->displayName,
                'variantDetails' => json_encode($otherVariants),
                'weight' => $details->weight,
                'weightUnit' => $details->weightUnit,
                'sku' => $details->sku,
                'barcode' => $details->barcode,
                'shopifyInventoryItem' => $details->inventoryItem->id,
                'inventoryItemID' => $inventoryItemID,
                'productActive' => 1,
                'pendingProcess' => 1,
                'pendingProcess2' => 1,
                'createdAt' => (new DateTime($details->createdAt))->format('Y-m-d H:i:s'),
                'defaultCursor' => $details->defaultCursor,
                'status' => $details->product->status,
            );


            ShopifyProductVariant::updateOrcreate(
                [
                    'clientCode' => $this->clientCode,
                    'newSystemVariantId' => $newSystemVariantId,
                ],
                $pdata
            );
        }

        if (@$datas->data->productVariants->pageInfo->endCursor) {
            $lastCursor = @$datas->data->productVariants->pageInfo->endCursor;
            ShopifyCursor::updateOrcreate(
                [
                    "clientCode" => $this->clientCode,
                    "cursorName" => "variationProduct",
                    "isLive" => $this->live
                ],
                [
                    "clientCode" => $this->clientCode,
                    "cursorName" => "variationProduct",
                    "cursor" => $lastCursor,
                    "isLive" => $this->live
                ]
            );
        }

        return response("Variation Product Synccing from Shopify...");
    }
    public function getProductSoh($req)
    {

        $this->setEnv($req);

        $debug = 0;
        if (isset($req->debug)) {
            $debug = $req->debug;
        }
        //first getting cursor
        $query = $this->getSohQuery($this->clientCode, $this->live);

        if ($debug == 1) {
            dd($query);
        }

        // $datas = $this->sendShopifyQueryRequest($this->url, "POST", $this->secret, $query);
        $datas = $this->sendShopifyQueryRequestV2("POST", $query, $this->live);

        if ($debug == 2) {
            dd($datas, $query);
        }

        foreach (@$datas->data->productVariants->edges as $data) {
            // dd($data);
            $details = array(
                "clientCode" => $this->clientCode,
                "cursors" => $data->cursor,
                "variationID" => @$data->node->id,
                "inventoryID" => str_replace('gid://shopify/InventoryItem/', '', @$data->node->inventoryItem->id),
                "sku" => @$data->node->sku,
                "name" => @$data->node->title,
                "isLive" => $this->live,
                "sohPending" => 1
            );

            // $isOnline
            $currentCursor = $data->cursor;
            foreach (@$data->node->inventoryItem->inventoryLevels->edges as $stock) {

                // if(@$stock->node->location->id == "gid://shopify/Location/35394846792"){
                $details["locationID"] = $stock->node->location->id;
                $details["available"] = $stock->node->available;
                $details["lastModified"] = date('Y-m-d H:i:s', strtotime($stock->node->updatedAt));

                ShopifyProductSoh::updateOrcreate(
                    [
                        "variationID" => @$data->node->id,
                        "isLive" => $this->live,
                        "locationID" => @$details["locationID"],
                        // "sohPending" => 1
                    ],
                    $details
                );

                // }

            }

            // dd($details);

        }

        //update last cursor
        if ($currentCursor != '') {
            $cursorPayload = array(
                "cursor" => $currentCursor,
                "cursorName" => $this->live == 1 ? "soh" : "soh_stageing",
                "isLive" => $this->live,
                "clientCode" => $this->clientCode,
            );

            ShopifyCursor::updateOrcreate(
                [
                    "cursorName" => $cursorPayload["cursorName"]
                ],
                $cursorPayload
            );
        }

        return response("Variation Product SOH Synccing from Shopify...");
    }

    public function getCustomers($req)
    {
        $debug = 0;
        if (isset($req->debug)) {
            $debug = $req->debug;
        }
        //first getting cursor
        $query = $this->getCustomerQuery($this->clientCode);

        if ($debug == 1) {
            dd($query);
        }

        // $datas = $this->sendShopifyQueryRequest($this->url, "POST", $this->secret, $query);
        $datas = $this->sendShopifyQueryRequestV2("POST", $query, $this->live);

        if ($debug == 2) {
            dd($datas, $query, $datas->data->customers->edges);
        }

        foreach (@$datas->data->customers->edges as $mp) {
            $details = $mp->node;
            $newSystemMemberID  = str_replace('gid://shopify/Customer/', '', $details->id);
            if ($debug == 3) {
                dd($details, $newSystemMemberID, $details->inventoryItem->id);
            }
            $pdata = array(
                'clientCode' => $this->clientCode, //$this->norrisClientCode,
                'newSystemMemberID' => $newSystemMemberID ,
                'shopifyCustomerId' => $details->id,
                'emailAddress' => $details->email ?? '',
                'firstName' => $details->firstName ?? '',
                'lastName' => $details->lastName ?? '',
                'companyName' =>isset($details->defaultAddress, $details->defaultAddress->company) ? $details->defaultAddress->company : '',
                'phone' => $details->phone ?? '',
                'street' => isset($details->defaultAddress, $details->defaultAddress->street) ? $details->defaultAddress->street : '',
                'city' => isset($details->defaultAddress, $details->defaultAddress->city) ? $details->defaultAddress->city : '',
                'postCode' => isset($details->defaultAddress, $details->defaultAddress->zip) ? $details->defaultAddress->zip : '',
                'country' => isset($details->defaultAddress, $details->defaultAddress->country) ? $details->defaultAddress->country : '',
                'state' => $details->state ?? '',
                'pendingProcess' => 1,
                'syncEntryDate' => date('Y-m-d H:i:s'),
                'lastupdateDate' => date('Y-m-d H:i:s'),
                'metafields' => json_encode($details->metafields),
                'note' => json_encode($details->note),
                'address1' =>isset($details->defaultAddress, $details->defaultAddress->address1) ? $details->defaultAddress->address1 : '',
                'address2' => isset($details->defaultAddress, $details->defaultAddress->address2) ? $details->defaultAddress->address2 : '',
                'smsMarketingConsent' => isset($details->defaultAddress, $details->defaultAddress->marketingState) ? $details->smsMarketingConsent->marketingState : '',
                'emailMarketingConsent' => isset($details->defaultAddress, $details->defaultAddress->marketingState) ? $details->emailMarketingConsent->marketingState : '',
                'numberOfOrders' => $details->numberOfOrders,
                'amountSpent' => $details->amountSpent->amount,
                'countryCodeV2' => isset($details->defaultAddress, $details->defaultAddress->countryCodeV2) ? $details->defaultAddress->countryCodeV2 : '',
                'provinceCode' => isset($details->defaultAddress, $details->defaultAddress->provinceCode) ? $details->defaultAddress->provinceCode : '',
            );


            ShopifyCustomer::updateOrcreate(
                [
                    'clientCode' => $this->clientCode,
                    'newSystemMemberID' => $newSystemMemberID,
                ],
                $pdata
            );
            
            if($mp->cursor){
                ShopifyCursor::updateOrcreate(
                [
                    "clientCode" => $this->clientCode,
                    "cursorName" => "customer",
                    "isLive" => $this->live
                ],
                [
                    "clientCode" => $this->clientCode,
                    "cursorName" => "customer",
                    "cursor" => $mp->cursor,
                    "isLive" => $this->live
                ]
            );
            }
        }

        return response("Customers Synccing from Shopify...");
    }

    //only for staging
    public function getProductAndDelete($req){
        $isDebug = 0;
        if($req->debug){
            $isDebug = $req->debug;
        }
        $query = "query{
            products(first: 1, sortKey: ID) {
              edges {
                cursor
                node {
                  id
                  title
                    
                }
              }
              
            }
          }
        ";

        $datas = $this->sendShopifyQueryRequestV2("POST", $query, 0);

        if($isDebug == 1){
            dd($datas);
        }

        $deleteQuery = "";
    }
}
