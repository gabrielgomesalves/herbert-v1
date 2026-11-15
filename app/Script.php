<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Script extends Model
{
    protected $table = 'scripts';
	
	public function episode()
    {
        return $this->belongsTo('App\Episode');
    }
	
	public function character()
    {
        return $this->belongsTo('App\Character');
    }
	
}
