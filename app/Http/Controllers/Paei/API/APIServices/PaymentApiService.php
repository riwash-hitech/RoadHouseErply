<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\Except;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Currency;
use App\Models\PAEI\Payment;

class PaymentApiService{

    protected $payment;
    protected $api;

    public function __construct(Payment $w, EAPIService $api){
        $this->payment = $w;
        $this->api = $api;
    }

   

    public function getPayments($req){

        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'paymentID';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }
        
        $pagination = $req->recordsOnPage ? $req->recordsOnPage : 20;
        $requestData = $req->except(Except::$except);

        $select = $req->select ? explode(",", $req->select) : 
            array(
                'paymentID',
                'documentID',
                'customerID',
                'typeID',
                'type',
                'date',
                'sum',
                'currencyCode',
                'currencyRate',
                'cashPaid',
                'cashChange',
                'info',
                'cardHolder',
                'cardNumber',
                'cardType',
                'authorizationCode',
                'referenceNumber',
                'isPrepayment',
                'bankTransactionID',
                'bankAccount',
                'bankDocumentNumber',
                'bankDate',
                'bankPayerAccount',
                'bankPayerName',
                'bankPayerCode',
                'bankSum',
                'bankReferenceNumber',
                'bankDescription',
                'bankCurrency',
                'archivalNumber',
                'storeCredit',
                'paymentServiceProvider',
                'aid',
                'applicationLabel',
                'pinStatement',
                'cryptogramType',
                'cryptogram',
                'expirationDate',
                'entryMethod',
                'transactionType',
                'transactionNumber',
                'transactionId',
                'transactionType2',
                'transactionTime',
                'klarnaPaymentID',
                'certificateBalance',
                'statusCode',
                'statusMessage',
                'giftCardVatRateID',
                'signature',
                'signatureIV',
                'attributes',
                'added',
                'lastModified',
            );
         
        // $groups = $this->group->paginate($pagination);
        $payments = $this->payment->select($select)->where(function ($q) use ($requestData, $req) {
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
        
        return response()->json(["status"=>200, "records" => $payments]);
    }


}
