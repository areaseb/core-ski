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
        Schema::create('cost_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('cost_id')->unsigned()->index();
            $table->foreign('cost_id')->references('id')->on('costs');
            $table->float('amount', 10, 4)->nullable();
            $table->date('date');
            $table->string('payment_type')->nullable();
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
        Schema::dropIfExists('cost_payments');
    }
};
