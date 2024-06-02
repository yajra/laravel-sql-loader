<?php

use Yajra\SQLLoader\TnsBuilder;

use function PHPUnit\Framework\assertEquals;

test('it can generate a tns string', function () {
    $username = config('database.connections.oracle.username');
    $password = config('database.connections.oracle.password');
    $host = config('database.connections.oracle.host');
    $port = config('database.connections.oracle.port');
    $database = config('database.connections.oracle.database');

    assertEquals("$username/$password@$host:$port/$database", TnsBuilder::make());
});

test('it accepts a connection', function () {
    $username = config('database.connections.mysql.username');
    $password = config('database.connections.mysql.password');
    $host = config('database.connections.mysql.host');
    $port = config('database.connections.mysql.port');
    $database = config('database.connections.mysql.database');

    assertEquals("$username/$password@$host:$port/$database", TnsBuilder::make('mysql'));
});
