<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
	protected $table = 'series';
	
	//HAS MANY
	
	public function episodes()
    {
        return $this->hasMany('App\Episode');
    }
	
	public function characters()
    {
        return $this->hasMany('App\Character');
    }
	
	public function corrections()
    {
        return $this->hasMany('App\Correction');
    }
	
	public function glossaries()
    {
        return $this->hasMany('App\Glossary');
    }
	
	public function assignments()
    {
        return $this->hasMany('App\Assignment');
    }
	
	//BELONGS TO


}
