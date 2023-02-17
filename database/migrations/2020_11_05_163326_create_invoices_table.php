<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            //INTESTAZIONE
            $table->integer('company_id')->unsigned()->index();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->char('tipo_doc', 2)->default('Pr'); /* Pr o Pu => Privato o Pubblico */
            $table->char('tipo', 1)->default('F'); /* (F=fattura, R=Ricevuta, A=notadicredito, D=DDT, U=Autofattura) */
            $table->integer('numero');
            $table->integer('numero_registrazione');
            $table->date('data');
            $table->date('data_registrazione');

            //INTESTAZIONE DDT
            $table->string('ddt_n_doc', 50)->nullable();
            $table->date('ddt_data_doc')->nullable();

            //INTESTAZIONE PUBBLICA AMMINSTRAZIONE
            $table->string('pa_n_doc', 50)->nullable();
            $table->date('pa_data_doc')->nullable();
            $table->string('pa_cup', 50)->nullable();
            $table->string('pa_cig', 50)->nullable();

            $table->float('imponibile', 10,4)->default(0);
            $table->float('iva', 10,4)->default(0);//totale ivato
            $table->string('riferimento', 100)->nullable();
            $table->decimal('bollo')->nullable();
            $table->string('bollo_a')->nullable();

            // DETTAGLI
            $table->char('pagamento', 4)->nullable();/* BOVF Bonifico vista fattura; BO3P Bonifico 30%; BO5P Bonifico 50%; BOFM Bonifico fine mese*/
            $table->char('tipo_saldo', 1)->nullable(); /* C=Contanti, P=POS, A=Assegno, B=Bonifico */
            $table->date('data_saldo')->nullable();
            $table->date('data_scadenza')->nullable();
            $table->float('spese', 10,4)->default(0);
            $table->integer('perc_ritenuta')->nullable();
            $table->float('ritenuta', 10,4)->default(0);
            $table->char('saldato', 1)->nullable(); /* Y=yes | N=no | A=attesa */
            $table->text('rate')->nullable();  /* devono essere generate le date delle rate se il pagamente e' rateale, partendo da data_saldo */

            $table->smallInteger('sendable')->default(0);//vedi config.fe
            $table->smallInteger('status')->default(0);//vedi config.fe

            $table->string('old_id')->nullable();// string identifier id
            $table->string('fe_id')->nullable();// string identifier id

            $table->string('split_payment')->nullable();

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
        Schema::dropIfExists('invoices');
    }
}
