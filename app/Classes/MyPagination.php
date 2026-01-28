<?php
namespace App\Classes;

use Illuminate\Pagination\LengthAwarePaginator;

class MyPagination{
    // protected $req;
    // protected $data;
    

    // public function setData($req, $data){
    //     $this->req = $req;
    //     $this->data = $data;
    // }

   public function getPagination($req, $data){
        $rOnPage = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        $page = $req->page == '' ? 1 : $req->page; // Get the current page or default to 1, this is what you miss!
        $perPage = $rOnPage;
        $offset = ($page * $perPage) - $perPage;
        return new LengthAwarePaginator(array_slice($data, $offset, $perPage, false), count($data), $perPage, $page, ['path' => $req->url(), 'query' => $req->query()]);
    }
}