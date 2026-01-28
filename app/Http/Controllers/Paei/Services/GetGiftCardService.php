<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\GiftCard;
use App\Traits\ResponseTrait;

class GetGiftCardService{

    use ResponseTrait;
    protected $giftcard;
    protected $api;

    public function __construct(GiftCard $c, EAPIService $api){
        $this->giftcard = $c;
        $this->api = $api;
    }

    public function saveUpdate($customers){

        foreach($customers as $c){
            $this->saveUpdateGiftCard($c);
        }

        return $this->successWithMessage("Gift Card fetched Successfully.");
        // return response()->json(['status'=>200, 'message'=>"Gift Card fetched Successfully."]);
    }

    protected function saveUpdateGiftCard($product){

        $this->giftcard->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "giftCardID"  =>  $product['giftCardID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "giftCardID" => $product['giftCardID'],
                    "typeID" => @$product['typeID'],
                    "code" => @$product['code'],
                    "value" => @$product['value'],
                    "balance"  => @$product['balance'],
                    "minimumSpend"  => @$product['minimumSpend'],
                    "purchasingCustomerID"  => @$product['purchasingCustomerID'],
                    "redeemingCustomerID"  => @$product['redeemingCustomerID'],
                    "purchaseDateTime"  => date('Y-m-d H:i:s', @$product['purchaseDateTime']), 
                    "redemptionDateTime"  => date('Y-m-d H:i:s', @$product['redemptionDateTime']),
                    "expirationDate"  =>  @$product['expirationDate'] ? @$product['expirationDate'] : '0000-00-00',
                    "purchaseInvoiceID"  =>  @$product['purchaseInvoiceID'],
                    "vatrateID"  =>  @$product['vatrateID'],
                    "information"  =>  @$product['information'],
                    "added"  => date('Y-m-d H:i:s', $product['added']), 
                    "lastModified"  => @$product['lastModified'] > 0 ? date('Y-m-d H:i:s', $product['lastModified']) : date('Y-m-d H:i:s', $product['added']), 
                     
                ]
            );
    }


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->giftcard->orderBy('added', 'desc')->first();
        if($latest){
            return strtotime($latest->added);
        }
        return 0;// strtotime($latest);
    }
}
