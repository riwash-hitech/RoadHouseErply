<?php
namespace App\Http\Controllers\Services;

use App\Http\Controllers\EAPI;
use App\Models\Client;
use Illuminate\Support\Facades\Log;

class SessionService
{
    protected $session;
    protected $erply;
    protected $client;
    public function __construct(EAPIService $api)
    {

        // $this->client = $c->findOrfail(1);
        $this->erply = $api;
        // $this->setModel();
    }
    protected function setModel(){
        // $client = new Client();
        // $this->client = $client->findOrfail(1);
        // $this->erply = new EAPIService();
    }
    public function verifyUser(){
        return $this->erply->verifyUser();
        // $this->client = Client::find(1);
        // Log::info("Verify Function Trigger");
        // $param = array(
        //     "username" => $this->client->username,
        //     "password" => $this->client->password,
        //     "sessionLength" => "86400"
        // );
        // $res = $this->erply->sendRequest("verifyUser", $param);
        // Log::info("response from erply".$res); 

        // if($res['status']['errorCode'] != 0){
        //     return response()->json(['stauts' => 'Error '. $res['status']['errorCode']]);
        // } 
        // $this->client->sessionKey = $res['records'][0]['sessionKey'];
        
        // $this->client->save();
        // Log::info("session key updated");
        // return $res['records'][0]['sessionKey'];
         
      
    }

    public function verifySession(){
        $this->erply->verifySession();
        // $this->client = Client::find(1);
        // if($this->client->sessionKey == ''){ 
        //     $this->verifyUser(); 
        // } 
        // $param = array(
        //     'sessionKey' => $this->client->sessionKey 
        // );
        // $res = $this->erply->sendRequest("getSessionKeyUser", $param, 0, 1);
         
        // if($res['status']['errorCode'] != 0 && $res['status']['recordsInResponse'] < 1){
        //     // return $this->client->sessionKey;
        //     $this->verifyUser();
        // }

        // $this->verifyUser();
           
    }

    public function verifySessionByKey($key){
        return $this->erply->verifySessionByKey($key); 
        // $param = array(
        //     'sessionKey' => $key 
        // );
        // $res = $this->erply->sendRequest("getSessionKeyUser", $param, 0, 1);
         
        // if($res['status']['errorCode'] != 0 && $res['status']['recordsInResponse'] < 1){
        //     return $this->verifyUser(); 
        // }  
        // return $key;   
    }

     


}