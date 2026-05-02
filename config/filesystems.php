<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'serve'  => true,
            'throw'  => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw'      => false,
        ],

        /*
        |----------------------------------------------------------------------
        | Private disk — prescription PDFs, signatures, patient documents
        |----------------------------------------------------------------------
        | All sensitive clinical files are stored here.
        | NOT publicly accessible. Served via signed URLs or controller streams.
        | Path: storage/app/private/
        |
        | Sub-directories created automatically at runtime:
        |   prescriptions/   — generated prescription PDFs
        |   signatures/      — prescriber PNG signatures (staff_id.png)
        |   labels/          — generated dispensing label PDFs
        |   documents/       — patient uploads, identity docs, GP letters
        |   dsar/            — DSAR data exports
        |   exports/         — compliance report exports
        |   invoices/        — invoice PDFs
        */
        'private' => [
            'driver'     => 'local',
            'root'       => storage_path('app/private'),
            'throw'      => true,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
