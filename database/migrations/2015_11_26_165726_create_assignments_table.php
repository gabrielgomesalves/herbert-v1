<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assignments', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('serie_id')->unsigned();
			$table->integer('actor_id')->unsigned();
			$table->integer('character_id')->unsigned();
			/*
			$table->foreign('serie_id')->references('id')->on('series')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('actor_id')->references('id')->on('actors')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('character_id')->references('id')->on('characters')->onUpdate('CASCADE')->onDelete('CASCADE');
			*/
			$table->timestamps();
			$table->unique(['serie_id','actor_id','character_id']);
		});
        
        Schema::table('assignments', function(Blueprint $table)
        {
            //DB::statement('ALTER TABLE assignments ADD PRIMARY KEY (id_character, id_actor)');
			$table->foreign('serie_id')->references('id')->on('series')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('actor_id')->references('id')->on('actors')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('character_id')->references('id')->on('characters')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('assignments');
    }
}
