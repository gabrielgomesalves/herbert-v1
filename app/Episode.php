<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    protected $table = 'episodes';
	
	//HAS MANY
	
	public function scripts()
    {
        return $this->hasMany('App\Script');
    }
	
	public function corrections()
    {
        return $this->hasMany('App\Correction');
    }
	
	public function songs()
    {
        return $this->hasMany('App\Song');
    }
	
	//BELONGS TO
	
	public function serie()
    {
        return $this->belongsTo('App\Serie');
    }
	
}
