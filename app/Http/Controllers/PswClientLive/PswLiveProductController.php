<?php

namespace App\Http\Controllers\PswClientLive;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PswClientLive\Services\PswLiveProductService;
use App\Models\PswClientLive\Product;
use Illuminate\Support\Facades\File;

class PswLiveProductController extends Controller
{
    //
    protected $service;

    public function __construct(PswLiveProductService $ps){
      $this->service = $ps;
    }



    //Generating Product File
    public function makeProductFile(){ 
        return $this->service->makeProductFile(); 
    }


    //Inserting Product File to Temp Table
    public function handleProductFile(){

        return $this->service->readProductFileAndStore();
        
    }

    public function syncTempToCurrentsystem(){
        return $this->service->syncTempToCurrentsystem();
    }

    public function syncTempToCurrentsystemMatrix(){
        return $this->service->syncTempToCurrentsystemMatrix();
    }

    public function pswToMiddlewareSizeSort(){
        return $this->service->syncPswLivetoMiddleware();
    }


    //FOR DESCRIPTION

    public function makeDescriptionFile(){
        return $this->service->makeDescriptionFile();
    }

    public function readDescriptionFile(){
        return $this->service->readProductDescriptionAndStore();
    }

    public function syncDescriptionNewsystem(){
        return $this->service->syncDescriptionNewsystem();
    }


    

     
}
 