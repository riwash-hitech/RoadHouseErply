<?php
namespace App\Http\Controllers\Services;

use App\Http\Controllers\EAPI;
use App\Http\Controllers\Response\CustomeResponse;
use App\Models\Client;
use App\Models\PAEI\ErplyRequest;
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
        
        // if (session_status() === PHP_SESSION_NONE) {
        //     session_start();
        // }
        $this->api = $api;
        $this->client = $client;
       
        // $this->verifySession();
    }


    public function sendRequest($url, $param, $isBulk = 0, $errorFlag = 0, $sessionKeyFlag = 1)
    {

        $param["sessionKey"] = $this->client->sessionKey;
        if($isBulk == 0)$param["clientCode"] = $this->client->clientCode;

        // print_r($param);
        // die;
        $unZipUrl = json_decode($url, true);

        //recording erply request
        $er = ErplyRequest::create(
            [
                "requestName" => $isBulk == 1 ? "Bulk ".$unZipUrl[0]["requestName"]  : $url,
                "dateTime" => date('Y-m-d H:i:s'),
                "clientCode" => $this->client->clientCode
            ]
        );

        $result = $this->api->sendRequest($url, $param, $isBulk);

        
        // dd($result);
        $products = json_decode($result, true);
        // if($errorFlag == 0){
            $er->responseStatus = $products['status']["errorCode"] == 0 ? "Success" : $products['status']["errorCode"];
            $er->save();
            
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

            if($products['status']['errorCode'] == 1002){
                info("Maximum API Request Quota Exceeded.");
                die;
            }
        // }
        return $products;
    }

    public function callServicePoint($sessionKey){
        $param = array(
            "clientCode" => $this->client->clientCode,
            "sessionKey" => $sessionKey,
            // "sessionLength" => "86400"
        );
        $res = $this->api->sendRequest("getServiceEndpoints", $param);
        info($res);
    }

    public function sendRequestBySwagger($url,$param){
         
        // $this->callServicePoint();
        $data = Http::withHeaders([
                'sessionKey' => $this->client->sessionKey,
                'clientCode' => $this->client->clientCode,
                 ])->get($url,
                  $param
                  
                );
        $data = $data->json();
        return $data;
    }

    public function sendRequestByCDNApi($url,$param){
         
        // $this->callServicePoint();
        $data = Http::withHeaders([
                'JWT' => $this->client->jwt
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
    
    public function sendRequestByCDNApiPost($url,$param){
         
        // $this->callServicePoint();
        $data = Http::withHeaders([
                'JWT' => $this->client->jwt
                 ])->post($url,
                  $param
                  
                );
        $data = $data->json();
        return $data;
    }

    public function sendRequestByCDNApiPostWithClientCode($url,$param){
         
        // $this->callServicePoint();
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $param,
          CURLOPT_HTTPHEADER => array(
            'clientCode: '.$this->client->clientCode,
            'sessionKey: '.$this->client->sessionKey,
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response,true);
    }

    public function sendRequestByCDNApiPutWithClientCode($url,$param){
         
        // $this->callServicePoint();
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'PUT',
          CURLOPT_POSTFIELDS => $param,
          CURLOPT_HTTPHEADER => array(
            'clientCode: '.$this->client->clientCode,
            'sessionKey: '.$this->client->sessionKey,
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response,true);
    }

    public function sendRequestBySwaggerWithoutData($url){ 
        
        $data = Http::timeout(-1)->withHeaders([
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

        //problem verify user calling by multiple function
        //solution using TSL
        //once session is expired then verifyUser function will called
        //whichever cron call this function  first time 
        //set db flag verifyUser to 1  and if 1 other cron request will die
        info("Session Expired and Called Verify User");
        $READ = Client::where("clientCode", $this->client->clientCode)->first();
        if($READ->verifyUser == 1){
            info("TSL Flag 1 and Request Die.");
            //cron already set this function so no need to request for verifyUser
            die;
        }
        Client::where("clientCode", $this->client->clientCode)->update(["verifyUser" => 1]);
        info("Verify User Flag Set to 1");

        Log::info("VerifyUser Function called");
        $param = array(
            "username" => $this->client->username,
            "password" => $this->client->password,
            "sessionLength" => "86400"
        );

        $er = ErplyRequest::create(
            [
                "requestName" => "verifyUser",
                "dateTime" => date('Y-m-d H:i:s'),
                "clientCode" => $this->client->clientCode
            ]
        );
        $res = $this->api->sendRequest("verifyUser", $param);
        $res = json_decode($res, true);


        $er->responseStatus = $res['status']["errorCode"] == 0 ? "Success" : $res['status']["errorCode"];
        $er->save();

        
        info("VerifyUser Response received");
        if($res['status']['errorCode'] != 0){
            info("erro while verifying user".$res['status']['errorCode']);
            return response()->json(['stauts' => 'Error '. $res['status']['errorCode']]);
        }
        Client::where("clientCode", $this->client->clientCode)->update(
            [
                "sessionKey" => $res['records'][0]['sessionKey'],
                "jwt" => $res['records'][0]['identityToken'],
                "verifyUser" => 0
            ]
        );
         
        Log::info("session key updated");
        $this->callServicePoint($res['records'][0]['sessionKey']);
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
