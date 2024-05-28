<?php

use Yajra\SQLLoader\SQLLoader;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertStringContainsString;

test('it can create an instance and build the command', function () {
    assertInstanceOf(SQLLoader::class, SQLLoader::make());

    $loader = new SQLLoader(['skip=1', 'load=2']);
    assertInstanceOf(SQLLoader::class, $loader);
    assertEquals('skip=1 load=2', $loader->buildOptions());

    $loader->inFile('file.sql');
    assertStringContainsString('file.sql', $loader->buildCommand());
});
