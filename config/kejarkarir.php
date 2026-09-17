<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Default number of records returned per page for list endpoints, and the
    | maximum allowed value to prevent abuse. Semua list endpoint wajib
    | memakai pagination sesuai aturan Ecampuz.
    |
    */

    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],
];
