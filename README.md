# Oracle SQL Loader for Laravel

A Laravel package that allows you to easily load data into Oracle database using `sqlldr`.

## Requirements

- Oracle Instant Client
- Oracle SQL Loader
- Laravel 10.x or higher
- Laravel OCI8 10.x or higher

## Installation

You can install the package via composer:

```bash
composer require yajra/laravel-sql-loader
```

## Usage

Basic usage:

```php
$loader = Yajra\SQLLoader\SQLLoader::make();
$loader->inFile(database_path('files/employees.csv'))
    ->options(['skip=1'])
    ->method(Yajra\SQLLoader\Method::TRUNCATE)
    ->delimiter(',')
    ->enclosure('"')
    ->into('employees', [
        'name',
        'dept_id',
    ])
    ->as('employees')
    ->disk('local')
    ->execute();

return nl2br($loader->logs());
```

## Example

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
        ->method(Yajra\SQLLoader\Method::TRUNCATE)
        ->into('employees', [
            'name',
            'dept_id',
        ])
        ->execute();

    return nl2br($loader->logs());
});
```

## Credits

- [Arjay Angeles][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-author]: https://github.com/yajra
[link-contributors]: ../../contributors
