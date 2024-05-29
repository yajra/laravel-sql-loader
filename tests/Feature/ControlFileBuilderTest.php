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
        ->and($controlFile)->toContain("users.bad'")
        ->and($controlFile)->toContain("users.dis'")
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
