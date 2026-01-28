<?php
namespace App\Http\Controllers\PswClientLive\Services;

 
use App\Models\PswClientLive\Local\LiveProductCategory;
use App\Models\PswClientLive\Local\LiveProductColor;
use App\Models\PswClientLive\Local\LiveProductDescription;
use App\Models\PswClientLive\Local\LiveProductGroup;
use App\Models\PswClientLive\Local\LiveProductMatrix;
use App\Models\PswClientLive\Local\LiveProductSize;
use App\Models\PswClientLive\Local\LiveProductSizeSortOrder;
use App\Models\PswClientLive\Local\LiveProductVariation;
use App\Models\PswClientLive\Local\TempProduct;
use App\Models\PswClientLive\Local\TempProductDescription;
use App\Models\PswClientLive\Product;
use App\Models\PswClientLive\ProductDescription;
use App\Models\PswClientLive\ProductSizeSortOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Traits\ResponseTrait;

class PswLiveProductService{

    use ResponseTrait;
    protected $psw_live_product;
    protected $temp_product;
    protected $currentsystem_product_matrix_live;
    protected $currentsystem_product_variation_live;

    public function __construct(Product $psw_product, TempProduct $temp_product, LiveProductMatrix $currentsystem_product_matrix, LiveProductVariation $currentsystem_product_variation){
        $this->psw_live_product = $psw_product;
        $this->temp_product = $temp_product;
        $this->currentsystem_product_matrix_live = $currentsystem_product_matrix;
        $this->currentsystem_product_variation_live = $currentsystem_product_variation;
    }

    function escapeFunc($val){
        
        $val = str_replace("'","\'",$val);
        $val = str_replace('"','\"',$val);
        // $val = trim($val);
        return $val;
    }


    public function makeProductFile(){

        $path = public_path('PswLiveTemp');

        File::delete($path . '/productDev.txt');

        if (!File::exists($path)) {

            File::makeDirectory($path);

        }

        $products = $this->psw_live_product->get();
        // dd($products);
        $chunkProduct = $products->chunk(500);

        foreach ($chunkProduct as $cpro) {

            $content = 'Insert into `temp_product_dev`(`SchoolID`,
                    `SchoolName`,
                    `CustomerGroup`,
                    `ERPLYSKU`,
                    `WEBSKU`,
                    `ITEMID`,
                    `ItemName`,
                    `ColourID`,
                    `ColourName`,
                    `SizeID`,
                    `CONFIGID`,
                    `ConfigName`,
                    `EANBarcode`,
                    `SOFTemplate`,
                    `SOFName`,
                    `SOFOrder`,
                    `SOFStatus`,
                    `PLMStatus`,
                    `ProductType`,
                    `ProductSubType`,
                    `Supplier`,
                    `Gender`,
                    `CategoryName`,
                    `ItemWeightGrams`,
                    `RetailSalesPrice`,
                    `RetailSalesPriceExclGST`,
                    `CostPrice`,
                    `DefaultStore`,
                    `SecondaryStore`,
                    `ERPLYFLAG`,
                    `ERPLYFLAGModified`,
                    `AvailableForPurchase`,
                    `WebEnabled`,
                    `SOFLastModified`,
                    `ItemLastModified`,
                    `Category_Name`,
                    `PSWPRICELISTITEMCATEGORY`,
                    `pendingProcess`) VALUES ';
            
            $q = null;

            foreach ($cpro as $key => $value) {

                //$compareField = $value->STOCKCODE . '-' . (int)$value->LOCNO . '-' . (int)$value->INSTOCKQTY . '-' . (int)$value->SALESORDQTY . '-' . (int)$value->VIRTSTOCK;

                $key = $cpro->last() == $value ? ';' : ',';

                $q .= '( "'. $value['School ID'] . '",
                        "' . $this->escapeFunc($value['School Name']) . '",
                        "' . $value['Customer Group'] . '",
                        "' . $value['ERPLY SKU'] . '",
                        "' . $value['WEB SKU'] . '",
                        "' . $value['ITEMID'] . '",
                        "' . $value['Item Name'] . '",
                        "' . $value['ColourID'] . '",
                        "' . $this->escapeFunc($value['Colour Name']) . '",
                        "' . $value['SizeID'] . '",
                        "' . $value['CONFIGID'] . '",
                        "' . $this->escapeFunc($value['Config Name']) . '",
                        "' . $value['EAN Barcode'] . '",
                        "' . $value['SOF Template'] . '",
                        "' . $this->escapeFunc($value['SOF Name']) . '",
                        "' . $value['SOF Order'] . '",
                        "' . $value['SOF Status'] . '",
                        "' . $value['PLM Status'] . '",
                        "' . $value['Product Type'] . '",
                        "' . $value['Product Sub Type'] . '",
                        "' . $value['Supplier'] . '",
                        "' . $value['Gender'] . '",
                        "' . $value['Category Name'] . '",
                        "' . $value['Item Weight - grams'] . '",
                        "' . $value['Retail Sales Price'] . '",
                        "' . $value['Retail Sales Price excl GST'] . '",
                        "' . $value['Cost Price'] . '",
                        "' . $value['Default Store'] . '",
                        "' . $value['Secondary Store'] . '",
                        "' . $value['ERPLY FLAG'] . '",
                        "' . $value['ERPLY FLAG Modified'] . '",
                        "' . $value['Available for Purchase'] . '",
                        "' . $value['Web Enabled'] . '", 
                        "' . $value['SOF Last Modified'] . '", 
                        "' . $value['Item Last Modified'] . '", 
                        "' . $value['CATEGORYNAME'] . '", 
                        "' . $value['PSW_PRICELISTITEMCATEGORY'] . '", 
                        "1")' . $key;

            }

            $content = $content . '' . $q . '' . "\n";

            File::append($path . '/productDev.txt', $content);

        }

        return $this->successWithMessage("Product File Generated Successfully.");
    }

    public function readProductFileAndStore(){
        $path = public_path('PswLiveTemp/productDev.txt');

        if (File::exists($path)) { 
             
            $tempProduct = $this->temp_product->where('pendingProcess', 0)->count(); 
            $this->temp_product->truncate();
            if ($tempProduct < 1) { 
                // $this->temp_product->truncate(); 
                return $this->processFile($path); 
            } 
        } else{
            echo "no file";
            die;
        }
    }

    protected function processFile($path){ 

        $file = File::get($path);
         
        $sqls = explode(";\n", $file); 
        
        foreach ($sqls as $sql) {
              
            if ($sql != '') { 
                DB::connection('mysql2')->select($sql); 
            }
            
           
        }   

        return $this->successWithMessage("Product File Executed Successfully.");
  
    }

    public function syncTempToCurrentsystemMatrix(){
        //get data from temp table
        $temp_product = $this->temp_product->where('matrixPending', '1')->groupBy('WEBSKU')->limit(100)->get();

        foreach($temp_product as $temp_p){
            $flag = $this->temp_product->where('WEBSKU', $temp_p->WEBSKU)->where('WebEnabled', '1')->first();
            $isActive = 1;
            if(!$flag){
                $isActive = 0;
            }
            $this->currentsystem_product_matrix_live->updateOrcreate(
                [
                    "WEBSKU" => trim($temp_p->WEBSKU)
                ],
                [
                    "SchoolID" =>    trim($temp_p->SchoolID),
                    "SchoolName" =>  trim($temp_p->SchoolName),
                    "CustomerGroup" =>   trim($temp_p->CustomerGroup),
                    "ERPLYSKU" =>    trim($temp_p->ERPLYSKU),
                    "WEBSKU" =>  trim($temp_p->WEBSKU),
                    "ITEMID" =>  trim($temp_p->ITEMID),
                    "ItemName" =>    trim($temp_p->ItemName),
                    "ColourID" =>    trim($temp_p->ColourID),
                    "ColourName" =>  trim($temp_p->ColourName),
                    "SizeID" =>  trim($temp_p->SizeID),
                    "CONFIGID" =>    trim($temp_p->CONFIGID),
                    "ConfigName" =>  trim($temp_p->ConfigName),
                    "EANBarcode" =>  trim($temp_p->EANBarcode),
                    "SOFTemplate" => trim($temp_p->SOFTemplate),
                    "SOFName" => trim($temp_p->SOFName),
                    "SOFOrder" =>    trim($temp_p->SOFOrder),
                    "SOFStatus" =>   trim($temp_p->SOFStatus),
                    "PLMStatus" =>   trim($temp_p->PLMStatus),
                    "ProductType" => trim($temp_p->ProductType),
                    "ProductSubType" =>  trim($temp_p->ProductSubType),
                    "Supplier" =>    trim($temp_p->Supplier),
                    "Gender" =>  trim($temp_p->Gender),
                    "CategoryName" =>    trim($temp_p->CategoryName),
                    "ItemWeightGrams" => trim($temp_p->ItemWeightGrams),
                    "RetailSalesPrice" =>    trim($temp_p->RetailSalesPrice == '' ? '0.00' : $temp_p->RetailSalesPrice),
                    "RetailSalesPriceExclGST" => trim($temp_p->RetailSalesPriceExclGST == '' ? '0.00' : $temp_p->RetailSalesPriceExclGST),
                    "CostPrice" =>   trim($temp_p->CostPrice == '' ? '0.00' : $temp_p->CostPrice),
                    "DefaultStore" =>    trim($temp_p->DefaultStore),
                    "SecondaryStore" =>  trim($temp_p->SecondaryStore),
                    "ERPLYFLAG" =>   trim($temp_p->ERPLYFLAG),
                    "ERPLYFLAGModified" =>   trim($temp_p->ERPLYFLAGModified),
                    "AvailableForPurchase" =>    trim($temp_p->AvailableForPurchase),
                    "WebEnabled" => $isActive,
                    "SOFLastModified" => $temp_p->SOFLastModified,
                    "ItemLastModified" => $temp_p->ItemLastModified,
                    "PSWPRICELISTITEMCATEGORY" => $temp_p->PSWPRICELISTITEMCATEGORY,
                    "Category_Name" => $temp_p->Category_Name,
                    "erplyPending" => 1
                ]
            );

            LiveProductGroup::updateOrcreate(
                [
                    "SchoolName" => trim($temp_p->SchoolName) , 
                ],
                [
                    "SchoolID" => trim($temp_p->SchoolID), 
                    "SchoolName" => trim($temp_p->SchoolName) , 
                    "WebEnabled" => $temp_p->WebEnabled,
                    "pendingProcess" => 1
                ]
            );
             

            //updang flag
            $this->temp_product->where("WEBSKU", $this->escapeFunc($temp_p->WEBSKU))->update(["matrixPending" => 0]);

        }

        return $this->successWithMessage("Temp Product Sync Successfully.");
    }


    public function syncTempToCurrentsystem(){
        //get data from temp table
        $temp_product = $this->temp_product->where('pendingProcess', '1')->limit(500)->get();

        foreach($temp_product as $temp_p){
            $current_p = $this->currentsystem_product_variation_live->updateOrcreate(
                [
                    "ERPLYSKU" => trim($temp_p->ERPLYSKU)
                ],
                [
                    "SchoolName" => trim($temp_p->SchoolName),
                    "SchoolID" => trim($temp_p->SchoolID),
                    "CustomerGroup" => trim($temp_p->CustomerGroup),
                    "ERPLYSKU" => trim($temp_p->ERPLYSKU),
                    "WEBSKU" => trim($temp_p->WEBSKU),
                    "ITEMID" => trim($temp_p->ITEMID),
                    "ItemName" => trim($temp_p->ItemName),
                    "ColourID" => trim($temp_p->ColourID),
                    "ColourName" => trim($temp_p->ColourName),
                    "SizeID" => trim($temp_p->SizeID),
                    "CONFIGID" => trim($temp_p->CONFIGID),
                    "ConfigName" => trim($temp_p->ConfigName),
                    "EANBarcode" => trim($temp_p->EANBarcode),
                    "SOFTemplate" => trim($temp_p->SOFTemplate),
                    "SOFName" => trim($temp_p->SOFName),
                    "SOFOrder" => trim($temp_p->SOFOrder),
                    "SOFStatus" => trim($temp_p->SOFStatus),
                    "PLMStatus" => trim($temp_p->PLMStatus),
                    "ProductType" => trim($temp_p->ProductType),
                    "ProductSubType" => trim($temp_p->ProductSubType),
                    "Supplier" => trim($temp_p->Supplier),
                    "Gender" => trim($temp_p->Gender),
                    "CategoryName" => trim($temp_p->CategoryName),
                    "ItemWeightGrams" => trim($temp_p->ItemWeightGrams),
                    "RetailSalesPrice" =>trim($temp_p->RetailSalesPrice == '' ? '0.00' : $temp_p->RetailSalesPrice),
                    "RetailSalesPriceExclGST" => trim($temp_p->RetailSalesPriceExclGST == '' ? '0.00' : $temp_p->RetailSalesPriceExclGST),
                    "CostPrice" =>  trim($temp_p->CostPrice == '' ? '0.00' : $temp_p->CostPrice),
                    "DefaultStore" => trim($temp_p->DefaultStore),
                    "SecondaryStore" => trim($temp_p->SecondaryStore),
                    "ERPLYFLAG" => trim($temp_p->ERPLYFLAG),
                    "ERPLYFLAGModified" => trim($temp_p->ERPLYFLAGModified),
                    "AvailableForPurchase" => trim($temp_p->AvailableForPurchase),
                    "WebEnabled" => trim($temp_p->WebEnabled),
                    "SOFLastModified" => $temp_p->SOFLastModified,
                    "ItemLastModified" => $temp_p->ItemLastModified,
                    "PSWPRICELISTITEMCATEGORY" => $temp_p->PSWPRICELISTITEMCATEGORY,
                    "Category_Name" => $temp_p->Category_Name,
                    "erplyPending" => 1
                ]
            );
           

            //Now saving Product Color and Size
            // $checkColor = LiveProductColor::where('name', trim($temp_p->ColourName))->first();
            // if(!$checkColor){
            //     LiveProductColor::create(["name" => trim($temp_p->ColourName) ]);
            // }

            // $checkColor = LiveProductSize::where('name', trim($temp_p->SizeID))->first();
            // if(!$checkColor){
            //     LiveProductSize::create(["name" => trim($temp_p->SizeID) ]);
            // }

            //For Category
            // $checkCat = LiveProductCategory::where('name', $temp_p->CategoryName)->first();
            // if($temp_p->CategoryName != ''){
            //     LiveProductCategory::updateOrcreate(
            //         [
            //             'name' => $temp_p->ProductType
            //         ],
            //         [
            //             'name' => $temp_p->ProductType,
            //             'pendingProcess' => 1
            //         ]
            //     );
            // }

            //For Group
            // $checkGroup = LiveProductGroup::where('SchoolName', $temp_p->SchoolName)->first();
            // if(!$checkGroup){
                
            // }
            //updang flag
            $temp_p->pendingProcess = '0';
            $temp_p->save();

        }
        info("Temp Product Sync to Live Product Variation");
        return $this->successWithMessage("Temp Product Sync Successfully.");
    }

    //For Product Size Sort
    public function syncPswLivetoMiddleware(){
        //PSW SQL Server
        $pswLiveSql = ProductSizeSortOrder::get();

        foreach($pswLiveSql as $sizeSort){
            LiveProductSizeSortOrder::updateOrcreate(
                [
                    "size" => $sizeSort["SIZE_"]
                ],
                [
                    "size" => $sizeSort["SIZE_"],
                    "sort_order" => $sizeSort["SORTORDER"],
                    "dmx_sort_order" => $sizeSort["DMX_SORTORDER"],
                    "recid" => $sizeSort["RECID"]
                ]
            );
        }

        return response()->json(["status" => "success"]);
    }

    public function makeDescriptionFile(){
        $path = public_path('PswLiveTemp');

        File::delete($path . '/productDescription.txt');

        if (!File::exists($path)) { 
            File::makeDirectory($path); 
        }

        $des = ProductDescription::get();
        // dd($products);
        $chunkDes = $des->chunk(500);

        foreach ($chunkDes as $cpd) {

            $content = 'Insert into `temp_product_description`(`WEBSKU`,
                    `ITEMID`,
                    `LongDescription`,
                    `ModifiedDateTime`,
                    `pendingProcess`
                    ) VALUES ';
            
            $q = null;

            foreach ($cpd as $key => $value) {

                //$compareField = $value->STOCKCODE . '-' . (int)$value->LOCNO . '-' . (int)$value->INSTOCKQTY . '-' . (int)$value->SALESORDQTY . '-' . (int)$value->VIRTSTOCK;

                $key = $cpd->last() == $value ? ';' : ',';

                $q .= '( "'. $value['WEB SKU'] . '",
                        "' . $value['ITEMID'] . '",
                        "' . $this->escapeFunc($value['Long Description']) . '",
                        "' . $value['Description ModifiedDateTime'] . '",
                        "1")' . $key;

            }

            $content = $content . '' . $q . '' . "\n";

            File::append($path . '/productDescription.txt', $content);

        }

        return $this->successWithMessage("Product Description File Generated Successfully.");

    }

    public function readProductDescriptionAndStore(){
        $path = public_path('PswLiveTemp/productDescription.txt');

        if (File::exists($path)) {  
            // $tempProDes = TempProductDescription::where('pendingProcess', 0)->count(); 
            // TempProductDescription::turncate();
            
            $file = File::get($path);
         
            $sqls = explode(";\n", $file); 
            
            foreach ($sqls as $sql) { 
                if ($sql != '') { 
                    DB::connection('mysql2')->select($sql); 
                } 
            }   

            return $this->successWithMessage("Product Description File Executed Successfully.");
            
        } else{
            echo "no file";
            die;
        }
    }

    public function syncDescriptionNewsystem(){
        $des = TempProductDescription::where('pendingProcess', 1)->limit(500)->get();

        foreach($des as $d){
            LiveProductDescription::updateOrcreate(
                [
                    "WEBSKU" => $d->WEBSKU,
                ],
                [
                    "WEBSKU" => $d->WEBSKU,
                    "ITEMID" => $d->ITEMID,
                    "LongDescription" => trim($d->LongDescription),
                    "ModifiedDateTime" => $d->ModifiedDateTime,
                ]
            );
            $d->pendingProcess = 0;
            $d->save();
        }

        return $this->successWithMessage("Product Description Sync to Newsystem Successfully.");

    }
 

}