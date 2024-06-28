<?php

use Yajra\SQLLoader\CsvFile;

describe('CSV File', function () {
    test('it can create a blank csv file', function () {
        $file = CsvFile::blank(storage_path('test.csv'));

        expect($file)->toBeString()
            ->and($file)->toBe(storage_path('test.csv'))
            ->and(file_exists($file))->toBeTrue();
    });

    test('it can create a csv file', function () {
        $file = CsvFile::create(storage_path('test.csv'));

        expect($file)->toBeString()
            ->and($file)->toBe(storage_path('test.csv'))
            ->and(file_exists($file))->toBeTrue();
    });

    test('it can make a csv file with array content', function () {
        $file = CsvFile::make(storage_path('test.csv'), 'w');
        $file->append(['id', 'name', 'email']);

        expect($file)->toBeInstanceOf(CsvFile::class)
            ->and($file->isEmpty())->toBeFalse()
            ->and($file->get())->toContain('id,name,email'.PHP_EOL);
    });

    test('it can have a csv header', function () {
        $file = CsvFile::make(storage_path('test.csv'), 'w');
        $file->headers(['id', 'name', 'email']);

        expect($file)->toBeInstanceOf(CsvFile::class)
            ->and($file->isEmpty())->toBeFalse()
            ->and($file->get())->toContain('id,name,email'.PHP_EOL);
    });

    test('it can insert data using array', function () {
        $file = CsvFile::make(storage_path('test.csv'), 'w');
        $file->headers(['id', 'name', 'email']);
        $file->insert([
            ['1', 'John Doe', 'email@example.com'],
            ['2', 'Jane Doe', 'e'],
            ['3', 'Jane, Doe', '3'],
            ['3', 'Jane" Doe', '3'],
        ]);

        $content = $file->get();

        expect($file)->toBeInstanceOf(CsvFile::class)
            ->and($file->isEmpty())->toBeFalse()
            ->and($content)->toContain('1,"John Doe",email@example.com'.PHP_EOL)
            ->and($content)->toContain('2,"Jane Doe",e'.PHP_EOL)
            ->and($content)->toContain('3,"Jane, Doe",3'.PHP_EOL)
            ->and($content)->toContain('3,"Jane"" Doe",3'.PHP_EOL);
    });

    test('it can sanitize headers', function () {
        $headers = CsvFile::make(__DIR__.'/../data/bad-header.dat', 'r')->getHeaders();
        expect($headers)->toBe([
            'NAME',
            'EMAIL',
            'PHONE',
        ]);
    });
});
