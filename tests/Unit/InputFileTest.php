<?php

use Yajra\SQLLoader\InputFile;

test('it can build it\'s own sql string', function () {
    $inputFile = new InputFile('path/to/file');

    expect($inputFile->__toString())->toBe("INFILE 'path/to/file'");
});

test('it can build it\'s own sql string with bad file', function () {
    $inputFile = new InputFile('path/to/file', 'path/to/badfile');

    expect($inputFile->__toString())->toBe("INFILE 'path/to/file' BADFILE 'path/to/badfile'");
});

test('it can build it\'s own sql string with discard file', function () {
    $inputFile = new InputFile('path/to/file', 'path/to/badfile', 'path/to/discardfile');

    expect($inputFile->__toString())->toBe("INFILE 'path/to/file' BADFILE 'path/to/badfile' DISCARDFILE 'path/to/discardfile'");
});

test('it can build it\'s own sql string with discard max', function () {
    $inputFile = new InputFile('path/to/file', 'path/to/badfile', 'path/to/discardfile', 1);

    expect($inputFile->__toString())->toBe("INFILE 'path/to/file' BADFILE 'path/to/badfile' DISCARDFILE 'path/to/discardfile' DISCARDMAX 1");
});

test('it can build it\'s own sql string with discard max as string', function () {
    $inputFile = new InputFile('path/to/file', 'path/to/badfile', 'path/to/discardfile', '1');

    expect($inputFile->__toString())->toBe("INFILE 'path/to/file' BADFILE 'path/to/badfile' DISCARDFILE 'path/to/discardfile' DISCARDMAX 1");
});

test('it can build with os file proc clause', function () {
    $inputFile = new InputFile('path/to/file', 'path/to/badfile', 'path/to/discardfile', '1', 'OS_FILE_PROC');

    expect($inputFile->__toString())->toBe("INFILE 'path/to/file' \"OS_FILE_PROC\" BADFILE 'path/to/badfile' DISCARDFILE 'path/to/discardfile' DISCARDMAX 1");
});

test('it accepts wildcard in file path', function () {
    $inputFile = new InputFile('path/to/chunk-*.dat');

    expect($inputFile->__toString())->toBe("INFILE 'path/to/chunk-*.dat'");

    $inputFile = new InputFile('path/to/chunk-?.dat');

    expect($inputFile->__toString())->toBe("INFILE 'path/to/chunk-?.dat'");
});
