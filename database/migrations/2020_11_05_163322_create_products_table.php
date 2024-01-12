<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome')->nullable();
            $table->text('descrizione')->nullable();
            $table->string('codice', 50)->nullable();
            $table->float('prezzo', 10, 4)->nullable();
            $table->float('costo', 10, 4)->nullable();
            $table->string('periodo')->nullable();
            $table->text('children')->nullable();
            $table->float('perc_agente', 10, 4)->nullable();
            $table->string('name_en')->nullable();
            $table->text('desc_en')->nullable();
            $table->string('name_de')->nullable();
            $table->text('desc_de')->nullable();
            $table->unsignedTinyInteger('perc_iva')->nullable();
            $table->integer('exemption_id')->nullable();
            $table->foreign('exemption_id')->references('id')->on('exemptions');
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
        Schema::dropIfExists('products');
    }
}
