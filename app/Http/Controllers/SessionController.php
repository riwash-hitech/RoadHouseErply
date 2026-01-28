<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\EAPIService;
use App\Http\Controllers\Services\SessionService;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    //
    protected $session;
    public function __construct(SessionService $api)
    {
        $this->session = $api;
    }
    public function verifyUser(){
        return $this->session->verifyUser();
    }

    public function verifySession(){
         
        return $this->session->verifySession();
        // dd($this->session->verifySessionByKey("6dc0ab34f9611b914b067a61959d88fbf86488cc8c4d"));
    }
}
