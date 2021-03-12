<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class postings extends Model
{
    protected $fillable = [
        'title', 'author', 'description', 'url','imageName', 'urlToImage','tag','category','countHit'
    ];
}
