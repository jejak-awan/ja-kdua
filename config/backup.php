<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Archive Password
    |--------------------------------------------------------------------------
    |
    | This password is used to encrypt the backup zip archives.
    | If not set, the application key or a random string might be used as fallback.
    |
    */
    'archive_password' => env('BACKUP_ARCHIVE_PASSWORD'),
];
