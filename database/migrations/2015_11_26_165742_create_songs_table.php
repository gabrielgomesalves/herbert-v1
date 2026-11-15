<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSongsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->increments('id');
            $table->time('timecode');
            $table->string('name');
            $table->mediumText('lyrics');
            $table->boolean('current_state')->default(false);
			$table->integer('episode_id')->unsigned();
            //$table->integer('songable_id');
            //$table->string('songable_type');
            $table->timestamps();
        });
		
		Schema::table('songs', function(Blueprint $table)
        {
            //DB::statement('ALTER TABLE assignments ADD PRIMARY KEY (id_character, id_actor)');
			$table->foreign('episode_id')->references('id')->on('episodes')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('songs');
    }
}
