<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'ScriptController@index');

Route::get('script/view', [ 
	'as' => 'script.view', 
	'uses' => 'ScriptController@view']);	
	
Route::post('script/getScripts', [ 
	'as' => 'script.getScripts', 
	'uses' => 'ScriptController@getScripts']);
	
Route::post('script/getScriptsByEpisode', [ 
	'as' => 'script.getScriptsByEpisode', 
	'uses' => 'ScriptController@getScriptsByEpisode']);
	
Route::post('script/mark_script_done', [ 
	'as' => 'script.mark_script_done', 
	'uses' => 'ScriptController@mark_script_done']);	
	
Route::post('script/update_script_speech', [ 
	'as' => 'script.update_script_speech', 
	'uses' => 'ScriptController@update_script_speech']);
	
Route::post('script/get_script_full', [ 
	'as' => 'script.get_script_full', 
	'uses' => 'ScriptController@get_script_full']);

Route::get('script/index', [ 
	'as' => 'script.index', 
	'uses' => 'ScriptController@index']);
	
Route::post('script/getScriptById', [ 
	'as' => 'script.getScriptById', 
	'uses' => 'ScriptController@getScriptById']);
	
Route::get('script/insert', [ 
	'as' => 'script.insert', 
	'uses' => 'ScriptController@insert']);	
	
Route::post('episode/getEpisodes', [ 
	'as' => 'episode.getEpisodes', 
	'uses' => 'EpisodeController@getEpisodes']);

Route::post('episode/changeState', [ 
	'as' => 'episode.changeState', 
	'uses' => 'EpisodeController@changeState']);	
	
Route::post('episode/delete', [ 
	'as' => 'episode.delete', 
	'uses' => 'EpisodeController@delete']);		

Route::post('episode/printEpisode', [ 
	'as' => 'episode.printEpisode', 
	'uses' => 'EpisodeController@printEpisode']);
	
Route::post('correction/changeState', [ 
	'as' => 'correction.changeState', 
	'uses' => 'CorrectionController@changeState']);
	
Route::post('correction/getCorrectionsSeries', [ 
	'as' => 'correction.getCorrectionsSeries', 
	'uses' => 'CorrectionController@getCorrectionsSeries']);
	
Route::post('correction/getCorrectionsActors', [ 
	'as' => 'correction.getCorrectionsActors', 
	'uses' => 'CorrectionController@getCorrectionsActors']);
	
Route::post('correction/delete', [ 
	'as' => 'correction.delete', 
	'uses' => 'CorrectionController@delete']);	
	
Route::post('glossary/getGlossary', [ 
	'as' => 'glossary.getGlossary', 
	'uses' => 'GlossaryController@getGlossary']);

Route::post('glossary/delete', [ 
	'as' => 'glossary.delete', 
	'uses' => 'GlossaryController@delete']);	
	
Route::post('assignment/listing', [ 
	'as' => 'assignment.listing', 
	'uses' => 'AssignmentController@listing']);
	
Route::get('assignment/automaton', [ 
	'as' => 'assignment.automaton', 
	'uses' => 'AssignmentController@automaton']);
	
Route::post('assignment/automaton_insert', [ 
	'as' => 'assignment.automaton_insert', 
	'uses' => 'AssignmentController@automaton_insert']);
	
Route::post('character/detect_conflicts', [ 
	'as' => 'character.detect_conflicts', 
	'uses' => 'CharacterController@detect_conflicts']);	
	
Route::post('character/change_character', [ 
	'as' => 'character.change_character', 
	'uses' => 'CharacterController@change_character']);
	
Route::post('character/getCharacters', [ 
	'as' => 'character.getCharacters', 
	'uses' => 'CharacterController@getCharacters']);	
	
Route::post('serie/changeState', [ 
	'as' => 'serie.changeState', 
	'uses' => 'SerieController@changeState']);		
	
Route::post('serie/getSeries', [ 
	'as' => 'serie.getSeries', 
	'uses' => 'SerieController@getSeries']);
	
Route::post('serie/delete', [ 
	'as' => 'serie.delete', 
	'uses' => 'SerieController@delete']);
	
Route::post('serie/create_statistics', [ 
	'as' => 'serie.create_statistics', 
	'uses' => 'SerieController@create_statistics']);	
	
Route::post('serie/create_scripts', [ 
	'as' => 'serie.create_scripts', 
	'uses' => 'SerieController@create_scripts']);	
	
Route::post('actor/getActors', [ 
	'as' => 'actor.getActors', 
	'uses' => 'ActorController@getActors']);

Route::post('actor/printHistory', [ 
	'as' => 'actor.printHistory', 
	'uses' => 'ActorController@printHistory']);
	
Route::post('actor/delete', [ 
	'as' => 'actor.delete', 
	'uses' => 'ActorController@delete']);
	
Route::post('song/songsExists', [ 
	'as' => 'song.songsExists', 
	'uses' => 'SongController@songsExists']);	
	
Route::post('song/getSongs', [ 
	'as' => 'song.getSongs', 
	'uses' => 'SongController@getSongs']);
	
Route::resource('actor', 'ActorController');
Route::resource('assignment', 'AssignmentController');
Route::resource('script', 'ScriptController');
Route::resource('character', 'CharacterController');
Route::resource('correction', 'CorrectionController');
Route::resource('episode', 'EpisodeController');
Route::resource('glossary', 'GlossaryController');
Route::resource('serie', 'SerieController');
Route::resource('song', 'SongController');
