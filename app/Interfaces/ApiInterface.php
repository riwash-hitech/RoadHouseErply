<?php
namespace App\Interfaces;

interface ApiInterface{
    
    public function verifyUser();
    public function verifySession();
    public function verifySessionByKey($key);
    public function sendRequest($url, $param, $isBulk = 0, $errorFlag = 0);
    
    
}