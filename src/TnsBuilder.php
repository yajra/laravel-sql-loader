<?php

declare(strict_types=1);

namespace Yajra\SQLLoader;

class TnsBuilder
{
    public static function make(): string
    {
        $connection = config('sql-loader.connection', 'oracle');
        $username = config('database.connections.'.$connection.'.username');
        $password = config('database.connections.'.$connection.'.password');
        $host = config('database.connections.'.$connection.'.host');
        $port = config('database.connections.'.$connection.'.port');
        $database = config('database.connections.'.$connection.'.database');

        return $username.'/'.$password.'@'.$host.':'.$port.'/'.$database;
    }
}
