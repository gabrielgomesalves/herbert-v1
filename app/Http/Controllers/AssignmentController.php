<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Excel;
use Storage;
use File;
use Response;

use Carbon\Carbon;
use App\Serie;
use App\Actor;
use App\Assignment;
use App\Character;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$series = Serie::orderBy('original_name', 'asc')->where('current_state', false)->get();
        return view('assignment.index', ['series' => $series]);
    }

	public function listing(Request $request)
    {
ini_set('memory_limit', '-1');		
$serie_id = $request->input('serie_id');

		$actors = Actor::orderBy('name', 'asc')->get();
		$assignments = Assignment::where('serie_id', $serie_id)->get();
		$assigned = array();
		$assignment_characterid = array();
		foreach($assignments as $assignment){
			array_push($assignment_characterid, $assignment->character_id);
			$character = Character::where('id', $assignment->character_id)->where('gender', '<>', 'T')->first();
			if(!isset($character) || $character == null){
				continue;
			}
			$actor = Actor::where('id', $assignment->actor_id)->first();
			$data = array(
				'character_id'  => $character->id,
				'character_name'  => $character->name,
				'character_gender'  => $character->gender,
				'actor_id'  => $actor->id,
				'actor_name' => $actor->name
			);
			array_push($assigned, $data);
		}
		$characters = Character::where('serie_id', $serie_id)->where('gender', '<>', 'T')->whereNotIn('id', $assignment_characterid)->get();
		foreach($characters as $character){
			$data = array(
				'character_id'  => $character->id,
				'character_name'  => $character->name,
				'character_gender'  => $character->gender,
				'actor_id'  => null,
				'actor_name' => null
			);
			array_push($assigned, $data);
		}
		$speeches_count = DB::table('scripts')
				->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
				->join('characters', 'scripts.character_id', '=', 'characters.id')
				->join('series', 'characters.serie_id', '=', 'series.id')
				->where('series.id', '=', $serie_id)
				->select(DB::raw('characters.id AS characters_id, characters.name AS characters_name, episodes.id AS episodes_id, episodes.number AS episodes_number, COUNT(scripts.id) AS scripts_speech_count'))		
				->groupBy('characters.id')
				->groupBy('episodes.id')
				->orderBy('episodes.number', 'asc')
				->get();
		$speeches_total_count = DB::table('scripts')
				->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
				->join('characters', 'scripts.character_id', '=', 'characters.id')
				->join('series', 'characters.serie_id', '=', 'series.id')
				->where('series.id', '=', $serie_id)
				->select(DB::raw('characters.id AS characters_id, characters.name AS characters_name, episodes.id AS episodes_id, episodes.number AS episodes_number, COUNT(scripts.id) AS scripts_speech_count'))		
				->groupBy('characters.id')
				->orderBy('episodes.number', 'asc')
				->get();
		$total_episodes_count = DB::table('episodes')
				->where('episodes.serie_id', '=', $serie_id)
				->count();
		return view('assignment.index', ['actors' => $actors, 'assigned' => $assigned, 'serie_id' => $serie_id, 'speeches_count' => $speeches_count, 'speeches_total_count' => $speeches_total_count, 'total_episodes_count' => $total_episodes_count]);
    }
	
	public function automaton()
	{
		return view('assignment.automaton');
	}
	
	public function automaton_insert(Request $request)
    {
		$file = $request->file('file');
		Storage::disk('local')->put($file->getClientOriginalName(),  File::get($file));
		$upload_success = Storage::disk('local')->exists($file->getClientOriginalName());
		if($upload_success){
			$storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
			$fileNamePath = $storagePath."/".$file->getClientOriginalName();
			$book = Excel::load($fileNamePath, 'utf-8')->formatDates(false)->all();
			DB::beginTransaction();
			try {
				$sheetcounter = 0;
				foreach ($book as $sheet){
					if($sheetcounter == 1){
						break;
					}
					$serie_id = trim($sheet->getTitle());
					if(!Serie::where('id', $serie_id)->exists()){
						throw new \Exception('A série com o identificador ' + $serie_id + ' não foi encontrada na base de dados.');
					}
					$actors_id = array();
					$row_counter = 0;
					foreach($sheet->toArray() as $row) {
						if($row_counter == 0){
							$row_counter = $row_counter + 1;
							continue;
						}
						if(array_values($row)[0] == null || array_values($row)[0] == ""){
							continue;
						}
						$character_name = trim(array_values($row)[0]);
						$character_gender = trim(array_values($row)[1]);
						$actor_name = trim(array_values($row)[2]);
						if(Character::where('name', $character_name)->where('gender', $character_gender)->where('serie_id', $serie_id)->exists()){
							$character = Character::where('name', $character_name)->where('gender', $character_gender)->where('serie_id', $serie_id)->first();
							if(Actor::where('name', $actor_name)->exists()){
								$actor = Actor::where('name', $actor_name)->first();
								if(!in_array($actor->id, $actors_id)){
									$actors_id[] = $actor->id;
								}
								if(!Assignment::where('character_id', $character->id)->where('actor_id', $actor->id)->exists()){
									$assignment = new Assignment;
									$assignment->serie_id = $serie_id;
									$assignment->actor_id = $actor->id;
									$assignment->character_id = $character->id;
									$assignment->save();
								} 
							} else {
								throw new \Exception('O actor com o nome ' + $actor_name + ' não foi encontrado na base de dados.');
							}
						}
						$row_counter = $row_counter + 1;	
					}
					$sheetcounter = $sheetcounter + 1;
				}
				$charactersWithGenderT = Character::where('serie_id', $serie_id)->where('gender', 'T')->get();
				foreach($charactersWithGenderT as $character){
					if(Assignment::where('character_id', $character->id)->exists()){
						$assignment = Assignment::where('character_id', $character->id)->delete();
					}				
					foreach($actors_id as $actor_id){
						$assignment = new Assignment;
						$assignment->serie_id = $serie_id;
						$assignment->actor_id = $actor_id;
						$assignment->character_id = $character->id;
						$assignment->save();
					}
				}			
				DB::commit();
			}
			catch(\Exception $ex)
			{
				DB::rollback();
				Storage::delete($file->getClientOriginalName());
				return response()->json(['error_msg' => $ex->getMessage(), 'filename' => $file->getClientOriginalName(), 'row' => $row_counter], 400);
			}
			Storage::delete($file->getClientOriginalName());
			return response()->json('success', 200);
		} else {
			Storage::delete($file->getClientOriginalName());
			return response()->json('Erro no upload do ficheiro: ' + $file->getClientOriginalName(), 400);
		}
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
		DB::beginTransaction();
		try {
			$serie_id = $request->input('serie_id');
			$input = $request->all();
			$actors_selected = array();
			foreach ($input as $key => $value){
				if(is_numeric($key)){
					array_push($actors_selected, $value);
				}
			}
			$actors_selected = array_unique($actors_selected);
			$charactersWithGenderT = Character::where('serie_id', $serie_id)->where('gender', 'T')->get();
			foreach($charactersWithGenderT as $character){
				if(Assignment::where('character_id', $character->id)->exists()){
					$assignment = Assignment::where('character_id', $character->id)->delete();
				}
				
				foreach($actors_selected as $actor_selected){
					$assignment = new Assignment;
					$assignment->serie_id = $serie_id;
					$assignment->actor_id = $actor_selected;
					$assignment->character_id = $character->id;
					$assignment->save();
				}
			}
			foreach ($input as $key => $value){
				if(is_numeric($key)){
					if(Assignment::where('character_id', $key)->exists()){
						$assignment = Assignment::where('character_id', $key)->first();
						$assignment->actor_id = $value;
						$assignment->save();
					} else {
						$assignment = new Assignment;
						$assignment->serie_id = $serie_id;
						$assignment->actor_id = $value;
						$assignment->character_id = $key;
						$assignment->save();
					}
				}
			}
			DB::commit();
		}
		catch(\Exception $ex)
		{
			DB::rollback();
			//return response()->json(['error_msg' => $ex->getMessage()], 400);
		}
		return redirect()->action('SerieController@index');
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
