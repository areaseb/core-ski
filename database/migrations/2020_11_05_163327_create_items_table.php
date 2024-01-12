<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned()->index();
            $table->foreign('product_id')->references('id')->on('products');
            $table->text('descrizione')->nullable();
            $table->decimal('qta', 7, 2)->nullable();
            $table->float('importo', 10, 4)->nullable();
            $table->float('sconto', 7, 4)->default(0);
            $table->string('perc_iva')->nullable();
            $table->float('iva', 9, 4)->nullable();
            $table->integer('invoice_id')->unsigned()->index();
            $table->foreign('invoice_id')->references('id')->on('invoices');
            $table->integer('exemption_id')->unsigned()->nullable();
            $table->integer('ora_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
