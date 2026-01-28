<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\Except;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\ProductGroup;
use App\Models\PAEI\ReasonCode;
use App\Models\PAEI\SalesDocument;
use App\Models\PAEI\Warehouse;

class SalesDocumentApiService{

    protected $sales;
    protected $api;
    public function __construct(SalesDocument $w,EAPIService $api){
        $this->sales = $w;
        $this->api = $api;
    } 

    public function getSalesDocuments($req){
        // echo $this->api->client->clientCode;
        // die;
        // echo "hello Test";
        // die;

        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'salesDocumentID';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }

        $pagination = $req->recordsOnPage ? $req->recordsOnPage : 20;
        $requestData = $req->except(Except::$except);
        
        $select = $req->select ? 
                explode(",", $req->select) 
                : 
                array(
                    'salesDocumentID',
                    'type',
                    'exportInvoiceType',
                    'currencyCode',
                    'currencyRate',
                    'warehouseID',
                    'warehouseName',
                    'pointOfSaleID',
                    'pointOfSaleName',
                    'pricelistID',
                    'number',
                    'date',
                    'inventoryTransactionDate',
                    'time',
                    'clientID',
                    'clientName',
                    'clientEmail',
                    'clientCardNumber',
                    'addressID',
                    'address',
                    'clientFactoringContractNumber',
                    'clientPaysViaFactoring',
                    'payerID',
                    'payerName',
                    'payerAddressID',
                    'payerAddress',
                    'payerFactoringContractNumber',
                    'payerPaysViaFactoring',
                    'shipToID',
                    'shipToName',
                    'shipToAddressID',
                    'shipToAddress',
                    'contactID',
                    'contactName',
                    'shipToContactID',
                    'shipToContactName',
                    'employeeID',
                    'employeeName',
                    'projectID',
                    'invoiceState',
                    'paymentType',
                    'paymentTypeID',
                    'paymentDays',
                    'paymentStatus',
                    'baseDocuments',
                    'followUpDocuments',
                    'previousReturnsExist',
                    'printDiscounts',
                    'algorithmVersion',
                    'algorithmVersionCalculated',
                    'confirmed',
                    'notes',
                    'internalNotes',
                    'netTotal',
                    'vatTotal',
                    'netTotalsByRate',
                    'vatTotalsByRate',
                    'netTotalsByTaxRate',
                    'vatTotalsByTaxRate',
                    'rounding',
                    'total',
                    'paid',
                    'externalNetTotal',
                    'externalVatTotal',
                    'externalRounding',
                    'externalTotal',
                    'taxExemptCertificateNumber',
                    'otherCommissionReceivers',
                    'packerID',
                    'referenceNumber',
                    'webShopOrderNumbers',
                    'trackingNumber',
                    'fulfillmentStatus',
                    'customReferenceNumber',
                    'cost',
                    'reserveGoods',
                    'reserveGoodsUntilDate',
                    'deliveryDate',
                    'deliveryTypeID',
                    'deliveryTypeName',
                    'shippingDate',
                    'packingUnitsDescription',
                    'penalty',
                    'triangularTransaction',
                    'purchaseOrderDone',
                    'transactionTypeID',
                    'transactionTypeName',
                    'transportTypeID',
                    'transportTypeName',
                    'deliveryTerms',
                    'deliveryTermsLocation',
                    'euInvoiceType',
                    'deliveryOnlyWhenAllItemsInStock',
                    'eInvoiceBuyerID',
                    'workOrderID',
                    'lastModified',
                    'lastModifierUsername',
                    'added',
                    'invoiceLink',
                    'receiptLink',
                    'returnedPayments',
                    'amountAddedToStoreCredit',
                    'amountPaidWithStoreCredit',
                    'applianceID',
                    'applianceReference',
                    'assignmentID',
                    'vehicleMileage',
                    'customNumber',
                    'advancePayment',
                    'advancePaymentPercent',
                    'printWithOriginalProductNames',
                    'hidePrices',
                    'hideAmounts',
                    'hideTotal',
                    'isFactoringInvoice',
                    'taxOfficeID',
                    'periodStartDate',
                    'periodEndDate',
                    'orderArrived',
                    'orderInvoiced',
                    'ediStatus',
                    'ediText',
                    'documentURL',
                    'hidePaymentDays',
                    'creditInvoiceType',
                    'issuedCouponIDs',
                    'attributes',
                    'longAttributes',
                    'jdoc',
                    'rows'
                );

         
        // $groups = $this->group->paginate($pagination);
        $salesDocs = $this->sales->select($select)->with('SalesDetails')->where(function ($q) use ($requestData, $req) {
            $q->where('clientCode', $this->api->client->clientCode);
            foreach ($requestData as $keys => $value) {
                if ($value != null) { 
                    if($req->strictFilter == true){
                        $q->Where($keys, $value);
                    }else{
                        $q->Where($keys, 'LIKE', '%'.$value.'%');
                    }
                    // 'like', '%' . $value . '%'); 
                }
            }
        })->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        
        return response()->json(["status"=>200, "records" => $salesDocs]);
    }


}
