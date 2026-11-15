<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Excel;
use DB;
use App\Serie;
use App\Actor;
use App\Assignment;
use App\Character;

class CreateSerieStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:serie_statistics {serie_id} {serie_name} {folder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $serie_id = $this->argument('serie_id');
		$serie_name = $this->argument('serie_name');
		$folder = $this->argument('folder');
		
		try {
			$serie = Serie::find($serie_id);
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
			
			$episodes_numbers = DB::table('episodes')
						->join('series', 'episodes.serie_id', '=', 'series.id')
						->where('series.id', '=', $serie_id)
						->select('episodes.id AS episodes_id', 'episodes.number AS episodes_number')
						->orderBy('episodes.number', 'asc')
						->get();
			$actors = DB::table('actors')
				->join('assignments', 'actors.id', '=', 'assignments.actor_id')
				->where('assignments.serie_id', '=', $serie->id)
				->select(DB::raw('actors.id AS actors_id, actors.name AS actors_name'))
				->orderBy('actors.name', 'asc')
				->groupBy('actors.id')
				->get();
			$words_count = DB::select(DB::raw("SELECT table1.episodes_number AS episodes_number, table1.characters_id AS characters_id, table1.characters_name AS characters_name, SUM(table1.contagem) AS contagem_palavras FROM (select episodes.number AS episodes_number, characters.id AS characters_id, characters.name AS characters_name, wordcount(scripts.speech) As contagem FROM characters, scripts, episodes WHERE characters.id = scripts.character_id AND scripts.episode_id = episodes.id AND episodes.serie_id = '$serie_id' ORDER BY episodes.number ASC) AS table1 GROUP BY episodes_number, table1.characters_name ORDER BY table1.episodes_number, table1.characters_name ASC"));
			$speeches_count = DB::select(DB::raw("SELECT episodes.number AS episodes_number, characters.id AS characters_id, characters.name AS characters_name, COUNT(scripts.id) As contagem_takes FROM characters, scripts, episodes WHERE characters.id = scripts.character_id AND scripts.episode_id = episodes.id AND episodes.serie_id = '$serie_id' GROUP BY episodes.number, characters.id ORDER BY episodes.number ASC"));
			Excel::create($serie_name, function($excel) use($serie, $assigned, $speeches_count, $episodes_numbers, $actors, $words_count){
				$excel->setTitle('112 Studios - ' . $serie->original_name);
				$excel->setCreator('112 Studios')->setCompany('112 Studios');
				$excel->setDescription('Valores de estatistica para a serie ' . $serie->original_name);
				$excel->sheet('estatistica', function($sheet) use($serie, $assigned, $speeches_count, $episodes_numbers, $words_count){
					$sheet->mergeCells('B1:G1');
					$sheet->cell('A1', function($cell) {
						$cell->setValue('Nome da série');
						$cell->setFontWeight('bold');
						$cell->setAlignment('center');
						$cell->setValignment('center');
					});
					$sheet->cell('B1', function($cell) use($serie){
						$cell->setValue($serie->original_name);
						$cell->setAlignment('left');
						$cell->setValignment('center');
					});
					
					$sheet->mergeCells('A3:A4');
					$sheet->mergeCells('B3:B4');
					$sheet->mergeCells('C3:C4');
					$sheet->mergeCells('D3:D4');
					$sheet->mergeCells('E3:E4');
					
					$sheet->cell('A3', function($cell) {
						$cell->setValue('Personagem');
						$cell->setFontWeight('bold');
						$cell->setAlignment('center');
						$cell->setValignment('center');
					});
					$sheet->cell('B3', function($cell) {
						$cell->setValue('Género');
						$cell->setFontWeight('bold');
						$cell->setAlignment('center');
						$cell->setValignment('center');
					});
					$sheet->cell('C3', function($cell) {
						$cell->setValue('Actor');
						$cell->setFontWeight('bold');
						$cell->setAlignment('center');
						$cell->setValignment('center');
					});
					$sheet->cell('D3', function($cell) {
						$cell->setValue('Total de Takes');
						$cell->setFontWeight('bold');
						$cell->setAlignment('center');
						$cell->setValignment('center');
					});
					$sheet->cell('E3', function($cell) {
						$cell->setValue('Total de Palavras');
						$cell->setFontWeight('bold');
						$cell->setAlignment('center');
						$cell->setValignment('center');
					});
					
					$line = 5;
					foreach($assigned as $assign){
						$sheet->cell('A'.$line, function($cell) use($assign) {
							$cell->setValue($assign['character_name']);
							$cell->setAlignment('center');
							$cell->setValignment('center');
						});	
						$sheet->cell('B'.$line, function($cell) use($assign) {
							$cell->setValue($assign['character_gender']);
							$cell->setAlignment('center');
							$cell->setValignment('center');
						});	
						$sheet->cell('C'.$line, function($cell) use($assign) {
							$cell->setValue($assign['actor_name']);
							$cell->setAlignment('center');
							$cell->setValignment('center');
						});	
						
						$ep_col = array();
						$column1 = 'F';
						$column2 = 'G';
						
						foreach($episodes_numbers as $episode_number){
							$coord = $column1 . '3:' . $column2 . '3';
							
							$sheet->mergeCells($coord);
							
							$sheet->cell($column1 . '3', function($cell) use($episode_number) {
									$cell->setValue('Ep. ' . $episode_number->episodes_number);
									$cell->setFontWeight('bold');
									$cell->setAlignment('center');
									$cell->setValignment('middle');
							});
							
							$sheet->cell($column1 . '4', function($cell) use($episode_number) {
									$cell->setValue('Takes');
									$cell->setFontWeight('bold');
									$cell->setAlignment('center');
									$cell->setValignment('middle');
							});
							
							$sheet->cell($column2 . '4', function($cell) use($episode_number) {
									$cell->setValue('Palavras');
									$cell->setFontWeight('bold');
									$cell->setAlignment('center');
									$cell->setValignment('middle');
							});
							
							$ep_col[$episode_number->episodes_number] = array($column1, $column2);
							$column1++;
							$column1++;
							$column2++;
							$column2++;
						}
						
						$wc_total = 0;
						$tk_total = 0;
						foreach($speeches_count as $speech_count){
							if($assign['character_id'] == $speech_count->characters_id){
								$col1 = $ep_col[$speech_count->episodes_number][0];
								$col2 = $ep_col[$speech_count->episodes_number][1];
								$wc = 0;
								foreach($words_count as $word_count){
									if($word_count->characters_id == $speech_count->characters_id && $word_count->episodes_number == $speech_count->episodes_number){
										$wc = $word_count->contagem_palavras;
										$wc_total += $wc;
										break;
									}
								}
								$sheet->cell($col1 . $line, function($cell) use($speech_count) {
									if($speech_count->contagem_takes == null || empty($speech_count->contagem_takes)){
										$cell->setValue('0');
									} else {
										$cell->setValue($speech_count->contagem_takes);
									}
									$cell->setAlignment('center');
									$cell->setValignment('center');
								});
								$tk_total += $speech_count->contagem_takes;
								$sheet->cell($col2 . $line, function($cell) use($wc) {
									$cell->setValue($wc);
									$cell->setAlignment('center');
									$cell->setValignment('center');
								});
							}
						}
						
						$sheet->cell('D' . $line, function($cell) use($tk_total) {
							$cell->setValue($tk_total);
							$cell->setAlignment('center');
							$cell->setValignment('middle');
						});
					
						$sheet->cell('E' . $line, function($cell) use($wc_total) {
							$cell->setValue($wc_total);
							$cell->setAlignment('center');
							$cell->setValignment('middle');
						});
						
						$line++;
					}
				});
			})->store('xls', public_path() . '/files/' . $folder);
		}
		catch(\Exception $ex)
		{
			Storage::disk('local')->deleteDirectory($folder);
			return response()->json(['error_msg' => $ex->getMessage()], 400);
		}
    }
}
