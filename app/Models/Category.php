<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $connection = "mysql2";
    protected $table = 'newsystem_internet_category';
    protected $primaryKey = 'newSystemcategoryID'; 
    protected $fillable = [];
    protected $guarded = [];
    public $timestamps = false;
}
