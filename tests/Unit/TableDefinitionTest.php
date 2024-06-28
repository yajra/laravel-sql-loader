<?php

use Yajra\SQLLoader\TableDefinition;

use function PHPUnit\Framework\assertEquals;

describe('Table Definition Builder', function () {

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
            "INTO TABLE users\nFIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'\nDATE FORMAT \"YYYY-MM-DD\"\nTRAILING NULLCOLS\n(\n  id,\n  name,\n  email\n)\n",
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
            "INTO TABLE users\nFIELDS TERMINATED BY ','\n(\n  id,\n  name,\n  email\n)\n",
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
            $table->__toString()
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

    test('it can build with when clause', function () {
        $table = new TableDefinition(
            'users',
            ['id', 'name', 'email'],
            when: 'id > 0',
        );

        assertEquals(
            "INTO TABLE users\nWHEN id > 0\n(\n  id,\n  name,\n  email\n)\n",
            $table
        );
    });

    test('it can build with csv format', function () {
        $table = new TableDefinition(
            'users',
            ['id', 'name', 'email'],
            csv: true,
        );

        assertEquals(
            "INTO TABLE users\nFIELDS CSV WITH EMBEDDED\n(\n  id,\n  name,\n  email\n)\n",
            $table->__toString()
        );
    });

    test('it can build with csv format without embedded', function () {
        $table = new TableDefinition(
            'users',
            ['id', 'name', 'email'],
            csv: true,
            withEmbedded: false,
        );

        assertEquals(
            "INTO TABLE users\nFIELDS CSV WITHOUT EMBEDDED\n(\n  id,\n  name,\n  email\n)\n",
            $table
        );
    });
});
