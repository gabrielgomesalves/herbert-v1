<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Response;

use App\Character;
use App\Serie;
use App\Script;
use App\Assignment;

class CharacterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$series = Serie::orderBy('original_name', 'asc')->where('current_state', false)->get();
        return view('character.index', ['series' => $series]);
    }

	public function detect_conflicts(Request $request)
	{
		$series = Serie::orderBy('original_name', 'asc')->where('current_state', false)->get();
		$serie_id = $request->input('serie_id');
		$threshold = $request->input('threshold');
		$characters = DB::table('characters')
						->join('scripts', 'characters.id', '=', 'scripts.character_id')
						->select(DB::raw('characters.id AS character_id, characters.name AS character_name, characters.gender AS character_gender, COUNT(scripts.id) AS script_count'))
						->where('characters.serie_id', '=', $serie_id)
						->groupBy('character_id')
						->orderBy('character_name', 'asc')
						->get();
		$all_characters = Character::where('serie_id', $serie_id)->orderBy('name', 'asc')->distinct()->get();
		$characters_detected = array();
		foreach($characters as $character_1){
			foreach($characters as $character_2){
				if($character_1->character_name == $character_2->character_name && $character_1->character_gender !== $character_2->character_gender){
					if(!in_array($character_1, $characters_detected)){
						array_push($characters_detected, $character_1);
					}
					if(!in_array($character_2, $characters_detected)){
						array_push($characters_detected, $character_2);
					}
					continue;
				}
				if($character_1->character_name == $character_2->character_name && $character_1->character_gender == $character_2->character_gender && (in_array($character_1, $characters_detected) || in_array($character_2, $characters_detected))){
					continue;
				}
				if(strlen($character_1->character_name) > strlen($character_2->character_name) || strlen($character_1->character_name) > strlen($character_2->character_name)){
					$lev = levenshtein($character_1->character_name, $character_2->character_name);
					if($lev <= $threshold){
						if(!in_array($character_1, $characters_detected)){
							array_push($characters_detected, $character_1);
						}
						if(!in_array($character_2, $characters_detected)){
							array_push($characters_detected, $character_2);
						}
					}
				}
			}
		}
		
		$characters_options = "";
		foreach($all_characters as $character){
			$characters_options = $characters_options . "<option value='".$character->id."'>".$character->name." (".$character->gender.")</option>";
		}
		
		$rowContent = "";
		foreach($characters_detected as $character_detected){
			$rowContent = $rowContent . "<tr><td style='text-align:center; vertical-align:middle;'>".$character_detected->character_name." (".$character_detected->character_gender.")</td><td style='text-align:center; vertical-align:middle;'>".$character_detected->script_count."</td><td style='text-align:center; vertical-align:middle;'><fieldset data-role='controlgroup' data-type='horizontal'> <select id='".$character_detected->character_id."' name='".$character_detected->character_id."' onFocus='this.selectedIndex = -1;'> <option selected disabled>Personagem</option>".$characters_options."</select> <button class='ui-btn ui-btn-inline ui-btn-icon-notext ui-icon-recycle' onclick=\"character_change_characters('".$character_detected->character_id."');\">Button</button> </fieldset></td></tr>";
		}
		
		return response()->json(['conflictsData' => $rowContent]);
	}
	
	
	public function change_character(Request $request)
	{
		DB::beginTransaction();
		try {
			$character_new_id = $request->input('character_new_id');
			$character_old_id = $request->input('character_old_id');
			$character_old = Character::find($character_old_id);
			$scripts = Script::where('character_id', $character_old_id)->get();
			foreach($scripts as $script){
				$script->character_id = $character_new_id;
				$script->save();
			}
			$assignments = Assignment::where('character_id', $character_old_id)->get();
			foreach($assignments as $assignment ){
				$assignment->delete();
			}
			$character_old->delete();
			DB::commit();
		}
		catch(\Exception $ex)
		{
			DB::rollback();
			return response()->json(['error_msg' => $ex->getMessage()], 400);
		}
		return response()->json('success', 200);
	}
	
	public function getCharacters(Request $request)
	{
		try {
			$serie_id = $request->input('serie_id');
			if(Character::where('serie_id', $serie_id)->exists()){
				$characters = Character::where('serie_id', '=', $serie_id)->orderBy('name', 'asc')->get();
				return json_encode($characters);
			} else {
				throw new \Exception('Não foram encontradas personagens');
			}
		}
		catch(\Exception $ex)
		{
			return response()->json(['error_msg' => $ex->getMessage()], 400);
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
