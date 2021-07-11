<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Books extends Model
{
    protected $fillable = [
        'title', 'author', 'source', 'description', 'image_name', 'url_to_image'
    ];
}