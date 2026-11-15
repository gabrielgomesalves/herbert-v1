<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScriptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scripts', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('number')->unsigned();
            $table->time('timecode');
            $table->mediumText('speech');
            $table->boolean('current_state')->default(false);
			$table->integer('episode_id')->unsigned();
			$table->integer('character_id')->unsigned();
            //$table->integer('scriptable_id');
            //$table->string('scriptable_type');
			/*
			$table->foreign('episode_id')->references('id')->on('episodes')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('character_id')->references('id')->on('characters')->onUpdate('CASCADE')->onDelete('CASCADE');
			*/
            $table->timestamps();
        });
		
		Schema::table('scripts', function(Blueprint $table)
        {
            //DB::statement('ALTER TABLE assignments ADD PRIMARY KEY (id_character, id_actor)');
			$table->foreign('episode_id')->references('id')->on('episodes')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('character_id')->references('id')->on('characters')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
		
		DB::unprepared("CREATE FUNCTION wordcount(str TEXT) RETURNS INT DETERMINISTIC SQL SECURITY INVOKER NO SQL BEGIN DECLARE wordCnt, idx, maxIdx INT DEFAULT 0; DECLARE currChar, prevChar BOOL DEFAULT 0; SET maxIdx=char_length(str); WHILE idx < maxIdx DO SET currChar=SUBSTRING(str, idx, 1) RLIKE '[[:alnum:]]'; IF NOT prevChar AND currChar THEN SET wordCnt=wordCnt+1; END IF; SET prevChar=currChar; SET idx=idx+1; END WHILE; RETURN wordCnt; END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('scripts');
		DB::unprepared('DROP FUNCTION IF EXISTS wordcount');
    }
}
