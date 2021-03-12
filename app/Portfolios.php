<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Portfolios extends Model
{
    protected $fillable = [
        'type', 'title', 'subtitle', 'urlToImage', 'notes'
    ];
}
