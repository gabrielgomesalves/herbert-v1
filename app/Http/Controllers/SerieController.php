<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use Storage;
use Response;
use Artisan;
use Redirect;

use App\Serie;
use App\Episode;

class SerieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		$current_state = $request->input('series_current_state');
		if(!isset($current_state)){
			$current_state = false;
		}
		$series = DB::table('series')
					->join('episodes', 'series.id', '=', 'episodes.serie_id')
					->select(DB::raw('series.id AS serie_id, series.original_name AS serie_original_name, series.translated_name AS serie_translated_name, series.current_state AS serie_current_state, COUNT(episodes.id) AS episodes_count'))		
					->where('series.current_state', '=', $current_state)
					->groupBy('series.id')
					->orderBy('series.original_name', 'asc')
					->get();
		return view('serie.index', ['series' => $series, 'current_state' => $current_state]);
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
	
	public function changeState(Request $request)
	{
		DB::beginTransaction();
		try {
			$serie_id = $request->input('serie_id');
			$current_state = $request->input('current_state');
			$serie = Serie::find($serie_id);
			$episodes = Episode::where('serie_id', $serie_id)->get();
			foreach($episodes as $episode){
				$episode->current_state = $current_state;
				$episode->save();
			}
			$serie->current_state = $current_state;
			$serie->save();
			DB::commit();
		}
		catch(\Exception $ex)
		{
			DB::rollback();
			return response()->json(['error_msg' => $ex->getMessage()], 400);
		}
		return response()->json('success', 200);	
	}

	public function getSeries(Request $request)
	{
		$series_current_state = $request->input('current_state');
		$series = Serie::where("current_state", '=', $series_current_state)->orderBy('original_name', 'asc')->get();
		return json_encode($series);
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
		/*
		$serie_original_name = mb_strtoupper(trim($request->input('original_name')), "UTF-8");
		$serie_translated_name = mb_strtoupper(trim($request->input('translated_name')), "UTF-8");
		$serie_old = Serie::find($id);
		if(Serie::where('original_name', '=', $serie_original_name)->where('translated_name', '=', $serie_translated_name)->exists()){
			$serie_new = Serie::where('original_name', '=', $serie_original_name)->where('translated_name', '=', $serie_translated_name)->first();
			$episodes = Episode::where('serie_id', $id)->get();
			foreach($episodes as $episode){
				if(!Episode::where('original_name', '=', $episode->original_name)->where('translated_name', '=', $episode->translated_name)->where('number', '=', $episode->number)->where('serie_id', '=', $serie_new->id)->exists()){
					$episode->serie_id = $serie_new->id;
					$episode->save();
				} else {
					$episode->delete();
				}
			}
			$characters = Character::where('serie_id', $id)->get();
			foreach($characters as $character){
				if(!Character::where('serie_id', $serie_new->id)->exists()){
					
				} else {
					
				}
				$character->serie_id = $serie_new->id;
				$character->save();
			}
			$assignments = Assignment::where('serie_id', $id)->get();
			foreach($assignments as $assignment){
				if(){
					
				} else {
					
				}
				$assignment->serie_id = $serie_new->id;
				$assignment->save();
			}
			$glossaries = Glossary::where('serie_id', $id)->get();
			foreach($glossaries as $glossary){
				if(){
					
				} else {
					
				}
				$glossary->serie_id = $serie_new->id;
				$glossary->save();
			}
			$corrections = Correction::where('serie_id', $id)->get();
			foreach($corrections as $correction){
				if(){
					
				} else {
					
				}
				$correction->serie_id = $serie_new->id;
				$correction->save();
			}
			$serie_old->delete();
		} else {
			$serie_old->original_name = $serie_original_name;
			$serie_old->translated_name = $serie_translated_name;
			$serie_old->save();
		}
		$series = DB::table('series')
					->join('episodes', 'series.id', '=', 'episodes.serie_id')
					->select(DB::raw('series.id AS serie_id, series.original_name AS serie_original_name, series.translated_name AS serie_translated_name, series.current_state AS serie_current_state, COUNT(episodes.id) AS episodes_count'))		
					->groupBy('series.id')
					->orderBy('series.original_name', 'asc')
					->get();
		return Redirect::back()->with('series', $series);
		*/
    }
	
	public function create_scripts(Request $request)
	{
		try
		{
			$serie_id = $request->input('serie_id');
			$serie_info = Serie::find($serie_id);
			/*
			$storagePath = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
			$folder_list = File::directories($storagePath);
			$now = Carbon::now();
			foreach($folder_list as $folder_l){
				$timestamp = File::lastModified($folder_l);
				$date_from_timestamp = Carbon::createFromTimestamp($timestamp);
				if ($now->diffInDays($date_from_timestamp, false) <= -1)
				{
					File::deleteDirectory($folder_l);
				}
			}
			*/
			$folder = md5(Carbon::now()->toDateTimeString());
			Storage::disk('local')->makeDirectory($folder);
			$filename = preg_replace('/\s+/', '_', $serie_info->original_name);
			
			Artisan::queue('create:serie_zip', [
				'serie_id' => $serie_id,
				'folder' => $folder,
				'filename' => $filename
			]);
			
			return response()->json(['url' => url('/files/' . $folder . '/') . '/' . $filename . '.zip'], 200);
		}
		catch(\Exception $ex)
		{
			Storage::disk('local')->deleteDirectory($folder);
			return response()->json(['error_msg' => $ex->getMessage()], 400);
		}
	}
	
	public function create_statistics(Request $request)
	{
		try {
			$serie_id = $request->input('serie_id');	
			$serie = Serie::find($serie_id);
			$serie_name = str_replace(' ', '_', $serie->original_name);
			$folder = md5(Carbon::now()->toDateTimeString());
			
			Storage::disk('local')->makeDirectory($folder);
			
			$url = url('/files/' . $folder . '/') . '/' . $serie_name . '.xls';
			
			Artisan::queue('create:serie_statistics', [
				'serie_id' => $serie_id,
				'serie_name' => $serie_name,
				'folder' => $folder
			]);
			
			return response()->json(['url' => $url], 200);
		}
		catch(\Exception $ex){
			Storage::disk('local')->deleteDirectory($folder);
			return response()->json(['error_msg' => $ex->getMessage()], 400);
		}
	}
	
	public function delete(Request $request)
    {
		DB::beginTransaction();
		try {
			$serie_id = $request->input('serie_id');
			$serie = Serie::find($serie_id);
			$serie->delete();
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
