<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Categories extends Model
{
    protected $fillable = [
        'category_name', 'category_countHit', 'category_total',
    ];
}