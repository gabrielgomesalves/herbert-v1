<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharactersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('gender', 1);
            $table->integer('serie_id')->unsigned();
			//$table->integer('assignment_id')->unsigned();
			//$table->integer('script_id')->unsigned();
			/*
			$table->foreign('serie_id')->references('id')->on('series')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('assignment_id')->references('id')->on('assignments')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('script_id')->references('id')->on('scripts')->onUpdate('CASCADE')->onDelete('CASCADE');
			*/
			$table->timestamps();
            $table->unique(['name','gender','serie_id'], 'name_gender_serieid');
        });
		
		Schema::table('characters', function(Blueprint $table)
        {
            //DB::statement('ALTER TABLE assignments ADD PRIMARY KEY (id_character, id_actor)');
			$table->foreign('serie_id')->references('id')->on('series')->onUpdate('CASCADE')->onDelete('CASCADE');
			//$table->foreign('assignment_id')->references('id')->on('assignments')->onUpdate('CASCADE')->onDelete('CASCADE');
			//$table->foreign('script_id')->references('id')->on('scripts')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('characters');
    }
}
