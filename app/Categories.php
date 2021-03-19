<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Categories extends Model
{
    protected $fillable = [
        'category_name', 'category_count_hit', 'category_total',
    ];
}