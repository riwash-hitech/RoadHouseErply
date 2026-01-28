<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public static function disperror($error)

  {

    return response()->json([

      'responseCode' => app('Illuminate\Http\Response')->status(),

      'success' => false,

      'message' => $error,

      'error' => $error,

    ]);

  }




  public function validationError($error){

    foreach ($error as $key => $value) {

      foreach ($value as $key1 => $value1) {
        
          $arr[] = $value1; 

      }

    }

    return $this->disperror($arr);

  }
}
