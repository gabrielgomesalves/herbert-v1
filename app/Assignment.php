<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $table = 'assignments';
	
	public function actor()
    {
        return $this->belongsTo('App\Actor');
    }
	
	public function character()
    {
        return $this->belongsTo('App\Character');
    }
	
	public function serie()
    {
        return $this->belongsTo('App\Serie');
    }
	
}
