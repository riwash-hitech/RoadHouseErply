<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempCat extends Model
{
    use HasFactory;

    // protected $connection = "mysql2";
    protected $table = 'category_csv';
    protected $fillable = [];
    protected $guarded = [];
    
    public $timestamps = false;
}