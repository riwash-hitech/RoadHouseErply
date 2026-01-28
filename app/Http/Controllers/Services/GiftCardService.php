<?php
namespace App\Http\Controllers\Services;

use App\Models\Client;
use App\Models\GiftCard;

class GiftCardService{

    protected $api;
    protected $gitfcard;
    // protected $client;

    public function __construct(EAPIService $api,GiftCard $cc)
    {
        $this->api = $api;
        $this->gitfcard = $cc;
 
    }

    public function saveGiftCard($req){
        $limit = $req->limit == '' ? 5 : $req->limit;
        //FIRST VERIFYING USER

        $suppliers = $this->gitfcard->where('ciEmail', '<>', '')
                    ->where('erplyPending', 1)
                    ->where('webActive', 1)
                    ->whereIn('ciCustomerGroup',['Retail', 'Wholesale'])
                    ->limit($limit)
                    ->get();
        // dd($customer);
        // die;
        $bulkCustomer = $this->makeBundleJSON($suppliers);
        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $this->api->client->sessionKey
        );

        $bulkRes = $this->api->sendRequest($bulkCustomer, $bulkparam, 1,0,0);
        if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests'])){
            foreach($suppliers as $key => $c){
                if($bulkRes['requests'][$key]['status']['errorCode'] == 0){
                    $c->erplySupplierID = $bulkRes['requests'][$key]['records'][0]['giftCardID'];
                    $c->erplyPending = 0;
                    $c->save();
                    info("GiftCard created ". $bulkRes['requests'][$key]['records'][0]['giftCardID']);
                }else{
                    $c->error = $bulkRes['requests'][$key]['status']['errorCode'];
                    $c->save();
                    info("Error While creating supplier". $bulkRes['requests'][$key]['status']['errorCode']);
                }
            }
            // Log::info($bulkRes);
            // return response()->json(['status' => 200, 'data' => $bulkRes]);
        }    
        // return response()->json( ['status' => 401,'data'=> $bulkRes]);

    }


    protected function makeBundleJSON($data){
        $verifiedSessionKey = $this->api->client->sessionKey; //$this->api->verifySessionByKey($this->api->client->sessionKey);
        $BundleArray = array();
        foreach($data as $gift){
            $reqArray = array(
                "requestName" => "saveGiftCard",
                "sessionKey" => $verifiedSessionKey,
                "clientCode" => $this->api->client->clientCode,
                "typeID" => $gift->ciFirstName,
                "code" => $gift->ciLastName,
                "value" => $gift->ciLastName,
                "balance" => $gift->ciCustomerGroup == 'Retail' ? 16 : 17,
                "information" => $gift->ciCustomerGroup == 'Retail' ? 16 : 17,
                "purchasingCustomerID" => $gift->ciEmail,
                "purchaseDateTime" => $gift->ciPhone,
                "purchaseWarehouseID" => $gift->ciMobile,
                "purchasePointOfSaleID" => $gift->ciMobile,
                "purchaseInvoiceID" => $gift->ciMobile,
                "purchaseEmployeeID" => $gift->ciMobile,
                "redeemingCustomerID" => $gift->ciMobile,
                "redemptionDateTime" => $gift->ciMobile,
                "redemptionWarehouseID" => $gift->ciMobile,
                "redemptionPointOfSaleID" => $gift->ciMobile,
                "redemptionInvoiceID" => $gift->ciMobile,
                "redemptionEmployeeID" => $gift->ciMobile,
                "expirationDate" => $gift->ciMobile,
                "vatrateID" => $gift->ciMobile,
                
                
            );

            $giftCardID = $this->checkGiftCard($gift->code);
            if($giftCardID != ''){
                $reqArray['giftCardID'] = $giftCardID;
            }

            

            array_push($BundleArray,$reqArray );
             
        }

        $BundleArray = json_encode($BundleArray, true);
        return $BundleArray; 
    }

    protected function checkGiftCard($code){
        $param = array(
            "code" => $code,
            "sessionKey" => $this->api->client->sessionKey,
        );

        $res = $this->api->sendRequest("getGiftCards", $param,0,0,0);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            info("GiftCard exist ID".$res['records'][0]['giftCardID']);
            return $res['records'][0]['giftCardID'];
        }

        return '';

    }

}
