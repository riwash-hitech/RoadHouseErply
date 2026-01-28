<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Payment;

class GetPaymentService{

    protected $payment;
    protected $api;

    public function __construct(Payment $c, EAPIService $api){
        $this->payment = $c;
        $this->api = $api;
    }

    public function saveUpdate($customers){

        foreach($customers as $c){

            $this->saveUpdatePayment($c);
        }

        return response()->json(['status'=>200, 'message'=>"Payment fetched Successfully."]);
    }

    protected function saveUpdatePayment($product){

        $this->payment->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "paymentID"  =>  $product['paymentID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "paymentID" => @$product["paymentID"],
                    "documentID" => @$product["documentID"],
                    "customerID" => @$product["customerID"],
                    "typeID" => @$product["typeID"],
                    "type" => @$product["type"],
                    "date" => @$product["date"],
                    "sum" => @$product["sum"],
                    "currencyCode" => @$product["currencyCode"],
                    "currencyRate" => @$product["currencyRate"],
                    "cashPaid" => @$product["cashPaid"],
                    "cashChange" => @$product["cashChange"],
                    "info" => @$product["info"],
                    "cardHolder" => @$product["cardHolder"],
                    "cardNumber" => @$product["cardNumber"],
                    "cardType" => @$product["cardType"],
                    "authorizationCode" => @$product["authorizationCode"],
                    "referenceNumber" => @$product["referenceNumber"],
                    "isPrepayment" => @$product["isPrepayment"],
                    "bankTransactionID" => @$product["bankTransactionID"],
                    "bankAccount" => @$product["bankAccount"],
                    "bankDocumentNumber" => @$product["bankDocumentNumber"],
                    "bankDate" => @$product["bankDate"],
                    "bankPayerAccount" => @$product["bankPayerAccount"],
                    "bankPayerName" => @$product["bankPayerName"],
                    "bankPayerCode" => @$product["bankPayerCode"],
                    "bankSum" => @$product["bankSum"],
                    "bankReferenceNumber" => @$product["bankReferenceNumber"],
                    "bankDescription" => @$product["bankDescription"],
                    "bankCurrency" => @$product["bankCurrency"],
                    "archivalNumber" => @$product["archivalNumber"],
                    "storeCredit" => @$product["storeCredit"],
                    "paymentServiceProvider" => @$product["paymentServiceProvider"],
                    "aid" => @$product["aid"],
                    "applicationLabel" => @$product["applicationLabel"],
                    "pinStatement" => @$product["pinStatement"],
                    "cryptogramType" => @$product["cryptogramType"],
                    "cryptogram" => @$product["cryptogram"],
                    "expirationDate" => @$product["expirationDate"],
                    "entryMethod" => @$product["entryMethod"],
                    "transactionType" => @$product["transactionType"],
                    "transactionNumber" => @$product["transactionNumber"],
                    "transactionId" => @$product["transactionId"],
                    "transactionType2" => @$product["transactionType"],
                    "transactionTime" => date('H:i:s',@$product['transactionTime']), 
                    "klarnaPaymentID" => @$product["klarnaPaymentID"],
                    "certificateBalance" => @$product["certificateBalance"],
                    "statusCode" => @$product["statusCode"],
                    "statusMessage" => @$product["statusMessage"],
                    "giftCardVatRateID" => @$product["giftCardVatRateID"],
                    "signature" => @$product["signature"],
                    "signatureIV" => @$product["signatureIV"],
                    "attributes" => !empty($product['attributes']) ? json_encode($product['attributes'],1) : '', 
                    "added" =>  date('Y-m-d H:i:s',$product['added']), 
                    "lastModified" => date('Y-m-d H:i:s', $product['lastModified']), 
                     
                ]
            );
    }


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->payment->orderBy('lastModified', 'desc')->first();
        if($latest){
            return strtotime($latest->lastModified);
        }
        return 0;// strtotime($latest);
    }
}
