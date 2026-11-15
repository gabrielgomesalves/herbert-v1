<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Correction extends Model
{
    protected $table = 'corrections';
	
	public function serie()
    {
        return $this->belongsTo('App\Serie');
    }
	
	public function episode()
    {
        return $this->belongsTo('App\Episode');
    }
	
	public function actor()
    {
        return $this->belongsTo('App\Actor');
    }
}
