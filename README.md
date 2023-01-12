# Save specific tables to remote storage

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ekoukltd/s3-import-export.svg?style=flat-square)](https://packagist.org/packages/ekoukltd/laravel-import-export)
[![Total Downloads](https://img.shields.io/packagist/dt/ekoukltd/s3-import-export.svg?style=flat-square)](https://packagist.org/packages/ekoukltd/laravel-import-export)
![GitHub Actions](https://github.com/ekoukltd/s3-import-export/actions/workflows/main.yml/badge.svg)

Ever needed to copy selected models between server environments?  Or seed a database with latest content?

This package exports models to S3 or other remote disks as a JSON file.

#### This is not designed to be a robust backup service
Just a tool to move content based models between different environments without any hassle.


## Installation

Install the package via composer:

```bash
composer require ekoukltd/s3-import-export
```

## Configuration

Publish the config file to config/s3-import-export.php
```php
php artisan vendor:publish --provider="Ekoukltd\S3ImportExport\S3IOServiceProvider" --tag="config"
```

and define which what should be exported to where.
For S3 you need to have setup league/filesystem see: https://laravel.com/docs/9.x/filesystem#s3-driver-configuration
```php
    //Data will be exported to this disk -> choose from config.filesystems.disks options
	//Note that if using s3 a local copy will also be created in storage when importing
	//When running tests local storage copy will be used.
	'disk'           => 's3',
	
	//Where to stick em.  Note trailing slash
	'export_dir' => 'content-export/',
	
	//Add your models to import and export here
	'content_models' => [
		//Json object exports are ideal for copying content type data like pages, posts and templates 
		//without affecting the reset of the database
		'App\\Models\\Pages',
		'App\\Models\\Posts',
		'App\\Models\\EmailTemplates',
	],
	 
	 /** IMPORTANT **/
	//Carbon dates export with a timezone by default, which throws an error when importing to sql
	//If your using timestamps either set any other date fields here 
	'date_columns' => ['created_at', 'updated_at', 'deleted_at']
    
    //or set the format in the model
    protected $casts = [
        'email_verified_at' => 'datetime:Y-m-d H:i:s'
        'my_date_field'     => 'datetime:Y-m-d H:i:s',
    ];
```

## Usage

```php
#Export from the CLI
php artisan data:export

#Import
php artisan data:import

#Or in a scheduled task:
Artisan::call('data:export');
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email support@ekouk.com instead of using the issue tracker.

## Credits

-   [Lee Evans](https://github.com/ekoukltd)
-   [Eko UK](https://www.ekouk.com)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
