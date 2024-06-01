<?php

declare(strict_types=1);

namespace Yajra\SQLLoader;

use Illuminate\Support\Facades\File;

final class CsvFile
{
    /**
     * @param  resource  $stream
     */
    private function __construct(public string $file, public $stream)
    {
    }

    /**
     * A list of possible modes. The default is 'w' (open for writing).:
     *
     * 'r' - Open for reading only; place the file pointer at the beginning of the file.
     * 'r+' - Open for reading and writing; place the file pointer at the beginning of the file.
     * 'w' - Open for writing only; place the file pointer at the beginning of the file and truncate the file to zero length. If the file does not exist, attempt to create it.
     * 'w+' - Open for reading and writing; place the file pointer at the beginning of the file and truncate the file to zero length. If the file does not exist, attempt to create it.
     * 'a' - Open for writing only; place the file pointer at the end of the file. If the file does not exist, attempt to create it.
     * 'a+' - Open for reading and writing; place the file pointer at the end of the file. If the file does not exist, attempt to create it.
     * 'x' - Create and open for writing only; place the file pointer at the beginning of the file. If the file already exists, the fopen call will fail by returning false and generating an error of level E_WARNING. If the file does not exist, attempt to create it. This is equivalent to specifying O_EXCL|O_CREAT flags for the underlying open(2) system call.
     * 'x+' - Create and open for reading and writing; place the file pointer at the beginning of the file. If the file already exists, the fopen call will fail by returning false and generating an error of level E_WARNING. If the file does not exist, attempt to create it. This is equivalent to specifying O_EXCL|O_CREAT flags for the underlying open(2) system call.
     *
     * @see https://www.php.net/manual/en/function.fopen.php
     */
    public static function make(string $filename, string $mode = 'w'): CsvFile
    {
        $csv = self::create($filename);

        $stream = fopen($csv, $mode);

        if ($stream === false) {
            throw new \RuntimeException("Failed to open file [{$csv}].");
        }

        return new self($csv, $stream);
    }

    public static function create(string $file): string
    {
        $directory = File::dirname($file);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true, true);
        }

        if (! File::exists($file)) {
            File::append($file, '');
        }

        return $file;
    }

    public function append(
        array $fields,
        string $separator = ',',
        string $enclosure = '"',
        string $escape = '\\',
        string $eol = PHP_EOL
    ): CsvFile {
        fputcsv($this->stream, $fields, $separator, $enclosure, $escape, $eol);

        return $this;
    }

    public static function blank(string $file): string
    {
        if (File::exists($file)) {
            File::delete($file);
        }

        return self::create($file);
    }

    public function close(): void
    {
        $this->__destruct();
    }

    public function __destruct()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    public function headers(array $fields): CsvFile
    {
        if ($this->isEmpty()) {
            $this->append($fields);
        }

        return $this;
    }

    public function isEmpty(): bool
    {
        clearstatcache(true, $this->file);

        return filesize($this->file) === 0;
    }

    public function insert(array $updates): CsvFile
    {
        foreach ($updates as $row) {
            $this->append($row);
        }

        return $this;
    }

    public function get(): string
    {
        $contents = file_get_contents($this->file);

        if ($contents === false) {
            return '';
        }

        return $contents;
    }

    /**
     * @param  null|int<0, max>  $length
     */
    public function getHeaders(?int $length = null): array
    {
        $headers = fgetcsv($this->stream, $length);

        if ($headers === false) {
            return [];
        }

        return $headers;
    }
}
