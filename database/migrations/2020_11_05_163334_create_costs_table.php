<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('costs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->index();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->integer('expense_id')->unsigned()->index();
            $table->foreign('expense_id')->references('id')->on('expenses');
            $table->string('numero')->nullable();
            $table->string('anno')->nullable();
            $table->date('data');
            $table->date('data_scadenza');
            $table->date('data_ricezione')->nullable();
            $table->float('imponibile', 10,4)->default(0);
            $table->float('totale', 10,4)->default(0);
            $table->float('netto', 10,4)->default(0);
            $table->integer('iva')->default(0);
            $table->integer('rit_acc')->default(0);
            $table->string('rate')->nullable();
            $table->boolean('saldato')->default(0);
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
        Schema::dropIfExists('costs');
    }
}
