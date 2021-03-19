<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class postings extends Model
{
    protected $fillable = [
        'title', 'author', 'description', 'url','image_name', 'url_to_image','tag','category','count_hit'
    ];
}
