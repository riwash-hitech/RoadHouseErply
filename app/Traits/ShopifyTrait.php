<?php

namespace App\Traits;

use App\Models\Shopify\ShopifyCursor;
use GuzzleHttp\Client;

trait ShopifyTrait
{

  public function __construct()
  {
  }

  public function getShopifyCredentails($isLive = 0)
  {
    if ($isLive == 1) {
      return ["url" => config("shopify.live.url"), "secret" => config("shopify.live.secret")];
    }
    return ["url" => config("shopify.staging.url"), "secret" => config("shopify.staging.secret")];
  }

  public function sendShopifyQueryRequest($url, $method, $secret, $query)
  {
    $headers = [
      'X-Shopify-Access-Token' => $secret,
      'Content-Type' => 'application/json'
    ];

    $client = new Client([
      'headers' => $headers
    ]);

    $request = $client->request($method, $url, [
      'json' => [
        'query' => $query
      ]
    ]);

    $products = json_decode($request->getBody()->getContents());

    return $products;
  }
  public function sendShopifyQueryRequestV2($method, $query, $isLive = 0)
  {

    $shopifyDetails = $this->getShopifyCredentails($isLive);
    // dump($shopifyDetails["url"]);
    $headers = [
      'X-Shopify-Access-Token' => $shopifyDetails["secret"],
      'Content-Type' => 'application/json'
    ];

    $client = new Client([
      'headers' => $headers
    ]);

    $request = $client->request($method, $shopifyDetails["url"], [
      'json' => [
        'query' => $query
      ]
    ]);

    $products = json_decode($request->getBody()->getContents());

    return $products;
  }

  public function getCursor($clientCode, $name, $isLive = null)
  {
    $cursor = ShopifyCursor::where("clientCode", $clientCode)->where("cursorName", $name)->first();
    if ($cursor) {
      return $cursor->cursor;
    }

    return '';
  }


  public function getMatrixProductQuery($clientCode)
  {
    $cursor = $this->getCursor($clientCode, "matrixProduct");
    $magic = 'products(first:3,sortKey:ID) {';
    if ($cursor != '') {
      $magic = 'products(first:3,sortKey:ID, after:"' . $cursor . '") {';
    }
    $query = <<<GQL
            query {
                $magic
                edges {
                    cursor
                    node {
                      id
                      title
                      totalInventory
                      updatedAt
                      createdAt
                      totalVariants
                      images(first: 100) {
                        edges {
                          node {
                            url
                          }
                        }
                      }
                      status
                      defaultCursor
                      handle
                      vendor
                      productType
                      hasOnlyDefaultVariant
                      description
                      descriptionHtml 
                      tags
                    }
                  }
                  pageInfo {
                    hasNextPage
                    hasPreviousPage
                    startCursor
                    endCursor
                  }
                }
              }
          GQL;

    return $query;
  }

  public function getVariationProductQuery($clientCode)
  {
    $cursor = $this->getCursor($clientCode, "variationProduct");
    $magic = 'productVariants(first:3,sortKey:ID) {';
    if ($cursor != '') {
      $magic = 'productVariants(first:3,sortKey:ID, after:"' . $cursor . '") {';
    }
    $query = <<<GQL
            query {
                $magic
                edges {
                  cursor
                  node {
                    id
                    title
                    product {
                      id
                      status
                    } 
                    price
                    selectedOptions {
                      name
                      value
                    }
                    defaultCursor
                    inventoryItem {
                      id
                    }
                    inventoryQuantity 
                    availableForSale
                    compareAtPrice
                    createdAt
                    displayName
                    weight
                    weightUnit
                    sku
                    barcode
                    image {
                      url
                    }
                  }
                }
                pageInfo {
                  hasNextPage
                  hasPreviousPage
                  startCursor
                  endCursor
                }
              }
            }
          GQL;

    return $query;
  }

  public function getSohQuery($clientCode, $isLive)
  {
    if ($isLive == 1) {
      $cursor = $this->getCursor($clientCode, "soh");
    } else {
      $cursor = $this->getCursor($clientCode, "soh_stageing");
    }
    $magic = 'productVariants(first:3,sortKey:ID) {';
    if ($cursor != '') {
      $magic = 'productVariants(first:3,sortKey:ID, after:"' . $cursor . '") {';
    }
    $query = <<<GQL
            query {
                $magic
                edges {
                  cursor
                  node {
                    id
                    title
                    sku
                    inventoryItem {
                      id
                      inventoryLevels(first: 100) {
                        edges {
                          node {
                            available
                            location {
                              id
                              name
                            }
                            updatedAt
                          }
                        }
                      }
                    }
                  }
                }
                pageInfo {
                  hasNextPage
                }
              }
            }
          GQL;

    return $query;
  }

  public function getCustomerQuery($clientCode)
  {
    $cursor = $this->getCursor($clientCode, "customer");
    $magic = 'customers(first:3,sortKey:ID) {';
    if ($cursor != '') {
      $magic = 'customers(first:3,sortKey:ID, after:"' . $cursor . '") {';
    }
    $query = <<<GQL
            query {
                $magic
                edges {
                  cursor
                  node {
                    id
                    email
                    firstName
                    lastName
                    phone
                    state
                    updatedAt
                    createdAt
                    note
                    defaultAddress{
                      id
                      city
                      company
                      country
                      address1
                      address2
                      zip
                      province
                      countryCodeV2
                      provinceCode
                  }
                    smsMarketingConsent{
                      marketingState
                    }
                    emailMarketingConsent{
                        marketingState
                    }
                    numberOfOrders
                    amountSpent{
                        amount
                    }
                    metafields(first: 5) {
                      edges {
                        node {
                          id
                          key
                          namespace
                          description
                          value
                        }
                      }
                    }
                  
                  }
                  
                }
              }
            }
          GQL;

    return $query;
  }

  public function getOrdersQuery($clientCode)
  {
    $cursor = $this->getCursor($clientCode, "orders");
    $magic = 'orders(first:3) {';
    if ($cursor != '') {
      $magic = 'orders(first:3, after:"' . $cursor . '") {';
    }
    $query = <<<GQL
            query {
                $magic
                edges {
                  cursor
                    node {        
                    id
                        name
                  createdAt
                  processedAt
                  displayFulfillmentStatus
                    displayFinancialStatus
                    totalPriceSet {
                        shopMoney {
                          amount
                        }
                      }  
                  tags
                  note
                  shippingLine {
                        source
                        deliveryCategory
                        code
                        originalPriceSet {
                          shopMoney
                          {
                            amount
                          }
                        }
                      }
                    discountCodes
                  
                  totalDiscountsSet {
                    
                    shopMoney {
                      amount
                    }
                  }
                  currencyCode
                  subtotalPriceSet {
                          shopMoney {
                          amount
                        }
                      }
                  totalTaxSet {
                        shopMoney {
                          amount
                        }
                      }
                  discountApplications(first: 5) {
                    edges {
                      node {
                    targetType
                        value {
                          
                          ... on PricingPercentageValue {
                            percentage
                            
                            
                          }
                          ... on MoneyV2 {
                            amount
                            currencyCode
                          
                          }
                        }
                      }
                    }
                  }
                    risks {
                            display
                            level
                            message
                          }
                paymentGatewayNames
                  customer {
                  id
                    firstName
                    lastName
                    email
                    phone
                  
                    defaultAddress {
                    address1
                    address2
                    city
                    province
                    country
                    zip
                    phone
                        }
                  }
                    billingAddress {
                    firstName
                    lastName
                    address1
                    address2
                    city
                    province
                    country
                    zip
                    phone
                  }
                    shippingAddress {
                    firstName
                    lastName
                    address1
                    address2
                    city
                    province
                    country
                    zip
                    phone
                  }
                  lineItems(first: 50) {
                    edges {
                      node {
                        
                        sku
                        id
                        quantity
                        totalDiscountSet {shopMoney {amount}}
                            
                        variant {
                          title
                          price
                          sku
                          id
                            selectedOptions {
                                name
                                value
                              }
                        }
                      }
                    }
                  }
                    }
                  }
                  pageInfo {
                    hasNextPage
                    hasPreviousPage
                    startCursor
                    endCursor
                  }
                }
            }
          GQL;

    return $query;
  }

  public function getRefundQuery($clientCode)
  {
    $cursor = $this->getCursor($clientCode, "refund");
    $magic = 'orders(first:3, query:"financial_status:PARTIALLY_REFUNDED OR financial_status:REFUNDED") {';
    if ($cursor != '') {
      // $magic = 'orders(first:3, query:"financial_status:PARTIALLY_REFUNDED OR financial_status:REFUNDED") {';
      $magic = 'orders(first:3, after:"' . $cursor . '", query:"financial_status:PARTIALLY_REFUNDED OR financial_status:REFUNDED") {';
    }
    $query = <<<GQL
            query {
                $magic
                pageInfo{
                  endCursor
                }
                edges{
                  cursor
                  node{
                    name 
                    displayFinancialStatus
                    id
                    shippingLine{
                      originalPriceSet{
                        shopMoney{
                          amount
                        }
                      }
                    }
                    totalDiscountsSet{
                      shopMoney{
                        amount
                      }
                    }
                    refunds{
                      id
                      note
                      totalRefundedSet{
                        shopMoney{
                          amount
                        }
                      }  
                      refundLineItems(first:50){
                        edges{
                          node{
                            lineItem{
                              id 
                              sku     
                            }
                            quantity
                            priceSet{
                              shopMoney{
                                amount
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          GQL;

    return $query;
  }

  public function getDefaultOnlineLocation(){
    $isLive = config("shopify.isLive");
    if($isLive == 1){
      return config("shopify.live.onlineLocation");
    }else{
      return config("shopify.staging.onlineLocation");
    }
  }
  
  public function checkIsLiveEnv(){
    return config("shopify.isLive");
  }


}
