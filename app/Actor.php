<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    protected $table = 'actors';
	
	public function assignment()
    {
        return $this->belongsTo('App\Assignment');
    }
	
	public function corrections()
    {
        return $this->hasMany('App\Correction');
    }
	
}
