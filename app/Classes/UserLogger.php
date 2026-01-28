<?php
namespace App\Classes;

use App\Models\PAEI\UserLog;
use Illuminate\Support\Facades\Auth;

class UserLogger{

    protected $log;

    public function __construct(UserLog $log){
        $this->log = $log;
    }

    public function setLog($pdata, $ndata, $title){
        $this->log->create(
            [
                "userID" => auth('sanctum')->user()->id,
                "previousData" => "$pdata",
                "newData" => "$ndata",
                "title" => $title,
                "url" => url()->current(),
            ]
        );
        // info("User Log Done");
    }

    public function setChronLog($pdata, $ndata, $title){
        $this->log->create(
            [
                "userID" => '0',
                "previousData" => "$pdata",
                "newData" => "$ndata",
                "title" => $title,
                "url" => url()->current(),
            ]
        );
        // info("User Log Done");
    }

    public function setLogin($id,$title){
        $this->log->create(
            [
                "userID" => $id,
                "previousData" => "",
                "newData" => "",
                "title" => $title,
                "url" => url()->current(),
            ]
        );
        // info("User Log Done");
    }
}