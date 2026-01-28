<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\PurchaseDocument;
use App\Models\PAEI\PurchaseDocumentDetail;
use App\Models\PAEI\Supplier;

class GetPurchaseDocumentService{

    protected $pd;
    protected $detail;
    protected $api;

    public function __construct(PurchaseDocument $c, PurchaseDocumentDetail $detail, EAPIService $api){
        $this->pd = $c;
        $this->detail = $detail;
        $this->api = $api;
    }

    public function saveUpdate($pds){

        foreach($pds as $c){
            $this->saveUpdatePurchaseDocument($c);
            if(@$c['rows']){
                $this->saveUpdatePurchaseDocumentDetails($c['rows'], $c['id']);
            }
        }

        return response()->json(['status'=>200, 'message'=>"Purchase Document fetched Successfully."]);
    }

    protected function saveUpdatePurchaseDocument($product){

        $this->pd->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "purchaseDocumentID"  =>  $product['id']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "purchaseDocumentID" => $product['id'],
                    "type" => $product['type'],
                    "status" => $product['status'],
                    "currencyCode" => @$product['currencyCode'],
                    "currencyRate"  => @$product['currencyRate'],
                    "warehouseID"  => @$product['warehouseID'],
                    "warehouseName"  => @$product['warehouseName'],
                    "number"  => @$product['number'],
                    "regnumber"  => @$product['regnumber'],
                    "date"  =>  @$product['date']  == '' ? '0000-00-00' : $product['date'],
                    "inventoryTransactionDate"  =>  @$product['inventoryTransactionDate'],
                    "time"  =>  @$product['time'],
                    "supplierID"  =>  @$product['supplierID'],
                    "supplierName"  => $product['supplierName'],
                    "supplierGroupID"  => $product['supplierGroupID'],
                    "addressID"  => @$product['addressID'],
                    "address"  => @$product['address'],
                    "contactID"  => @$product['contactID'],
                    "contactName"  => $product['contactName'],
                    "employeeID"  => @$product['employeeID'],
                    "employeeName"  => @$product['employeeName'],
                    "supplierID2"  => @$product['supplierID2'],
                    "supplierName2"  => @$product['supplierName2'],
                    "stateID"  => @$product['stateID'],
                    "paymentDays"  => @$product['paymentDays'],
                    "paid"  => @$product['paid'],
                    "transactionTypeID"  => @$product['transactionTypeID'],
                    "transportTypeID"  => @$product['transportTypeID'],
                    "deliveryTermsID"  => @$product['deliveryTermsID'],
                    "deliveryTermsLocation"  => @$product['deliveryTermsLocation'],
                    "deliveryAddressID"  => @$product['deliveryAddressID'],
                    "triangularTransaction"  => @$product['triangularTransaction'],
                    "projectID"  => @$product['projectID'],
                    "reasonID"  => @$product['reasonID'],
                    "confirmed"  => @$product['confirmed'],
                    "referenceNumber"  => @$product['referenceNumber'],
                    "notes"  => @$product['notes'],
                    "ediStatus"  => @$product['ediStatus'],//today date time
                    "ediText"  => @$product['ediText'],
                    "documentURL"  => @$product['documentURL'],
                    "rounding"  => @$product['rounding'],// today date
                    "netTotal"  => @$product['netTotal'],
                    "vatTotal"  => @$product['vatTotal'],
                    "total"  => @$product['total'],
                    "netTotalsByTaxRate"  =>  !empty(@$product['netTotalsByTaxRate']) ? json_encode($product['netTotalsByTaxRate'],true) : '', 
                    "vatTotalsByTaxRate"  => !empty(@$product['vatTotalsByTaxRate']) ? json_encode($product['vatTotalsByTaxRate'],true) : '', 
                    "invoiceLink"  => @$product['invoiceLink'],
                    "shipDate"  => @$product['shipDate'],
                    "cost"  => @$product['cost'],
                    "netTotalForAccounting"  => @$product['netTotalForAccounting'],
                    "totalForAccounting"  => @$product['totalForAccounting'],
                    "additionalCosts"  => @$product['additionalCosts'],
                    "additionalCostsCurrencyId"  => @$product['additionalCostsCurrencyId'],
                    "additionalCostsCurrencyRate"  => @$product['additionalCostsCurrencyRate'],
                    "additionalCostsDividedBy"  => @$product['additionalCostsDividedBy'],
                    "baseToDocuments"  => !empty(@$product['baseToDocuments']) ? json_encode($product['baseToDocuments'],true) : '', 
                    "baseDocuments"  => !empty(@$product['baseDocuments']) ? json_encode($product['baseDocuments'],true) : '', 
                    "added"  => date('Y-m-d H:i:s',$product['added']),
                    "addedby"  => @$product['addedby'],
                    "changedby"  => @$product['changedby'],
                    "lastModified"  => date('Y-m-d H:i:s',$product['added']),
                    "rows"  =>!empty(@$product['rows']) ? json_encode($product['rows'],true) : '', 
                    "attributes"  => !empty(@$product['attributes']) ? json_encode($product['attributes'],true) : '', 
                     
                ]
            );
    }

    protected function saveUpdatePurchaseDocumentDetails($details, $pid){
        $this->detail->where('clientCode', $this->api->client->clientCode)->where('purchaseDocumentID', $pid)->delete();
        foreach($details as $d){
            $this->detail->create(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "purchaseDocumentID" => $pid, 
                    "stableRowID" => @$d["stableRowID"],
                    "productID" => @$d["productID"],
                    "serviceID" => @$d["serviceID"],
                    "itemName" => @$d["itemName"],
                    "code" => @$d["code"],
                    "code2" => @$d["code2"],
                    "vatrateID" => @$d["vatrateID"],
                    "amount" => @$d["amount"],
                    "price" => @$d["price"],
                    "discount" => @$d["discount"],
                    "deliveryDate" => @$d["deliveryDate"],
                    "unitCost" => @$d["unitCost"],
                    "costTotal" => @$d["costTotal"],
                    "packageID" => @$d["packageID"],
                    "amountOfPackages" => @$d["amountOfPackages"],
                    "amountInPackage" => @$d["amountInPackage"],
                    "packageType" => @$d["packageType"],
                    "packageTypeID" => @$d["packageTypeID"],
                    "jdoc" => !empty($product['jdoc']) ? json_encode($product['jdoc'],true) : '', 
                    "supplierPriceListSupplierCode" => @$d["supplierPriceListSupplierCode"],
                    "supplierPriceListImportCode" => @$d["supplierPriceListImportCode"],
                    "supplierPriceListNotes" => @$d["supplierPriceListNotes"],
                     
                ]
                );
        }
    }


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->pd->orderBy('lastModified', 'desc')->first();
        if($latest){
            return strtotime($latest->lastModified);
        }
        return 0;// strtotime($latest);
    }
}
