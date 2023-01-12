<?php

namespace Ekoukltd\S3ImportExport;

use Ekoukltd\S3ImportExport\Console\ExportCommand;
use Ekoukltd\S3ImportExport\Console\ImportCommand;
use Illuminate\Support\ServiceProvider;

class S3IOServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
		
        if ($this->app->runningInConsole()) {
			
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('s3-import-export.php'),
            ], 'config');
			
			
	
	        $this->commands([
		                        ExportCommand::class,
		                        ImportCommand::class
	                        ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 's3-import-export');

        // Register the main class to use with the facade
        $this->app->singleton('s3-import-export', function () {
            return new S3IO;
        });
    }
}
