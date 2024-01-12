<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');

            $table->string('rag_soc');
            $table->string('address');
            $table->string('zip');
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->integer('city_id')->unsigned()->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->char('nation', 2)->default('IT');
            $table->char('lang', 2)->default('it');
            $table->boolean('private')->default(0);

            $table->string('pec')->nullable();
            $table->string('piva')->nullable();
            $table->string('cf')->nullable();
            $table->string('sdi')->nullable();
            $table->string('settore')->nullable();

            $table->boolean('supplier')->default(0);
            $table->boolean('partner')->default(0);
            $table->boolean('active')->default(1);

            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('email_ordini')->nullable();
            $table->string('email_fatture')->nullable();

            $table->integer('parent_id')->nullable();
            $table->integer('client_id')->unsigned();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->integer('exemption_id')->nullable();
            $table->integer('sector_id')->nullable();
            $table->string('pagamento',4)->nullable();
            $table->text('note')->nullable();
            $table->float('s1', 6,2)->default(0);
            $table->float('s2', 6,2)->default(0);
            $table->float('s3', 6,2)->default(0);
            $table->string('origin')->nullable();
            $table->integer('old_id')->nullable();

            $table->string('nickname')->nullable();
            $table->string('luogo_nascita')->nullable();
            $table->string('data_nascita')->nullable();
            $table->char('sesso', 1)->nullable();

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
        Schema::dropIfExists('companies');
    }
}
