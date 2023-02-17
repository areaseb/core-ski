<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('calendar_id')->unsigned()->index();
            $table->foreign('calendar_id')->references('id')->on('calendars');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->boolean('allday');
            $table->string('location')->nullable();
            $table->integer('parent_event_id')->nullable();
            $table->integer('eventable_id')->nullable();
            $table->string('eventable_type')->nullable();
            $table->string('backgroundColor')->default('#3788d8');
            $table->boolean('done')->default(0);
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
        Schema::dropIfExists('events');
    }
}
