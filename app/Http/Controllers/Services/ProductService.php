<?php
namespace App\Http\Controllers\Services;

use App\Http\Controllers\EAPI;
use App\Models\Client;
use App\Models\PAEI\Pricelist;
use App\Models\PAEI\ProductSoh;
use App\Models\Product;

use App\Models\PAEI\VariationProduct;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;


class ProductService
{
    protected $api;
    protected $product;
    protected $productvariant;
    protected $client;
    public function __construct(EAPIService $api, Product $product, ProductVariant $pv)
    {
        $this->api = $api;
        $this->product = $product;
        $this->productvariant = $pv;
    }
    // protected function setModel(){
    //     session_start(); 
    //     $this->api = new EAPIService();  
    //     // $this->client = Client::findOrfail(1);
    //     // $this->api->clientCode = $this->client->clientCode;
    //     // $this->api->username = $this->client->username;
    //     // $this->api->password = $this->client->password;
    //     // $this->api->url = "https://".$this->api->clientCode.".erply.com/api/";

    //     $this->product = new Product();
    //     $this->productvariant = new ProductVariant();
    // }

    public function getData($param)
    {
        $result = $this->api->sendRequest("getProducts", $param);
        $products = json_decode($result, true);
        if ($products['status']['errorCode'] != 0) {
            return response()->json(['stauts' => 'Error ' . $products['status']['errorCode']]);
        }
        return $products['records'];
    }



    public function handleProduct($param)
    {

        $products = $this->getData($param, "getProducts");
        // $products = json_decode($products, true);
        // if($products['status']['errorCode'] != 0){
        //     return response()->json(['stauts' => 'Error '. $products['status']['errorCode']]);
        // }
        //  print_r($products);
        //  die;
        foreach ($products as $product) {
            if (!array_key_exists("parentProductID", $product)) {
                // dd($product);
                // echo $product['productID'];
                $this->ProductUpdateCreate($product);
                // echo "Product Create Or Updated";
            } else {
                if ($product["parentProductID"] == 0) {
                    // dd($product);
                    $this->product->updateOrCreate($product);
                    // echo "Product Create Or Updated";
                } else {
                    // echo "Product Variants Create Or Updated";
                    // echo $product['productID'];
                    // print_r($product);
                    $this->ProductVariantUpdateCreate($product);
                }
            }

        }

        echo "Product Create or Updated Successfully.";
        die;


    }

    public function handleCronJob($param)
    {
        Log::info("cron Job Called");
        $data = $this->getData($param);
        $bulkP = array();
        $bulkPV = array();
        foreach ($data as $d) {
            $p = $this->product->findOrfail($d['productID']);
            if (!array_key_exists("parentProductID", $data) || $data["parentProductID"] == 0) {
                // dd($product);
                if (!$p) {
                    array_push($bulkP, $d);
                } else {
                    $this->ProductUpdateCreate($d);
                }

            } else {

                if (!$p) {
                    array_push($bulkPV, $d);
                } else {
                    $this->ProductVariantUpdateCreate($d);
                }

            }
        }

        //for bulk entry
        if ($bulkP) {
            $this->product->insert($bulkP);
        }
        if ($bulkPV) {
            $this->productvariant->insert($bulkPV);
        }

    }


    protected function ProductVariantUpdateCreate($product)
    {
        // dd($product);
        //counting variation id
        $rows = 0;
        if (array_key_exists('variationDescription', $product)) {
            $rows = count($product['variationDescription']);
        }

        $this->productvariant->updateOrCreate(
            [
                "erplyVariationProductID" => $product['productID']
            ],
            [
                "syncCompanyID" => 16,
                "erplyClientCode" => 466822,
                "erplyParentProductID" => $product["parentProductID"],
                "erplyVariationProductID" => $product['productID'],
                "productCode" => $product['code'],
                "erplyVariation1ID" => $rows >= 1 ? $product['variationDescription'][0]['variationID'] : 0,
                "erplyVariation2ID" => $rows >= 2 ? $product['variationDescription'][1]['variationID'] : 0,
                "erplyVariation3ID" => $rows >= 3 ? $product['variationDescription'][2]['variationID'] : 0,
                "erplyVariation4ID" => $rows >= 4 ? $product['variationDescription'][3]['variationID'] : 0,
                "erplyVariation5ID" => $rows >= 5 ? $product['variationDescription'][4]['variationID'] : 0,
                "erplyVariationProductName" => $product['name'],
                "productCode2" => $product['code2'],
                "productCode3" => $product['code3'] != '' ? $product['code3'] : '',
                "productSupplierCode" => $product['supplierCode'] != '' ? $product['supplierCode'] : '',
                "productCode5" => 0,
                "productCode6" => 0,
                "productCode7" => 0,
                "productCode8" => 0,
                "erplyVariationProductType" => $product['type'],
                "erplyVariationActive" => $product['status'] == "Active" ? 1 : 0,
                "erplyVariationArchive" => $product['status'] == "Archive" ? 1 : 0,
                "erplyVariationDescription" => $product['descriptionENG'],
                "erplyVariationLongDescription" => $product['longdescENG'],
                "erplyVariationPrice" => $product['price'],
                "erplyVariationPriceWithTax" => $product['priceWithVat'],
                "erplyVariationSalePrice" => 0,
                "erplyVariationSalePriceWithTax" => 0,
                "cost" => 0,
                "FIFOCost" => 0,
                "purchasePrice" => 0,
                "lastModifiedDateTime" => date('Y-m-d H:i:s', $product['lastModified']),
                "pendingProcess" => 1,
            ]
        );
    }

    protected function ProductUpdateCreate($product)
    {
        // dd($product);

        $this->product->updateOrCreate(
            [
                "erplyProductID" => $product['productID']
            ],
            [
                "syncCompanyID" => 16,
                "erplyClientCode" => 466822,
                "erplyProductID" => $product['productID'],
                "productCode" => $product['code'],
                "productType" => $product['type'],
                "productActive" => $product['active'],
                "productStatus" => $product['status'],
                "productName" => $product['name'],
                "productCode2" => $product['code2'] != '' ? $product['code2'] : '',
                "productCode3" => $product['code3'] != '' ? $product['code3'] : '',
                "productSupplierCode" => $product['supplierCode'] != '' ? $product['supplierCode'] : '',
                "productCode5" => '',
                "productCode6" => '',
                "productCode7" => '',
                "productCode8" => '',
                "productGroupID" => $product['groupID'],
                "productPrice" => $product['price'],
                "productPriceWithTax" => $product['priceWithVat'],
                "salesPrice" => 0,
                "salesPricewithTax" => 0,
                "cost" => $product['cost'],
                "FIFOCost" => 0,
                "purchasePrice" => 0,
                "productWebActive" => $product['status'] == "ACTIVE" ? 1 : 0,
                "productCategoryID" => $product['categoryID'],
                "productSupplierID" => $product['supplierID'],
                "productUnitID" => $product['unitID'],
                "productTaxRateID" => 0,
                "productManufacturer" => 0,
                "productBrandID" => $product['brandID'],
                "productWidth" => $product['width'],
                "productHeight" => $product['height'],
                "productLength" => $product['length'],
                "productNonStock" => $product['nonStockProduct'],
                "productNetWeight" => $product['netWeight'] == '' ? 0 : $product['netWeight'],
                "productGrossWeight" => $product['grossWeight'] == '' ? 0 : $product['grossWeight'],
                "productShortDescription" => $product['descriptionENG'],
                "productLongDescription" => '',
                "lastModifiedDateTime" => date('Y-m-d H:i:s', $product['lastModified']),
                "lastProcessedTime" => date('Y-m-d H:i:s'),//today date time 
                "pendingProcess" => 1,
                "imagePending" => 1,
                "imageProcessedTime" => date('Y-m-d H:i:s'),// today date  
                "eslPending" => 0,
                "eslActive" => 0,
                "sohPending" => 0,
                "eslLastUpdated" => '0000-00-00 00:00:00',
                "sohProcessedTime" => '0000-00-00 00:00:00',
                "attrSize" => '',


            ]
        );

    }

    public function getLastUpdateDate()
    {
        $latest = $this->product->orderBy('lastModifiedDateTime', 'desc')->first()->lastModifiedDateTime;
        return $latest;

    }


    public function getCogsInvoiceRows(array $params, string $clientCode, string $sessionKey)
    {
        $baseUrl = 'https://api-reports-au.erply.com/v1/generated-reports/cogs-invoice-rows';

        // Encode query parameters
        $queryParams = http_build_query($params);

        $url = $baseUrl . '?' . $queryParams;

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                "clientCode: {$clientCode}",
                "sessionKey: {$sessionKey}",
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('cURL error: ' . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($response, true);
    }
    public function fetchLastSalesAndPurchasesByLocations(array $productIds, array $warehouseIds, string $startDate, string $endDate, string $clientCode, string $sessionKey): array
    {
        $baseUrl = 'https://api-reports-au.erply.com/v1/products/last-sales-and-purchases/by-locations';

        $params = [
            'productIds' => implode(',', $productIds),
            'warehouseIds' => implode(',', $warehouseIds),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        $url = $baseUrl . '?' . http_build_query($params);

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                "clientCode: {$clientCode}",
                "sessionKey: {$sessionKey}",
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('cURL Error: ' . curl_error($ch));
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        return $decoded['data'] ?? [];
    }
    private function getPricePolicyForVariants(array $productIDs): array
    {
        if (empty($productIDs)) {
            return [];
        }

        $activePriceListIDsRaw = Pricelist::where('active', 1)
                                      ->where('isDeleted', 0)
                                      ->pluck('pricelistID')
                                      ->toArray();

        if (empty($activePriceListIDs)) {
            return [];
        }
        $rows = \App\Models\PAEI\PricelistRule::select('productID', DB::raw('MIN(priceWithVat) AS salesPrice'))
                                ->whereIn('productID', $productIDs)
                                ->whereIn('priceListID', $activePriceListIDs)
                                ->where('isDeleted', 0)
                                ->where('priceWithVat', '>', 0)
                                ->groupBy('productID')
                                ->get();
        $results = [];
        foreach ($rows as $row) {
            $results[$row->productID] = [
                'status' => 1,
                'salesPrice' => (float) $row->salesPrice,
            ];
        }
        foreach ($productIDs as $productID) {
            if (!isset($results[$productID])) {
                $results[$productID] = ['status' => 0];
            }
        }
        return $results;
    }


    public function getProductsReport($req)
    {
        // ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);
        ini_set('memory_limit', '500M');
        ini_set('max_execution_time', 300);

        $from = $req->from ?? date('Y-m-d', strtotime('-1 month'));
        $to = $req->to ?? date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        if (!$from || !$to) {
            return response()->json(['error' => 'Invalid date range'], 400);
        }
        $clientCode = $this->api->client->clientCode ?? 'YOUR_CLIENT_CODE';
        $sessionKey = $this->api->client->sessionKey ?? 'YOUR_SESSION_KEY';
        // dd($clientCode);
        // return response()->json($clientCode); die;
        $firstOfMonth = date('Y-m-01', strtotime($now));
        $firstOfYear = date('Y-01-01', strtotime($now));
        $debug = $req->debug ?? 0;
        $typeStr = 'FootWare';
        $groupArray = [];
        $groups = $req->group ?? '';
        $brandIDs = $req->brand ?? '';
        $categoryIDs = $req->category ?? '';
        $supplierIDs = $req->supplier ?? '';
        $priorityIDs = $req->priority ?? '';


        // if ($group == 'FW') {
        //     $groupArray = [34, 32, 22, 18, 19, 20];
        //     $groupStr = '34,32,22,18,19,20';
        // } else {
        // $typeStr = "Accessories";
        // $groupArray = [54, 55, 56, 150, 151, 149, 57, 61];
        // $groupStr = '54, 55, 56, 150, 151, 149, 57,61';
        // }
        $paramsCOGS = [
            'startDate' => $from,
            'endDate' => $to,
            'regenerate' => true,
            'waitForReport' => true,
            'fieldFilters' => implode(',', [
                'locationId',
                'discountPercent',
                'netDiscountTotal',
                'productId',
                'profitPercent',
                'salesProfit',
                'salesWithTaxTotal',
                'soldQuantity',
            ]),
        ];
        $groupsArray = [];
        if ($groups != '') {
            $groupsArray = explode(',', $groups);
            $paramsCOGS['productGroupIds'] = $groups;
        }
        $brandArray = [];
        $categoryArray = [];
        $supplierArray = [];
        $priorityArray = [];
        if ($brandIDs != '') {
            $brandArray = explode(',', $brandIDs);
            $paramsCOGS['productBrandIds'] = $brandIDs;
        }
        if ($categoryIDs != '') {
            $categoryArray = explode(',', $categoryIDs);
            $paramsCOGS['productCategoryIds'] = $categoryIDs;
        }
        if ($supplierIDs != '') {
            $supplierArray = explode(',', $supplierIDs);
            $paramsCOGS['productSupplierIds'] = $supplierIDs;
        }
        if ($priorityIDs != '') {
            $priorityArray = explode(',', $priorityIDs);
            $paramsCOGS['productPriorityIds'] = $priorityIDs;
        }

        $salesReportByDate = $this->getCogsInvoiceRows($paramsCOGS, $clientCode, $sessionKey);
        // dd($salesReportByDate);
        $cogsMap = [];
        $cogsMap = [];
        if (isset($salesReportByDate['data']['report']) && is_array($salesReportByDate['data']['report'])) {
            foreach ($salesReportByDate['data']['report'] as $cogsEntry) {
                $pid = $cogsEntry['productId'];
                $wid = $cogsEntry['locationId'];
                if (!isset($cogsMap[$pid][$wid])) {
                    $cogsMap[$pid][$wid] = [
                        'discountPercentSum' => 0, // Sum of percentages for averaging later if needed
                        'netDiscountTotal' => 0,
                        'profitPercentSum' => 0,   // Sum of percentages for averaging later if needed
                        'salesProfit' => 0,
                        'salesWithTaxTotal' => 0,
                        'soldQuantity' => 0,
                        'entryCount' => 0,         // To count entries for averaging percentages
                    ];
                }
                $cogsMap[$pid][$wid]['netDiscountTotal'] += (float) ($cogsEntry['netDiscountTotal'] ?? 0);
                $cogsMap[$pid][$wid]['salesProfit'] += (float) ($cogsEntry['salesProfit'] ?? 0);
                $cogsMap[$pid][$wid]['salesWithTaxTotal'] += (float) ($cogsEntry['salesWithTaxTotal'] ?? 0);
                $cogsMap[$pid][$wid]['soldQuantity'] += (int) ($cogsEntry['soldQuantity'] ?? 0);

                $cogsMap[$pid][$wid]['discountPercentSum'] += (float) ($cogsEntry['discountPercent'] ?? 0);
                $cogsMap[$pid][$wid]['profitPercentSum'] += (float) ($cogsEntry['profitPercent'] ?? 0);
                $cogsMap[$pid][$wid]['entryCount']++; // Increment entry count for averaging
            }

            foreach ($cogsMap as $pid => $locations) {
                foreach ($locations as $wid => $aggregatedData) {
                    if ($aggregatedData['salesWithTaxTotal'] > 0) {
                        $cogsMap[$pid][$wid]['discountPercent'] = ($aggregatedData['netDiscountTotal'] / $aggregatedData['salesWithTaxTotal']) * 100;
                        $cogsMap[$pid][$wid]['profitPercent'] = ($aggregatedData['salesProfit'] / $aggregatedData['salesWithTaxTotal']) * 100;
                    } else {
                        $cogsMap[$pid][$wid]['discountPercent'] = 0;
                        $cogsMap[$pid][$wid]['profitPercent'] = 0;
                    }
                    unset($cogsMap[$pid][$wid]['discountPercentSum']);
                    unset($cogsMap[$pid][$wid]['profitPercentSum']);
                    unset($cogsMap[$pid][$wid]['entryCount']);
                }
            }
        }
        if ($debug == 1) {
            // dd($salesReportByDate);
            // return response()->json(['status' => 'ok']);
            // print_r(json_encode($salesReportByDate));die;
            header('Content-Type: application/json');
            echo json_encode($salesReportByDate);
            die;
        }
        $csvHeaders = [
            'Product ID',
            'SKU',
            'Manufacturer SKU',
            'Brand',
            'Size',
            'Colour',
            'Season',
            'Gender',
            'Category',
            'Width',
            'Support Level',
            'Description',
            'Product Type',
            'Outlet',
            'POS Price',
            'Qty Avail',
            'Qty Ord',
            'Qty Sold',
            'Sold MTD',
            'Sold YTD',
            'Sales Inc',
            '% of Sales',
            'Discount',
            'Disc %',
            'GP',
            'GP %',
            'Last Sold Date',
            'Last Fulfilled Date'
        ];
        $warehouseMap = [
            '1' => 'Roadhouse Essendon',
            '2' => 'Roadhouse Cheltenham',
            '3' => 'Roadhouse WA',
            '4' => 'Roadhouse Canberra',
            '5' => 'Birkenstock at Roadhouse Essendon',
            '6' => 'Birkenstock at Roadhouse Uni Hill',
            '7' => 'Roadhouse South Wharf',
            '8' => 'Roadhouse Australia',
        ];
        $warehouseIds = array_keys($warehouseMap); // Get just the IDs for queries
        $escapeCsvValue = function ($value) {
            $value = (string) $value;
            $value = str_replace('"', '""', $value);
            if (strpos($value, ',') !== false || strpos($value, "\n") !== false || strpos($value, '"') !== false) {
                $value = '"' . $value . '"';
            }
            return $value;
        };
        // dd($escapeCsvValue);
        if ($debug == 5) {
            $query = VariationProduct::where('erplyDeleted', 0);

            if (count($groupsArray) > 0) {
                $query->whereIn('groupID', $groupsArray);
            }

            if (count($brandArray) > 0) {
                $query->whereIn('brandID', $brandArray);
            }

            if (count($categoryArray) > 0) {
                $query->whereIn('categoryID', $categoryArray);
            }

            if (count($supplierArray) > 0) {
                $query->whereIn('supplierID', $supplierArray);
            }

            if (count($priorityArray) > 0) {
                $query->whereIn('priorityGroupID', $priorityArray);
            }
            $data = $query->select([
                'productID',
                'code',
                'code2',
                'name',
                'priceWithVat',
                'brandName',
                'groupName',
                'variationDescription',
                'extraField1Code',
                'extraField2Code',
                'extraField3Code',
                'extraField4Code',
                'categoryName',
            ])->get();
            $sql = $query->toSql();
            foreach ($query->getBindings() as $binding) {
                $binding = is_numeric($binding) ? $binding : "'" . addslashes($binding) . "'";
                $sql = preg_replace('/\?/', $binding, $sql, 1);
            }
            dd($groupsArray, $brandArray, $categoryArray, $supplierArray, $priorityArray, $from, $to, $data, $sql);
        }
        return response()->stream(
            function () use ($groupsArray, $brandArray, $categoryArray, $supplierArray, $priorityArray, $from, $to, $clientCode, $sessionKey, $firstOfMonth, $firstOfYear, $cogsMap, $warehouseMap, $warehouseIds, $csvHeaders, $escapeCsvValue, $typeStr) {
                    echo implode(',', array_map($escapeCsvValue, $csvHeaders)) . "\n";
                    flush();
                    $totalProcessedProducts = 0; // Keep track of products processed for progress
                    $query = VariationProduct::where('erplyDeleted', 0);
                    if (count($groupsArray) > 0) {
                        $query->whereIn('groupID', $groupsArray);
                    }
                    if (count($brandArray) > 0) {
                        $query->whereIn('brandID', $brandArray);
                    }
                    if (count($categoryArray) > 0) {
                        $query->whereIn('categoryID', $categoryArray);
                    }
                    if (count($supplierArray) > 0) {
                        $query->whereIn('supplierID', $supplierArray);
                    }
                    if (count($priorityArray) > 0) {
                        $query->whereIn('priorityGroupID', $priorityArray);
                    }
                    $query->select([
                        'productID',
                        'code',
                        'code2',
                        'name',
                        'priceWithVat',
                        'brandName',
                        'groupName',
                        'variationDescription',
                        'extraField1Code',
                        'extraField2Code',
                        'extraField3Code',
                        'extraField4Code',
                        'categoryName',
                    ])->chunk(1000, function ($productsChunk) use (&$totalProcessedProducts, $from, $to, $clientCode, $sessionKey, $firstOfMonth, $firstOfYear, $cogsMap, $warehouseMap, $warehouseIds, $csvHeaders, $escapeCsvValue, $typeStr) {
                        $mappedProducts = $productsChunk->map(function ($item) {
                            $variationData = json_decode($item->variationDescription, true);

                            $color = null;
                            $size = null;

                            if (is_array($variationData)) {
                                foreach ($variationData as $var) {
                                    if (isset($var['name']) && $var['name'] === 'Colour') {
                                        $color = $var['value'];
                                    }
                                    if (isset($var['name']) && $var['name'] === 'Size') {
                                        $size = $var['value'];
                                    }
                                }
                            }

                            return [
                                'productID' => $item->productID,
                                'code' => $item->code,
                                'SKU' => $item->code2, // Manufacturer SKU
                                'groupName' => $item->groupName,
                                'Description' => $item->name,
                                'POS Price' => $item->priceWithVat,
                                'Brand' => $item->brandName,
                                'color' => $color,
                                'size' => $size,
                                'Support Level' => $item->extraField1Code,
                                'Width' => $item->extraField2Code,
                                'AF Season' => $item->extraField3Code, // Season
                                'Gender' => $item->extraField4Code,
                                'categoryName' => $item->categoryName,
                            ];
                        });

                        $chunkProductIds = $mappedProducts->pluck('productID')->toArray();
                        $priceListPrices = $this->getPricePolicyForVariants($chunkProductIds);
                        // dd($priceListPrices);
                        $salesData = $this->fetchLastSalesAndPurchasesByLocations(
                            $chunkProductIds,
                            $warehouseIds,
                            $from,
                            $to,
                            $clientCode,
                            $sessionKey
                        );
                        $salesMap = [];
                        foreach ($salesData as $entry) {
                            $salesMap[$entry['productId']][$entry['warehouseId']] = $entry;
                        }
                        // dd($salesMap);
                        $salesYear = $this->fetchLastSalesAndPurchasesByLocations(
                            $chunkProductIds,
                            $warehouseIds,
                            $firstOfYear,
                            date('Y-m-d'),
                            $clientCode,
                            $sessionKey
                        );
                        $salesYearMap = [];
                        foreach ($salesYear as $entry) {
                            $salesYearMap[$entry['productId']][$entry['warehouseId']] = $entry['totalQuantitySoldInPeriod'];
                        }
                        $salesMonth = $this->fetchLastSalesAndPurchasesByLocations(
                            $chunkProductIds,
                            $warehouseIds,
                            $firstOfMonth,
                            date('Y-m-d'),
                            $clientCode,
                            $sessionKey
                        );
                        $salesMonthMap = [];
                        foreach ($salesMonth as $entry) {
                            $salesMonthMap[$entry['productId']][$entry['warehouseId']] = $entry['totalQuantitySoldInPeriod'];
                        }
                        // dd($salesMap, $salesYearMap, $salesMonthMap);
                        $SOH = ProductSoh::whereIn('erplyProductID', $chunkProductIds)
                            ->whereIn('erplyWarehouseID', $warehouseIds)
                            ->get()
                            ->keyBy(function ($item) {
                                return $item->erplyProductID . '-' . $item->erplyWarehouseID;
                            });
                        $sohMap = [];
                        foreach ($SOH as $key => $record) {
                            list($pid, $wid) = explode('-', $key);
                            $sohMap[$pid][$wid] = $record->erplyCurrentStockValue;
                        }
                        // dd($sohMap);
                        foreach ($mappedProducts as $product) {
                            $productId = $product['productID'];
                            $sku = $product['code'];
                            $brand = $product['Brand'];
                            $posPrice = isset($priceListPrices[$productId]) && $priceListPrices[$productId]['status'] == 1
                                ? $priceListPrices[$productId]['salesPrice']
                                : ($product['POS Price'] ?? 0);
                            $posPriceFormatted = number_format((float) $posPrice, 2, '.', '');

                            foreach ($warehouseMap as $warehouseId => $warehouseName) {
                                $soh = (float) ($sohMap[$productId][$warehouseId] ?? 0);
                                $soldQty = (float) ($cogsMap[$productId][$warehouseId]['soldQuantity'] ?? 0);
                                $soldMTD = (float) ($salesMonthMap[$productId][$warehouseId] ?? 0);

                                // Skip if there's nothing sold or in stock
                                if ($soh == 0 && $soldQty == 0 && $soldMTD == 0) {
                                    continue;
                                }

                                $cogs = $cogsMap[$productId][$warehouseId] ?? [];
                                $currentSales = $salesMap[$productId][$warehouseId] ?? [];

                                $row = [
                                    'Product ID' => $productId,
                                    'SKU' => $sku,
                                    'Manufacturer SKU' => $product['SKU'],
                                    'Brand' => $brand,
                                    'Size' => $product['size'],
                                    'Colour' => $product['color'],
                                    'Season' => $product['AF Season'],
                                    'Gender' => $product['Gender'],
                                    'Category' => $product['categoryName'],
                                    'Width' => $product['Width'],
                                    'Support Level' => $product['Support Level'],
                                    'Description' => $product['Description'],
                                    'Product Type' => $product['groupName'],
                                    'Outlet' => $warehouseName,
                                    'POS Price' => $posPriceFormatted,
                                    'Qty Avail' => number_format($soh, 2, '.', ''),
                                    'Qty Ord' => '0',
                                    'Qty Sold' => $soldQty,
                                    'Sales Inc' => number_format((float) ($cogs['salesWithTaxTotal'] ?? 0), 2, '.', ''),
                                    'Discount' => number_format((float) ($cogs['netDiscountTotal'] ?? 0), 2, '.', ''),
                                    'Disc %' => number_format((float) ($cogs['discountPercent'] ?? 0), 2, '.', ''),
                                    'GP' => number_format((float) ($cogs['salesProfit'] ?? 0), 2, '.', ''),
                                    'GP %' => number_format((float) ($cogs['profitPercent'] ?? 0), 2, '.', ''),
                                    'Sold MTD' => $soldMTD,
                                    'Sold YTD' => $salesYearMap[$productId][$warehouseId] ?? 0,
                                    'Last Sold Date' => !empty($currentSales['lastSoldDate']) ? date('d/m/Y H:i', strtotime($currentSales['lastSoldDate'])) : '',
                                    'Last Fulfilled Date' => !empty($currentSales['lastPurchasedDate']) ? date('d/m/Y H:i', strtotime($currentSales['lastPurchasedDate'])) : '',
                                    '% of Sales' => '0.00',
                                ];
                                $csvRowValues = [];
                                foreach ($csvHeaders as $header) {
                                    $csvRowValues[] = $escapeCsvValue($row[$header] ?? '');
                                }
                                echo implode(',', $csvRowValues) . "\n";
                                flush();
                            }
                        }
                        // die;
                        $totalProcessedProducts += $productsChunk->count();
                        unset($productsChunk, $mappedProducts, $chunkProductIds, $salesMap, $salesYearMap, $salesMonthMap, $sohMap, $cogsEntry);
                    });
                
            },
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="product_sales_report.csv"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Accel-Buffering' => 'no', // Disable nginx buffering
            ]
        );
    }

}