<?php

return [
    /* ------------------------------------------------------
     * Oracle database connection name.
     * ------------------------------------------------------
     */
    'connection' => env('SQL_LOADER_CONNECTION', 'oracle'),

    /* ------------------------------------------------------
     * SQL Loader binary path.
     * ------------------------------------------------------
     */
    'sqlldr' => env('SQL_LOADER_PATH', '/usr/local/bin/sqlldr'),

    /* ------------------------------------------------------
     * Disk storage to store control files.
     * ------------------------------------------------------
     */
    'disk' => env('SQL_LOADER_DISK', 'local'),

    /* ------------------------------------------------------
     * Character set for the control file.
     * Set to null to omit the CHARACTERSET definition.
     * ------------------------------------------------------
     */
    'characterset' => env('SQL_LOADER_CHARACTERSET', 'AL32UTF8'),
];
