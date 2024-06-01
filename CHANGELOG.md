# Laravel-SQL-Loader CHANGELOG

## [Unreleased]

## [v1.3.1](https://github.com/yajra/laravel-sql-loader/compare/v1.3.0...v1.3.1) - 2024-06-01

- fix: set default CsvFile mode to `w`

## [v1.3.0](https://github.com/yajra/laravel-sql-loader/compare/v1.2.0...v1.3.0) - 2024-06-01

- feat: dynamically build columns based on csv file header and table schema
- feat: add support for default date formats
- fix: column issue when name is reserved word
- feat: set trailing nullcols as default behavior
- feat: add withHeader method to skip csv header
- fix: creating of CSV file when directory does not exist
- fix: headers appending the content of the array

## [v1.2.0](https://github.com/yajra/laravel-sql-loader/compare/v1.1.0...v1.2.0) - 2024-06-01

- feat: add csv file helper class

## [v1.1.0](https://github.com/yajra/laravel-sql-loader/compare/v1.0.1...v1.1.0) - 2024-05-31

- feat: add support for table when clause
- feat: add execution timeout option

## [v1.0.1](https://github.com/yajra/laravel-sql-loader/compare/v1.0.0...v1.0.1) - 2024-05-31

- fix: trailing with date format options #1

## [v1.0.0](https://github.com/yajra/laravel-sql-loader/compare/main...v1.0.0) - 2024-05-30

- First SQL Loader package release :rocket:
