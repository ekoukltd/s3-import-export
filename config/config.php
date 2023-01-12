<?php

return [
	
	//Data will be exported to this disk -> choose from config.filesystems.disks
	//Note that if using s3 a local copy will also be created in storage when importing
	'disk'           => 's3',
	
	//Where to stick em.  Note trailing slash
	'export_dir' => 'content-export/',
	
	//Add your models to import and export here
	'content_models' => [
		//This is a demo example - in practice you probably wouldn't use this routine for exporting Users - but you could
		//Json object exports are ideal for copying content type data between environments, like pages, posts and templates
		'App\\Models\\User',
	],
	
	//Carbon dates export with a timezone by default, which throws an error when importing to sql
	//These fields will be changed to datetime:Y-m-d H:i:s on import
	'date_columns' => ['created_at', 'updated_at', 'deleted_at']
];