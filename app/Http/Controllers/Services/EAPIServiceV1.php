<?php
namespace App\Http\Controllers\Services;

use App\Http\Controllers\EAPI;
use App\Http\Controllers\Response\CustomeResponse;
use App\Models\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
// include("EAPI.class.php");

class EAPIService //implements ApiInterface
{
    protected $api;
    public $client;
    protected $session;

    public function __construct(EAPI $api, Client $client)
    {
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->api = $api;
        $this->client = $client;
       
        // $this->verifySession();
    }


    public function sendRequest($url, $param, $isBulk = 0, $errorFlag = 0, $sessionKeyFlag = 1)
    {
        if($sessionKeyFlag == 1)$param["sessionKey"] = $this->client->sessionKey;
        if($isBulk == 0)$param["clientCode"] = $this->client->clientCode;

        // print_r($param);
        // die;

        $result = $this->api->sendRequest($url, $param, $isBulk);
        // dd($result);
        $products = json_decode($result, true);
        if($products['status']['errorCode'] == 1054 || $products['status']['errorCode'] == 1055 || $products['status']['errorCode'] == 1056 || $products['status']['errorCode'] == 1057){
                info("Session Key Expired");
                $this->verifyUser();
                die;
                // info($products);
                // return CustomeResponse::failWithMessageAndData("Error ".$products['status']['errorField'], $products);
                // return $this->errorWithMessageAndData("Error ".$products['status']['errorField'], $products);
                // 
                // die;
                
                // return $products;
            }
        return $products;
    }

    public function callServicePoint(){
        $param = array(
            "clientCode" => $this->client->clientCode,
            "sessionKey" => $this->client->sessionKey,
            // "sessionLength" => "86400"
        );
        $res = $this->api->sendRequest("getServiceEndpoints", $param);
        info($res);
    }

    public function sendRequestBySwagger($url,$param){
         
        $this->callServicePoint();
        $data = Http::withHeaders([
                'sessionKey' => $this->client->sessionKey,
                'clientCode' => $this->client->clientCode,
                 ])->get($url,
                  $param
                  
                );
        $data = $data->json();
        return $data;
    }
    
    
    public function sendRequestByCDNApiGetWithJwt($url,$param){
         
        // $this->callServicePoint();
        $data = Http::withHeaders([
                'JWT' => $this->client->jwt
                 ])->get($url,
                  $param
                  
                );
        $data = $data->json();
        return $data;
    }

    public function sendRequestBySwaggerWithoutData($url){

        

        $this->callServicePoint();
        
        $data = Http::withHeaders([
                'sessionKey' => $this->client->sessionKey,
                'clientCode' => $this->client->clientCode,
                 ])->delete($url,);
        $data = $data->json();
        return $data;
    }

    // public function sendRequest($url, $param, $isBulk, $errorFlag){

    //     // $param["clientcode"] = $this->client->clientCode;
    //     $param["sessionKey"] = $this->verifySessionByKey($this->client->sessionKey);
    //     // print_r($param);
    //     // die;
    //     $result = $this->api->sendRequest($url, $param, $isBulk);
    //     // return $result;
    //     $products = json_decode($result, true);
    //     // print_r($products);
    //     // die;
    //     if($errorFlag == 0){
    //         if($products['status']['errorCode'] != 0){
    //             return $products;
    //             // echo "Error : ";
    //             // print_r($products);
    //             // die;
    //             // return response()->json(['stauts' => 'Error '. $products['status']['errorCode']]);
    //         }
    //     }
    //     return $products;
    // }

    public function verifyUser(){

        Log::info("VerifyUser Function called");
        $param = array(
            "username" => $this->client->username,
            "password" => $this->client->password,
            "sessionLength" => "86400"
        );
        $res = $this->api->sendRequest("verifyUser", $param);
        $res = json_decode($res, true);
        info("VerifyUser Response received");
        if($res['status']['errorCode'] != 0){
            info("erro while verifying user".$res['status']['errorCode']);
            return response()->json(['stauts' => 'Error '. $res['status']['errorCode']]);
        }
        $this->client->sessionKey = $res['records'][0]['sessionKey'];
        $this->client->jwt = $res['records'][0]['identityToken'];
        $this->client->save();
        Log::info("session key updated");
        return $res['records'][0]['sessionKey'];


    }

    public function verifySession(){

        if($this->client->sessionKey == ''){
            $this->verifyUser();
        }

        $param = array(
            'sessionKey' => $this->client->sessionKey
        );
        $res = $this->api->sendRequest("getSessionKeyUser", $param);
        $res = json_decode($res, true);
        // print_r($res);
        // die;
        if($res['status']['errorCode'] != 0 && $res['status']['recordsInResponse'] < 1){
            // return $this->client->sessionKey;
            return $this->verifyUser();
        }

    }

    public function verifySessionByKey($key){
        info("verify session by key called");
        $param = array(
            'sessionKey' => $key
        );
        $res = $this->api->sendRequest("getSessionKeyUser", $param);
        // $this->sendRequest("getSessionKeyUser", $param, 0, 1, 1);
        $res = json_decode($res, true);
        // info($res);
        if($res['status']['errorCode'] != 0 && $res['status']['recordsInResponse'] < 1){
            return $this->verifyUser();
        }
        return $key;
    }


}
