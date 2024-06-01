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

    public static function make(string $filename, string $mode): CsvFile
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
            $this->append(array_keys($fields));
        }

        $this->append($fields);

        return $this;
    }

    public function isEmpty(): bool
    {
        clearstatcache(true, $this->file);

        return filesize($this->file) === 0;
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
}
