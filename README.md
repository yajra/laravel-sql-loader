# Oracle SQL Loader for Laravel

[![Continuous Integration](https://github.com/yajra/laravel-sql-loader/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/yajra/laravel-sql-loader/actions/workflows/continuous-integration.yml)
[![Static Analysis](https://github.com/yajra/laravel-sql-loader/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/yajra/laravel-sql-loader/actions/workflows/static-analysis.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/yajra/laravel-sql-loader.svg)](https://packagist.org/packages/yajra/laravel-sql-loader)
[![Total Downloads](https://poser.pugx.org/yajra/laravel-sql-loader/downloads.png)](https://packagist.org/packages/yajra/laravel-sql-loader)
[![License](https://img.shields.io/github/license/mashape/apistatus.svg)](https://packagist.org/packages/yajra/laravel-sql-loader)

A Laravel package that allows you to easily load data into Oracle database using `sqlldr`.

## Requirements

- [Oracle Instant Client with Tools Package](https://www.oracle.com/database/technologies/instant-client/macos-intel-x86-downloads.html)
- [Laravel 10.x](https://laravel.com) or higher
- [Laravel OCI8](https://yajrabox.com/docs/laravel-oci8) 10.x or higher

## Prerequisites

- Before you can use this package, you need to install the Oracle Instant Client with Tools Package. You can download the package from the [Oracle website](https://www.oracle.com/database/technologies/instant-client/macos-intel-x86-downloads.html). 
- You should also take note of the path where the `sqlldr` executable is located.
- Knowledge of how to use `sqlldr` is also required. You can read the documentation [here](https://docs.oracle.com/en/database/oracle/oracle-database/23/sutil/oracle-sql-loader.html#GUID-8D037494-07FA-4226-B507-E1B2ED10C144).

## Installation

You can install the package via composer:

```bash
composer require yajra/laravel-sql-loader:^1.0
```

## Quick Start

Below is a quick example of how to use the package:

```php
Route::get('sql-loader', function () {
    Schema::dropIfExists('employees');
    Schema::create('employees', function ($table) {
        $table->id();
        $table->string('name');
        $table->integer('dept_id');
        $table->timestamps();
    });

    Yajra\SQLLoader\CsvFile::make(database_path('files/employees.csv'), 'w')
        ->headers(['name', 'dept_id', 'created_at', 'updated_at'])
        ->insert([
            ['John Doe', 1, now(), now()],
            ['Jane Doe', 2, now(), now()],
            ['John Doe', 1, now(), now()],
            ['Jane Doe', 2, now(), now()],
        ]);

    $loader = Yajra\SQLLoader\SQLLoader::make();
    $loader->inFile(database_path('files/employees.csv'))
        ->dateFormat('YYYY-MM-DD HH24:MI:SS')
        ->withHeaders()
        ->into('employees')
        ->execute();

    return DB::table('employees')->get();
});
```

## Execution Mode

The default execution mode is `Mode::APPEND`. The package supports the following execution mode:

- `Yajra\SQLLoader\Mode::INSERT` - Insert data into table.
- `Yajra\SQLLoader\Mode::APPEND` - Append data to table.
- `Yajra\SQLLoader\Mode::REPLACE` - Replace data in table.
- `Yajra\SQLLoader\Mode::TRUNCATE` - Truncate table then insert data.

## Available Methods

### Options

You can pass additional options to the `sqlldr` command using the `options` method.

```php
$loader->options(['skip=1', 'load=1000']);
```

### Input File(/s)

You can set the input file to use for the SQL Loader command using the `inFile` method.

```php
$loader->inFile(database_path('files/employees.csv'));
```

You can also set multiple input files.

```php
$loader->inFile(database_path('files/employees.csv'))
    ->inFile(database_path('files/departments.csv')),
```

### Mode

You can set the execution mode using the `mode` method.

```php
$loader->mode(Yajra\SQLLoader\Mode::TRUNCATE);
```

### Into Table

You can set the table to load the data into using the `into` method. This method accepts the following parameters:

- [`table`](https://docs.oracle.com/en/database/oracle/oracle-database/23/sutil/oracle-sql-loader-control-file-contents.html#GUID-9E95D9E3-C554-495C-9400-A0B0840DCF35) - Specifies the table into which you load data.
- [`columns`](https://docs.oracle.com/en/database/oracle/oracle-database/23/sutil/oracle-sql-loader-field-list-contents.html#GUID-46A9380D-3BFD-49E4-9DD5-0AC5785A6DB9) - The field-list portion of a SQL*Loader control file provides information about fields being loaded.
- [`terminatedBy`](https://docs.oracle.com/en/database/oracle/oracle-database/23/sutil/oracle-sql-loader-control-file-contents.html#GUID-D1762699-8154-40F6-90DE-EFB8EB6A9AB0) - The terminated by character.
- [`enclosedBy`](https://docs.oracle.com/en/database/oracle/oracle-database/23/sutil/oracle-sql-loader-control-file-contents.html#GUID-D1762699-8154-40F6-90DE-EFB8EB6A9AB0) - The enclosed by character.
- [`trailing`](https://docs.oracle.com/en/database/oracle/oracle-database/23/sutil/oracle-sql-loader-control-file-contents.html#GUID-717EBE8E-C972-4D2C-9E42-16440CF069AA) - set to `true` to configure SQL*Loader to treat missing columns as null columns.
- [`formatOptions`](https://docs.oracle.com/en/database/oracle/oracle-database/23/sutil/oracle-sql-loader-control-file-contents.html#GUID-5740B2F9-C5C3-4D44-BB3F-81484417F02D) - Specifying Datetime Formats At the Table Level.
- [`when`](https://docs.oracle.com/en/database/oracle/oracle-database/23/sutil/oracle-sql-loader-control-file-contents.html#GUID-227B995D-72A8-42EE-ADD9-350B8A229495) - Specifies a WHEN clause that is applied to all data records read from the data file.

```php
$loader->into('employees', ['name', 'dept_id']);
```

### Disk

You can set the disk to use for the control file using the `disk` method.

```php
$loader->disk('local');
```

### Logging

You can get the logs of the execution using the `logs` method.

```php
return nl2br($loader->logs());
```

### Custom Control File

You can use a custom control file by passing the control file name to the `as` method.

```php
$loader->as('employees.ctl');
```

### Execute

You can execute the SQL Loader command using the `execute` method.

```php
$loader->execute();
```

You can also set the execution timeout in seconds. Default is 3600 seconds / 1 hr.

```php
$loader->execute(60);
```

### Execution Result

You can check if the execution was successful using the `successfull` method.

```php
if ($loader->successfull()) {
    return 'Data loaded successfully!';
}
```

### Process Result

You can get the process result using the `result` method.

```php
$result = $loader->result();
```

## Using array as data source

You can use an array as a data source by using `begindData` method.

```php
$loader = Yajra\SQLLoader\SQLLoader::make();
$loader->beginData([
        ['John', 1],
        ['Jane', 1],
        ['Jim, K', 2],
        ['Joe', 2],
    ])
    ->mode(Yajra\SQLLoader\Mode::TRUNCATE)
    ->into('employees', [
        'name',
        'dept_id',
    ])
    ->execute();
```

## Available Configuration

You can publish the configuration file using the following command:

```bash
php artisan vendor:publish --provider="Yajra\SQLLoader\SQLLoaderServiceProvider" --tag="config"
```

### Connection Config

You can set the connection name to use for the SQL Loader command.

```php
'connection' => env('SQL_LOADER_CONNECTION', 'oracle'),
```

### SQL Loader Path Config

You can set the path to the SQL Loader executable.

```php
'sqlldr' => env('SQL_LOADER_PATH', '/usr/local/bin/sqlldr'),
```

### Disk Config

You can set the disk to use for the control file.

```php
'disk' => env('SQL_LOADER_DISK', 'local'),
```

## Credits

- [Arjay Angeles][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-author]: https://github.com/yajra
[link-contributors]: ../../contributors
