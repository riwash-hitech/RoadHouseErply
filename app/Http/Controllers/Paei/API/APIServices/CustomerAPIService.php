<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\Except; 
use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Customer;
use App\Models\PAEI\CustomerGroup;

class CustomerAPIService{

    protected $customer;
    // protected $sorting;
    // protected $pagination;
    protected $group;
    protected $api;
    protected $letsLog;


    public function __construct(Customer $c,CustomerGroup $group, EAPIService $api, UserLogger $logger){
        $this->customer = $c;
        // $this->sorting = $sorting;
        // $this->pagination = $pagination;
        $this->group = $group;
        $this->api = $api;
        $this->letsLog = $logger;
    }

    public function getByCustomerID($id){
        $customer = $this->customer->where("id", $id)->first();
        if(!$customer){
            return response()->json(["status" => 400, "message" => "Invalid customer ID!"]);
        }
        return response()->json(["status"=>200, "records" => $customer]);

    }

    public function getCustomer($req){
        
        if(isset($req->deleted) == 0){
            $req->deleted = 0;
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = false;
        }
        // info($req->direction.' '. $req->sort_by);
        $requestData = $req->except(Except::$except);


        $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        $select = $req->select ? explode(",", $req->select) :
                array(
                    'id',
                    'customerID',
                    'customerType',
                    'fullName',
                    'companyName',
                    'companyName2',
                    'companyTypeID',
                    'firstName',
                    'lastName',
                    'personTitleID',
                    'gender',
                    'groupID',
                    'countryID',
                    'groupName',
                    'payerID',
                    'phone',
                    'mobile',
                    'email',
                    'fax',
                    'code',
                    'birthday',
                    'integrationCode',
                    'flagStatus',
                    'doNotSell',
                    'colorStatus',
                    'image',
                    'taxExempt',
                    'partialTaxExemption',
                    'paysViaFactoring',
                    'rewardPoints',
                    'twitterID',
                    'facebookName',
                    'creditCardLastNumbers',
                    'isPOSDefaultCustomer',
                    'euCustomerType',
                    'credit',
                    'salesBlocked',
                    'referenceNumber',
                    'customerCardNumber',
                    'rewardPointsDisabled',
                    'customerBalanceDisabled',
                    'posCouponsDisabled',
                    'emailOptOut',
                    'lastModifierUsername',
                    'shipGoodsWithWaybills',
                    'addresses',
                    'contactPersons',
                    'defaultAssociationID',
                    'defaultAssociationName',
                    'defaultProfessionalID',
                    'defaultProfessionalName',
                    'associations',
                    'professionals',
                    'attributes',
                    'longAttributes',
                    'externalIDs',
                    'actualBalance',
                    'creditLimit',
                    'availableCredit',
                    'creditAllowed',
                    'vatNumber',
                    'skype',
                    'website',
                    'webshopUsername',
                    'webshopLastLogin',
                    'bankName',
                    'bankAccountNumber',
                    'bankIBAN',
                    'bankSWIFT',
                    'jobTitleID',
                    'jobTitleName',
                    'companyID',
                    'employerName',
                    'customerManagerID',
                    'customerManagerName',
                    'paymentDays',
                    'penaltyPerDay',
                    'priceListID',
                    'priceListID2',
                    'priceListID3',
                    'priceListID4',
                    'priceListID5',
                    'outsideEU',
                    'businessAreaID',
                    'businessAreaName',
                    'deliveryTypeID',
                    'signUpStoreID',
                    'homeStoreID',
                    'taxOfficeID',
                    'notes',
                    'lastModified',
                    'lastModifierEmployeeID',
                    'added',
                    'emailEnabled',
                    'eInvoiceEnabled',
                    'docuraEDIEnabled',
                    'eInvoiceEmail',
                    'eInvoiceReference',
                    'mailEnabled',
                    'operatorIdentifier',
                    'EDI',
                    'PeppolID',
                    'GLN',
                    'ediType',
                    'address',
                    'street',
                    'address2',
                    'city',
                    'postalCode',
                    'state',
                    'country',
                    'addressTypeID',
                    'addressTypeName',
                    'deleted',
                    'created_at',
                    'updated_at'
                );
        // $customers = $this->customer->filter($req)->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        $customers = $this->customer->select($select)->where(function ($q) use ($requestData, $req) {
            $q->where('clientCode', $this->api->client->clientCode);
            $q->where('deleted', $req->deleted);
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
         
        //SORTING
        // $customers = $this->sorting->letsSort($customers, $req);

        //PAGINATION
        // $customers = $this->pagination->getPagination($req, $customers);

        return response()->json(["status"=>200, "success" => true, "records" => collect($customers)]);
    }

    public function saveCustomer($req){
        $data = array(
            "customerType" => $req['customerType'],
                "fullName" => $req['firstName'].' '.$req['lastName'],
                "companyName" => @$req['companyName'],
                "companyName2" => @$req['companyName2'],
                "companyTypeID"  => @$req['companyTypeID'] == '' ? 0 : $req['companyTypeID'],
                "firstName"  => $req['firstName'],
                "lastName"  => $req['lastName'],
                "personTitleID"  => @$req['personTitleID'] == '' ? 0 : $req['titleId'],
                "gender"  => @$req['gender'],
                "groupID"  =>  @$req['groupID'] == '' ? 0 : $req['groupID'],
                "countryID"  =>  @$req['countryID'] == '' ? 25 : $req['countryID'],
                "groupName"  =>  isset($req['groupID']) == 0 ? '' : $this->group->where('customerGroupID', $req['groupID'])->first()->name,
                "payerID"  =>  @$req['payerID'] == '' ? 0 : $req['payerID'],
                "phone"  => $req['phone'],
                "mobile"  => $req['mobile'],
                "email"  => @$req['email'],
                "fax"  => @$req['fax'],
                "code"  => @$req['code'],
                "birthday"  => $req['birthday']  == '' ? '0000-00-00' : $req['birthday'],
                "integrationCode"  => @$req['integrationCode'],
                "flagStatus"  => @$req['flagStatus'],
                "doNotSell"  => @$req['doNotSell'],
                "colorStatus"  => @$req['colorStatus'],
                "image"  => @$req['image'],
                "taxExempt"  => @$req['taxExempt'] == '' ? 0 : $req['taxExempt'],
                "partialTaxExemption"  => @$req['partialTaxExemption'],
                "factoringContractNumber"  => @$req['factoringContractNumber'],
                "paysViaFactoring"  => @$req['paysViaFactoring'] == '' ? 0 : $req['paysViaFactoring'],
                "rewardPoints"  => @$req['rewardPoints'] == '' ? 0 : $req['rewardPoints'],
                "twitterID"  => @$req['twitterId'],
                "facebookName"  => @$req['facebookName'],
                "creditCardLastNumbers"  => @$req['creditCardLastNumbers'],
                "isPOSDefaultCustomer"  => @$req['isPOSDefaultCustomer'],
                "euCustomerType"  => @$req['euCustomerType'],
                "credit"  => @$req['credit'] == '' ? 0 : $req['credit'],
                "salesBlocked"  => @$req['salesBlocked'],
                "referenceNumber"  => @$req['referenceNumber'],
                "customerCardNumber"  => @$req['customerCardNumber'],//today date time
                "rewardPointsDisabled"  => @$req['rewardPointsDisabled'] == '' ? 0 : $req['rewardPointsDisabled'],
                "customerBalanceDisabled"  => @$req['customerBalanceDisabled'] == '' ? 0 : $req['customerBalanceDisabled'],
                "posCouponsDisabled"  => @$req['posCouponsDisabled'] == '' ? 0 : $req['posCouponsDisabled'],// today date
                "emailOptOut"  => @$req['emailOptOut'] == '' ? 0 : $req['emailOptOut'],
                "lastModifierUsername"  => @$req['changedBy'],//@$req['lastModifierUsername'],
                "shipGoodsWithWaybills"  => @$req['shipGoodsWithWaybills'] == '' ? 0 : $req['shipGoodsWithWaybills'],
                "addresses"  => !empty($req['addresses']) ? json_encode($req['addresses'], true) : '',
                "contactPersons"  => !empty($req['contactPersons']) ? json_encode($req['contactPersons'], true) : '',
                "defaultAssociationID"  => @$req['defaultAssociationID'] == '' ? 0 : $req['defaultAssociationID'],
                "defaultAssociationName"  => @$req['defaultAssociationName'],
                "defaultProfessionalID"  => @$req['defaultProfessionalID'] == '' ? 0 : $req['defaultProfessionalID'],
                "defaultProfessionalName"  => @$req['defaultProfessionalName'],
                "associations"  => !empty($req['associations']) ? json_encode($req['associations'], true) : '',
                "professionals"  => !empty($req['professionals']) ? json_encode($req['professionals'], true) : '',
                "attributes"  => !empty($req['attributes']) ? json_encode($req['attributes'], true) : '',
                "longAttributes"  => !empty($req['longAttributes']) ? json_encode($req['longAttributes'], true) : '',
                "externalIDs"  => !empty($req['externalIDs']) ? json_encode($req['externalIDs'], true) : '',
                "actualBalance"  => @$req['actualBalance'] == '' ? 0 : $req['actualBalance'],
                "creditLimit"  => @$req['creditLimit'] == '' ? 0 : $req['creditLimit'],
                "availableCredit"  => @$req['availableCredit'] == '' ? 0 : $req['availableCredit'],
                "creditAllowed"  => @$req['creditAllowed'],
                "vatNumber"  => @$req['vatNumber'],
                "skype"  => @$req['skype'],
                "website"  => @$req['website'],
                "webshopUsername"  => @$req['webShopUsername'],
                "webshopLastLogin"  => @$req['webshopLastLogin'],
                "bankName"  => @$req['bankName'],
                "bankAccountNumber"  => @$req['bankAccountNumber'],
                "bankIBAN"  => @$req['bankIBAN'],
                "bankSWIFT"  => @$req['bankSWIFT'],
                "jobTitleID"  => @$req['jobTitleID'] == '' ? 0 : $req['jobTitleID'],
                "jobTitleName"  => @$req['jobTitleName'],
                "companyID"  => @$req['companyID'] == '' ? 0 : $req['companyID'],
                "employerName"  => @$req['employerName'],
                "customerManagerID"  => @$req['customerManagerID'] == '' ? 0 : $req['customerManagerID'],
                "customerManagerName"  => @$req['customerManagerName'],
                "paymentDays"  => @$req['paymentDays'] == '' ? 0 : $req['paymentDays'],
                "penaltyPerDay"  => @$req['penaltyPerDay'] == '' ? 0 : $req['penaltyPerDay'],
                "priceListID"  => @$req['priceListID'] == '' ? 0 : $req['priceListID'],
                "priceListID2"  => @$req['priceListID2'] == '' ? 0 : $req['priceListID2'],
                "priceListID3"  => @$req['priceListID3'] == '' ? 0 : $req['priceListID3'],
                "priceListID4"  => @$req['priceListID4'] == '' ? 0 : $req['priceListID4'],
                "priceListID5"  => @$req['priceListID5'] == '' ? 0 : $req['priceListID5'],
                "outsideEU"  => @$req['outsideEU'] == 1 ? 1 : 0,
                "businessAreaID"  => @$req['businessAreaID'] == '' ? 0 : $req['businessAreaID'],
                "businessAreaName"  => @$req['businessAreaName'],
                "deliveryTypeID"  => @$req['deliveryTypeID'] == '' ? 0 : $req['deliveryTypeID'],
                "signUpStoreID"  => @$req['signUpStoreID'] == '' ? 0 : $req['signUpStoreID'],
                "homeStoreID"  => @$req['homeStoreID'] == '' ? 0 : $req['homeStoreID'],
                "taxOfficeID"  => @$req['taxOfficeID'] == '' ? 0 : $req['taxOfficeID'],
                "notes"  => @$req['notes'],
                "lastModified"  => '0000-00-00 00:00',
                "lastModifierEmployeeID"  => @$req['lastModifierEmployeeID'] == '' ? 0 : $req['lastModifierEmployeeID'],
                "added"  => date('Y-m-d H:i:s'),
                "emailEnabled"  => @$req['emailEnabled'] == '' ? 1 : $req['emailEnabled'],
                "eInvoiceEnabled"  => @$req['eInvoiceEnabled'] == '' ? 0 : $req['eInvoiceEnabled'],
                "docuraEDIEnabled"  => @$req['docuraEDIEnabled'],
                "eInvoiceEmail"  => @$req['eInvoiceEmail'],
                "eInvoiceReference"  => @$req['eInvoiceReference'],
                "mailEnabled"  => @$req['mailEnabled'],
                "operatorIdentifier"  => @$req['operatorIdentifier'],
                "EDI"  => @$req['EDI'],
                "PeppolID"  => @$req['PeppolID'],
                "GLN"  => @$req['GLN'],
                "ediType"  => @$req['ediType'],
                "address"  => @$req['address'],
                "street"  => @$req['street'],
                "address2"  => @$req['address2'],
                "city"  => @$req['city'],
                "postalCode"  => @$req['postalCode'],
                "state"  => @$req['state'],
                "country"  => @$req['country'],
                "addressTypeID"  => @$req['addressTypeID'] == '' ? 0 : $req['addressTypeID'],
                "addressTypeName"  => @$req['addressTypeName'],
        );
        

        $attributes = array();
        $chunk = array();
        $count = 0;
        foreach($req->toArray() as $key => $val){ 
            if(str_contains($key, 'attribute') && !str_contains($key, 'longAttributeName') && !str_contains($key, 'longAttributeValue')) { 
                $chunk["$key"] = $val;
                $count = $count + 1;
                if($count == 3){
                    array_push($attributes, $chunk);
                    $count = 0;
                    unset($chunk);
                }
            }
        }
        if(count($attributes) > 0){
            $attributes = json_encode($attributes, true);
            $data['attributes'] = $attributes;
        }
        unset($chunk);
        $count = 0;
        $longAttribute = array();
        foreach($req->toArray() as $key => $val){ 
            if(str_contains($key, 'longAttribute')) { 
                $chunk["$key"] = $val;
                $count = $count + 1;
                if($count == 2){
                    array_push($longAttribute, $chunk);
                    $count = 0;
                    unset($chunk);
                }
            }
        }
        if(count($longAttribute) > 0){
            $longAttribute = json_encode($longAttribute, true);
            $data['longAttributes'] = $longAttribute;
        }
        $old_customer = '';
        if($req->id){
            $old_customer = $this->customer->where('id', $req->id)->first();
        }
        
        $customer = $this->customer->updateOrCreate( 
            [
                "id" => $req['id'],
            ],
            $data
        );
        $this->letsLog->setLog($old_customer ? json_encode($old_customer, true) : '', json_encode($customer, true), $req->id ? "Customer Updated" : "Customer Created");
        return $this->saveErply($req,$customer->id,$customer->customerID,$old_customer );

        // return response()->json(["status"=>200, "response" => "Customer Created Successfully"]);

        //updating to erply server
    }


    protected function saveErply($req, $lid, $eid, $oldCustomer){
        $param = array(
                "customerType" => $req['customerType'],
                "fullName" => $req['firstName'].' '.$req['lastName'],
                "companyName" => @$req['companyName'],
                "companyName2" => @$req['companyName2'],
                "companyTypeID"  => @$req['companyTypeID'] == '' ? 0 : $req['companyTypeID'],
                "firstName"  => $req['firstName'],
                "lastName"  => $req['lastName'],
                "personTitleID"  => @$req['personTitleID'] == '' ? 0 : $req['titleId'],
                "gender"  => @$req['gender'],
                "groupID"  =>  @$req['groupID'] == '' ? 0 : $req['groupID'],
                "countryID"  =>  @$req['countryID'] == '' ? 25 : $req['countryID'],
                "groupName"  =>  isset($req['groupID']) == 0 ? '' : $this->group->where('customerGroupID', $req['groupID'])->first()->name,
                "payerID"  =>  @$req['payerID'] == '' ? 0 : $req['payerID'],
                "phone"  => $req['phone'],
                "mobile"  => $req['mobile'],
                "email"  => @$req['email'],
                "fax"  => @$req['fax'],
                "code"  => @$req['code'],
                "birthday"  => $req['birthday']  == '' ? '0000-00-00' : $req['birthday'],
                "integrationCode"  => @$req['integrationCode'],
                "flagStatus"  => @$req['flagStatus'],
                "doNotSell"  => @$req['doNotSell'],
                "colorStatus"  => @$req['colorStatus'],
                "image"  => @$req['image'],
                "taxExempt"  => @$req['taxExempt'] == '' ? 0 : $req['taxExempt'],
               
                "factoringContractNumber"  => @$req['factoringContractNumber'],
                "paysViaFactoring"  => @$req['paysViaFactoring'] == '' ? 0 : $req['paysViaFactoring'],
                "rewardPoints"  => @$req['rewardPoints'] == '' ? 0 : $req['rewardPoints'],
                "twitterID"  => @$req['twitterId'],
                "facebookName"  => @$req['facebookName'],
                "creditCardLastNumbers"  => @$req['creditCardLastNumbers'],
                "isPOSDefaultCustomer"  => @$req['isPOSDefaultCustomer'],
                "euCustomerType"  => @$req['euCustomerType'],
                "credit"  => @$req['credit'] == '' ? 0 : $req['credit'],
                "salesBlocked"  => @$req['salesBlocked'],
                "referenceNumber"  => @$req['referenceNumber'],
                "customerCardNumber"  => @$req['customerCardNumber'],//today date time
                "rewardPointsDisabled"  => @$req['rewardPointsDisabled'] == '' ? 0 : $req['rewardPointsDisabled'],
                "customerBalanceDisabled"  => @$req['customerBalanceDisabled'] == '' ? 0 : $req['customerBalanceDisabled'],
                "posCouponsDisabled"  => @$req['posCouponsDisabled'] == '' ? 0 : $req['posCouponsDisabled'],// today date
                "emailOptOut"  => @$req['emailOptOut'] == '' ? 0 : $req['emailOptOut'],
                "lastModifierUsername"  => @$req['changedBy'],//@$req['lastModifierUsername'],
                "shipGoodsWithWaybills"  => @$req['shipGoodsWithWaybills'] == '' ? 0 : $req['shipGoodsWithWaybills'],
                "addresses"  => !empty($req['addresses']) ? json_encode($req['addresses'], true) : '',
                "contactPersons"  => !empty($req['contactPersons']) ? json_encode($req['contactPersons'], true) : '',
                "defaultAssociationID"  => @$req['defaultAssociationID'] == '' ? 0 : $req['defaultAssociationID'],
                "defaultAssociationName"  => @$req['defaultAssociationName'],
                "defaultProfessionalID"  => @$req['defaultProfessionalID'] == '' ? 0 : $req['defaultProfessionalID'],
                "defaultProfessionalName"  => @$req['defaultProfessionalName'],
                "associations"  => !empty($req['associations']) ? json_encode($req['associations'], true) : '',
                "professionals"  => !empty($req['professionals']) ? json_encode($req['professionals'], true) : '',
               
                "externalIDs"  => !empty($req['externalIDs']) ? json_encode($req['externalIDs'], true) : '',
                "actualBalance"  => @$req['actualBalance'] == '' ? 0 : $req['actualBalance'],
                "creditLimit"  => @$req['creditLimit'] == '' ? 0 : $req['creditLimit'],
                "availableCredit"  => @$req['availableCredit'] == '' ? 0 : $req['availableCredit'],
                "creditAllowed"  => @$req['creditAllowed'],
                "vatNumber"  => @$req['vatNumber'],
                "skype"  => @$req['skype'],
                "website"  => @$req['website'],
                "webshopUsername"  => @$req['webShopUsername'],
                "webshopLastLogin"  => @$req['webshopLastLogin'],
                "bankName"  => @$req['bankName'],
                "bankAccountNumber"  => @$req['bankAccountNumber'],
                "bankIBAN"  => @$req['bankIBAN'],
                "bankSWIFT"  => @$req['bankSWIFT'],
                "jobTitleID"  => @$req['jobTitleID'] == '' ? 0 : $req['jobTitleID'],
                "jobTitleName"  => @$req['jobTitleName'],
                "companyID"  => @$req['companyID'] == '' ? 0 : $req['companyID'],
                "employerName"  => @$req['employerName'],
                "customerManagerID"  => @$req['customerManagerID'] == '' ? 0 : $req['customerManagerID'],
                "customerManagerName"  => @$req['customerManagerName'],
                "paymentDays"  => @$req['paymentDays'] == '' ? 0 : $req['paymentDays'],
                "penaltyPerDay"  => @$req['penaltyPerDay'] == '' ? 0 : $req['penaltyPerDay'],
                "priceListID"  => @$req['priceListID'] == '' ? 0 : $req['priceListID'],
                "priceListID2"  => @$req['priceListID2'] == '' ? 0 : $req['priceListID2'],
                "priceListID3"  => @$req['priceListID3'] == '' ? 0 : $req['priceListID3'],
                "priceListID4"  => @$req['priceListID4'] == '' ? 0 : $req['priceListID4'],
                "priceListID5"  => @$req['priceListID5'] == '' ? 0 : $req['priceListID5'],
                "outsideEU"  => @$req['outsideEU'] == 1 ? 1 : 0,
                "businessAreaID"  => @$req['businessAreaID'] == '' ? 0 : $req['businessAreaID'],
                "businessAreaName"  => @$req['businessAreaName'],
                "deliveryTypeID"  => @$req['deliveryTypeID'] == '' ? 0 : $req['deliveryTypeID'],
                "signUpStoreID"  => @$req['signUpStoreID'] == '' ? 0 : $req['signUpStoreID'],
                "homeStoreID"  => @$req['homeStoreID'] == '' ? 0 : $req['homeStoreID'],
                "taxOfficeID"  => @$req['taxOfficeID'] == '' ? 0 : $req['taxOfficeID'],
                "notes"  => @$req['notes'],
                // "lastModified"  => '0000-00-00 00:00',
                // "lastModifierEmployeeID"  => @$req['lastModifierEmployeeID'] == '' ? 0 : $req['lastModifierEmployeeID'],
                // "added"  => date('Y-m-d H:i:s'),
                "emailEnabled"  => @$req['emailEnabled'] == '' ? 1 : $req['emailEnabled'],
                "eInvoiceEnabled"  => @$req['eInvoiceEnabled'] == '' ? 0 : $req['eInvoiceEnabled'],
               
                "eInvoiceEmail"  => @$req['eInvoiceEmail'],
                "eInvoiceReference"  => @$req['eInvoiceReference'],
                "mailEnabled"  => @$req['mailEnabled'] == '' ? 0 : $req['mailEnabled'],
                "operatorIdentifier"  => @$req['operatorIdentifier'],
                "EDI"  => @$req['EDI'],
                "PeppolID"  => @$req['PeppolID'],
                "GLN"  => @$req['GLN'],
                "ediType"  => @$req['ediType'],
                "address"  => @$req['address'],
                "street"  => @$req['street'],
                "address2"  => @$req['address2'],
                "city"  => @$req['city'],
                "postalCode"  => @$req['postalCode'],
                "state"  => @$req['state'],
                "country"  => @$req['country'],
                "addressTypeID"  => @$req['addressTypeID'] == '' ? 0 : $req['addressTypeID'],
                "addressTypeName"  => @$req['addressTypeName'],
        );
        if($eid){
            $param['customerID'] = $eid;
        }
        if($req['partialTaxExemption']){
            $param['partialTaxExemption'] = $req['partialTaxExemption'];
        }
        if($req['docuraEDIEnabled']){
            $param['docuraEDIEnabled'] = $req['docuraEDIEnabled'];
        }

        //attributes and long attributes
        foreach($req->toArray() as $key => $val){ 
            if(str_contains($key, 'attribute') || str_contains($key, 'Attribute')) { 
                $param["$key"] = $val;
            }
        }

        // print_r($param);
        // die;

        $res = $this->api->sendRequest("saveCustomer", $param);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            $this->customer->where('id', $lid)->update(['customerID'=> $res['records'][0]['customerID']]);
            return response()->json(["status"=>200, "message" => $oldCustomer ? "Customer Updated Successfully." : "Customer Created Successfully"]);
        }
        return response()->json(["status"=>200, "message" => $res]);
    }

    public function deleteCustomer($req){
         
        $customer = $this->customer->where('id',$req->id)->first();
        
        if(!$customer){
            return response()->json(["status"=>400, "message" => "Invalid Customer ID!"]);
        }

        $param = array(
            'customerID' => $customer->customerID
        );
         
        $res = $this->api->sendRequest("deleteCustomer", $param);
        if($res['status']['errorCode'] == 0){ 
            $this->customer->where('id',$req->id)->update(["deleted"=>1]);
            
            $new_data = $this->customer->where('id',$req->id)->first();

            //set Log
            $this->letsLog->setLog(json_encode($customer, true), json_encode($new_data, true), "Customer Deleted");

            return response()->json(["status"=>200, "message" => "Customer Deleted Successfully"]);
        }
        return response()->json(["status"=>400, "message" => $res]);
    }
 



}
