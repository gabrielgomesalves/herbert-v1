<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Glossary extends Model
{
    protected $table = 'glossaries';
	
	public function serie()
    {
        return $this->belongsTo('App\Serie');
    }
	
	
}
