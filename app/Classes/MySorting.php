<?php
namespace App\Classes;

use Illuminate\Pagination\LengthAwarePaginator;

class MySorting{
     

   public function letsSort($data, $request){

        $direction = $request->direction;
        $sort_by = $request->sort_by;

        info($direction.' '. $sort_by);

        return $res = collect($data)->sortBy($sort_by, $direction== 'desc' ? true : false)->toArray();
    }
}