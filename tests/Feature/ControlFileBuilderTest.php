<?php

use Yajra\SQLLoader\ControlFileBuilder;
use Yajra\SQLLoader\SQLLoader;

test('it can build a control file', function () {
    $loader = new SQLLoader(['skip=1', 'load=2']);
    $loader->inFile(__DIR__.'/../data/users.dat')
        ->as('users.ctl')
        ->into('users', ['id', 'name', 'email']);

    $ctl = new ControlFileBuilder($loader);
    $controlFile = $ctl->build();

    expect($controlFile)->toBeString()
        ->and($controlFile)->toContain('OPTIONS(skip=1 load=2)')
        ->and($controlFile)->toContain("INFILE '".__DIR__."/../data/users.dat'")
        ->and($controlFile)->toContain("users.bad'")
        ->and($controlFile)->toContain("users.dis'")
        ->and($controlFile)->toContain('APPEND')
        ->and($controlFile)->toContain('INTO TABLE users')
        ->and($controlFile)->toContain("FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'")
        ->and($controlFile)->toContain('(id, name, email)');
});
