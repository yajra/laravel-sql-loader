<?php

declare(strict_types=1);

namespace Yajra\SQLLoader;

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
        if (! file_exists($filename)) {
            $filename = self::create($filename);
        }

        $stream = fopen($filename, $mode);

        if ($stream === false) {
            throw new \RuntimeException("Failed to open file [{$filename}].");
        }

        return new self($filename, $stream);
    }

    public static function create(string $file): string
    {
        $stream = fopen($file, 'w');
        if ($stream === false) {
            throw new \RuntimeException('Could not open file for writing: '.$file);
        }
        fclose($stream);

        return $file;
    }

    public static function blank(string $file): string
    {
        if (file_exists($file)) {
            unlink($file);
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
