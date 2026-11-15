<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Redirect;
use DB;
use PDF;

use App\Actor;
use App\Script;

class ActorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {	
        $actors = Actor::orderBy('name', 'asc')->get();
		return view('actor.index', ['actors' => $actors]);
    }
	
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$actor = new Actor;
		$actor->name = $request->input('name');
		$actor->gender = $request->input('gender');
		$actor->email = $request->input('email');
		$actor->phone = $request->input('phone');
		$actor->note = $request->input('note');
		$actor->save();
		$actors = Actor::orderBy('name', 'asc')->get();
		return Redirect::back()->with('actors', $actors);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }
	
	public function getActors(Request $request)
	{
		$serie_id = $request->input('serie_id');
		$episode_id = $request->input('episode_id');
		$character_id = Script::where('episode_id', $episode_id)->distinct()->lists('character_id');
		$actors = DB::table('actors')
					->join('assignments', 'actors.id', '=', 'assignments.actor_id')
					->join('scripts', 'assignments.character_id', '=', 'scripts.character_id')
					->where('assignments.serie_id', '=', $serie_id)
					->where('scripts.episode_id', '=', $episode_id)
					->whereIn('assignments.character_id', $character_id)
					->select(DB::raw('actors.id AS actor_id, actors.name AS actor_name, COUNT(actors.id) AS speech_count'))
					->orderBy('actors.name', 'asc')
					->groupBy('actors.id')
					->get();
		return json_encode($actors);
	}

	public function printHistory(Request $request)
	{
		$actor_id = $request->input('actor_id');
		$actor = Actor::find($actor_id);
		$history = DB::select( DB::raw("SELECT series.original_name AS series_original_name, episodes.number AS episodes_number, characters.name AS characters_name, scripts.id AS scripts_id, COUNT(scripts.id) AS count_scripts_id, characters.gender AS characters_gender, actors.name AS actor_name FROM series, episodes, characters, scripts, assignments, actors WHERE actors.id = assignments.actor_id AND assignments.character_id = characters.id AND assignments.serie_id = series.id AND series.id = episodes.serie_id AND scripts.episode_id = episodes.id AND scripts.character_id = characters.id AND actors.id = '$actor_id' AND characters.gender <> 'T' GROUP BY episodes.number ASC, characters.name ASC ORDER BY series.original_name ASC, episodes.number ASC") );
		return PDF::loadView('pdf.actor', ['history' => $history, 'actor' => $actor])->setPaper('a4', 'portrait')->setWarnings(false)->stream();
	}
	
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
		
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $actor = Actor::find($id);
		$actor->name = $request->input('name');
		$actor->gender = $request->input('gender');
		$actor->email = $request->input('email');
		$actor->phone = $request->input('phone');
		$actor->note = $request->input('note');
		$actor->save();
		$actors = Actor::orderBy('name', 'asc')->get();
		return Redirect::back()->with('actors', $actors);
    }
	
	public function delete(Request $request)
    {
		DB::beginTransaction();
		try {
			$actor_id = $request->input('actor_id');
			$actor = Actor::find($actor_id);
			$actor->delete();
			DB::commit();
		}
		catch(\Exception $ex)
		{
			DB::rollback();
			return response()->json(['error_msg' => $ex->getMessage()], 400);
		}
		return response()->json('success', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }
}
