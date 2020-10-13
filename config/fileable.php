<?php

return [
    /*
     * The disk on which to store added files by default.
     * Choose a disk you've configured in config/filesystems.php.
     */
    'disk' => 'local',

    /*
     * The file eloquent models FQCN.
     * If you want to use your own model you can define it here.
     * It should implement the Astrotomic\Fileable\Contracts\File interface
     * and extend Illuminate\Database\Eloquent\Model class.
     */
    'file_model' => \Astrotomic\Fileable\Models\File::class,

    /*
     * This is the name of the table that will be created by the migration and
     * used by the File model shipped with this package.
     */
    'table_name' => 'files',

    /*
     * This is the database connection that will be used by the migration and
     * the File model shipped with this package. In case it's not set
     * Laravel database.default will be used instead.
     */
    'database_connection' => null,
];
