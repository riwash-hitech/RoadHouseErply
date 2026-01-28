<?php

namespace App\Http\Controllers\PswClientLive;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PswClientLive\Services\PswLiveProductService;
use App\Http\Controllers\PswClientLive\Services\PswLiveStoreLocationService;
use App\Models\PswClientLive\Product;
use Illuminate\Support\Facades\File;

class PswLiveStoreLocationController extends Controller
{
    //
    protected $service;

    public function __construct(PswLiveStoreLocationService $ps){
      $this->service = $ps;
    }



    //Generating Product File
    public function makeStoreLocationFile(){ 
        return $this->service->makeStoreFile(); 
    }


    //Inserting Product File to Temp Table
    public function handleStoreLocationFile(){

        return $this->service->readStoreFileAndStore();
        
    }

    //syncing to live table
    public function syncToLive(){
        return $this->service->syncToLiveTable();
    }

    

     
}
 