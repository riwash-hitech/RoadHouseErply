<?php

namespace App\Http\Controllers\LivePushErply\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\GiftCard;
use App\Models\PAEI\MatrixProduct;
use App\Models\Shopify\ShopifySalesOrder;
use App\Models\Shopify\ShopifySalesOrderLine;
use App\Models\Shopify\ShopifySalesReturn;
use App\Models\Shopify\ShopifySalesOrderDelivery;
use App\Models\PAEI\VariationProduct;
use Illuminate\Http\Request;
// use Modules\Shopify\App\Traits\ShopifyTrait;
use App\Models\PAEI\Warehouse;

use App\Models\PAEI\GiftCard as NewSystemGiftCard;
use App\Models\Shopify\ShopifyCustomer;

class ErplySalesOrderService
{

    // use ShopifyTrait;
    protected $api;
    protected $live = 1;

    public function __construct(EAPIService $api)
    {
        $this->api = $api;
    }

    public function pushSalesOrders($req)
    {

        if ($req->dateflag) {
            date_default_timezone_set('Australia/Melbourne');
        }

        $limit = $req->limit ? $req->limit : 3;
        $isDebug = $req->debug ?? 0;
        if ($req->orderID) {
            $salesOrders = ShopifySalesOrder::join("newsystem_customers", "newsystem_customers.newSystemMemberID", "newsystem_orders.newSystemCustomerID")
                ->join("newsystem_order_delivery", "newsystem_order_delivery.newSystemOrderID", "newsystem_orders.newSystemOrderID")
                // ->join("newsystem_order_delivery", function($query){
                //         $query->on("newsystem_order_delivery.newSystemMemberID","newsystem_orders.newSystemCustomerID")
                //             ->on("newsystem_order_delivery.newSystemOrderID","newsystem_orders.newSystemOrderID");
                //     })
                ->where("newsystem_customers.erplyPending", 0)
                ->where("newsystem_customers.erplyAddressPending", 0)
                ->where("newsystem_customers.erplyAddressPending", 0)
                ->where("newsystem_orders.erplyPending", 1)
                ->where("newsystem_orders.payment_detail_status", 'PAID')
                ->where("newsystem_order_delivery.erplyPending", 0)
                ->where("newsystem_orders.orderID", trim($req->orderID))
                ->select(["newsystem_orders.*", "newsystem_customers.erplyCustomerID", "newsystem_customers.newSystemMemberID", "newsystem_customers.erplyAddressID", "newsystem_order_delivery.erplyDeliveryID"])
                ->limit($limit)
                ->get();
        } else {
            $salesOrders = ShopifySalesOrder::join("newsystem_customers", "newsystem_customers.newSystemMemberID", "newsystem_orders.newSystemCustomerID")
                ->join("newsystem_order_delivery", "newsystem_order_delivery.newSystemOrderID", "newsystem_orders.newSystemOrderID")
                // ->join("newsystem_order_delivery", function($query){
                //         $query->on("newsystem_order_delivery.newSystemMemberID","newsystem_orders.newSystemCustomerID")
                //             ->on("newsystem_order_delivery.newSystemOrderID","newsystem_orders.newSystemOrderID");
                //     })
                ->where("newsystem_customers.erplyPending", 0)
                ->where("newsystem_customers.erplyAddressPending", 0)
                ->where("newsystem_customers.erplyAddressPending", 0)
                ->where("newsystem_orders.erplyPending", 1)
                ->where("newsystem_orders.payment_detail_status", 'PAID')
                ->where("newsystem_order_delivery.erplyPending", 0)
                ->select(["newsystem_orders.*", "newsystem_customers.erplyCustomerID", "newsystem_customers.newSystemMemberID", "newsystem_customers.erplyAddressID", "newsystem_order_delivery.erplyDeliveryID"])
                ->limit($limit)
                ->get();
        }

        if ($isDebug == 1) {
            dd($salesOrders);
        }

        if ($salesOrders->isEmpty()) {
            info("All Sales Order Syncced to Erply.");
            return response("All Sales Order Syncced to Erply.");
        }

        $BundleArray = array();
        $bundleGiftCardArray = array();
        foreach ($salesOrders as $so) {

            //first saving delivery address before order placed
            // $deliveryID = $this->saveDeliveryAddress($so->newSystemMemberID,$so->erplyCustomerID, $so->newSystemOrderID);
            $reqArray = array(
                "requestName" => "saveSalesDocument",
                "sessionKey" => $this->api->client->sessionKey,
                "clientCode" => $this->api->client->clientCode,
                "type" => "CASHINVOICE",
                "invoiceNo" => substr($so->newSystemOrderNumber, 1),
                "customNumber" => $so->newSystemOrderNumber,
                "confirmInvoice" => 1,
                "warehouseID" => 8,
                "paymentType" => "CARD",
                "customerID" => $so->erplyCustomerID,
                "addressID" => $so->erplyDeliveryID,
                // "shipToAddressID" => $so->erplyDeliveryID,
                // "shipToID" => $so->erplyDeliveryID,
                "date" => date('Y-m-d', strtotime($so->order_created)),
                "time" => date('H:i:s', strtotime($so->order_created)),
                "attributeName1" => "ShopifyOrderID",
                "attributeType1" => "text",
                "attributeValue1" => $so->newSystemOrderID,
                "notes" => $so->note,
                "paymentInfo" => $so->payment_detail_title,
                // "webShopOrderNumbers" => "['". substr($so->newSystemOrderNumber, 1 ) ."']"

            );

            // Bundle Gift Card Array
            if (\request()->query('process') == 'giftcard') {
                if ($so->coupon_code != '' && $so->coupon_amount > 0) {

                    // Get Current Gift Card From Shopify
                    $current_giftcard = $this->getGiftCardShopify($so);

                    $temp_array = array(
                        "requestName" => "saveGiftCard",
                        "sessionKey" => $this->api->client->sessionKey,
                        "clientCode" => $this->api->client->clientCode,
                        "code" => $so->coupon_code,
                        "value" => $current_giftcard['initial_value'],
                        "balance" => $current_giftcard['remaining_balance'], // Remaining Balance
                        // "minimumSpend" => ,
                        "information" => $current_giftcard['note'],
                        "redeemingCustomerID" => $current_giftcard['customer_id'],
                        // "redemptionDateTime" => ,
                        "expirationDate" => $current_giftcard['expires_on'],
                        // "vatrateID" => ,
                    );
                    array_push($bundleGiftCardArray, $temp_array);
                }
            }

            $webshopNum = array();
            $webshopNum[] = substr($so->newSystemOrderNumber, 1);

            $webshopNum = json_encode($webshopNum, true);
            $reqArray["webShopOrderNumbers"] = $webshopNum;

            if ($so->payment_detail_status == "PAID") {
                $reqArray["paymentStatus"] = "PAID";
            }

            //checking order exist
            $sDocID = $this->getSalesDocument($so->newSystemOrderID);
            if ($sDocID != '') {
                $reqArray["id"] = $sDocID;
            }

            //add sales lines

            //getting Sales Lines
            $orderLines = ShopifySalesOrderLine::where("newSystemOrderID", $so->newSystemOrderID)->get();
            if ($isDebug == 2) {
                dd($orderLines);
            }

            $count = count($orderLines);
            $disAmt = 0;
            $disAmtIncGst = 0;
            if ((float) $so->coupon_amount > 0) {
                $disWithouGst = (float) $so->coupon_amount; // /1.1;
                $disAmtIncGst = $disWithouGst;

                $disWithouGst = $disWithouGst / 1.1;

                //discount per line with inc gst
                $disAmtIncGst = $disAmtIncGst / $count;

                //discount per line without gst
                $disAmt = (float) $disWithouGst / $count;
            }
            foreach ($orderLines as $key => $l) {

                //now getting product of sales line
                $product = VariationProduct::where("code", $l->stockCode)->where("active", 1)->first();
                if (!$product) {
                    $product = VariationProduct::where("code", $l->stockCode)->first();
                }

                if ($product) {

                    $productNetPrice = round($l->unitPrice / 1.1, 4);

                    $reqArray["productID" . $key + 1] = $product->productID;
                    $reqArray["vatrateID" . $key + 1] = 1;
                    $reqArray["amount" . $key + 1] = abs((int) $l->quantity);
                    // $reqArray["price".$key+1] = $product->priceListPrice;
                    $reqArray["price" . $key + 1] = $productNetPrice; //$product->priceListPrice;

                    $totalDiscount = 0;
                    if ($disAmt > 0) {
                        $totalDiscount += ($disAmtIncGst / abs($l->quantity));
                    }

                    if ($l->discountAmount > 0) {
                        $totalDiscount += ($l->discountAmount / abs($l->quantity));
                    }

                    if ($totalDiscount > 0) {
                        $totalDisAmt = round($totalDiscount / 1.1, 4);
                        $finalDiscount = ($totalDisAmt / $productNetPrice) * 100;
                        $reqArray["discount" . $key + 1] = $finalDiscount;
                    }
                }
            }

            if ($so->total_shipping > 0) {
                $reqArray["productID" . $count + 1] = 55554;
                $reqArray["vatrateID" . $count + 1] = 1;
                $reqArray["amount" . $count + 1] = 1;
                $reqArray["discount" . $count + 1] = 0;
                $reqArray["price" . $count + 1] = (float) $so->total_shipping / 1.1; //- (double)$so->coupon_amount;
            }

            array_push($BundleArray, $reqArray);
        }

        $finalOrder = $BundleArray;
        if (count($BundleArray) < 1) {
            info("All Sales Order Synced.");
            return response("All Sales Order Synced.");
        }

        if ($isDebug == 3) {
            dd($BundleArray);
        }

        $BundleArray = json_encode($BundleArray, true);
        $param = array(
            "lang" => 'eng',
            "responseType" => "json",
            "sessionKey" => $this->api->client->sessionKey,
        );
        $res = $this->api->sendRequest($BundleArray, $param, 1);

        if ($res['status']['errorCode'] == 0 && !empty($res['requests'])) {
            foreach ($salesOrders as $key => $c) {
                if ($res['requests'][$key]['status']['errorCode'] == 0) {
                    ShopifySalesOrder::where("orderID", $c["orderID"])->update(
                        [
                            'erplyPending' => 0,
                            'erplySalesDocumentID' => $res['requests'][$key]['records'][0]['invoiceID'],
                            'erplyTotal' => @$res['requests'][$key]['records'][0]['total'] ?? 0
                        ]
                    );

                    // Process Save Gift Card
                    if (\request()->query('process') == 'giftcard') {
                        $this->storeGiftCard($bundleGiftCardArray);
                    }
                }
            }
            info("Sales ORder Created or Updated to Erply");
        }

        return response()->json(["status" => "success", "response" => $res]);
    }

    protected function getGiftCardShopify($order)
    {
        $gift = NewSystemGiftCard::where('code', $order->coupon_code)->first();;
        if ($gift) {
            $giftCardId = $gift->shopifyGiftCardId;
        }

        if ($giftCardId == null)
            return [];

        $giftcard_query = 'query {
            giftCard(id: "' . $giftCardId . '") {
                initialValue {
                    amount
                    currencyCode
                }
                balance {
                    amount
                    currencyCode
                }
                note
                expiresOn
                createdAt
            }
        }';

        $response_array = [];
        $response = $this->sendShopifyQueryRequestV2("POST", $giftcard_query, $this->live);
        if (isset($response->data->giftCard)) {

            // $erply_customer_id = null;
            // $customer = ShopifyCustomer::where('newSystemMemberID', $order->newSystemCustomerID)->first();
            // if ($customer) {
            //     $erply_customer_id = $customer->erplyCustomerID;
            // }
            $erply_customer_id = $order->erplyCustomerID ?? null;
            $response_array = [
                'initial_value' => $response->data->giftCard->initialValue->amount,
                'remaining_balance' => $response->data->giftCard->balance->amount,
                'note' => $response->data->giftCard->note,
                'customer_id' => $erply_customer_id,
                'expires_on' => $response->data->giftCard->expiresOn ?? null
            ];
        }
        dump('Gift Card From Shopify : ', $response_array);
        return $response_array;
    }

    protected function storeGiftCard($giftCardParams)
    {
        $bundleArray = json_encode($giftCardParams, true);
        $param = array(
            "lang" => 'eng',
            "responseType" => "json",
            "sessionKey" => $this->api->client->sessionKey,
        );
        $res = $this->api->sendRequest($bundleArray, $param, 1);
    }

    protected function getSalesDocument($oid)
    {
        $param = array(
            "sessionKey" => $this->api->client->sessionKey,
            "searchAttributeName1" => "ShopifyOrderID",
            "searchAttributeValue1" => $oid
        );
        $res = $this->api->sendRequest("getSalesDocuments", $param);
        if ($res["status"]["errorCode"] == 0 && !empty($res["records"])) {
            return $res["records"][0]["id"];
        }

        return '';
    }

    public function saveDeliveryAddress()
    {

        //first getting order delivery address
        $orderDelivery = ShopifySalesOrderDelivery::join("newsystem_orders", "newsystem_orders.newSystemOrderID", "newsystem_order_delivery.newSystemOrderID")
            ->join("newsystem_customers", "newsystem_customers.newSystemMemberID", "newsystem_order_delivery.newSystemMemberID")
            ->where("newsystem_customers.erplyPending", 0)
            ->where("newsystem_order_delivery.erplyPending", 1)
            ->select(["newsystem_order_delivery.*", "newsystem_customers.erplyCustomerID"])
            ->limit(20)
            ->get();
        // dd($orderDelivery);

        if ($orderDelivery->isEmpty()) {
            info("All Order Delivery Address Syned to Erply.");
            return response("All Order Delivery Address Syned to Erply.");
        }

        $bulkDel = array();
        foreach ($orderDelivery as $da) {

            //now getting delivery from erply
            $param = array(
                "sessionKey" => $this->api->client->sessionKey,
                "typeID" => 7,
                "ownerID" => $da->erplyCustomerID,
                "recordsOnPage" => 100
            );



            $res = $this->api->sendRequest("getAddresses", $param);
            // dd($res);
            $isDeliveryExist = false;
            $aID = 0;
            if ($res["status"]["errorCode"] == 0) {
                foreach ($res["records"] as $add) {
                    if ($add["postalCode"] == $da->deliveryPostCode) {
                        $isDeliveryExist = true;
                        $aID = $add["addressID"];
                    }
                }
            }

            $delParam = array(
                "requestName" => "saveAddress",
                "sessionKey" => $this->api->client->sessionKey,
                "clientCode" => $this->api->client->clientCode,
                "ownerID" => $da->erplyCustomerID,
                "typeID" => 7,
                "street" => $da->deliveryStreet ? $da->deliveryStreet : '',
                "city" => $da->deliveryCity ? $da->deliveryCity : '',
                "postalCode" => $da->deliveryPostCode ? $da->deliveryPostCode : '',
                "state" => $da->deliveryState ? $da->deliveryState : '',
                "country" => $da->deliveryCountry ? $da->deliveryCountry : '',
            );

            if ($isDeliveryExist == true) {
                $delParam["addressID"] = $aID;
            }

            $bulkDel[] = $delParam;
        }

        // dd($bulkDel);

        if (count($bulkDel) < 1) {
            info("All Order Delivery Address Syned to Erply.");
            return response("All Order Delivery Address Syned to Erply.");
        }

        $bulkDel = json_encode($bulkDel, true);

        $paramB = array(
            "lang" => 'eng',
            "responseType" => "json",
            "sessionKey" => $this->api->client->sessionKey,
        );

        $bres = $this->api->sendRequest($bulkDel, $paramB, 1);

        if ($bres['status']['errorCode'] == 0 && !empty($bres['requests'])) {
            foreach ($orderDelivery as $key => $c) {
                if ($bres['requests'][$key]['status']['errorCode'] == 0) {
                    ShopifySalesOrderDelivery::where("id", $c["id"])->update(['erplyPending' => 0, 'erplyDeliveryID' => $bres['requests'][$key]['records'][0]['addressID']]);
                }
            }
            info("Sales ORder Created or Updated to Erply");
        }
        return response()->json($bres);
    }


    public function pushRefundReturn($req)
    {
        dd("Please refer to v2 !!!");
        // dd($req->number);
        $isDebug = $req->debug ?? 0;

        if ($req->number) {
            $result = ShopifySalesReturn::join("newsystem_orders", "newsystem_orders.newSystemOrderNumber", "newsystem_refunds.newSystemOrderNumber")
                ->join("newsystem_customers", "newsystem_customers.newSystemMemberID", "newsystem_orders.newSystemCustomerID")
                ->join("newsystem_order_delivery", function ($query) {
                    $query->on("newsystem_order_delivery.newSystemMemberID", "newsystem_orders.newSystemCustomerID")
                        ->on("newsystem_order_delivery.newSystemOrderID", "newsystem_orders.newSystemOrderID");
                })
                // ->where("newsystem_orders.erplyPending", 0)
                // ->whereIn("newsystem_refunds.erplyPending", [1,2])
                ->where("newsystem_refunds.newSystemOrderNumber", "#" . $req->number)
                ->whereIn("newsystem_refunds.erplyPending", [1, 2])
                ->select(["newsystem_orders.erplySalesDocumentID", "newsystem_orders.fullfillment_status", "newsystem_refunds.*", "newsystem_customers.erplyCustomerID", "newsystem_customers.erplyAddressID", "newsystem_order_delivery.erplyDeliveryID"])
                ->orderBy("updated_at", "asc")
                ->limit(1)
                ->get();
        } else {
            $result = ShopifySalesReturn::join("newsystem_orders", "newsystem_orders.newSystemOrderNumber", "newsystem_refunds.newSystemOrderNumber")
                ->join("newsystem_customers", "newsystem_customers.newSystemMemberID", "newsystem_orders.newSystemCustomerID")
                // ->join("newsystem_order_delivery", function($query){
                //     $query->on("newsystem_order_delivery.newSystemMemberID","newsystem_orders.newSystemCustomerID")
                //         ->on("newsystem_order_delivery.newSystemOrderID","newsystem_orders.newSystemOrderID");
                // })
                ->join("newsystem_order_delivery", "newsystem_orders.newSystemOrderID", "newsystem_order_delivery.newSystemOrderID")
                ->where("newsystem_orders.erplyPending", 0)
                ->whereIn("newsystem_refunds.erplyPending", [1, 2])
                ->select(["newsystem_orders.erplySalesDocumentID", "newsystem_orders.fullfillment_status", "newsystem_refunds.*", "newsystem_customers.erplyCustomerID", "newsystem_customers.erplyAddressID", "newsystem_order_delivery.erplyDeliveryID"])
                ->orderBy("updated_at", "asc")
                ->limit(1)
                // ->toSql();
                ->get();
        }

        if ($isDebug == 1) {
            dd($result);
        }
        // dd($result);
        if ($result->isEmpty()) {
            info("All Sales Refund/Return Syncced to Erply.");
            return response("All Sales Refund/Return Syncced to Erply.");
        }

        // dd($result);
        $bundleArray = array();
        $reqRefund = array();

        foreach ($result as $so) {
            ShopifySalesReturn::where("shopifyRefundString", $so["shopifyRefundString"])->update(['updated_at' => date('Y-m-d H:i:s'), "erplyPending" => 2]);
            $isPayload = 0;
            //first saving delivery address before order placed
            // $deliveryID = $this->saveDeliveryAddress($so->newSystemMemberID,$so->erplyCustomerID, $so->newSystemOrderID);

            if ($isDebug == 2) {
                dd($so);
            }

            $reqArray = array(
                "requestName" => "saveSalesDocument",
                "sessionKey" => $this->api->client->sessionKey,
                "clientCode" => $this->api->client->clientCode,
                "type" => "CREDITINVOICE",
                "creditToDocumentID" => $so->erplySalesDocumentID,
                "invoiceNo" => $so->newsystemRefundId,
                // "customNumber" => $so->newSystemOrderNumber,
                // "isCashInvoice" => 0,
                "confirmInvoice" => 1, //$so->fullfillment_status == "FULFILLED" ? 1 : 0,
                "warehouseID" => 8,
                "paymentType" => "CARD",
                "customerID" => $so->erplyCustomerID,
                "addressID" => $so->erplyAddressID,
                "shipToAddressID" => $so->erplyDeliveryID,
                "shipToID" => $so->erplyDeliveryID,
                "date" => date('Y-m-d', strtotime($so->orderUpdateDate)),
                "time" => date('H:i:s', strtotime($so->orderUpdateDate)),
                "attributeName1" => "ShopifyOrderID",
                "attributeType1" => "text",
                "attributeValue1" => $so->shopifyRefundString,
                // "notes" => "Test Order Refund/Return",
                // "paymentInfo" => $so->payment_detail_title,
            );

            $webshopNum = array();
            $webshopNum[] = "R" . substr($so->newSystemOrderNumber, 1);

            $webshopNum = json_encode($webshopNum, true);
            $reqArray["webShopOrderNumbers"] = $webshopNum;

            // if($so->payment_detail_status == "PAID"){
            $reqArray["paymentStatus"] = "PAID";
            // }

            //checking order exist
            $sDocID = $this->getSalesDocument($so->shopifyRefundString);
            if ($sDocID != '') {
                $reqArray["id"] = $sDocID;
            }

            // return items
            $count = 0;
            if ($so->refund_product_code != '') {
                $returnItems = explode(",", $so->refund_product_code);
                $returnItemsAmount = explode(",", $so->refund_product_price);
                $returnItemsQty = explode(",", $so->refund_product_qty);

                $count = count($returnItems);
                $flag = true;
                foreach ($returnItems as $key => $rItem) {
                    // dd($rItem);
                    $isPayload = 1;
                    $product = VariationProduct::where("code", $rItem)->first();

                    //now set product stock pending
                    if (@$product->parentProductID > 0) {
                        MatrixProduct::where("productID", $product->parentProductID)->update(["shopifySohPending" => 1]);
                    }

                    // dd($product);
                    $reqArray["productID" . $key + 1] = $product->productID;
                    $reqArray["vatrateID" . $key + 1] = 1;
                    $reqArray["amount" . $key + 1] = -abs((int) $returnItemsQty[$key]);
                    $reqArray["price" . $key + 1] = $returnItemsAmount[$key] / 1.1;

                    // if($l->discountAmount > 0){
                    //     $reqArray["discount".$key+1] = $l->discountAmount;
                    // }else{
                    //     if($disAmt > 0){

                    //         $disPer = ($disAmt / $product->priceListPrice) * 100;

                    //         $reqArray["discount".$key+1] = $disPer;
                    //     }
                    // } 

                }

                // if($so->refund_shipping_amount > 0){
                //     $reqArray["productID".$count+1] = 55554;
                //     $reqArray["vatrateID".$count+1] = 1;
                //     $reqArray["amount".$count+1] = -1;
                //     $reqArray["discount".$count+1] = 0;
                //     $reqArray["price".$count+1] = (double)$so->refund_shipping_amount;//- (double)$so->coupon_amount;
                // }

                // array_push($bundleArray,$reqArray );
            }
            // dd($so->refund_shipping_amount);
            if ($so->refund_shipping_amount > 0) {
                $reqArray["productID" . $count + 1] = 55554;
                $reqArray["vatrateID" . $count + 1] = 1;
                $reqArray["amount" . $count + 1] = -1;
                $reqArray["discount" . $count + 1] = 0;
                $reqArray["price" . $count + 1] = (float) $so->refund_shipping_amount / 1.1; //- (double)$so->coupon_amount;
                $isPayload = 1;
            }

            array_push($bundleArray, $reqArray);

            if ($isPayload == 1) {
                $reqRefund[] = $so;
            }
        }

        dd($bundleArray);
        if ($isDebug == 3) {
            dd($bundleArray);
            die;
        }

        // dd($bundleArray);
        if (count($reqRefund) < 1) {
            info("All Sales Refund Return Synced.");
            return response("All Sales Order Synced.");
        }

        $bundleArray = json_encode($bundleArray, true);
        $param = array(
            "lang" => 'eng',
            "responseType" => "json",
            "sessionKey" => $this->api->client->sessionKey,
        );
        $res = $this->api->sendRequest($bundleArray, $param, 1);

        if ($res['status']['errorCode'] == 0 && !empty($res['requests'])) {
            foreach ($reqRefund as $key => $c) {
                if ($res['requests'][$key]['status']['errorCode'] == 0) {
                    ShopifySalesReturn::where("shopifyRefundString", $c["shopifyRefundString"])->update(['erplyPending' => 0, 'erplyCreditInvoiceID' => $res['requests'][$key]['records'][0]['invoiceID']]);
                }
            }
            info("Sales Refund Return Created or Updated to Erply");
        }
        return response()->json(["status" => "success", "response" => $res]);
    }

    public function pushRefundReturnV2($req)
    {
        $sql = ShopifySalesReturn::join("newsystem_orders", "newsystem_orders.newSystemOrderNumber", "newsystem_refunds.newSystemOrderNumber")
            ->join("newsystem_customers", "newsystem_customers.newSystemMemberID", "newsystem_orders.newSystemCustomerID")
            ->join("newsystem_order_delivery", "newsystem_orders.newSystemOrderID", "newsystem_order_delivery.newSystemOrderID")
            ->where("newsystem_orders.erplyPending", 0)
            ->whereIn("newsystem_refunds.erplyPending", [1, 2])
            ->select(["newsystem_orders.erplySalesDocumentID", "newsystem_orders.fullfillment_status", "newsystem_refunds.*", "newsystem_customers.erplyCustomerID", "newsystem_customers.erplyAddressID", "newsystem_order_delivery.erplyDeliveryID"]);

        if (request()->query('invoice') != null) {
            $sql->where("newsystem_refunds.newSystemOrderNumber", "#" . request()->query('invoice'));
        }
        $result = $sql->orderBy("updated_at", "asc")->limit(1)->get();
        if (request()->query('debug') == 1)
            dd($result);

        if ($result->isEmpty()) {
            info("All Sales Refund/Return Syncced to Erply.");
            return response("All Sales Refund/Return Syncced to Erply.");
        }


        // Create Credit Tax Invoice 
        $response = $this->processCreditTaxInvoice($result);

        if (!empty($response)) {
            return response()->json(["status" => $response['type'], "response" => $response['response']]);
        } else {
            return response()->json(["status" => "fail", "response" => "Failed on response !!!"]);
        }
    }

    // Create Credit Tax Invoice 
    public function processCreditTaxInvoice($result)
    {

        $response_array = [];
        foreach ($result as $so) {
            $bundle_array = [];
            $req_refunds = [];

            // Change the status to 2 for processing 
            ShopifySalesReturn::where("shopifyRefundString", $so->shopifyRefundString)->update(['updated_at' => date('Y-m-d H:i:s'), "erplyPending" => 2]);

            // $webshopNum = [];
            // $webshopNum[] = "R" . substr($so->newSystemOrderNumber, 1);
            // $webshopNum = json_encode($webshopNum, true);

            if ($so->refund_product_code != '') {
                $returnItems = explode(",", $so->refund_product_code);
                $returnItemsAmount = explode(",", $so->refund_product_price);
                $returnItemsQty = explode(",", $so->refund_product_qty);
                $returnLocationId = explode(",", $so->refund_location_id);

                foreach ($returnItems as $key => $code) {

                    $req_array_1 = [];
                    $req_array_2 = $this->createRequest($so, $returnLocationId[$key]);
                    if (empty($req_array_2))
                        continue; // skip

                    // $req_array_1["webShopOrderNumbers"] = $webshopNum;
                    $req_array_1["paymentStatus"] = "PAID";

                    // checking order exist
                    $sales_document_id = $this->getSalesDocument($so->shopifyRefundString);

                    if ($sales_document_id != '') {
                        $req_array_1["id"] = $sales_document_id;
                    }

                    $product = VariationProduct::where("code", $code)->first();
                    if (empty($product))
                        continue; // Skip

                    // now set product stock pending
                    if (@$product->parentProductID > 0)
                        MatrixProduct::where("productID", $product->parentProductID)->update(["shopifySohPending" => 1]);

                    $req_array_1["productID1"] = $product->productID;
                    $req_array_1["vatrateID1"] = 1;
                    $req_array_1["amount1"] = -abs((int) $returnItemsQty[$key]);
                    $req_array_1["price1"] = $returnItemsAmount[$key] / 1.1;
                    array_push($bundle_array, array_merge($req_array_1, $req_array_2));
                    $req_refunds[] = $code;
                }
            }

            // Shipping Amount
            if ($so->refund_shipping_amount > 0) {
                $shipping_array = $this->createRequest($so, null);
                $shipping_array["productID1"] = 55554;
                $shipping_array["vatrateID1"] = 1;
                $shipping_array["amount1"] = -1;
                $shipping_array["discount1"] = 0;
                $shipping_array["price1"] = (float) $so->refund_shipping_amount / 1.1; //- (double)$so->coupon_amount;
                $req_refunds[] = 'freight';

                array_push($bundle_array, $shipping_array);
            }


            // dd($bundleArray);
            if (count($req_refunds) < 1)
                continue; // Skip

            if (request()->query('debug') == 2)
                dd('Bundle Array : ', $bundle_array);


            $bundle_array = json_encode($bundle_array, true);
            $param = array(
                "lang" => 'eng',
                "responseType" => "json",
                "sessionKey" => $this->api->client->sessionKey
            );



            // $res = $this->api->sendRequest($bundle_array, $param, 1);
            // dump('ERPLY Response : ', $res);


            //add logic of delete credit invoice (if refund and payment already done)
            if (isset($shipping_array['creditToDocumentID'])) {
                $payments = $this->getPaymentByDocumentId($shipping_array, $param, $so);

                dd($payments);
            }

            dd('out');

            $response_invoice_id_string = '';
            if ($res['status']['errorCode'] == 0 && !empty($res['requests'])) {
                foreach ($req_refunds as $key => $c) {
                    if ($res['requests'][$key]['status']['errorCode'] == 0) {
                        $response_invoice_id_string .= ($key > 0) ? ',' . $res['requests'][$key]['records'][0]['invoiceID'] : $res['requests'][$key]['records'][0]['invoiceID'];
                        $response_array[] = $res['requests'][$key]['records'][0]['invoiceID'];
                    }
                }
                if ($response_invoice_id_string != '') {
                    ShopifySalesReturn::where("shopifyRefundString", $so->shopifyRefundString)->update(['erplyPending' => 0, 'erply_credit_invoice_ids' => $response_invoice_id_string]);
                }
            } else {
                return ['type' => 'fail', 'response' => 'Sales Refund Return Not Synced.'];
            }
        }
        return ['type' => 'success', 'response' => $response_array];
    }

    public function createRequest($so, $locationId = null)
    {
        $location_id = null;
        if ($locationId == null) {
            $location_id = 8; // Roadhouse Australia
        } else {
            $location = Warehouse::where('shopifyId', $locationId)->first();
            if ($location)
                $location_id = $location->warehouseID;
        }
        if ($location_id != null) {
            return [
                "requestName" => "saveSalesDocument",
                "sessionKey" => $this->api->client->sessionKey,
                "clientCode" => $this->api->client->clientCode,
                "type" => "CREDITINVOICE", // Defines Refund
                "creditToDocumentID" => $so->erplySalesDocumentID,
                "warehouseID" => $location_id,
                "paymentType" => "CARD",
                "customerID" => $so->erplyCustomerID,
                "addressID" => $so->erplyAddressID,
                "shipToAddressID" => $so->erplyDeliveryID,
                "shipToID" => $so->erplyDeliveryID,
                "date" => date('Y-m-d', strtotime($so->orderUpdateDate)),
                "time" => date('H:i:s', strtotime($so->orderUpdateDate)),
                "attributeName1" => "ShopifyOrderID",
                "attributeType1" => "text",
                "attributeValue1" => $so->shopifyRefundString
            ];
        } else {
            return [];
        }
    }


    public function getPaymentByDocumentId($shippingArray, $param, $refundsModel)
    {

        $bundleArray[] = [
            'requestName' => 'getPayments',
            'documentID' => $shippingArray['creditToDocumentID'],
            'recordsOnPage' => 100,
            "sessionKey" => $shippingArray['sessionKey'],
            "clientCode" => $shippingArray['clientCode'],
        ];


        $jsonData = json_encode($bundleArray, true);
        $response  = $this->api->sendRequest($jsonData, $param, 1);


        if (isset($response['status']) && $response['status']['responseStatus'] == 'ok') {
            if (isset($response['requests'])) {
                $records = $response['requests'][0]['records'];
                //here type 4 mean credit invoice so we need to delete that 
                $paymentIds = collect($records)
                    ->where('typeID', 4)   // or '4'
                    ->pluck('paymentID')
                    ->values()
                    ->toArray();

                $deleteArray = [];

                $deleteResponse = [];

                foreach ($paymentIds as $key => $paymentId) {

                    //delete refund payment 

                    $deleteArray[] = [
                        'requestName' => 'deletepayment',
                        'paymentID' => $paymentId,
                        'sessionKey' => $shippingArray['sessionKey'],
                        "clientCode" => $shippingArray['clientCode'],
                    ];
                }

                dd('here');

                if (count($deleteArray) > 0) {

                    $deleteJsonData = json_encode($deleteArray, true);

                    $deleteResponse  = $this->api->sendRequest($deleteJsonData, $param, 1);

                    if (isset($deleteResponse['status']) && $deleteResponse['status']['responseStatus'] == 'ok') {
                        //after success update flag  1 = deleted, 0 is not deleted and 2 is if getting any error of deleted 
                        //note it only delete if type is 4 so if there was no type 4 and deleted flag is 0 it does not mean it pending

                        $jsonDataOfDeleted = [
                            'ids'  => $paymentIds, // already an array
                            'date' => now()->toDateTimeString(),
                        ];

                        $refundsModel->update([
                            'credit_invoice_delete' => 1,
                            'deleted_payment_id'    => json_encode($jsonDataOfDeleted),
                        ]);
                    } else {

                        $refundsModel->update(['credit_invoice_delete' => 2]);
                    }
                }
            }
        }
        return $deleteResponse;
    }
}
