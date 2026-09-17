<?php

use App\Providers\AppServiceProvider;
use App\Providers\CacheKeyServiceProvider;
use App\Providers\RepositoryServiceProvider;

return [
    AppServiceProvider::class,
    CacheKeyServiceProvider::class,
    RepositoryServiceProvider::class,
];
