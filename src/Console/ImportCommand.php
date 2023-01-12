<?php

namespace Ekoukltd\S3ImportExport\Console;

use Ekoukltd\S3ImportExport\S3IO;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportCommand extends Command
{
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'data:import';
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Imports content JSON files from S3 to tables';
	
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
	 * @return int
	 */
	public function handle()
	{
		try {
			$res = S3IO::importSetupData();
			$this->info($res);
		}
		catch (\Exception $ex) {
			Log::error("Error Importing Data. ".$ex->getMessage());
			return Command::FAILURE;
		}
		
		return Command::SUCCESS;
	}
}
