<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->integer('istat')->unsigned();
            $table->text('comune')->nullable();
            $table->text('regione')->nullable();
            $table->text('sigla_provincia')->nullable();
            $table->string('provincia')->nullable();
            $table->string('cap',6)->nullable();
            $table->text('prefisso')->nullable();
            $table->text('cod_fisco')->nullable();
            $table->unsignedTinyInteger('italia');
            $table->float('superficie', 10, 4);
            $table->integer('numero_residenti')->unsigned();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
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
        Schema::dropIfExists('cities');
    }
};
