<?php
namespace App\Http\Controllers\Response;

class CustomeResponse{


    public static function successWithData($data){
        return response()->json(["status"=>200, "success"=>true, "records" => collect($data)]);
    }

    public static function successWithMessage($msg){
        return response()->json(["status"=>200, "success"=>true, "message"=>$msg]);
    }

    public static function failWithMessageAndData($msg,$data){
        return response()->json(["status"=>400, "success"=>false, "message"=>$msg, 'records' => $data]);
    }


}