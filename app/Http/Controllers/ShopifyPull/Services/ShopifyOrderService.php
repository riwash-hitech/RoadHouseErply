<?php

namespace App\Http\Controllers\ShopifyPull\Services;

use DateTime;
use DateTimeZone;
use App\Traits\ShopifyTrait;
use App\Models\Shopify\ShopifyCursor;
use App\Models\Shopify\ShopifyCustomer;
use App\Models\Shopify\ShopifySalesOrder;
// use App\Models\Shopify\ShopifyOrderRefund;
use App\Models\Shopify\ShopifySalesOrderLine;
use App\Models\Shopify\ShopifySalesOrderDelivery;
use App\Models\Shopify\ShopifySalesReturn;

class ShopifyOrderService
{
    use ShopifyTrait;
    protected $clientCode;
    protected $live = 0;

    public function __construct()
    {
 
        $this->clientCode = '603965';
        $this->live = 1;
    }

    private function setEnv($req)
    {
        if (isset($req->islive) == 1) {
            $this->live = $req->islive;
        }
    }


    public function getOrders($req)
    {
        $this->setEnv($req);
        $debug = 0;
        if (isset($req->debug)) {
            $debug = $req->debug;
        }
        //first getting cursor
        $query = $this->getOrdersQuery($this->clientCode);
        if ($debug == 1) {
            dd($query);
        }
        $datas = $this->sendShopifyQueryRequestV2("POST", $query, $this->live);
        if ($debug == 2) {
            dd($datas, $query, @$datas->data->orders->edges);
        }

        if(!empty($datas->data->orders->edges)){
            foreach (@$datas->data->orders->edges as $mp) {
                $details = $mp->node;
                $newsystemOrderId = str_replace('gid://shopify/Order/', '', $details->id);
                $discountcode = [];
                foreach ($details->discountCodes as $dc) {
                    $discountcode[] = $dc;
                }
                $coupon = implode(",", $discountcode);
                $orderData = array(
                    'newSystemOrderNumber' => $details->name,
                    'newSystemCustomerID' => $details->customer->id,
                    'order_created' => (new DateTime($details->createdAt, new DateTimeZone('GMT')))->setTimezone(new DateTimeZone('Australia/Sydney'))->format('Y-m-d H:i:s'),
                    'newSystemCustomerEmail' => $details->customer->email,
                    'order_completed' => (new DateTime($details->processedAt, new DateTimeZone('GMT')))->setTimezone(new DateTimeZone('Australia/Sydney'))->format('Y-m-d H:i:s'),
                    'currency' => $details->currencyCode,
                    'order_total' => $details->totalPriceSet->shopMoney->amount,
                    'order_subtotal' => $details->subtotalPriceSet->shopMoney->amount,
                    'total_items' => count($details->lineItems->edges),
                    'shipping_methods' => @$details->shippingLine->code ?? '',
                    'total_shipping' => @$details->shippingLine->originalPriceSet->shopMoney->amount,
                    'payment_detail_title' => end($details->paymentGatewayNames),
                    'payment_detail_status' => $details->displayFinancialStatus,
                    'note' => $details->note ? addslashes($details->note) : '',
                    'taxAmount' => $details->totalTaxSet->shopMoney->amount,
                    'coupon_code' => $coupon,
                    'coupon_amount' => @$details->totalDiscountsSet->shopMoney->amount,
                    'internalPaymentType' => end($details->paymentGatewayNames),
                    'fullfillment_status' => $details->displayFulfillmentStatus,
                    'isLive' => $this->live,
                    'shopifyCursor' => $mp->cursor,
                    'risk_level' => $this->checkRiskLevel($details->risks),
                    'riskLevelLog' => $details->risks ? json_encode($details->risks, 1) : '' 
                );
                $order = ShopifySalesOrder::updateOrcreate(
                    [
                        'newsystemOrderId' => $newsystemOrderId,
                    ],
                    $orderData
                );
    
                if($order){
                    $this->pushOrderProducts($details, $newsystemOrderId, $debug);
                    $this->pushOrderDelivery($details, $newsystemOrderId);
                    $this->pushOrderCustomer($details);
                }
            }
            /** update or create cursor */
            if (@$datas->data->orders->pageInfo->endCursor) {
                $lastCursor = @$datas->data->orders->pageInfo->endCursor;
                ShopifyCursor::updateOrcreate(
                    [
                        "clientCode" => $this->clientCode,
                        "cursorName" => "orders",
                        "isLive" => $this->live
                    ],
                    [
                        "clientCode" => $this->clientCode,
                        "cursorName" => "orders",
                        "cursor" => $lastCursor,
                        "isLive" => $this->live
                    ]
                );
            }
            return response("Orders Synccing from Shopify...");
        }
        else{
            return response("No Orders Found to Sync");
        }
    }

    private function checkRiskLevel($risks) :int
    {
        $riskLevel = 0;
        foreach ($risks as $key => $risk) {
            if($risk->level == 'LOW'){
                $riskLevel = 1;
            }
            if($risk->level == 'MEDIUM'){
                $riskLevel = 2;
            }
            if($risk->level == 'HIGH'){
                $riskLevel = 3;
            }
        }
        return $riskLevel;
    }

    public function pushOrderProducts($details, $newsystemOrderId, $debug)
    {
        if(isset($details->lineItems->edges) && !empty($details->lineItems->edges)){
            foreach($details->lineItems->edges as $orderProduct) {
                $orderProduct = $orderProduct->node;
                $newsystemProductId = str_replace('gid://shopify/LineItem/', '', $orderProduct->id);
                $discountproducttotalamount = $orderProduct->totalDiscountSet->shopMoney->amount;
                $color = '';
                $size = '';
                $cistockcode = $orderProduct->sku;
                $price = 0;
                $quantity = $orderProduct->quantity;
                if (!empty($orderProduct->variant)) {
                    foreach($orderProduct->variant->selectedOptions as $productOption) {
                        if($productOption->name == 'Color') {
                            $color = $productOption->value;
                        }
                        if($productOption->name == 'Variant,Size') {
                            $size = $productOption->value;
                        }
                    }
                    $price = $orderProduct->variant->price;
                    $cistockcode =  $orderProduct->variant->sku;
                }
                $orderProductData = array(
                    'stockCode'=>$cistockcode,
                    'actualCIStockCode'=>$cistockcode,
                    'colour'=>$color,
                    'size'=>$size,
                    'quantity'=>$quantity,
                    'unitPrice'=>$price,
                    'discountAmount'=>$discountproducttotalamount
                );

                $order = ShopifySalesOrderLine::updateOrcreate(
                    [
                        'newSystemProductId' => $newsystemProductId,
                        'newsystemOrderID' => $newsystemOrderId,
                    ],
                    $orderProductData
                );
                if ($debug == 3) {
                    dd($orderProductData, $order);
                }
            }
        }

 
        
    }
    public function pushOrderDelivery($details, $newsystemOrderId)
    {
        if (isset($details->billingAddress) && $details->billingAddress != '') {
            $c_address = $details->billingAddress;
        }
        if ( isset($details->shippingAddress) && $details->shippingAddress != '') {
            $c_address = $details->shippingAddress;
        }
        if(!empty($c_address)){
            $newSystemMemberID  = str_replace('gid://shopify/Customer/', '', $details->customer->id);
            $deliveryData = array(
                'deliveryFirstName' => $c_address->firstName,
                'deliveryLastName' => $c_address->lastName,
                'deliveryPhone' => $c_address->phone,
                'deliveryStreet' => $c_address->address1 . ' ' . $c_address->address2,
                'deliverySuburb' => $c_address->address2 == '' ? '' : $c_address->address2,
                'deliveryPostCode' => $c_address->zip,
                'deliveryCountry' => $c_address->country,
                'deliveryCity' => $c_address->city,
                'deliveryState' => $c_address->province
            );
    
            ShopifySalesOrderDelivery::updateOrcreate(
                [
                    'newSystemMemberID' => $newSystemMemberID,
                    'newSystemOrderID' => $newsystemOrderId,
                ],
                $deliveryData
            );
        }
    }
    public function pushOrderCustomer($details)
    {
        if(!empty($details->customer)){
            $newSystemMemberID  = str_replace('gid://shopify/Customer/', '', $details->customer->id);
                $customerData = array(
                    'clientCode' => $this->clientCode, //$this->norrisClientCode,
                    'shopifyCustomerId' => $details->customer->id,
                    'newSystemMemberID' => $newSystemMemberID,
                    'emailAddress' => $details->customer->email ?? '',
                    'firstName' => $details->customer->firstName ?? '',
                    'lastName' => $details->customer->lastName ?? '',
                    'companyName' =>isset($details->defaultAddress, $details->defaultAddress->company) ? $details->defaultAddress->company : '',
                    'phone' => $details->customer->defaultAddress->phone ?? '',
                    'street' => isset($details->customer->defaultAddress, $details->customer->defaultAddress->street) ? $details->customer->defaultAddress->street : '',
                    'city' => isset($details->customer->defaultAddress, $details->customer->defaultAddress->city) ? $details->customer->defaultAddress->city : '',
                    'postCode' => isset($details->customer->defaultAddress, $details->customer->defaultAddress->zip) ? $details->customer->defaultAddress->zip : '',
                    'country' => isset($details->customer->defaultAddress, $details->customer->defaultAddress->country) ? $details->customer->defaultAddress->country : '',
                    'state' =>isset($details->customer->defaultAddress, $details->customer->defaultAddress->province) ? $details->customer->defaultAddress->province : '',
                    'erplyPending' => 1,
                    'syncEntryDate' => date('Y-m-d H:i:s'),
                    'lastupdateDate' => date('Y-m-d H:i:s'),
                    'lastMapped' => date('Y-m-d H:i:s'),
                    'metafields' => null,
                    'note' => '',
                    'address1' =>isset($details->customer->defaultAddress, $details->customer->defaultAddress->address1) ? $details->customer->defaultAddress->address1 : '',
                    'address2' => isset($details->customer->defaultAddress, $details->customer->defaultAddress->address2) ? $details->customer->defaultAddress->address2 : '',
                    'smsMarketingConsent' => '',
                    'emailMarketingConsent' => '',
                    'numberOfOrders' => '',
                    'amountSpent' => '',
                    'countryCodeV2' => '',
                    'provinceCode' => '',
                );
    
                ShopifyCustomer::updateOrcreate(
                    [
                        'shopifyCustomerId' => $details->customer->id,
                    ],
                    $customerData
                );
        }
    }

    public function getRefund($req)
    {
        // $this->setEnv($req);
        $debug = $req->debug ? $req->debug : 0;
        
        //first getting cursor
        $query = $this->getRefundQuery($this->clientCode);
        if ($debug == 1) {
            dd($query);
        }
        $datas = $this->sendShopifyQueryRequestV2("POST", $query, $this->live);
        if ($debug == 2) {
            dd($datas, $query);
        }

        // die;

        if(!empty($datas->data->orders->edges)){
            foreach (@$datas->data->orders->edges as $mp) {
                $details = $mp->node;
                $newsystemOrderId = str_replace('gid://shopify/Order/', '', $details->id);
                $newsystemRefundId = str_replace('gid://shopify/Refund/', '', $details->refunds[0]->id);
                $refund_product_id = [];
                $refund_product_code = [];
                $refund_product_price = [];
                $refund_product_qty = [];
                foreach ($details->refunds[0]->refundLineItems->edges as $lineItem) {
                    $refundProductId = str_replace('gid://shopify/LineItem/', '', $lineItem->node->lineItem->id);
                    $refund_product_id[] = $refundProductId;
                    $refund_product_code[] = $lineItem->node->lineItem->sku;
                    $refund_product_price[] = $lineItem->node->priceSet->shopMoney->amount;
                    $refund_product_qty[] = $lineItem->node->quantity;
                }

                $refundData = array(
                    'shopifyRefundString' => $details->refunds[0]->id,
                    'newsystemRefundId' => $newsystemRefundId,
                    'newsystemOrderId' => $newsystemOrderId,
                    'newSystemOrderNumber' => $details->name,
                    'refund_amount' => $details->refunds[0]->totalRefundedSet->shopMoney->amount,
                    'refund_product_id' => isset($refund_product_id) ? implode(',', $refund_product_id) : '',
                    'refund_product_code' => isset($refund_product_code) ? implode(',', $refund_product_code) : '',
                    'refund_product_price' => isset($refund_product_price) ? implode(',', $refund_product_price) : '',
                    'refund_product_qty' => isset($refund_product_qty) ? implode(',', $refund_product_qty) : '',
                    'refund_shipping_amount' => $details->shippingLine->originalPriceSet->shopMoney->amount ?? 0,
                );
                if ($debug == 3) {
                    dd($refundData);
                }

                // ShopifySalesReturn::updateOrcreate(
                //     [
                //         'newsystemRefundId' => $newsystemRefundId,
                //     ],
                //     $refundData
                // );
            }
            /** update or create cursor */
            if (@$datas->data->orders->pageInfo->endCursor) {
                $lastCursor = @$datas->data->orders->pageInfo->endCursor;
                ShopifyCursor::updateOrcreate(
                    [
                        "clientCode" => $this->clientCode,
                        "cursorName" => "refund",
                        "isLive" => $this->live
                    ],
                    [
                        "clientCode" => $this->clientCode,
                        "cursorName" => "refund",
                        "cursor" => $lastCursor,
                        "isLive" => $this->live
                    ]
                );
            }
            return response("Orders Synccing from Shopify...");
        }
        else{
            return response("No Orders Found to Sync");
        }
    }
}
