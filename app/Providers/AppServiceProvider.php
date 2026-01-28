<?php

namespace App\Providers;

use App\Http\Controllers\EAPI;
use App\Http\Controllers\Paei\API\APIServices\CurrencyApiService;
use App\Http\Controllers\Paei\API\CurrencyApiController;
use App\Http\Controllers\Paei\GetUserOperationLogController;
use App\Http\Controllers\Paei\Services\UserOperationInterface;
use App\Http\Controllers\ProductBulkDeleteController;
use App\Http\Controllers\Services\EAPIService;
use App\Interfaces\ApiInterface;
use App\Models\Client;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
include("EAPI.class.php");

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        // $this->app->bind(ApiInterface::class, EAPIService::class);
        // $this->app->bind(EAPIService::class, function(){ 
        //     $api = new EAPI();
        //     $client = Client::findOrfail(2);
        //     $api->clientCode = $client->clientCode;
        //     $api->username = $client->username;
        //     $api->password = $client->password;
        //     $api->url = "https://".$api->clientCode.".erply.com/api/";
        //     return new EAPIService($api, $client);
        // });

        // $this->app->bind(Client::class, function(){
        //     $client = Client::findOrfail(2);
        //     return $client;
        // });

        // $this->app->bind(EAPIService::class, function(){ 
        //     $api = new EAPI();
        //     $client = Client::findOrfail(2);
        //     $api->clientCode = $client->clientCode;
        //     $api->username = $client->username;
        //     $api->password = $client->password;
        //     $api->url = "https://".$api->clientCode.".erply.com/api/";
        //     return new EAPIService($api, $client);
        // });
        
        // $this->app->when(GetUserOperationLogController::class)->needs(UserOperationInterface::class)->give(function(Request $req){
            
        // });

        // $this->app->bind(UserOperationInterface::class, function(){

        // });

        $this->app->bind(EAPIService::class, function(){ 
            $req = app(\Illuminate\Http\Request::class);
            $api = new EAPI();

            $client = Client::findOrfail(1);
            // if($req->env == "TEST"){
            //     $client = Client::findOrfail(3);
            // }
            // if($req->env == "LIVE"){
            //     $client = Client::findOrfail(5);
            // }
            $api->clientCode = $client->clientCode;
            $api->username = $client->username;
            $api->password = $client->password;
            $api->url = "https://".$api->clientCode.".erply.com/api/";
            return new EAPIService($api, $client);
        });
        
        // $this->app->when(ProductBulkDeleteController::class)->needs(EAPIService::class)->give(function(){
        //     $api = new EAPI();
        //     $client = Client::findOrfail(3);
        //     $api->clientCode = $client->clientCode;
        //     $api->username = $client->username;
        //     $api->password = $client->password;
        //     $api->url = "https://".$api->clientCode.".erply.com/api/";
        //     return new EAPIService($api, $client);
        // });

        // $this->app->when(CurrencyApiService::class)->needs(EAPIService::class)->give(function(){
        //     $api = new EAPI();
        //     $client = Client::findOrfail(2);
        //     $api->clientCode = $client->clientCode;
        //     $api->username = $client->username;
        //     $api->password = $client->password;
        //     $api->url = "https://".$api->clientCode.".erply.com/api/";
        //     return new EAPIService($api, $client);
        // });

        // $this->app->when(ProductBulkDeleteController::class)->needs(EAPIService::class, function(){
        //     $api = new EAPI();
        //     $client = Client::findOrfail(1);
        //     $api->clientCode = $client->clientCode;
        //     $api->username = $client->username;
        //     $api->password = $client->password;
        //     $api->url = "https://".$api->clientCode.".erply.com/api/";
        //     return new EAPIService($api, $client);
        // });

        // $this->app->when(CurrencyApiService::class)->needs(EAPIService::class, function(){
        //     $api = new EAPI();
        //     $client = Client::findOrfail(1);
        //     $api->clientCode = $client->clientCode;
        //     $api->username = $client->username;
        //     $api->password = $client->password;
        //     $api->url = "https://".$api->clientCode.".erply.com/api/";
        //     return new EAPIService($api, $client);
        // });

        // $this->app->when(ProductBulkDeleteController::class, function($app){
        //     $this->app->bind(EAPIService::class, function(){ 
        //         $api = new EAPI();
        //         $client = Client::findOrfail(1);
        //         $api->clientCode = $client->clientCode;
        //         $api->username = $client->username;
        //         $api->password = $client->password;
        //         $api->url = "https://".$api->clientCode.".erply.com/api/";
        //         return new EAPIService($api, $client);
        //     });
    
        //     $this->app->bind(Client::class, function(){
        //         $client = Client::findOrfail(1);
        //         return $client;
        //     });
        // });

        // $this->app->when(CurrencyApiController::class, function($app)
        // {
        //     $this
        //     $this->app->bind(EAPIService::class, function(){ 
        //         $api = new EAPI();
        //         $client = Client::findOrfail(2);
        //         $api->clientCode = $client->clientCode;
        //         $api->username = $client->username;
        //         $api->password = $client->password;
        //         $api->url = "https://".$api->clientCode.".erply.com/api/";
        //         return new EAPIService($api, $client);
        //     });
    
        //     $this->app->bind(Client::class, function(){
        //         $client = Client::findOrfail(2);
        //         return $client;
        //     });
        // });
           
      
        

        

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
