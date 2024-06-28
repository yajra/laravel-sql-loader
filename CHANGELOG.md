# Laravel-SQL-Loader CHANGELOG

## [Unreleased]

## [v1.8.1](https://github.com/yajra/laravel-sql-loader/compare/v1.8.0...v1.8.1) - 2024-06-28

- fix: get column connection and table prefix #5
- Fix issue when connection uses table prefix 
- Fix issue where custom connection is not used when fetching the table columns

## [v1.8.0](https://github.com/yajra/laravel-sql-loader/compare/v1.7.1...v1.8.0) - 2024-06-20

- feat: add support for CSV Format Files #4

## [v1.7.1](https://github.com/yajra/laravel-sql-loader/compare/v1.7.0...v1.7.1) - 2024-06-04

- fix: Fix osFileProcClause #3
- fix: Sanitize headers #2
- test: Add test for osFileProcClause
- test: Add test for sanitize headers

## [v1.7.0](https://github.com/yajra/laravel-sql-loader/compare/v1.6.2...v1.7.0) - 2024-06-04

- feat: add support for constant columns when using withHeaders
- test: date format
- test: fix missing migration
- chore: bump deps

## [v1.6.2](https://github.com/yajra/laravel-sql-loader/compare/v1.6.1...v1.6.2) - 2024-06-02

- fix: default value for boolean

## [v1.6.1](https://github.com/yajra/laravel-sql-loader/compare/v1.6.0...v1.6.1) - 2024-06-02

- fix: withHeaders when using wildcard path

## [v1.6.0](https://github.com/yajra/laravel-sql-loader/compare/v1.5.0...v1.6.0) - 2024-06-02

- feat: add support for wildcard in file path
- feat: createColumnsFromHeaders api added

## [v1.5.0](https://github.com/yajra/laravel-sql-loader/compare/v1.4.0...v1.5.0) - 2024-06-02

- fix: build columns using the latest schema get columns builder
- feat: automatically detects filler columns
- tests: boolean fields

## [v1.4.0](https://github.com/yajra/laravel-sql-loader/compare/v1.3.1...v1.4.0) - 2024-06-02

- feat: add ability to set the database connection
- docs: withHeader and connection added

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
