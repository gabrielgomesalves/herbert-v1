<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlossariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('glossaries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('word');
            $table->string('pronounce');
			$table->integer('serie_id')->unsigned();
            //$table->integer('glossaryable_id');
            //$table->string('glossaryable_type');
			/*
			$table->foreign('serie_id')->references('id')->on('series')->onUpdate('CASCADE')->onDelete('CASCADE');
			*/
            $table->timestamps();
        });
		
		Schema::table('glossaries', function(Blueprint $table)
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
        Schema::drop('glossaries');
    }
}
