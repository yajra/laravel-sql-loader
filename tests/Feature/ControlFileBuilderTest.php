<?php

use Yajra\SQLLoader\ControlFileBuilder;
use Yajra\SQLLoader\SQLLoader;

test('it can build a control file', function () {
    $loader = new SQLLoader(['skip=1', 'load=2']);
    $loader->inFile(__DIR__.'/../data/users.dat')
        ->as('users.ctl')
        ->into(
            table: 'users',
            columns: ['id', 'name', 'email'],
            trailing: 'TRAILING NULLCOLS'
        );

    $ctl = new ControlFileBuilder($loader);
    $controlFile = $ctl->build();

    expect($controlFile)->toBeString()
        ->and($controlFile)->toContain('OPTIONS(skip=1, load=2)')
        ->and($controlFile)->toContain("INFILE '".__DIR__."/../data/users.dat'")
        ->and($controlFile)->toContain('APPEND')
        ->and($controlFile)->toContain('INTO TABLE users')
        ->and($controlFile)->toContain("FIELDS TERMINATED BY ','")
        ->and($controlFile)->toContain('OPTIONALLY')
        ->and($controlFile)->toContain("ENCLOSED BY '\"'")
        ->and($controlFile)->toContain('TRAILING NULLCOLS')
        ->and($controlFile)->toContain('(')
        ->and($controlFile)->toContain('id,')
        ->and($controlFile)->toContain('name,')
        ->and($controlFile)->toContain('email')
        ->and($controlFile)->toContain(')');
});

test('it can build multiple input files', function () {
    $loader = new SQLLoader(['skip=1', 'load=2']);
    $loader->inFile(__DIR__.'/../data/users.dat')
        ->inFile(__DIR__.'/../data/roles.dat')
        ->as('users.ctl')
        ->into(
            table: 'users',
            columns: ['id', 'name', 'email'],
            trailing: 'TRAILING NULLCOLS'
        );

    $ctl = new ControlFileBuilder($loader);
    $controlFile = $ctl->build();

    expect($controlFile)->toBeString()
        ->and($controlFile)->toContain("INFILE '".__DIR__."/../data/users.dat'")
        ->and($controlFile)->toContain("INFILE '".__DIR__."/../data/roles.dat'");
});

test('it can build with bad file, discard file and discard max', function () {
    $loader = new SQLLoader(['skip=1', 'load=2']);
    $loader->inFile(__DIR__.'/../data/users.dat', badFile: 'users.bad', discardFile: 'users.dis', discardMax: '1')
        ->as('users.ctl')
        ->into(
            table: 'users',
            columns: ['id', 'name', 'email'],
            trailing: 'TRAILING NULLCOLS'
        );

    $ctl = new ControlFileBuilder($loader);
    $controlFile = $ctl->build();

    expect($controlFile)->toBeString()
        ->and($controlFile)->toContain("BADFILE 'users.bad'")
        ->and($controlFile)->toContain("DISCARDFILE 'users.dis'")
        ->and($controlFile)->toContain('DISCARDMAX 1');
});
