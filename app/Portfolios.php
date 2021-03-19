<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Portfolios extends Model
{
    protected $fillable = [
        'type', 'title', 'subtitle', 'url_to_image', 'notes'
    ];
}
