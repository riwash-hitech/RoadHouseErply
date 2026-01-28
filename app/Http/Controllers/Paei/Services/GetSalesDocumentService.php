<?php
namespace App\Http\Controllers\Paei\Services;

use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\ErplySync;
use App\Models\PAEI\PurchaseDocument;
use App\Models\PAEI\SalesDetail;
use App\Models\PAEI\SalesDocument;
use App\Models\PAEI\SalesDocumentDetail;
use App\Models\Shopify\ModuleShopifySalesOrder;
use App\Models\Shopify\ShopifySalesOrder;

class GetSalesDocumentService{

    protected $sd;
    protected $detail;
    protected $api;
    protected $letsLog;

    public function __construct(SalesDocument $c, SalesDocumentDetail $detail, EAPIService $api,UserLogger $logger){
        $this->sd = $c;
        $this->detail = $detail;
        $this->api = $api;
        $this->letsLog = $logger;
    }

    public function saveUpdate($sds){
        foreach($sds as $c){
            $this->saveUpdateSalesDocument($c);
            // if(@$c['rows']){
            //     $this->saveSalesDetails($c['rows'], $c['id']);
            // }

        }
        ErplySync::where("id", 1)->update(["salesDocument" => date('Y-m-d H:i:s', $c['lastModified'] )]);
        return response()->json(['status'=>200, 'message'=>"Sales Document fetched Successfully."]);
    }

    protected function saveUpdateSalesDocument($product){
        // $old = $this->sd->where('clientCode',  $this->api->client->clientCode)->where('salesDocumentID', $product['id'])->first();
        if(strtolower($product["invoiceState"]) == "shipped"){
            $checkOrderExist = ShopifySalesOrder::where("erplySalesDocumentID", $product["id"])->first();
            if($checkOrderExist){
                ModuleShopifySalesOrder::where("shopify_order_string_id", $checkOrderExist->newSystemOrderID)->update(["is_invoice_fulfilled" => 1]);
            }
        }
        return 0;
        die("dead end");
        $change = $this->sd->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "salesDocumentID"  =>  $product['id']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    'salesDocumentID' => $product["id"],
                    'type' => @$product["type"],
                    'exportInvoiceType' => @$product["exportInvoiceType"],
                    'currencyCode' => @$product["currencyCode"],
                    'currencyRate' => @$product["currencyRate"],
                    'warehouseID' => @$product["warehouseID"],
                    'warehouseName' => @$product["warehouseName"],
                    'pointOfSaleID' => @$product["pointOfSaleID"],
                    'pointOfSaleName' => @$product["pointOfSaleName"],
                    'pricelistID' => @$product["pricelistID"],
                    'number' => @$product["number"],
                    'date' => @$product["date"],
                    'inventoryTransactionDate' => @$product["inventoryTransactionDate"] ? @$product["inventoryTransactionDate"] : '0000-00-00 00:00:00',
                    'time' => @$product["time"],
                    'clientID' => @$product["clientID"],
                    'clientName' => @$product["clientName"],
                    'clientEmail' => @$product["clientEmail"],
                    'clientCardNumber' => @$product["clientCardNumber"],
                    'addressID' => @$product["addressID"],
                    'address' => @$product["address"],
                    'clientFactoringContractNumber' => @$product["clientFactoringContractNumber"],
                    'clientPaysViaFactoring' => @$product["clientPaysViaFactoring"],
                    'payerID' => @$product["payerID"],
                    'payerName' => @$product["payerName"],
                    'payerAddressID' => @$product["payerAddressID"],
                    'payerAddress' => @$product["payerAddress"],
                    'payerFactoringContractNumber' => @$product["payerFactoringContractNumber"],
                    'payerPaysViaFactoring' => @$product["payerPaysViaFactoring"],
                    'shipToID' => @$product["shipToID"],
                    'shipToName' => @$product["shipToName"],
                    'shipToAddressID' => @$product["shipToAddressID"],
                    'shipToAddress' => @$product["shipToAddress"],
                    'contactID' => @$product["contactID"],
                    'contactName' => @$product["contactName"],
                    'shipToContactID' => @$product["shipToContactID"],
                    'shipToContactName' => @$product["shipToContactName"],
                    'employeeID' => @$product["employeeID"],
                    'employeeName' => @$product["employeeName"],
                    'projectID' => @$product["projectID"],
                    'invoiceState' => @$product["invoiceState"],
                    'paymentType' => @$product["paymentType"],
                    'paymentTypeID' => @$product["paymentTypeID"],
                    'paymentDays' => @$product["paymentDays"],
                    'paymentStatus' => @$product["paymentStatus"],
                    'baseDocuments' => !empty($product['baseDocuments']) ? json_encode($product['baseDocuments'],true) : '', 
                    'followUpDocuments' => !empty($product['followUpDocuments']) ? json_encode($product['followUpDocuments'],true) : '', 
                    'previousReturnsExist' => @$product["previousReturnsExist"],
                    'printDiscounts' => @$product["printDiscounts"],
                    'algorithmVersion' => @$product["algorithmVersion"],
                    'algorithmVersionCalculated' => @$product["algorithmVersionCalculated"],
                    'confirmed' => @$product["confirmed"],
                    'notes' => @$product["notes"],
                    'internalNotes' => @$product["internalNotes"],
                    'netTotal' => @$product["netTotal"],
                    'vatTotal' => @$product["vatTotal"],
                    'netTotalsByRate' => !empty($product['netTotalsByRate']) ? json_encode($product['netTotalsByRate'],true) : '', 
                    'vatTotalsByRate' => !empty($product['vatTotalsByRate']) ? json_encode($product['vatTotalsByRate'],true) : '', 
                    'netTotalsByTaxRate' =>  !empty($product['netTotalsByTaxRate']) ? json_encode($product['netTotalsByTaxRate'],true) : '', 
                    'vatTotalsByTaxRate' =>  !empty($product['vatTotalsByTaxRate']) ? json_encode($product['vatTotalsByTaxRate'],true) : '', 
                    'rounding' => @$product["rounding"],
                    'total' => @$product["total"],
                    'paid' => @$product["paid"],
                    'externalNetTotal' => @$product["externalNetTotal"],
                    'externalVatTotal' => @$product["externalVatTotal"],
                    'externalRounding' => @$product["externalRounding"],
                    'externalTotal' => @$product["externalTotal"],
                    'taxExemptCertificateNumber' => @$product["taxExemptCertificateNumber"],
                    'otherCommissionReceivers' =>!empty($product['otherCommissionReceivers']) ? json_encode($product['otherCommissionReceivers'],true) : '', 
                    'packerID' => @$product["packerID"],
                    'referenceNumber' => @$product["referenceNumber"],
                    'webShopOrderNumbers' => !empty($product['webShopOrderNumbers']) ? json_encode($product['webShopOrderNumbers'],true) : '', 
                    'trackingNumber' => @$product["trackingNumber"],
                    'fulfillmentStatus' => @$product["fulfillmentStatus"],
                    'customReferenceNumber' => @$product["customReferenceNumber"],
                    'cost' => @$product["cost"],
                    'reserveGoods' => @$product["reserveGoods"],
                    'reserveGoodsUntilDate' => @$product["reserveGoodsUntilDate"] ? @$product["reserveGoodsUntilDate"] : '0000-00-00',
                    'deliveryDate' => @$product["deliveryDate"] ? $product["deliveryDate"] : '0000-00-00',
                    'deliveryTypeID' => @$product["deliveryTypeID"],
                    'deliveryTypeName' => @$product["deliveryTypeName"],
                    'shippingDate' => @$product["shippingDate"] ? @$product["shippingDate"] : '0000-00-00',
                    'packingUnitsDescription' => @$product["packingUnitsDescription"],
                    'penalty' => @$product["penalty"],
                    'triangularTransaction' => @$product["triangularTransaction"],
                    'purchaseOrderDone' => @$product["purchaseOrderDone"],
                    'transactionTypeID' => @$product["transactionTypeID"],
                    'transactionTypeName' => @$product["transactionTypeName"],
                    'transportTypeID' => @$product["transportTypeID"],
                    'transportTypeName' => @$product["transportTypeName"],
                    'deliveryTerms' => @$product["deliveryTerms"],
                    'deliveryTermsLocation' => @$product["deliveryTermsLocation"],
                    'euInvoiceType' => @$product["euInvoiceType"],
                    'deliveryOnlyWhenAllItemsInStock' => @$product["deliveryOnlyWhenAllItemsInStock"],
                    'eInvoiceBuyerID' => @$product["eInvoiceBuyerID"],
                    'workOrderID' => @$product["workOrderID"],
                    'lastModified' => date('Y-m-d H:i:s', $product['lastModified']), 
                    'lastModifierUsername' => @$product["lastModifierUsername"],
                    'added' =>date('Y-m-d H:i:s', @$product['added']), 
                    'invoiceLink' => @$product["invoiceLink"],
                    'receiptLink' => @$product["receiptLink"],
                    'returnedPayments' => !empty($product['returnedPayments']) ? json_encode($product['returnedPayments'],true) : '', 
                    'amountAddedToStoreCredit' => @$product["amountAddedToStoreCredit"],
                    'amountPaidWithStoreCredit' => @$product["amountPaidWithStoreCredit"],
                    'applianceID' => @$product["applianceID"],
                    'applianceReference' => @$product["applianceReference"],
                    'assignmentID' => @$product["assignmentID"],
                    'vehicleMileage' => @$product["vehicleMileage"],
                    'customNumber' => @$product["customNumber"],
                    'advancePayment' => @$product["advancePayment"],
                    'advancePaymentPercent' => @$product["advancePaymentPercent"],
                    'printWithOriginalProductNames' => @$product["printWithOriginalProductNames"],
                    'hidePrices' => @$product["hidePrices"],
                    'hideAmounts' => @$product["hideAmounts"],
                    'hideTotal' => @$product["hideTotal"],
                    'isFactoringInvoice' => @$product["isFactoringInvoice"],
                    'taxOfficeID' => @$product["taxOfficeID"],
                    'periodStartDate' => @$product["periodStartDate"],
                    'periodEndDate' => @$product["periodEndDate"],
                    'orderArrived' => @$product["orderArrived"],
                    'orderInvoiced' => @$product["orderInvoiced"],
                    'ediStatus' => @$product["ediStatus"],
                    'ediText' => @$product["ediText"],
                    'documentURL' => @$product["documentURL"],
                    'hidePaymentDays' => @$product["hidePaymentDays"],
                    'creditInvoiceType' => @$product["creditInvoiceType"],
                    'issuedCouponIDs' => @$product["issuedCouponIDs"],
                    'attributes' => !empty($product['attributes']) ? json_encode($product['attributes'],1) : '', 
                    'longAttributes' => !empty($product['longAttributes']) ? json_encode($product['longAttributes'],1) : '', 
                    'jdoc' => !empty($product['jdoc']) ? json_encode($product['jdoc'],1) : '', 
                    'rows' => !empty($product['rows']) ? json_encode($product['rows'],1) : '', 
                ]
            );
            // $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($change, true), $old  ? "Sales Document Updated" : "Sales Document Created");        
    }

    protected function saveSalesDetails($details, $sdid){
        //first delete all details if exist
        $this->detail->where('salesDocumentID', $sdid)->delete();
        foreach($details as $d){
            $this->detail->updateOrcreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "salesDocumentID" => $sdid,
                    "rowID" => @$d["rowID"],
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "salesDocumentID" => $sdid,
                    "rowID" => @$d["rowID"],
                    "stableRowID" => @$d["stableRowID"],
                    "productID" => @$d["productID"],
                    "serviceID" => @$d["serviceID"],
                    "itemName" => @$d["itemName"],
                    "code" => @$d["code"],
                    "vatrateID" => @$d["vatrateID"],
                    "amount" => @$d["amount"],
                    "price" => @$d["price"],
                    "discount" => @$d["discount"],
                    "finalNetPrice" => @$d["finalNetPrice"],
                    "finalPriceWithVAT" => @$d["finalPriceWithVAT"],
                    "rowNetTotal" => @$d["rowNetTotal"],
                    "rowVAT" => @$d["rowVAT"],
                    "rowTotal" => @$d["rowTotal"],
                    "deliveryDate" => @$d["deliveryDate"] == '' ? "0000-00-00 00:00:00" : @$d["deliveryDate"],
                    "returnReasonID" => @$d["returnReasonID"],
                    "employeeID" => @$d["employeeID"],
                    "campaignIDs" => @$d["campaignIDs"],
                    "containerID" => @$d["containerID"],
                    "containerAmount" => @$d["containerAmount"],
                    "originalPriceIsZero" => @$d["originalPriceIsZero"],
                    "packageID" => @$d["packageID"],
                    "amountOfPackages" => @$d["amountOfPackages"],
                    "amountInPackage" => @$d["amountInPackage"],
                    "packageType" => @$d["packageType"],
                    "packageTypeID" => @$d["packageTypeID"],
                    "sourceWaybillID" => @$d["sourceWaybillID"],
                    "billingStatementID" => @$d["billingStatementID"],
                    "billingStartDate" => @$d["billingStartDate"],
                    "billingEndDate" => @$d["billingEndDate"],
                    "batch" => @$d["batch"],
                    "warehouseValue" => @$d["warehouseValue"],
                    "jdoc" => !empty($product['jdoc']) ? json_encode($product['jdoc'],true) : '', 
                ]
                );
        }
    }


    public function getLastUpdateDate(){
        $latest = ErplySync::first()->salesDocument;
        if ($latest) {
            return strtotime($latest);

        }
        return 0; // strtotime($latest);
    }
}
