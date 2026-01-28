<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\Except;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\PurchaseDocument; 

class PurchaseDocumentApiService{

    protected $pd;
    protected $api;

    public function __construct(PurchaseDocument $c, EAPIService $api){
        $this->pd = $c;
        $this->api = $api;
    }
 

    public function getPurchaseDocuments($req){
        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'purchaseDocumentID';
        }

        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }

        $pagination = $req->recordsOnPage ? $req->recordsOnPage : 20;
        $requestData = $req->except(Except::$except);

        $select = $req->select ? explode(",", $req->select) : 
            array(
                'purchaseDocumentID',
                'type',
                'status',
                'currencyCode',
                'currencyRate',
                'warehouseID',
                'warehouseName',
                'number',
                'regnumber',
                'date',
                'inventoryTransactionDate',
                'time',
                'supplierID',
                'supplierName',
                'supplierGroupID',
                'addressID',
                'address',
                'contactID',
                'contactName',
                'employeeID',
                'employeeName',
                'supplierID2',
                'supplierName2',
                'stateID',
                'paymentDays',
                'paid',
                'transactionTypeID',
                'transportTypeID',
                'deliveryTermsID',
                'deliveryTermsLocation',
                'deliveryAddressID',
                'triangularTransaction',
                'projectID',
                'reasonID',
                'confirmed',
                'referenceNumber',
                'notes',
                'ediStatus',
                'ediText',
                'documentURL',
                'rounding',
                'netTotal',
                'vatTotal',
                'total',
                'netTotalsByTaxRate',
                'vatTotalsByTaxRate',
                'invoiceLink',
                'shipDate',
                'cost',
                'netTotalForAccounting',
                'totalForAccounting',
                'additionalCosts',
                'additionalCostsCurrencyId',
                'additionalCostsCurrencyRate',
                'additionalCostsDividedBy',
                'baseToDocuments',
                'baseDocuments',
                'added',
                'addedby',
                'changedby',
                'lastModified',
                'rows',
                'attributes',
                'created_at',
                'updated_at'
            );
         
        // $groups = $this->group->paginate($pagination);
        $purchaseDocs = $this->pd->select($select)->with('purchaseDetails')->where(function ($q) use ($requestData, $req) {
            $q->where('clientCode', $this->api->client->clientCode);
            foreach ($requestData as $keys => $value) {
                if ($value != null) { 
                    if($req->strictFilter == true){
                        $q->Where($keys, $value);
                    }else{
                        $q->Where($keys, 'LIKE', '%'.$value.'%');
                    } 
                }
            }
        })->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        
        return response()->json(["status"=>200, "records" => $purchaseDocs]);
    }
 
 



}
