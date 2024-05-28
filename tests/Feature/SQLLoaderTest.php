<?php

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Yajra\SQLLoader\SQLLoader;

use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertStringContainsString;

test('it throws an error on invalid/non-existent file', function () {
    $loader = new SQLLoader(['skip=1', 'load=2']);
    $loader->inFile('file.dat');
})->throws(InvalidArgumentException::class, 'File [file.dat] does not exist.');

test('it can create an instance and build the command', function () {
    assertInstanceOf(SQLLoader::class, SQLLoader::make());

    $file = __DIR__.'/../data/users.dat';

    $loader = new SQLLoader(['skip=1', 'load=2']);
    $loader->inFile($file)
        ->as('users.ctl')
        ->into('users', ['id', 'name', 'email']);

    assertInstanceOf(SQLLoader::class, $loader);

    $controlFile = $loader->buildControlFile();
    assertStringContainsString('OPTIONS(skip=1 load=2)', $controlFile);
    assertStringContainsString("INFILE '{$file}'", $controlFile);
    assertStringContainsString("users.bad'", $controlFile);
    assertStringContainsString("users.dis'", $controlFile);
    assertStringContainsString('APPEND', $controlFile);
    assertStringContainsString('INTO TABLE users', $controlFile);
    assertStringContainsString("FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'", $controlFile);
    assertStringContainsString('(id, name, email)', $controlFile);
});

test('sqlldr process is invoked', function () {
    Process::fake();

    $file = __DIR__.'/../data/users.dat';

    $loader = new SQLLoader(['skip=1']);
    $loader->inFile($file)
        ->as('users.ctl')
        ->into('users', ['id', 'name', 'email'])
        ->execute();

    Process::assertRan(function (PendingProcess $process, ProcessResult $result) {
        $username = config('database.connections.oracle.username');
        $password = config('database.connections.oracle.password');
        $host = config('database.connections.oracle.host');
        $port = config('database.connections.oracle.port');
        $database = config('database.connections.oracle.database');

        $controlFile = storage_path('app/users.ctl');

        return str_contains($process->command, "sqlldr userid={$username}/{$password}@{$host}:{$port}/{$database} control={$controlFile}");
    });
});
