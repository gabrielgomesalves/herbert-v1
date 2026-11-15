<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $table = 'songs';
	
	//BELONGS TO
	
	public function episode()
    {
        return $this->belongsTo('App\Episode');
    }
	
}
