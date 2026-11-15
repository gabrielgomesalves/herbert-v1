<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    protected $table = 'characters';
	
	//HAS MANY
	
	public function scripts()
    {
        return $this->hasMany('App\Script');
    }
	
	public function assignments()
    {
        return $this->hasMany('App\Assignment');
    }
	
	//BELONGS TO
	
	public function serie()
    {
        return $this->belongsTo('App\Serie');
    }
}
