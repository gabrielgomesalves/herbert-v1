<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Redirect;
use DB;
use Response;
use Carbon\Carbon;

use App\Correction;
use App\Actor;
use App\Serie;

class CorrectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		$series = Serie::orderBy('original_name', 'asc')->where('current_state', false)->get();
		$actors = Actor::orderBy('name', 'asc')->get();
		$current_state = $request->input('corrections_current_state');
		if(!isset($current_state)){
			$current_state = false;
		}
		$corrections = DB::table('corrections')
						->leftjoin('series', 'corrections.serie_id', '=', 'series.id')
						->leftjoin('episodes', 'corrections.episode_id', '=', 'episodes.id')
						->leftjoin('actors', 'corrections.actor_id', '=', 'actors.id')
						->select(DB::raw('corrections.id AS correction_id, series.id AS serie_id, series.original_name AS serie_original_name, episodes.id AS episode_id, episodes.number AS episode_number, actors.id AS actor_id, actors.name AS actor_name, corrections.timecode AS correction_timecode, corrections.correction AS correction_correction, corrections.current_state AS correction_current_state'))
						->where('corrections.current_state', '=', $current_state)
						->orderBy('serie_original_name', 'asc')
						->orderBy('episode_number', 'asc')
						->orderBy('actor_name', 'asc')
						->get();
        return view('correction.index', ['series' => $series, 'actors' => $actors, 'corrections' => $corrections, 'current_state' => $current_state]);
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
        $serie_id = $request->input('series_options');
		$episode_id = $request->input('episodes_options');
		$actor_id = $request->input('actor');
		$correction_text = $request->input('correction_text');
		$timecode = $request->input('timecode');
		$correction = new Correction();
		if(isset($serie_id) && $serie_id != -1){
			$correction->serie_id = $serie_id;
		} else {
			return;
		}
		$correction->episode_id = (isset($episode_id) && $episode_id != -1 ? $episode_id : null);
		$correction->actor_id = (isset($actor_id) && $actor_id != -1 ? $actor_id : null);
		$correction->timecode = $this->CorrectTime($timecode);
		$correction->correction = $correction_text;
		$correction->save();
		$current_state = false;
		$corrections = DB::table('corrections')
						->leftjoin('series', 'corrections.serie_id', '=', 'series.id')
						->leftjoin('episodes', 'corrections.episode_id', '=', 'episodes.id')
						->leftjoin('actors', 'corrections.actor_id', '=', 'actors.id')
						->select(DB::raw('corrections.id AS correction_id, series.id AS serie_id, series.original_name AS serie_original_name, episodes.id AS episode_id, episodes.number AS episode_number, actors.id AS actor_id, actors.name AS actor_name, corrections.timecode AS correction_timecode, corrections.correction AS correction_correction, corrections.current_state AS correction_current_state'))
						->where('corrections.current_state', '=', $current_state)
						->orderBy('serie_original_name', 'asc')
						->orderBy('episode_number', 'asc')
						->orderBy('actor_name', 'asc')
						->get();
		$series = Serie::orderBy('original_name', 'asc')->where('current_state', false)->get();
		$actors = Actor::orderBy('name', 'asc')->get();
		return view('correction.index', ['series' => $series, 'actors' => $actors, 'corrections' => $corrections, 'current_state' => $current_state]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
		$serie_id = $request->input('series_edit_options');
		$episode_id = $request->input('episodes_edit_options');
		$actor_id = $request->input('actor');
		$correction_text = $request->input('correction_text');
		$timecode = $request->input('timecode');
        $correction = Correction::find($id);
		if(isset($serie_id) && $serie_id != -1){
			$correction->serie_id = $serie_id;
		} else {
			return;
		}
		$correction->episode_id = (isset($episode_id) && $episode_id != -1 ? $episode_id : null);
		$correction->actor_id = (isset($actor_id) && $actor_id != -1 ? $actor_id : null);
		$correction->timecode = $this->CorrectTime($timecode);
		$correction->correction = $correction_text;
		$correction->save();
		$current_state = false;
		$corrections = DB::table('corrections')
						->leftjoin('series', 'corrections.serie_id', '=', 'series.id')
						->leftjoin('episodes', 'corrections.episode_id', '=', 'episodes.id')
						->leftjoin('actors', 'corrections.actor_id', '=', 'actors.id')
						->select(DB::raw('corrections.id AS correction_id, series.id AS serie_id, series.original_name AS serie_original_name, episodes.id AS episode_id, episodes.number AS episode_number, actors.id AS actor_id, actors.name AS actor_name, corrections.timecode AS correction_timecode, corrections.correction AS correction_correction, corrections.current_state AS correction_current_state'))
						->where('corrections.current_state', '=', $current_state)
						->orderBy('serie_original_name', 'asc')
						->orderBy('episode_number', 'asc')
						->orderBy('actor_name', 'asc')
						->get();
		$series = Serie::orderBy('original_name', 'asc')->where('current_state', false)->get();
		$actors = Actor::orderBy('name', 'asc')->get();
		return Redirect::back()->with(['series' => $series, 'actors' => $actors, 'corrections' => $corrections, 'current_state' => $current_state]);
    }
	
	public function CorrectTime($timeToCorrect)
	{
		switch(strlen($timeToCorrect)){
			case 8:
				$errorDividers = array(",", ";", ".", "_", "-");
				$timeCorrected = str_replace($errorDividers, ":", $timeToCorrect);
				$p1 = '/^(0?\d|1\d|2[0-3]):[0-5]\d:[0-5]\d$/';
				if(preg_match($p1, $timeCorrected)){
					$carbon = new Carbon($timeCorrected);
					return $carbon->toTimeString();
				} else {
					$carbon = new Carbon('00:00:00');
					return $carbon->toTimeString();
				}
				break;
			case 6:
				if(ctype_digit($timeToCorrect)){
					$build_timecode = $timeToCorrect[0] . $timeToCorrect[1] . ":" . $timeToCorrect[2] . $timeToCorrect[3] . ":" . $timeToCorrect[4] . $timeToCorrect[5];
					$carbon = new Carbon($build_timecode);
					return $carbon->toTimeString();
				} else {
					$carbon = new Carbon('00:00:00');
					return $carbon->toTimeString();
				}
				break;
			case 5:
				$errorDividers = array(",", ";", ".", "_", "-");
				$timeCorrected = str_replace($errorDividers, ":", $timeToCorrect);
				$p1 = '/^(0?\d|1\d|2[0-3]):[0-5]\d:[0-5]\d$/';
				if(preg_match($p1, $timeCorrected)){
					$carbon = new Carbon($timeCorrected);
					return $carbon->toTimeString();
				} else {
					$carbon = new Carbon('00:00:00');
					return $carbon->toTimeString();
				}
				break;
			case 4:
				if(ctype_digit($timeToCorrect)){
					$build_timecode = "00:" . $timeToCorrect[0] . $timeToCorrect[1] . ":" . $timeToCorrect[2] . $timeToCorrect[3];
					$carbon = new Carbon($build_timecode);
					return $carbon->toTimeString();
				} else {
					$carbon = new Carbon('00:00:00');
					return $carbon->toTimeString();
				}
				break;
			default:
				$errorDividers = array(",", ";", ".", "_", "-");
				$timeCorrected = str_replace($errorDividers, ":", $timeToCorrect);
				$p1 = '/^(0?\d|1\d|2[0-3]):[0-5]\d:[0-5]\d$/';
				if(preg_match($p1, $timeCorrected)){
					$carbon = new Carbon($timeCorrected);
					return $carbon->toTimeString();
				} else {
					$carbon = new Carbon('00:00:00');
					return $carbon->toTimeString();
				}
				break;
		}
		/*
		$errorDividers = array(",", ";", ".", "_", "-");
		$timeCorrected = str_replace($errorDividers, ":", $timeToCorrect);
		$p1 = '/^(0?\d|1\d|2[0-3]):[0-5]\d:[0-5]\d$/';
		if(preg_match($p1, $timeCorrected)){
			$carbon = new Carbon($timeCorrected);
			return $carbon->toTimeString();
		} else {
			$carbon = new Carbon('00:00:00');
			return $carbon->toTimeString();
		}
		*/
	}
	
	public function changeState(Request $request)
	{
		$correction_id = $request->input('correction_id');
		$current_state = $request->input('current_state');
		$correction = Correction::find($correction_id);
		$correction->current_state = $current_state;
		$correction->save();
		return response()->json("success");	
	}

	public function getCorrectionsSeries(Request $request)
	{
		$serie_id = $request->input('serie_id');
		$current_state = $request->input('current_state');
		if(isset($current_state)){
			$corrections = DB::table('corrections')
						->leftjoin('series', 'corrections.serie_id', '=', 'series.id')
						->leftjoin('episodes', 'corrections.episode_id', '=', 'episodes.id')
						->leftjoin('actors', 'corrections.actor_id', '=', 'actors.id')
						->select(DB::raw('corrections.id AS correction_id, series.id AS serie_id, series.original_name AS serie_original_name, episodes.id AS episode_id, episodes.number AS episode_number, actors.id AS actor_id, actors.name AS actor_name, corrections.timecode AS correction_timecode, corrections.correction AS correction_correction, corrections.current_state AS correction_current_state'))
						->where('corrections.serie_id', '=', $serie_id)
						->where('corrections.current_state', '=', $current_state)
						->orderBy('serie_original_name', 'asc')
						->orderBy('episode_number', 'asc')
						->orderBy('actor_name', 'asc')
						->get();
		} else {
			$corrections = DB::table('corrections')
						->leftjoin('series', 'corrections.serie_id', '=', 'series.id')
						->leftjoin('episodes', 'corrections.episode_id', '=', 'episodes.id')
						->leftjoin('actors', 'corrections.actor_id', '=', 'actors.id')
						->select(DB::raw('corrections.id AS correction_id, series.id AS serie_id, series.original_name AS serie_original_name, episodes.id AS episode_id, episodes.number AS episode_number, actors.id AS actor_id, actors.name AS actor_name, corrections.timecode AS correction_timecode, corrections.correction AS correction_correction, corrections.current_state AS correction_current_state'))
						->where('corrections.serie_id', '=', $serie_id)
						->orderBy('serie_original_name', 'asc')
						->orderBy('episode_number', 'asc')
						->orderBy('actor_name', 'asc')
						->get();
		}
		$correctionsTable = "";
		if(count($corrections) > 0){
			$correctionsTable = "<thead> <tr> <th style='text-align:center; vertical-align:middle;'>Nº Episódio</th> <th style='text-align:center; vertical-align:middle;'>Actor</th> <th style='text-align:center; vertical-align:middle;'>Timecode</th> <th style='text-align:center; vertical-align:middle;'>Correcção</th> </tr> </thead> <tbody>";
			foreach($corrections as $correction){
				$correctionsTable = $correctionsTable . "<tr> <td style='text-align:center; vertical-align:middle;'>". ((is_null($correction->episode_number) || !isset($correction->episode_number)) ? "Não definido" : $correction->episode_number) ."</td> <td style='text-align:center; vertical-align:middle;'>". ((is_null($correction->actor_name) || !isset($correction->actor_name)) ? "Não definido" : $correction->actor_name) ."</td> <td style='text-align:center; vertical-align:middle;'>". $correction->correction_timecode ."</td> <td style='text-align:center; vertical-align:middle;'>". $correction->correction_correction ."</td> </tr>";
			}
			$correctionsTable = $correctionsTable . "</tbody>";
		}
		
		return response()->json(['correctionsTable' => $correctionsTable]);
	}
	
	public function getCorrectionsActors(Request $request)
	{
		$actors_selected = $request->input('actors_selected');
		$current_state = $request->input('current_state');
		if(isset($current_state)){
			$corrections = DB::table('corrections')
						->leftjoin('series', 'corrections.serie_id', '=', 'series.id')
						->leftjoin('episodes', 'corrections.episode_id', '=', 'episodes.id')
						->leftjoin('actors', 'corrections.actor_id', '=', 'actors.id')
						->select(DB::raw('corrections.id AS correction_id, series.id AS serie_id, series.original_name AS serie_original_name, episodes.id AS episode_id, episodes.number AS episode_number, actors.id AS actor_id, actors.name AS actor_name, corrections.timecode AS correction_timecode, corrections.correction AS correction_correction, corrections.current_state AS correction_current_state'))		
						->whereIn('corrections.actor_id', $actors_selected)
						->where('corrections.current_state', '=', $current_state)
						->orWhere('corrections.actor_id', '=', 'NULL')						
						->orderBy('serie_original_name', 'asc')
						->orderBy('episode_number', 'asc')
						->orderBy('actor_name', 'asc')
						->get();
		} else {
			$corrections = DB::table('corrections')
						->leftjoin('series', 'corrections.serie_id', '=', 'series.id')
						->leftjoin('episodes', 'corrections.episode_id', '=', 'episodes.id')
						->leftjoin('actors', 'corrections.actor_id', '=', 'actors.id')
						->select(DB::raw('corrections.id AS correction_id, series.id AS serie_id, series.original_name AS serie_original_name, episodes.id AS episode_id, episodes.number AS episode_number, actors.id AS actor_id, actors.name AS actor_name, corrections.timecode AS correction_timecode, corrections.correction AS correction_correction, corrections.current_state AS correction_current_state'))
						->whereIn('corrections.actor_id', $actors_selected)
						->orWhere('corrections.actor_id', '=', 'NULL')
						->orderBy('serie_original_name', 'asc')
						->orderBy('episode_number', 'asc')
						->orderBy('actor_name', 'asc')
						->get();
		}
		$correctionsTable = "";
		if(count($corrections) > 0){
			$correctionsTable = "<thead> <tr> <th style='text-align:center; vertical-align:middle;'>Série</th> <th style='text-align:center; vertical-align:middle;'>Nº Episódio</th> <th style='text-align:center; vertical-align:middle;'>Actor</th> <th style='text-align:center; vertical-align:middle;'>Timecode</th> <th style='text-align:center; vertical-align:middle;'>Correcção</th> </tr> </thead> <tbody>";
			foreach($corrections as $correction){
				$correctionsTable = $correctionsTable . "<tr> <td style='text-align:center; vertical-align:middle;'>". ((is_null($correction->serie_original_name) || !isset($correction->serie_original_name)) ? "Não definido" : $correction->serie_original_name) ."</td> <td style='text-align:center; vertical-align:middle;'>". ((is_null($correction->episode_number) || !isset($correction->episode_number)) ? "Não definido" : $correction->episode_number) ."</td> <td style='text-align:center; vertical-align:middle;'>". ((is_null($correction->actor_name) || !isset($correction->actor_name)) ? "Não definido" : $correction->actor_name) ."</td> <td style='text-align:center; vertical-align:middle;'>". $correction->correction_timecode ."</td> <td style='text-align:center; vertical-align:middle;'>". $correction->correction_correction ."</td> </tr>";
			}
			$correctionsTable = $correctionsTable . "</tbody>";
		}
		return response()->json(['correctionsTable' => $correctionsTable]);
	}
	
	public function delete(Request $request)
    {
		DB::beginTransaction();
		try {
			$correction_id = $request->input('correction_id');
			$correction = Correction::find($correction_id);
			$correction->delete();
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
