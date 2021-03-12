<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Books extends Model
{
    protected $fillable = [
        'title', 'author', 'source', 'description', 'url','imageName', 'urlToImage'
    ];
}