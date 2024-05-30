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

Create a CSV file named `employees.csv` inside `database/files` directory.

```csv
NAME,DEPT_ID
John,1
Jane,1
"Jim, K",2
Joe,2
```

Create a route to test the package.

```php
Route::get('sql-loader', function () {
    Schema::dropIfExists('employees');
    Schema::create('employees', function ($table) {
        $table->id();
        $table->string('name');
        $table->integer('dept_id');
    });

    $loader = Yajra\SQLLoader\SQLLoader::make();
    $loader->inFile(database_path('files/employees.csv'))
        ->options(['skip=1'])
        ->mode(Yajra\SQLLoader\Mode::TRUNCATE)
        ->into('employees', [
            'name',
            'dept_id',
        ])
        ->execute();

    return nl2br($loader->logs());
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

- `table` - The table name.
- `columns` - The columns to load.
- `terminatedBy` - The terminated by character.
- `enclosedBy` - The enclosed by character.
- `trailing` - Set to true if the data is trailing.
- `formatOptions` - The format options in array.

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
