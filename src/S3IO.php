<?php

namespace Ekoukltd\S3ImportExport;

use Aws\S3\Exception\S3Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Output\ConsoleOutput;


class S3IO
{
	/**
	 * Begin Import / Export Routines
	 * Saves and retrives data to S3 to allow moving content between installations.
	 */
	public static function exportSetupData() {
		$exports = self::getContentModels();
		$res     = "Exporting Tables<br>";
		foreach ($exports as $model) {
			$res .= "...".self::getTableNameFromModel($model)."<br/>";
			self::exportModel($model);
		}
		return $res;
	}
	
	public static function importSetupData() {
		$imports = self::getContentModels();
		$res     = "Importing Tables<br>";
		foreach ($imports as $model) {
			if($result = self::importModel($model)) {
				$res .= "...$result<br>";
			}
			else {
				return false;
			}
		}
		return $res;
	}
	
	/**
	 * @return array[]
	 */
	public static function getContentModels():array {
		return config('s3-import-export.content_models');
	}
	
	private static function getDisk():string {
		return config('s3-import-export.disk') ?? 'local';
	}
	
	private static function dateColumns():array {
		return config('s3-import-export.date_columns');
	}
	
	private static function exportDir(): string {
		return config('s3-import-export.export_dir');
	}
	
	/**
	 * @param $modelName
	 *
	 * @return string
	 */
	private static function getTableNameFromModel($model) {
		$model = new $model();
		return $model->getTable();
	}
	
	/**
	 * @param $model
	 */
	private static function exportModel($model) {
		
		$columns = DB::getSchemaBuilder()->getColumnListing(self::getTableNameFromModel($model));
		$excludeColumns = config('s3-import-export.excluded_columns')??[];
		$selectedColumns = array_diff($columns, $excludeColumns);
		
		if(!$json = DB::table(self::getTableNameFromModel($model))->select($selectedColumns)->get()->toJson()) {
			Log::emergency('Error Exporting Json Table');
			return false;
		}
		
		$filename = self::getFilenameFromModel($model);
		
		$disk   = self::getDisk();
		$fs     = Storage::disk($disk);
		$output = new ConsoleOutput();
		if($fs->put($filename, $json, config('s3-import-export.s3permission'))) {
			$output->writeln("<info>$filename saved to $disk.</info>");
		}
		else {
			$output->writeln("<error>Error saving: $filename</error>");
		}
	}
	
	/**
	 * @param $model
	 *
	 * @return string
	 */
	private static function getFilenameFromModel($model) {
		return self::exportDir().class_basename($model).".json";
	}
	
	private static function getModelFromStorage($model) {
		$diskIsOnline = false;
		$disk         = self::getDisk();
		switch ($disk) {
			case ('local'):
				$diskIsOnline = false;
				break;
			case ('s3'):
				//Check we have necessary access and we're not in testing environment
				if(config('filesystems.disks.s3.key') && config('filesystems.disks.s3.secret') && self::is_connected() && !App::environment(['testing'])) {
					try {
						if(Storage::disk('s3')->exists(self::exportDir())) {
							$diskIsOnline = true;
						}
					}
					catch (S3Exception $ex) {
						//Do Nothing we will use the local copy
					}
				}
				break;
			default:
				$diskIsOnline = self::is_connected() && !App::environment(['testing']);
		}
		
		try {
			if($diskIsOnline) {
				$filename = self::getFilenameFromModel($model);
				Storage::disk('local')->put($filename, Storage::disk('s3')->get($filename));
			}
		}
		catch (\Exception $ex) {
			echo "File $filename copy encountered an error: ".$ex->getMessage()."\r\n";
		}
	}
	
	/**
	 * @param $modelName
	 *
	 * @return string tablename|false
	 */
	private static function importModel($model) {
		try {
			self::getModelFromStorage($model);
			if($data = self::getData($model)) {
				//Convert Arrays to Json String
				foreach ($data as $dataKey => $row) {
					foreach ($row as $key => $value) {
						if(is_array($value)) {
							$data[ $dataKey ][ $key ] = json_encode($value);
						}
						if(in_array($key, self::dateColumns()) && $value !== null) {
							$data[ $dataKey ][ $key ] = Carbon::create($value)->toDateTimeString();
						}
					}
				}
				if(count($data)) {
					$table = self::getTableNameFromModel($model);
					DB::statement("SET foreign_key_checks=0");
					DB::table($table)->truncate();
					DB::statement("SET foreign_key_checks=1");
					DB::table($table)->insert($data);
					return $table;
				}
			}
		}
		catch (\Exception $ex) {
			echo "Importer encountered an error: ".$ex->getMessage()."\r\n";
		}
		return false;
	}
	
	private static function getData($model) {
		try {
			$filename = self::getFilenameFromModel($model);
			
			if($exists = Storage::disk('local')->exists($filename)) {
				return json_decode(Storage::disk('local')->get($filename), true);
			}
		}
		catch (\Exception $ex) {
			Log::emergency("Model $model import encountered an error: ".$ex->getMessage());
		}
	}
	
	private static function is_connected() {
		$connected = @fsockopen("aws.amazon.com", 80);
		if($connected) {
			fclose($connected);
			return true;
		}
		return false;
	}
}
