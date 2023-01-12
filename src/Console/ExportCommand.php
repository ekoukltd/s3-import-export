<?php

namespace Ekoukltd\S3ImportExport\Console;

use Ekoukltd\S3ImportExport\S3IO;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExportCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'data:export';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Exports tables containing changeable content to Json arrays and saves in S3 Bucket';
	
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
		Log::info('Running Data Export');
		try {
			$res = S3IO::exportSetupData();
			$this->info($res);
		} catch (\Exception $ex){
			Log::error("Error Exporting Data. ".$ex->getMessage());
			return Command::FAILURE;
		}
		
		return Command::SUCCESS;
	}
}
