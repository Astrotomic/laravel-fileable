<?php

return [

    /*
     * The disk on which to store added files by default.
     * Choose a disk you've configured in config/filesystems.php.
     */
    'disk' => env('FILEABLE_DISK', 'local'),


    /*
     * The file eloquent models FQCN.
     * If you want to use your own model you can define it here.
     */
    'model' => \Astrotomic\Fileable\Models\File::class,
];
