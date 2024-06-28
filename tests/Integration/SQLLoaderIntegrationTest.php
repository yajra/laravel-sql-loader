<?php

use Yajra\SQLLoader\SQLLoader;

use function Pest\Laravel\assertDatabaseCount;

test('it can load data to oracle table', function () {
    $count = DB::table('users')->count();

    SQLLoader::make()
        ->inFile(__DIR__.'/../data/users.dat')
        ->withHeaders()
        ->into('users')
        ->execute();

    assertDatabaseCount('users', $count + 4);
})->skip(! file_exists('/usr/local/bin/sqlldr'), 'This test is skipped because it requires SQLLDR binary.');
