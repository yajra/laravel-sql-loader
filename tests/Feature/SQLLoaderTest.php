<?php

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Yajra\SQLLoader\SQLLoader;
use Yajra\SQLLoader\TnsBuilder;

test('it throws an error on invalid/non-existent file', function () {
    $loader = new SQLLoader(['skip=1', 'load=2']);
    $loader->inFile('file.dat');
})->throws(InvalidArgumentException::class, 'File [file.dat] does not exist.');

test('it can create an instance and build the command', function () {
    expect(SQLLoader::make())->toBeInstanceOf(SQLLoader::class);

    $file = __DIR__.'/../data/users.dat';

    $loader = new SQLLoader(['skip=1', 'load=2']);
    $loader->inFile(path: $file, badFile: 'users.bad', discardFile: 'users.dis', discardMax: '1')
        ->as('users.ctl')
        ->into('users', ['id', 'name', 'email']);

    expect($loader)->toBeInstanceOf(SQLLoader::class);

    $controlFile = $loader->buildControlFile();

    expect($controlFile)->toBeString()
        ->toContain('OPTIONS(skip=1, load=2)')
        ->toContain("INFILE '{$file}'")
        ->toContain("BADFILE 'users.bad'")
        ->toContain("DISCARDFILE 'users.dis'")
        ->toContain('DISCARDMAX 1')
        ->toContain('APPEND')
        ->toContain('INTO TABLE users')
        ->toContain("FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'")
        ->toContain('(')
        ->toContain('  id,')
        ->toContain('  name,')
        ->toContain('  email')
        ->toContain(')');
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

    expect($controlFile)->toBeString()
        ->toContain("INFILE '".__DIR__.'/../data/filler.dat')
        ->toContain('INTO TABLE users')
        ->toContain('FIELDS TERMINATED BY \',\' OPTIONALLY ENCLOSED BY \'"\'')
        ->toContain('TRAILING NULLCOLS')
        ->toContain('(')
        ->toContain('"NAME",')
        ->toContain('"EMAIL",')
        ->toContain('"PHONE" FILLER', $controlFile)
        ->toContain('"CREATED_AT" DATE')
        ->toContain(')');
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

    expect($controlFile)->toBeString()
        ->toContain('"NAME"')
        ->toContain('"EMAIL"')
        ->toContain('"PHONE" FILLER')
        ->toContain('"CREATED_AT" DATE')
        ->toContain('"IS_ACTIVE" "DECODE(:is_active, \'\', \'1\', :is_active)"');
});

test('it can detect BOOLEAN columns and set the default value to 0 if no default was defined', function () {
    Process::fake();

    $loader = new SQLLoader();
    $loader->inFile(__DIR__.'/../data/filler.dat')
        ->as('users.ctl')
        ->withHeaders()
        ->into('users_bool_no_default')
        ->execute();

    $controlFile = $loader->buildControlFile();

    expect($controlFile)->toBeString()
        ->toContain('"NAME"')
        ->toContain('"EMAIL"')
        ->toContain('"PHONE" FILLER')
        ->toContain('"CREATED_AT" DATE')
        ->toContain('"IS_ACTIVE" "DECODE(:is_active, \'\', \'0\', :is_active)"');
});

test('it accepts withHeader on input file with wildcard', function () {
    Process::fake();

    $loader = new SQLLoader();
    $path = __DIR__.'/../data/wildcard/*.dat';
    $loader->inFile($path)
        ->as('users.ctl')
        ->withHeaders()
        ->into('users')
        ->execute();

    $controlFile = $loader->buildControlFile();

    expect($controlFile)->toBeString()
        ->toContain("INFILE '{$path}'")
        ->toContain('INTO TABLE users')
        ->toContain('FIELDS TERMINATED BY \',\' OPTIONALLY ENCLOSED BY \'"\'')
        ->toContain('TRAILING NULLCOLS')
        ->toContain('(')
        ->toContain('"NAME",')
        ->toContain('"EMAIL"')
        ->toContain(')');
});

test('it can set the default date format', function () {
    Process::fake();

    $loader = new SQLLoader();
    $loader->inFile(__DIR__.'/../data/filler.dat')
        ->as('users.ctl')
        ->dateFormat('YYYY-MM-DD')
        ->withHeaders()
        ->into('users')
        ->execute();

    $controlFile = $loader->buildControlFile();

    expect($controlFile)->toBeString()
        ->toContain("DATE FORMAT 'YYYY-MM-DD'\n")
        ->toContain("TIMESTAMP FORMAT 'YYYY-MM-DD'\n")
        ->toContain("TIMESTAMP WITH TIME ZONE 'YYYY-MM-DD'\n")
        ->toContain("TIMESTAMP WITH LOCAL TIME ZONE 'YYYY-MM-DD'\n");
});

test('it can process constants columns', function () {
    Process::fake();

    $loader = new SQLLoader();
    $loader->inFile(__DIR__.'/../data/users.dat')
        ->as('users.ctl')
        ->withHeaders()
        ->constants([
            'created_by CONSTANT 1',
            'created_at EXPRESSION "current_timestamp(3)"',
            'updated_by CONSTANT 1',
            'updated_at EXPRESSION "current_timestamp(3)"',
        ])
        ->into('users')
        ->execute();

    $controlFile = $loader->buildControlFile();

    expect($controlFile)->toBeString()
        ->toContain("\"NAME\",\n")
        ->toContain("\"EMAIL\",\n")
        ->toContain("created_by CONSTANT 1,\n")
        ->toContain("created_at EXPRESSION \"current_timestamp(3)\",\n")
        ->toContain("updated_by CONSTANT 1,\n")
        ->toContain("updated_at EXPRESSION \"current_timestamp(3)\"\n");
});

test('it can set input file os file proc clause', function () {
    Process::fake();

    $loader = new SQLLoader();
    $loader->inFile(__DIR__.'/../data/users.dat', osFileProcClause: 'os file proc')
        ->as('users.ctl')
        ->withHeaders()
        ->into('users')
        ->execute();

    $controlFile = $loader->buildControlFile();

    expect($controlFile)->toBeString()
        ->toContain("INFILE '".__DIR__."/../data/users.dat' \"os file proc\"")
        ->toContain('INTO TABLE users')
        ->toContain('FIELDS TERMINATED BY \',\' OPTIONALLY ENCLOSED BY \'"\'')
        ->toContain('TRAILING NULLCOLS')
        ->toContain('(')
        ->toContain('"NAME",')
        ->toContain('"EMAIL"')
        ->toContain(')');
});
