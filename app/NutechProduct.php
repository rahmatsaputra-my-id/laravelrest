<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NutechProduct extends Model
{
   protected $fillable = [
      'product_name', 'purchase_price', 'selling_price', 'stock', 'image_name', 'url_to_image'
   ];
}
