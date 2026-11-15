<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use PDF;
use Storage;
use Response;

use App\Serie;
use App\Episode;

class EpisodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $series = Serie::orderBy('original_name', 'asc')->get();
		return view('episode.index', ['series' => $series]);
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
        //
    }

	public function changeState(Request $request)
	{
		DB::beginTransaction();
		try {
			$episode_id = $request->input('episode_id');
			$current_state = $request->input('current_state');
			$episode = Episode::find($episode_id);
			$episode->current_state = $current_state;
			$episode->save();
			if($current_state == false){
				$serie = Serie::find($episode->serie_id);
				$serie->current_state = $current_state;
				$serie->save();
			} else if($current_state == true){
        			$serie_id = $episode->serie_id;
				$episodes_todo = Episode::where('serie_id', $serie_id)->where('current_state', false)->get();
        			if($episodes_todo->isEmpty()){
          				$serie = Serie::find($serie_id);
  					$serie->current_state = $current_state;
  					$serie->save();
        			} else {
          				$serie = Serie::find($serie_id);
  					$serie->current_state = false;
  					$serie->save();
        			}
     			}
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

	public function getEpisodes(Request $request)
	{
		$serie_id = $request->input('serie_id');
		$current_state = $request->input('current_state');
		if(isset($current_state)){
			$episodes = Episode::where("serie_id", $serie_id)->where("current_state", $current_state)->orderBy('number', 'asc')->get();
		} else {
			$episodes = Episode::where("serie_id", "=", $serie_id)->orderBy('number', 'asc')->get();
		}
        return json_encode($episodes);
	}

	public function printEpisode(Request $request)
	{
		$episode_id = $request->input('episode_id');
		$episode_info = Episode::find($episode_id);
		$serie_info = Serie::find($episode_info->serie_id);
		$scripts = DB::table('scripts')
						->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
						->join('series', 'episodes.serie_id', '=', 'series.id')
						->join('characters', 'scripts.character_id', '=', 'characters.id')
						->where('episodes.id', '=', $episode_id)
						->select('series.original_name AS serie_original_name', 'episodes.id AS episode_id', 'episodes.original_name AS episode_original_name', 'episodes.number AS episode_number', 'characters.id AS character_id', 'characters.name AS character_name', 'characters.gender AS character_gender', 'scripts.timecode AS script_timecode', 'scripts.id AS script_id', 'scripts.number AS script_number', 'scripts.speech AS script_speech')
						->orderBy('scripts.number', 'asc')
						->get();

		return PDF::loadView('pdf.episode', ['serie_info' => $serie_info, 'episode_info' => $episode_info, 'scripts' => $scripts])->setPaper('a4', 'portrait')->setWarnings(false)->stream();//->download($actor->name . '.pdf');
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
        //
    }

	public function delete(Request $request)
    {
		DB::beginTransaction();
		try {
			$episode_id = $request->input('episode_id');
			$episode = Episode::find($episode_id);
			$serie_id = $episode->serie_id;
			$episode->delete();

			if(!Episode::where('serie_id', '=', $serie_id)->exists()){
				$serie = Serie::find($serie_id);
				$serie->delete();
			}

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
