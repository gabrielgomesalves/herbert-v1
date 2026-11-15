<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEpisodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('original_name');
            $table->string('translated_name');
            $table->smallInteger('number')->unsigned();
            $table->boolean('current_state')->default(false);
			$table->integer('serie_id')->unsigned();
			/*
			$table->foreign('serie_id')->references('id')->on('series')->onUpdate('CASCADE')->onDelete('CASCADE');
			*/
            $table->timestamps();
            $table->unique(['original_name','translated_name','serie_id','number']);
        });
		
		Schema::table('episodes', function(Blueprint $table)
        {
            //DB::statement('ALTER TABLE assignments ADD PRIMARY KEY (id_character, id_actor)');
			$table->foreign('serie_id')->references('id')->on('series')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('episodes');
    }
}
