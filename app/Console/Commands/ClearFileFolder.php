<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Storage;
use Response;
use Carbon\Carbon;
use File;

class ClearFileFolder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:file_folder';

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
		try
		{
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
		}
		catch(\Exception $ex)
		{
			return response()->json(['error_msg' => $ex->getMessage()], 400);
		}
    }
}
