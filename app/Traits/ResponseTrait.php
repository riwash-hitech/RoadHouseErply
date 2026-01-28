<?php

namespace App\Traits; 

trait ResponseTrait{

    public function __construct(){
        
    }
 
    public  function successWithData($data){
        return response()->json(["status"=>200, "success"=>true, "records" => collect($data)]);
    }

    public  function successWithDataAndMessage($msg, $data){
        return response()->json(["status"=>200, "success"=>true, "message" => $msg, "records" => collect($data)]);
    }

    public  function successWithMessage($msg){
        return response()->json(["status"=>200, "success"=>true, "message"=>$msg]);
    }

    public  function failWithMessageAndData($msg,$data){
        return response()->json(["status"=>400, "success"=>false, "message"=>$msg, 'records' => $data]);
    }

    public  function failWithMessage($msg){
        return response()->json(["status"=>400, "success"=>false, "message"=>$msg]);
    }

    public  function successDelUser($msg, $flag){
        return response()->json(["status"=>200, "success"=>true, "logout" => $flag, "message"=>$msg]);
    }

    

}