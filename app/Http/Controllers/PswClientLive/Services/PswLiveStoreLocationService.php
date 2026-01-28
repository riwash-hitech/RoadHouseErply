<?php
namespace App\Http\Controllers\PswClientLive\Services;

use App\Models\PswClientLive\Local\LiveWarehouseLocation;
use App\Models\PswClientLive\Local\TempWarehouseLocation;
use App\Models\PswClientLive\StoreLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Traits\ResponseTrait;

class PswLiveStoreLocationService{

    use ResponseTrait;
    protected $store_location_live;
    protected $temp_location;
    protected $current_live;


    public function __construct(StoreLocation $pswLive, TempWarehouseLocation $temp_warehouse, LiveWarehouseLocation $curren_system_live){
        $this->store_location_live = $pswLive;
        $this->temp_location = $temp_warehouse;
        $this->current_live = $curren_system_live;
    }

    function escapeFunc($val){
        $val = trim($val);
        // $val = str_replace("'","\'",$val);
        // $val = str_replace('"','\"',$val);
        return $val;
    }

    public function makeStoreFile(){

        $path = public_path('PswLiveTemp');

        File::delete($path . '/storeLocation.txt');

        if (!File::exists($path)) {

            File::makeDirectory($path); 
        }

        $products = $this->store_location_live->get();
        // dd($products);
        $chunkProduct = $products->chunk(500);

        foreach ($chunkProduct as $cpro) {

            $content = 'Insert into `temp_store_location`(`LocationID`,
                    `LocationName`,
                    `LocationType`,
                    `MainStoreFlag`,
                    `AllowClickAndCollect`,
                    `ADDRESS`,
                    `STREET`,
                    `CITY`,
                    `STATE`,
                    `Postcode`,
                    `EMAIL`,
                    `PHONE`,
                    `ClickAndCollectInfo`,
                    `LONGITUDE`,
                    `LATITUDE`,
                    `StoreHours`,
                    `AllowTransferOrdersTo`,
                    `AllowTransferOrdersFrom`,
                    `Division`,
                    `CostCentre`,
                    `State2`,
                    `Region`,
                    `DefaultReceiptLocation`,
                    `DefaultIssueLocation`,
                    `DefaultCustomer`,
                    `StoreID`,
                    `LocationFlag`,
                    `LastModifiedDateTime`,
                    `pendingProcess`
                   ) VALUES ';
            
            $q = null;

            foreach ($cpro as $key => $value) {

                //$compareField = $value->STOCKCODE . '-' . (int)$value->LOCNO . '-' . (int)$value->INSTOCKQTY . '-' . (int)$value->SALESORDQTY . '-' . (int)$value->VIRTSTOCK;

                $key = $cpro->last() == $value ? ';' : ',';

                $q .= '( "'. $value['Location ID'] . '",
                        "' . $this->escapeFunc($value['Location Name']) . '",
                        "' . $value['Location Type'] . '",
                        ' . $value['Main Store Flad'] . ',
                        ' . $value['Allow Click and Collect'] . ',
                        "' . $value['ADDRESS'] . '",
                        "' . $value['STREET'] . '",
                        "' . $value['CITY'] . '",
                        "' . $this->escapeFunc($value['STATE']) . '",
                        "' . $value['Postcode'] . '",
                        "' . $value['EMAIL'] . '",
                        "' . $value['PHONE'] . '",
                        "' . $value['Click and Collect Info'] . '",
                        "' . $value['LONGITUDE'] . '",
                        "' . $value['LATITUDE'] . '",
                        "' . $value['Store Hours'] . '",
                        ' . $value['Allow Transfer Orders To'] . ',
                        ' . $value['Allow Transfer Orders From'] . ',
                        "' . $value['Division'] . '",
                        "' . $value['Cost Centre'] . '",
                        "' . $value['State'] . '",
                        "' . $value['Region'] . '",
                        "' . $value['Default Receipt Location'] . '",
                        "' . $value['Default Issue Location'] . '",
                        ' . $value['Default Customer'] . ',
                        ' . $value['Store ID'] . ',
                        "' . $value['Location Flag'] . '",
                        "' . $value['Last Modified Date Time'] . '", 
                        1)' . $key;

            }

            $content = $content . '' . $q . '' . "\n";

            File::append($path . '/storeLocation.txt', $content);

        }

        return $this->successWithMessage("Store Location File Generated Successfully.");
    }

    public function readStoreFileAndStore(){
        $path = public_path('PswLiveTemp/storeLocation.txt');

        if (File::exists($path)) { 
             
            $tempLocation = $this->temp_location->where('pendingProcess', 0)->count(); 
            $this->temp_location->truncate();
            if ($tempLocation < 1) { 
                // $this->temp_product->truncate(); 
                return $this->processFile($path); 
            } 
        } else{
            echo "no data";
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

        return $this->successWithMessage("Store Location File Executed Successfully.");
  
    }


    public function syncToLiveTable(){
        $temp_location = $this->temp_location->where('pendingProcess', '1')->limit(500)->get();

        foreach($temp_location as $tl){
            $current_live = new $this->current_live;
            $current_live->LocationID = $tl->LocationID;
            $current_live->LocationName = $this->escapeFunc($tl->LocationName);
            $current_live->LocationType = $tl->LocationType;
            $current_live->MainStoreFlag = $tl->MainStoreFlag;
            $current_live->AllowClickAndCollect = $tl->AllowClickAndCollect;
            $current_live->ADDRESS = $tl->ADDRESS;
            $current_live->STREET = $tl->STREET;
            $current_live->CITY = $tl->CITY;
            $current_live->STATE = $this->escapeFunc($tl->STATE);
            $current_live->Postcode = $tl->Postcode;
            $current_live->EMAIL = $tl->EMAIL;
            $current_live->PHONE = $tl->PHONE;
            $current_live->ClickAndCollectInfo = $tl->ClickAndCollectInfo;
            $current_live->LONGITUDE = $tl->LONGITUDE;
            $current_live->LATITUDE = $tl->LATITUDE;
            $current_live->StoreHours = $tl->StoreHours;
            $current_live->AllowTransferOrdersTo = $tl->AllowTransferOrdersTo;
            $current_live->AllowTransferOrdersFrom = $tl->AllowTransferOrdersFrom;
            $current_live->Division = $tl->Division;
            $current_live->CostCentre = $tl->CostCentre;
            $current_live->State2 = $tl->State2;
            $current_live->Region = $tl->Region;
            $current_live->DefaultReceiptLocation = $tl->DefaultReceiptLocation;
            $current_live->DefaultIssueLocation = $tl->DefaultIssueLocation;
            $current_live->DefaultCustomer = $tl->DefaultCustomer;
            $current_live->StoreID = $tl->StoreID;
            $current_live->LocationFlag = $tl->LocationFlag;
            $current_live->LastModifiedDateTime = $tl->LastModifiedDateTime;
            $current_live->pendingProcess = 1;
            $current_live->save();

            $tl->pendingProcess = 0;
            $tl->save();

        }

        return $this->successWithMessage("Store Location Data Sync to Location Live Successfully.");

    }




}