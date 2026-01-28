<?php
namespace App\Http\Controllers\Paei\Services;

use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Customer;
use App\Models\PAEI\ErplySync;

class GetCustomerService{

    protected $customer;
    protected $letsLog;
    protected $api;

    public function __construct(Customer $c,UserLogger $logger, EAPIService $api){
        $this->customer = $c;
        $this->letsLog = $logger;
        $this->api = $api;
    }

    public function saveUpdate($customers, $isAdded){
        $lastModified = 0;
        foreach($customers as $c){
            $this->saveUpdateCustomer($c);
            if($lastModified < $c["lastModified"]){
                $lastModified = $c["lastModified"];
            }
        }

        if($isAdded == 0){
            ErplySync::where("id", 1)->update(["customer" => date('Y-m-d H:i:s', $lastModified)]);
        }  

        return response()->json(['status'=>200, 'message'=>"Customer fetched Successfully."]);
    }

    protected function saveUpdateCustomer($product){
        // $check = Customer::where("clientCode", $clientCode)->where("customerID", $product["customerID"])->first();
        // $netsuitePending = 1;
        // if($check && date('Y-m-d H:i:s',$product['lastModified']) == $check->lastModified){
        //     $netsuitePending = $check->netsuitePending;
        // }
        $payload = [
            "clientCode" =>   $this->api->client->clientCode,
            "customerID" => $product['customerID'],
            "customerType" => $product['customerType'],
            "fullName" => trim($product['fullName']),
            "companyName" => @$product['companyName'],
            "companyTypeID"  => @$product['companyTypeID'] == '' ? 0 : $product['companyTypeID'],
            "firstName"  => trim($product['firstName']),
            "lastName"  => trim(@$product['lastName']),
            "personTitleID"  => @$product['personTitleID'] == '' ? 0 : $product['personTitleID'],
            "gender"  => @$product['gender'],
            "groupID"  =>  @$product['groupID'] == '' ? 0 : $product['groupID'],
            "countryID"  =>  @$product['countryID'] == '' ? 0 : $product['countryID'],
            "groupName"  =>  @$product['groupName'],
            "payerID"  =>  @$product['payerID'] == '' ? 0 : $product['payerID'],
            "phone"  => $product['phone'],
            "mobile"  => $product['mobile'],
            "email"  => trim(@$product['email']),
            "fax"  => @$product['fax'],
            "code"  => @$product['code'],
            "birthday"  => @$product['birthday']  == '' ? '0000-00-00' : @$product['birthday'],
            "integrationCode"  => @$product['integrationCode'],
            "flagStatus"  => @$product['flagStatus'],
            "doNotSell"  => @$product['doNotSell'],
            "colorStatus"  => @$product['colorStatus'],
            "image"  => @$product['image'],
            "taxExempt"  => @$product['taxExempt'],
            "partialTaxExemption"  => @$product['partialTaxExemption'],
            "factoringContractNumber"  => @$product['factoringContractNumber'],
            "paysViaFactoring"  => @$product['paysViaFactoring'],
            "rewardPoints"  => @$product['rewardPoints'] == '' ? 0 : $product['rewardPoints'],
            "twitterID"  => @$product['twitterId'],
            "facebookName"  => @$product['facebookName'],
            "creditCardLastNumbers"  => @$product['creditCardLastNumbers'],
            "isPOSDefaultCustomer"  => @$product['isPOSDefaultCustomer'],
            "euCustomerType"  => @$product['euCustomerType'],
            "credit"  => @$product['credit'] == '' ? 0 : $product['credit'],
            "salesBlocked"  => @$product['salesBlocked'],
            "referenceNumber"  => @$product['referenceNumber'],
            "customerCardNumber"  => @$product['customerCardNumber'],//today date time
            "rewardPointsDisabled"  => @$product['rewardPointsDisabled'],
            "customerBalanceDisabled"  => @$product['customerBalanceDisabled'],
            "posCouponsDisabled"  => @$product['posCouponsDisabled'],// today date
            "emailOptOut"  => @$product['emailOptOut'],
            "lastModifierUsername"  =>@$product['lastModifierUsername'],
            "shipGoodsWithWaybills"  => @$product['shipGoodsWithWaybills'],
            "addresses"  => !empty($product['addresses']) ? json_encode($product['addresses'], true) : '',
            "contactPersons"  => !empty($product['contactPersons']) ? json_encode($product['contactPersons'], true) : '',
            "defaultAssociationID"  => @$product['defaultAssociationID'] == '' ? 0 : $product['defaultAssociationID'],
            "defaultAssociationName"  => @$product['defaultAssociationName'],
            "defaultProfessionalID"  => @$product['defaultProfessionalID'] == '' ? 0 : $product['defaultProfessionalID'],
            "defaultProfessionalName"  => @$product['defaultProfessionalName'],
            "associations"  => !empty($product['associations']) ? json_encode($product['associations'], true) : '',
            "professionals"  => !empty($product['professionals']) ? json_encode($product['professionals'], true) : '',
            "attributes"  => !empty($product['attributes']) ? json_encode($product['attributes'], true) : '',
            "longAttributes"  => !empty($product['longAttributes']) ? json_encode($product['longAttributes'], true) : '',
            "externalIDs"  => !empty($product['externalIDs']) ? json_encode($product['externalIDs'], true) : '',
            "actualBalance"  => @$product['actualBalance'] == '' ? 0 : $product['actualBalance'],
            "creditLimit"  => @$product['creditLimit'] == '' ? 0 : $product['creditLimit'],
            "availableCredit"  => @$product['availableCredit'] == '' ? 0 : $product['availableCredit'],
            "creditAllowed"  => @$product['creditAllowed'],
            "vatNumber"  => @$product['vatNumber'],
            "skype"  => @$product['skype'],
            "website"  => @$product['website'],
            "webshopUsername"  => @$product['webShopUsername'],
            "webshopLastLogin"  => @$product['webshopLastLogin'],
            "bankName"  => @$product['bankName'],
            "bankAccountNumber"  => @$product['bankAccountNumber'],
            "bankIBAN"  => @$product['bankIBAN'],
            "bankSWIFT"  => @$product['bankSWIFT'],
            "jobTitleID"  => @$product['jobTitleID'] == '' ? 0 : $product['jobTitleID'],
            "jobTitleName"  => @$product['jobTitleName'],
            "companyID"  => @$product['companyID'] == '' ? 0 : $product['companyID'],
            "employerName"  => @$product['employerName'],
            "customerManagerID"  => @$product['customerManagerID'] == '' ? 0 : $product['customerManagerID'],
            "customerManagerName"  => @$product['customerManagerName'],
            "paymentDays"  => @$product['paymentDays'] == '' ? 0 : $product['paymentDays'],
            "penaltyPerDay"  => @$product['penaltyPerDay'],
            "priceListID"  => @$product['priceListID'] == '' ? 0 : $product['priceListID'],
            "priceListID2"  => @$product['priceListID2'] == '' ? 0 : $product['priceListID2'],
            "priceListID3"  => @$product['priceListID3'] == '' ? 0 : $product['priceListID3'],
            "priceListID4"  => @$product['priceListID4'] == '' ? 0 : $product['priceListID4'],
            "priceListID5"  => @$product['priceListID5'] == '' ? 0 : $product['priceListID5'],
            "outsideEU"  => @$product['outsideEU'] == 1 ? 1 : 0,
            "businessAreaID"  => @$product['businessAreaID'] == '' ? 0 : $product['businessAreaID'],
            "businessAreaName"  => @$product['businessAreaName'],
            "deliveryTypeID"  => @$product['deliveryTypeID'] == '' ? 0 : $product['deliveryTypeID'],
            "signUpStoreID"  => @$product['signUpStoreID'] == '' ? 0 : $product['signUpStoreID'],
            "homeStoreID"  => @$product['homeStoreID'] == '' ? 0 : $product['homeStoreID'],
            "taxOfficeID"  => @$product['taxOfficeID'] == '' ? 0 : $product['taxOfficeID'],
            "notes"  => @$product['notes'],
            "lastModified"  => isset($product['lastModified']) == 1 ? date('Y-m-d H:i:s',$product['lastModified']) : '0000-00-00 00:00',
            "lastModifierEmployeeID"  => @$product['lastModifierEmployeeID'] == '' ? 0 : $product['lastModifierEmployeeID'],
            "added"  => date('Y-m-d H:i:s',$product['added']),
            "emailEnabled"  => @$product['emailEnabled'],
            "eInvoiceEnabled"  => @$product['eInvoiceEnabled'],
            "docuraEDIEnabled"  => @$product['docuraEDIEnabled'],
            "eInvoiceEmail"  => @$product['eInvoiceEmail'],
            "eInvoiceReference"  => @$product['eInvoiceReference'],
            "mailEnabled"  => @$product['mailEnabled'],
            "operatorIdentifier"  => @$product['operatorIdentifier'],
            "EDI"  => @$product['EDI'],
            "PeppolID"  => @$product['PeppolID'],
            "GLN"  => @$product['GLN'],
            "ediType"  => @$product['ediType'],
            "address"  => @$product['address'],
            "street"  => @$product['street'],
            "address2"  => @$product['address2'],
            "city"  => @$product['city'],
            "postalCode"  => @$product['postalCode'],
            "state"  => @$product['state'],
            "country"  => @$product['country'],
            "addressTypeID"  => @$product['addressTypeID'] == '' ? 0 : $product['addressTypeID'],
            "addressTypeName"  => @$product['addressTypeName'],
            // "netsuitePending" => $netsuitePending
        ];
        Customer::updateOrCreate(
                [
                    "clientCode" =>   $this->api->client->clientCode,
                    "customerID"  =>  $product['customerID']
                ],
                $payload
            );
    }


    public function getLastUpdateDate($isAdded){
        // if($isAdded == 1){
        //     $latest = ErplySync::first()->customerAdded;
        //     if($latest){
        //         return strtotime($latest);
        //     }
        // }
        $latest = ErplySync::first()->customer;
        if($latest){
            return strtotime($latest);
        }
        return 0;
        
    }
}
