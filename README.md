# Oracle SQL Loader for Laravel

A Laravel package that allows you to easily load data into Oracle database using `sqlldr`.

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

if (! $loader->successful()) {
    return $loader->debug();
}

return $loader->output();
```

## Credits

- [Arjay Angeles][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-author]: https://github.com/yajra
[link-contributors]: ../../contributors
