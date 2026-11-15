<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorrectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corrections', function (Blueprint $table) {
            $table->increments('id');
			$table->mediumText('correction');
			$table->boolean('current_state')->default(false);
			$table->integer('serie_id')->unsigned()->nullable();
			$table->integer('episode_id')->unsigned()->nullable();
			$table->time('timecode')->nullable();
			$table->integer('actor_id')->unsigned()->nullable();
            //$table->integer('correctionable_id');
            //$table->string('correctionable_type');
			/*
			$table->foreign('serie_id')->references('id')->on('series')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('episode_id')->references('id')->on('episodes')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('actor_id')->references('id')->on('actors')->onUpdate('CASCADE')->onDelete('CASCADE');
			*/
            $table->timestamps();
        });
		
		Schema::table('corrections', function(Blueprint $table)
        {
            //DB::statement('ALTER TABLE assignments ADD PRIMARY KEY (id_character, id_actor)');
			$table->foreign('serie_id')->references('id')->on('series')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('episode_id')->references('id')->on('episodes')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('actor_id')->references('id')->on('actors')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('corrections');
    }
}
