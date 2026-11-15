<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use PDF;
use Storage;
use Zipper;
use Response;

use App\Serie;
use App\Episode;

class CreateSerieZip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:serie_zip {serie_id} {folder} {filename}';

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
		$folder = $this->argument('folder');
		$filename = $this->argument('filename');
		try
		{
			$serie_info = Serie::find($serie_id);
			$storagePath = public_path() . '/files/' . $folder;
			$storagePathToZip = $storagePath . '/';
			$storagePathToSave = $storagePath . '/' . $filename . '.zip';
			$episodes = DB::table('episodes')
						->join('series', 'episodes.serie_id', '=', 'series.id')
						->where('series.id', '=', $serie_id)
						->select('episodes.id AS episode_id', 'episodes.number AS episode_number')
						->orderBy('episodes.number', 'asc')
						->get();
			if(isset($episodes)){
				foreach($episodes as $episode){
					$episode_info = Episode::find($episode->episode_id);
					$scripts = DB::table('scripts')
							->join('episodes', 'scripts.episode_id', '=', 'episodes.id')
							->join('series', 'episodes.serie_id', '=', 'series.id')
							->join('characters', 'scripts.character_id', '=', 'characters.id')
							->where('episodes.id', '=', $episode->episode_id)
							->select('series.original_name AS serie_original_name', 'episodes.id AS episode_id', 'episodes.original_name AS episode_original_name', 'episodes.number AS episode_number', 'characters.id AS character_id', 'characters.name AS character_name', 'characters.gender AS character_gender', 'scripts.timecode AS script_timecode', 'scripts.id AS script_id', 'scripts.number AS script_number', 'scripts.speech AS script_speech')
							->orderBy('scripts.number', 'asc')
							->get();
					$pdf = PDF::loadView('pdf.episode', ['serie_info' => $serie_info, 'episode_info' => $episode_info, 'scripts' => $scripts])->setPaper('a4', 'portrait')->setWarnings(false)->save($storagePathToZip . $filename . "_" . $episode_info->number . '.pdf' );
				}
			}
			Zipper::make($storagePathToSave)->add($storagePathToZip);
		}
		catch(\Exception $ex)
		{
			Storage::disk('local')->deleteDirectory($folder);
			return response()->json(['error_msg' => $ex->getMessage()], 400);
		}
    }
}
