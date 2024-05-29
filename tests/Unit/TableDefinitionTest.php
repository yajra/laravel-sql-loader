<?php

use Yajra\SQLLoader\TableDefinition;

use function PHPUnit\Framework\assertEquals;

test('it can build it\'s own sql string', function () {
    $table = new TableDefinition(
        'users',
        ['id', 'name', 'email'],
        terminatedBy: ',',
        enclosedBy: '"',
        trailing: 'TRAILING NULLCOLS',
        formatOptions: [
            'DATE FORMAT "YYYY-MM-DD"',
        ]
    );

    assertEquals(
        "INTO TABLE users\nFIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'\nTRAILING NULLCOLS\nDATE FORMAT \"YYYY-MM-DD\"\n(\n  id,\n  name,\n  email\n)\n",
        $table
    );
});

test('it can build it\'s own sql string without format options', function () {
    $table = new TableDefinition(
        'users',
        ['id', 'name', 'email'],
        terminatedBy: ',',
        enclosedBy: '"',
        trailing: 'TRAILING NULLCOLS',
    );

    assertEquals(
        "INTO TABLE users\nFIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'\nTRAILING NULLCOLS\n(\n  id,\n  name,\n  email\n)\n",
        $table
    );
});

test('it can build it\'s own sql string without trailing', function () {
    $table = new TableDefinition(
        'users',
        ['id', 'name', 'email'],
        terminatedBy: ',',
        enclosedBy: '"',
    );

    assertEquals(
        "INTO TABLE users\nFIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'\n(\n  id,\n  name,\n  email\n)\n",
        $table
    );
});

test('it can build it\'s own sql string without enclosed by', function () {
    $table = new TableDefinition(
        'users',
        ['id', 'name', 'email'],
        terminatedBy: ',',
    );

    assertEquals(
        "INTO TABLE users\nFIELDS TERMINATED BY ',' (\n  id,\n  name,\n  email\n)\n",
        $table
    );
});

test('it can build it\'s own sql string without terminated by', function () {
    $table = new TableDefinition(
        'users',
        ['id', 'name', 'email'],
    );

    assertEquals(
        "INTO TABLE users\n(\n  id,\n  name,\n  email\n)\n",
        $table
    );
});

test('it can build it\'s own sql string with empty columns', function () {
    $table = new TableDefinition(
        'users',
        [],
    );

    assertEquals(
        "INTO TABLE users\n(\n)\n",
        $table
    );
});
