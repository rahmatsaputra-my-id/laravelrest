<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNutechProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nutech_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->integer('purchase_price');
            $table->integer('selling_price');
            $table->integer('stock');
            $table->text('image_name')->nullable();
            $table->text('url_to_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nutech_products');
    }
}
