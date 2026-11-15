<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Storage;
use Response;
use File;
use DB;
use Excel;
use Carbon\Carbon;

use App\Serie;
use App\Episode;
use App\Script;
use App\Character;
use App\Song;

class ScriptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$series = Serie::where('current_state', false)->orderBy('original_name', 'asc')->get();
		return view('script.view', ['series' => $series]);
    }
	
	public function insert()
    {
		return view('script.insert');
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
        $file = $request->file('file');
		Storage::disk('local')->put($file->getClientOriginalName(),  File::get($file));
		$upload_success = Storage::disk('local')->exists($file->getClientOriginalName());
		if($upload_success){
			$storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
			$fileNamePath = $storagePath."/".$file->getClientOriginalName();
			$book = Excel::load($fileNamePath, 'utf-8')->formatDates(false)->all();

			DB::beginTransaction();
			try {
				foreach ($book as $sheet){
					
					switch($sheet->getTitle()) {
						
						case "info geral":
							$row_counter = 1;
							foreach($sheet->toArray() as $row) {
								switch($row_counter){
									case 1:
										$serie_original_name = mb_strtoupper(trim(array_values($row)[1]), "UTF-8");
										break;
									case 2:
										$episode_original_name = mb_strtoupper(trim(array_values($row)[1]), "UTF-8");
										break;
									case 3:
										$serie_translated_name = mb_strtoupper(trim(array_values($row)[1]), "UTF-8");
										break;
									case 4:
										$episode_translated_name = mb_strtoupper(trim(array_values($row)[1]), "UTF-8");
										break;
									case 5:
										$episode_number = $this->VerifyIfNumeric(trim(array_values($row)[1]));
										break;
									default:
										break;
								}
								$row_counter = $row_counter + 1;
							}
							
							if(Serie::where('original_name', $serie_original_name)->where('translated_name', $serie_translated_name)->exists()){
								$serie = Serie::where('original_name', $serie_original_name)->where('translated_name', $serie_translated_name)->first();
								$serie->current_state = false;
								
								if(Episode::where('original_name', $episode_original_name)->where('translated_name', $episode_translated_name)->where('serie_id', $serie->id)->exists()){
									throw new \Exception('Episódio já existe na base de dados');
								}

								$episode = new Episode;
								$episode->original_name = $episode_original_name;
								$episode->translated_name = $episode_translated_name;
								$episode->number = $episode_number;
								$episode->current_state = false;
								$episode->serie_id = $serie->id;
								$episode->save();
								
								$serie->episodes()->save($episode);
							} else {
								$serie = new Serie;
								$serie->original_name = $serie_original_name;
								$serie->translated_name = $serie_translated_name;
								$serie->current_state = false;
								$serie->save();
								
								$episode = new Episode;
								$episode->original_name = $episode_original_name;
								$episode->translated_name = $episode_translated_name;
								$episode->number = $episode_number;
								$episode->current_state = false;
								$episode->serie_id = $serie->id;
								$episode->save();
								
								$serie->episodes()->save($episode);
							}
							break;
							
						case "texto":
							$row_counter = -1;
							foreach($sheet->toArray() as $row) {
								$row_counter = $row_counter + 1;
								if($row_counter == 0){
									continue;
								}
								
								if(array_values($row)[1] == null || array_values($row)[1] == ""){
									continue;
								}
								
								$character_name = $this->GetStringBeforeGivenString(array_values($row)[1], "(");
								$character_gender = $this->VerifyCharacterGender($this->GetStringBetweenGivenStrings(array_values($row)[1], "(", ")"));
								
								if(!isset($character_name) || !isset($character_gender) || $character_name == null || $character_gender == null){
									continue;
								}
								
								if(Character::where('name', '=', $character_name)->where('gender', '=', $character_gender)->where('serie_id', '=', $serie->id)->exists()){
									$character = Character::where('name', '=', $character_name)->where('gender', '=', $character_gender)->where('serie_id', '=', $serie->id)->first();
									
								} else {
									$character = new Character;
									$character->name = $character_name;
									$character->gender = $character_gender;
									$character->serie_id = $serie->id;
									$character->save();
									
									$serie->characters()->save($character);
								}
								
								$speech = trim(array_values($row)[2]);
								
								if(!isset($speech) || $speech == null){
									continue;
								}
								
								$script = new Script;
								$script->number = $row_counter;
								$script->timecode = $this->CorrectTime(array_values($row)[0]);
								$script->speech = $speech . "";
								$script->current_state = false;
								$script->character_id = $character->id;
								$script->episode_id = $episode->id;
								$script->save();
								
								$character->scripts()->save($script);
								$episode->scripts()->save($script);

							}
							break;
							
						case "cancoes":
							$row_counter = -1;
							foreach($sheet->toArray() as $row) {
								$row_counter = $row_counter + 1;
								if($row_counter == 0){
									continue;
								}
								
								if(array_values($row)[1] == null || array_values($row)[1] == ""){
									continue;
								}
								
								$song = new Song;
								$song->timecode = $this->CorrectTime(array_values($row)[0]);
								$song->name = mb_strtoupper(trim(array_values($row)[1]), "UTF-8");
								$song->lyrics = trim(array_values($row)[2]);
								$song->current_state = false;
								$episode->scripts()->save($song);
								
							}
							break;
							
						default:
							break;
					}
				}
				DB::commit();
			}
			catch(\Exception $ex)
			{
				DB::rollback();
				Storage::delete($file->getClientOriginalName());
				if(!isset($episode_number)){
					return response()->json(['error_msg' => $ex->getMessage(), 'filename' => $file->getClientOriginalName(), 'serie_original_name' => $serie_original_name, 'episode_original_name' => $episode_original_name, 'episode_number' => null, 'row' => $row_counter], 400);
				}
				return response()->json(['error_msg' => $ex->getMessage(), 'filename' => $file->getClientOriginalName(), 'serie_original_name' => $serie_original_name, 'episode_original_name' => $episode_original_name, 'episode_number' => $episode_number, 'row' => ($row_counter + 1)], 400);
			}
			Storage::delete($file->getClientOriginalName());
			return response()->json(['filename' => $file->getClientOriginalName(), 'serie_original_name' => $serie_original_name, 'episode_original_name' => $episode_original_name, 'episode_number' => $episode_number], 200);
		} else {
			Storage::delete($file->getClientOriginalName());
			return response()->json('Erro no upload do ficheiro: ' + $file->getClientOriginalName(), 400);
		}
	}
	
	public function VerifyIfNumeric($testcase)
	{
		if(ctype_digit($testcase)){
			return $testcase;
		} else {
			throw new \Exception('Formato do campo não numérico');
		}
	}
	
	public function VerifyCharacterGender($gender)
	{
		if($gender == 'O' || $gender == 'T' || $gender == 'M' || $gender == 'F'){
			return $gender;
		} else {
			throw new \Exception('Formato do campo género incorreto');
		}
	}
	
	public function GetStringBeforeGivenString($str, $marker)
	{
		return mb_strtoupper(trim(substr($str, 0, strpos($str, $marker))), "UTF-8");
	}
	
	public function GetStringBetweenGivenStrings($str, $from, $to)
	{
		$sub = substr($str, strpos($str,$from)+strlen($from),strlen($str));
		return mb_strtoupper(trim(substr($sub,0,strpos($sub,$to))), "UTF-8");
	}
	
	public function CorrectTime($timeToCorrect)
	{
		$errorDividers = array(",", ";", ".", "_", "-");
		$timeCorrected = str_replace($errorDividers, ":", $timeToCorrect);
		$p1 = '/^(0?\d|1\d|2[0-3]):[0-5]\d:[0-5]\d$/';
		if(preg_match($p1, $timeCorrected)){
			$carbon = new Carbon($timeCorrected);
			return $carbon->toTimeString();
		} else {
			throw new \Exception('Formato do campo timecode incorreto');
		}
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
	
	public function getScriptsByEpisode(Request $request)
	{
		$episode_id = $request->input('episode_id');
		$scripts = DB::table('scripts')
					->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
					->join('characters', 'scripts.character_id', '=', 'characters.id')
					->where('episodes.id', '=', $episode_id)
					->select('characters.id AS character_id', 'characters.name AS character_name', 'characters.gender AS character_gender', 'scripts.timecode AS script_timecode', 'scripts.id AS script_id', 'scripts.number AS script_number', 'scripts.speech AS script_speech')
					->orderBy('scripts.number', 'asc')
					->get();	
					
		return json_encode($scripts);
	}
	
	public function getScripts(Request $request)
	{
		$actors_selected = $request->input('actors_selected');
		$serie_id = $request->input('serie_id');
		$episode_id = $request->input('episode_id');
		$view_option = $request->input('view_option');

		if(isset($actors_selected) && is_array($actors_selected) && (count($actors_selected) == 1)){
			
			if(isset($view_option) && $view_option == 0){
				$scripts_id = DB::table('scripts')
					->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
					->join('characters', 'scripts.character_id', '=', 'characters.id')
					->leftjoin('assignments', 'characters.id', '=', 'assignments.character_id')
					->leftjoin('actors', 'assignments.actor_id', '=', 'actors.id')
					->where('episodes.id', '=', $episode_id)
					->whereIn('assignments.actor_id', $actors_selected)
					->select('scripts.id AS script_id')
					->orderBy('scripts.id', 'asc')
					->get();
			
				foreach($scripts_id as $script_id)
				{
					$single_script_before = DB::table('scripts')
						->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
						->join('characters', 'scripts.character_id', '=', 'characters.id')
						->leftjoin('assignments', 'characters.id', '=', 'assignments.character_id')
						->leftjoin('actors', 'assignments.actor_id', '=', 'actors.id')
						->where('episodes.id', '=', $episode_id)
						->where('scripts.id', intval($script_id->script_id) - 1)
						->select(DB::raw('assignments.actor_id AS actor_id, actors.name AS actor_name, characters.id AS character_id, characters.name AS character_name, characters.gender AS character_gender, scripts.id AS script_id, scripts.timecode AS script_timecode, scripts.number AS script_number, scripts.speech AS script_speech, "0" AS paint'))
						->orderBy('scripts.number', 'asc')
						->first();
					
					$single_script_present = DB::table('scripts')
						->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
						->join('characters', 'scripts.character_id', '=', 'characters.id')
						->leftjoin('assignments', 'characters.id', '=', 'assignments.character_id')
						->leftjoin('actors', 'assignments.actor_id', '=', 'actors.id')
						->where('episodes.id', '=', $episode_id)
						->where('scripts.id', intval($script_id->script_id))
						->whereIn('assignments.actor_id', $actors_selected)
						->select(DB::raw('assignments.actor_id AS actor_id, actors.name AS actor_name, characters.id AS character_id, characters.name AS character_name, characters.gender AS character_gender, scripts.id AS script_id, scripts.timecode AS script_timecode, scripts.number AS script_number, scripts.speech AS script_speech, "1" AS paint'))
						->orderBy('scripts.number', 'asc')
						->first();
						
					$single_script_next = DB::table('scripts')
						->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
						->join('characters', 'scripts.character_id', '=', 'characters.id')
						->leftjoin('assignments', 'characters.id', '=', 'assignments.character_id')
						->leftjoin('actors', 'assignments.actor_id', '=', 'actors.id')
						->where('episodes.id', '=', $episode_id)
						->where('scripts.id', intval($script_id->script_id) + 1)
						->select(DB::raw('assignments.actor_id AS actor_id, actors.name AS actor_name, characters.id AS character_id, characters.name AS character_name, characters.gender AS character_gender, scripts.id AS script_id, scripts.timecode AS script_timecode, scripts.number AS script_number, scripts.speech AS script_speech, "0" AS paint'))
						->orderBy('scripts.number', 'asc')
						->first();
						
					$scripts[] = $single_script_before;
					$scripts[] = $single_script_present;
					$scripts[] = $single_script_next;
				}
						
			} else if(isset($view_option) && $view_option == 1){
				
				$scripts_id = DB::table('scripts')
					->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
					->join('characters', 'scripts.character_id', '=', 'characters.id')
					->leftjoin('assignments', 'characters.id', '=', 'assignments.character_id')
					->leftjoin('actors', 'assignments.actor_id', '=', 'actors.id')
					->where('episodes.id', '=', $episode_id)
					->whereIn('assignments.actor_id', $actors_selected)
					->select('scripts.id AS script_id', 'characters.id AS character_id', 'characters.name AS character_name')
					->orderBy('characters.name', 'asc')
					->orderBy('scripts.id', 'asc')
					->get();
				
				foreach($scripts_id as $script_id)
				{
					$single_script_before = DB::table('scripts')
										->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
										->join('characters', 'scripts.character_id', '=', 'characters.id')
										->leftjoin('assignments', 'characters.id', '=', 'assignments.character_id')
										->leftjoin('actors', 'assignments.actor_id', '=', 'actors.id')
										->where('episodes.id', '=', $episode_id)
										->where('scripts.id', ($script_id->script_id - 1))
										->select(DB::raw('assignments.actor_id AS actor_id, actors.name AS actor_name, characters.id AS character_id, characters.name AS character_name, characters.gender AS character_gender, scripts.id AS script_id, scripts.timecode AS script_timecode, scripts.number AS script_number, scripts.speech AS script_speech, "0" AS paint'))
										->first();			
										
					$single_script_present = DB::table('scripts')
										->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
										->join('characters', 'scripts.character_id', '=', 'characters.id')
										->leftjoin('assignments', 'characters.id', '=', 'assignments.character_id')
										->leftjoin('actors', 'assignments.actor_id', '=', 'actors.id')
										->where('episodes.id', '=', $episode_id)
										->where('scripts.id', $script_id->script_id)
										->whereIn('assignments.actor_id', $actors_selected)
										->select(DB::raw('assignments.actor_id AS actor_id, actors.name AS actor_name, characters.id AS character_id, characters.name AS character_name, characters.gender AS character_gender, scripts.id AS script_id, scripts.timecode AS script_timecode, scripts.number AS script_number, scripts.speech AS script_speech, "1" AS paint'))
										->first();		
					
					$single_script_next = DB::table('scripts')
										->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
										->join('characters', 'scripts.character_id', '=', 'characters.id')
										->leftjoin('assignments', 'characters.id', '=', 'assignments.character_id')
										->leftjoin('actors', 'assignments.actor_id', '=', 'actors.id')
										->where('episodes.id', '=', $episode_id)
										->where('scripts.id', ($script_id->script_id + 1))
										->select(DB::raw('assignments.actor_id AS actor_id, actors.name AS actor_name, characters.id AS character_id, characters.name AS character_name, characters.gender AS character_gender, scripts.id AS script_id, scripts.timecode AS script_timecode, scripts.number AS script_number, scripts.speech AS script_speech, "0" AS paint'))
										->first();								
					
					$scripts[] = $single_script_before;
					$scripts[] = $single_script_present;
					$scripts[] = $single_script_next;
				}
					
			}
			//echo print_r($scripts);
			$scriptData = "";
			foreach($scripts as $script){
				if(!isset($script)){
					continue;
				}
				if($script->paint == '1'){
					$scriptData = $scriptData . "<div id='" . $script->script_id . "' class='fala'><div class='info'>" . $script->character_name . " (" . $script->character_gender . ") - " . $script->actor_name . " - TC: " . $script->script_timecode . " - N.º: " . $script->script_number . " - <a href='' class='ui-btn ui-shadow ui-corner-all ui-icon-edit ui-btn-icon-notext ui-btn-inline' onclick=\"script_show_popupdialog_scriptEdit('" . $serie_id . "', '" . $script->character_id . "', '" . $script->script_id . "');\">Editar</a><a href='' class='ui-btn ui-shadow ui-corner-all ui-icon-refresh ui-btn-icon-notext ui-btn-inline' onclick=\"script_update_speech_div('" . $script->script_id . "');\">Actualizar</a></div><div id='" . $script->script_id . "' class='texto_fala' >" . nl2br($script->script_speech) . "</div><div class='fala_buttons' data-role='controlgroup' data-type='horizontal'><a href='' class='ui-btn ui-btn-inline ui-icon-carat-u ui-btn-icon-notext' data-inline='true' onclick=\"jump_to_previous_id('" . $script->script_id . "');\">Anterior</a><a href='' class='ui-btn ui-btn-inline ui-icon-carat-d ui-btn-icon-notext' data-inline='true' onclick=\"jump_to_next_id('" . $script->script_id . "');\">Seguinte</a></div></div>";				
				} else {
					if(!isset($script->actor_name) || is_null($script->actor_name)){
						$scriptData = $scriptData . "<div class='fala_outro'><div class='info_outro'>" . $script->character_name . " (" . $script->character_gender . ") - Não distribuido - TC: " . $script->script_timecode . " - N.º: " . $script->script_number . "</div><div class='texto_fala_outro' >" . $script->script_speech . "</div></div>";
					} else {
						$scriptData = $scriptData . "<div class='fala_outro'><div class='info_outro'>" . $script->character_name . " (" . $script->character_gender . ") - " . $script->actor_name . " - TC: " . $script->script_timecode . " - N.º: " . $script->script_number . "</div><div class='texto_fala_outro' >" . $script->script_speech . "</div></div>";
					}	
				}
			}
			
			return response()->json(['scriptData' => $scriptData]);
		}
		else if(isset($actors_selected) && is_array($actors_selected) && (count($actors_selected) > 1)){
			
			if(isset($view_option) && $view_option == 0){
				$scripts = DB::table('scripts')
						->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
						->join('characters', 'scripts.character_id', '=', 'characters.id')
						->join('assignments', 'characters.id', '=', 'assignments.character_id')
						->join('actors', 'assignments.actor_id', '=', 'actors.id')
						->where('episodes.id', '=', $episode_id)
						->whereIn('assignments.actor_id', $actors_selected)
						->select('assignments.actor_id AS actor_id', 'actors.name AS actor_name', 'characters.id AS character_id', 'characters.name AS character_name', 'characters.gender AS character_gender', 'scripts.timecode AS script_timecode', 'scripts.id AS script_id', 'scripts.number AS script_number', 'scripts.speech AS script_speech')
						->orderBy('scripts.number', 'asc')
						->get();	
			} else if(isset($view_option) && $view_option == 1){
				
				$scripts = DB::table('scripts')
						->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
						->join('characters', 'scripts.character_id', '=', 'characters.id')
						->join('assignments', 'characters.id', '=', 'assignments.character_id')
						->join('actors', 'assignments.actor_id', '=', 'actors.id')
						->where('episodes.id', '=', $episode_id)
						->whereIn('assignments.actor_id', $actors_selected)
						->select('assignments.actor_id AS actor_id', 'actors.name AS actor_name', 'characters.id AS character_id', 'characters.name AS character_name', 'characters.gender AS character_gender', 'scripts.timecode AS script_timecode', 'scripts.id AS script_id', 'scripts.number AS script_number', 'scripts.speech AS script_speech')
						->orderBy('characters.name', 'asc')
						->orderBy('scripts.number', 'asc')
						->get();
				
			}
			
			$scriptData = "";
			foreach($scripts as $script){
				if(!isset($script)){
					continue;
				}
				$scriptData = $scriptData . "<div id='" . $script->script_id . "' class='fala'><div class='info'>" . $script->character_name . " (" . $script->character_gender . ") - " . $script->actor_name . " - TC: " . $script->script_timecode . " - N.º: " . $script->script_number . " - <a href='' class='ui-btn ui-shadow ui-corner-all ui-icon-edit ui-btn-icon-notext ui-btn-inline' onclick=\"script_show_popupdialog_scriptEdit('" . $serie_id . "', '" . $script->character_id . "', '" . $script->script_id . "');\">Editar</a><a href='' class='ui-btn ui-shadow ui-corner-all ui-icon-refresh ui-btn-icon-notext ui-btn-inline' onclick=\"script_update_speech_div('" . $script->script_id . "');\">Actualizar</a></div><div id='" . $script->script_id . "' class='texto_fala' >" . nl2br($script->script_speech) . "</div><div class='fala_buttons' data-role='controlgroup' data-type='horizontal'><a href='' class='ui-btn ui-btn-inline ui-icon-carat-u ui-btn-icon-notext' data-inline='true' onclick=\"jump_to_previous_id('" . $script->script_id . "');\">Anterior</a><a href='' class='ui-btn ui-btn-inline ui-icon-carat-d ui-btn-icon-notext' data-inline='true' onclick=\"jump_to_next_id('" . $script->script_id . "');\">Seguinte</a></div></div>";
			}
			
			return response()->json(['scriptData' => $scriptData]);	
		}
	}
	
	public function get_script_full(Request $request)
	{
		$serie_id = $request->input('serie_id');
		$episode_id = $request->input('episode_id');
		$scripts = DB::table('scripts')
						->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
						->join('characters', 'scripts.character_id', '=', 'characters.id')
						->leftjoin('assignments', 'characters.id', '=', 'assignments.character_id')
						->leftjoin('actors', 'assignments.actor_id', '=', 'actors.id')
						->where('episodes.id', '=', $episode_id)
						->select('assignments.actor_id AS actor_id', 'actors.name AS actor_name', 'characters.id AS character_id', 'characters.name AS character_name', 'characters.gender AS character_gender', 'scripts.timecode AS script_timecode', 'scripts.id AS script_id', 'scripts.number AS script_number', 'scripts.speech AS script_speech')
						->orderBy('scripts.number', 'asc')
						->get();
		$scriptData = "";
		foreach($scripts as $script){
			if(!isset($script->actor_name) || is_null($script->actor_name)){
				$scriptData = $scriptData . "<div id='" . $script->script_id . "' class='fala'><div class='info'>" . $script->character_name . " (" . $script->character_gender . ") - Não distribuido - TC: " . $script->script_timecode . " - N.º: " . $script->script_number . " - <a href='' class='ui-btn ui-shadow ui-corner-all ui-icon-edit ui-btn-icon-notext ui-btn-inline' onclick=\"script_show_popupdialog_scriptEdit('" . $serie_id . "', '" . $script->character_id . "', '" . $script->script_id . "');\">Editar</a><a href='' class='ui-btn ui-shadow ui-corner-all ui-icon-refresh ui-btn-icon-notext ui-btn-inline' onclick=\"script_update_speech_div('" . $script->script_id . "');\">Actualizar</a></div><div id='" . $script->script_id . "' class='texto_fala' >" . nl2br($script->script_speech) . "</div><div class='fala_buttons' data-role='controlgroup' data-type='horizontal'><a href='' class='ui-btn ui-btn-inline ui-icon-carat-u ui-btn-icon-notext' data-inline='true' onclick=\"jump_to_previous_id('" . $script->script_id . "');\">Anterior</a><a href='' class='ui-btn ui-btn-inline ui-icon-carat-d ui-btn-icon-notext' data-inline='true' onclick=\"jump_to_next_id('" . $script->script_id . "');\">Seguinte</a></div></div>";
			} else {
				$scriptData = $scriptData . "<div id='" . $script->script_id . "' class='fala'><div class='info'>" . $script->character_name . " (" . $script->character_gender . ") - " . $script->actor_name . " - TC: " . $script->script_timecode . " - N.º: " . $script->script_number . " - <a href='' class='ui-btn ui-shadow ui-corner-all ui-icon-edit ui-btn-icon-notext ui-btn-inline' onclick=\"script_show_popupdialog_scriptEdit('" . $serie_id . "', '" . $script->character_id . "', '" . $script->script_id . "');\">Editar</a><a href='' class='ui-btn ui-shadow ui-corner-all ui-icon-refresh ui-btn-icon-notext ui-btn-inline' onclick=\"script_update_speech_div('" . $script->script_id . "');\">Actualizar</a></div><div id='" . $script->script_id . "' class='texto_fala' >" . nl2br($script->script_speech) . "</div><div class='fala_buttons' data-role='controlgroup' data-type='horizontal'><a href='' class='ui-btn ui-btn-inline ui-icon-carat-u ui-btn-icon-notext' data-inline='true' onclick=\"jump_to_previous_id('" . $script->script_id . "');\">Anterior</a><a href='' class='ui-btn ui-btn-inline ui-icon-carat-d ui-btn-icon-notext' data-inline='true' onclick=\"jump_to_next_id('" . $script->script_id . "');\">Seguinte</a></div></div>";
			}
		}
		
		return response()->json(['scriptData' => $scriptData]);	
	}
	
	public function view()
	{
		$series = Serie::where('current_state', false)->orderBy('original_name', 'asc')->get();
		return view('script.view', ['series' => $series]);
	}
	
	public function mark_script_done(Request $request)
	{
		DB::beginTransaction();
		try {
			$script_id = $request->input('script_id');
			$script = Script::find($script_id);
			$script->current_state = true;
			$script->save();
			DB::commit();
		}
		catch(\Exception $ex)
		{
			DB::rollback();
			return response()->json(['error_msg' => $ex->getMessage()], 400);
		}
		return response()->json('success', 200);
	}

	public function update_script_speech(Request $request)
	{
		DB::beginTransaction();
		try {
			$script_id = $request->input('script_id');
			$script = Script::find($script_id);
			//$script->speech = trim(strip_tags($request->input('script_speech')));
			$script->speech = trim($request->input('script_speech'));
			$script->character_id = $request->input('character_id');
			$script->save();
			DB::commit();
		}
		catch(\Exception $ex)
		{
			DB::rollback();
			return response()->json(['error_msg' => $ex->getMessage()], 400);
		}
		return response()->json('success', 200);
	}
	
	
	public function getScriptById(Request $request)
	{
		$script_id = $request->input('script_id');
		$script = Script::find($script_id);
		return json_encode($script);
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
