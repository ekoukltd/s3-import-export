<?php

namespace Ekoukltd\S3ImportExport;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ekoukltd\S3ImportExport\Skeleton\SkeletonClass
 */
class S3IOFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 's3-import-export';
    }
}
