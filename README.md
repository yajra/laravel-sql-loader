# Oracle SQL*Loader for Laravel

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
  - For example, if you installed the Oracle Instant Client with Tools Package in `/usr/local/oracle/instantclient_19_6`, the `sqlldr` executable will be located in `/usr/local/oracle/instantclient_19_6/sqlldr`.
  - You can also add the path to the `sqlldr` executable to your system's PATH environment variable.
  - You can also set the path to the `sqlldr` executable in the `.env` file using the `SQL_LOADER_PATH` key.
  - You can also set the path to the `sqlldr` executable in the `config/sql-loader.php` file using the `sqlldr` key.
  - You can symlink the `sqlldr` executable to `/usr/local/bin` using the following command:
    ```bash
    sudo ln -nfs /usr/local/oracle/instantclient_19_6/sqlldr /usr/local/bin/sqlldr
    ```
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
        ])
        ->close();

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

## Date Formats

The SQL*Loader default date format is `YYYY-MM-DD"T"HH24:MI:SS."000000Z"` to match Laravel's model date serialization. 
You can change the date format using the `dateFormat` method.

```php
$loader->dateFormat('YYYY-MM-DD HH24:MI:SS');
```

## Available Methods

### Options

You can pass additional options to the `sqlldr` command using the `options` method.

```php
$loader->options(['skip=1', 'load=1000']);
```

### Input File(/s)

You can set the input file to use for the SQL*Loader command using the `inFile` method.

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

### With Headers

Using `withHeaders` will skip the first row of the CSV file. 

> [!IMPORTANT] 
> 1. `withHeaders` must be called before the `into` method.
> 2. This method assumes that the headers are the same as the table columns. 
> 3. Non-existent columns will be flagged as `FILLER`.
> 4. Date headers will be automatically detected and data type is appended in the control file. 
> 5. Date values must follow the default date format. If not, use the `dateFormat` method. 
> 6. If the headers are different from the table columns, you should define the `columns` in the `into` method. 

#### Building a CSV File from Eloquent Collection

```php
$users = User::all();
Yajra\SQLLoader\CsvFile::make(database_path('files/users.csv'), 'w')
    ->headers(array_keys($users->first()->toArray()))
    ->insert($users->toArray())
    ->close();
```

#### Loading CSV File with Headers

Load users from `oracle` to `backup` database connection.

```php
$loader->inFile(database_path('files/users.csv'))
    ->withHeaders()
    ->mode(Yajra\SQLLoader\Mode::TRUNCATE)
    ->connection('backup')
    ->into('users')
    ->execute();
```

### Wildcard Path with Headers

When using a wildcard path, the first file is assumed to contain the headers. The succeeding files should not have headers or it will be reported as a bad record.

```php
$loader->inFile(database_path('files/*.csv'))
    ->withHeaders()
    ->mode(Yajra\SQLLoader\Mode::TRUNCATE)
    ->into('employees')
    ->execute();
```

- employees-1.csv

```csv
name,dept_id
John Doe,1
Jane Doe,2
```

- employees-2.csv

```csv
John Doe,1
Jane Doe,2
```

### Constants

In some cases, we need to insert constant values to the table. You can use the `constants` method to set the constant value.

> [!IMPORTANT]
>`constants` must be called before the `into` method.

```php
$loader->withHeaders()
    ->constants([
        'file_id CONSTANT 1',
        'created_at EXPRESSION "current_timestamp(3)"',
        'updated_at EXPRESSION "current_timestamp(3)"',
    ])
    ->into('users');
```

### Connection

You can set the connection name to use for the SQL*Loader command using the `connection` method.

```php
$loader->connection('oracle');
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

You can execute the SQL*Loader command using the `execute` method.

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

You can set the connection name to use for the SQL*Loader command.

```php
'connection' => env('SQL_LOADER_CONNECTION', 'oracle'),
```

### SQL*Loader Path Config

You can set the path to the SQL*Loader executable.

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
