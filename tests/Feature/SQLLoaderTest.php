<?php

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Yajra\SQLLoader\SQLLoader;
use Yajra\SQLLoader\TnsBuilder;

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
    $loader->inFile(path: $file, badFile: 'users.bad', discardFile: 'users.dis', discardMax: '1')
        ->as('users.ctl')
        ->into('users', ['id', 'name', 'email']);

    assertInstanceOf(SQLLoader::class, $loader);

    $controlFile = $loader->buildControlFile();
    assertStringContainsString('OPTIONS(skip=1, load=2)', $controlFile);
    assertStringContainsString("INFILE '{$file}'", $controlFile);
    assertStringContainsString("BADFILE 'users.bad'", $controlFile);
    assertStringContainsString("DISCARDFILE 'users.dis'", $controlFile);
    assertStringContainsString('DISCARDMAX 1', $controlFile);
    assertStringContainsString('APPEND', $controlFile);
    assertStringContainsString('INTO TABLE users', $controlFile);
    assertStringContainsString("FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'", $controlFile);
    assertStringContainsString('(', $controlFile);
    assertStringContainsString('  id,', $controlFile);
    assertStringContainsString('  name,', $controlFile);
    assertStringContainsString('  email', $controlFile);
    assertStringContainsString(')', $controlFile);
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

        return str_contains((string) $process->command, "sqlldr userid={$username}/{$password}@{$host}:{$port}/{$database} control={$controlFile}");
    });
});

test('it allows * input file', function () {
    Process::fake();

    $loader = new SQLLoader(['skip=1']);
    $loader->inFile('*')
        ->as('users.ctl')
        ->into('users', ['id', 'name', 'email'])
        ->execute();

    Process::assertRan(function (PendingProcess $process, ProcessResult $result) {
        $tns = TnsBuilder::make();
        $controlFile = storage_path('app/users.ctl');

        return str_contains((string) $process->command, "sqlldr userid={$tns} control={$controlFile}");
    });
});

test('it accepts multiple input files', function () {
    Process::fake();

    $loader = new SQLLoader(['skip=1']);
    $loader->inFile(__DIR__.'/../data/users.dat')
        ->inFile(__DIR__.'/../data/roles.dat')
        ->as('users.ctl')
        ->into('users', ['id', 'name', 'email'])
        ->execute();

    Process::assertRan(function (PendingProcess $process, ProcessResult $result) {
        $tns = TnsBuilder::make();
        $controlFile = storage_path('app/users.ctl');

        return str_contains((string) $process->command, "sqlldr userid={$tns} control={$controlFile}");
    });
});

test('it can use another database connection', function () {
    Process::fake();

    $loader = new SQLLoader(['skip=1']);
    $loader->inFile(__DIR__.'/../data/users.dat')
        ->as('users.ctl')
        ->connection('mysql')
        ->into('users', ['id', 'name', 'email'])
        ->execute();

    Process::assertRan(function (PendingProcess $process, ProcessResult $result) {
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $controlFile = storage_path('app/users.ctl');

        return str_contains((string) $process->command, "sqlldr userid={$username}/{$password}@{$host}:{$port}/{$database} control={$controlFile}");
    });
});

test('it can detect FILLER and DATE columns', function () {
    Process::fake();

    $loader = new SQLLoader();
    $loader->inFile(__DIR__.'/../data/filler.dat')
        ->as('users.ctl')
        ->withHeaders()
        ->into('users')
        ->execute();

    $controlFile = $loader->buildControlFile();
    assertStringContainsString("\"NAME\",\n", $controlFile);
    assertStringContainsString("\"EMAIL\",\n", $controlFile);
    assertStringContainsString('"PHONE" FILLER', $controlFile);
    assertStringContainsString('"CREATED_AT" DATE', $controlFile);
});

test('it can detect BOOLEAN columns and set the default value if empty', function () {
    Process::fake();

    $loader = new SQLLoader();
    $loader->inFile(__DIR__.'/../data/filler.dat')
        ->as('users.ctl')
        ->withHeaders()
        ->into('users')
        ->execute();

    $controlFile = $loader->buildControlFile();
    assertStringContainsString("\"NAME\",\n", $controlFile);
    assertStringContainsString("\"EMAIL\",\n", $controlFile);
    assertStringContainsString('"PHONE" FILLER', $controlFile);
    assertStringContainsString('"CREATED_AT" DATE', $controlFile);
    assertStringContainsString("\"IS_ACTIVE\" \"DECODE(:is_active, '', '1', :is_active)\"\n", $controlFile);
});
