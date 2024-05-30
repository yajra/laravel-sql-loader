<?php

declare(strict_types=1);

namespace Yajra\SQLLoader;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LogicException;
use Stringable;

class ControlFileBuilder implements Stringable
{
    public function __construct(public SQLLoader $loader)
    {
    }

    public function __toString(): string
    {
        return $this->build();
    }

    public function build(): string
    {
        $template = File::get($this->getStub());

        return Str::of($template)
            ->replace('$OPTIONS', $this->options())
            ->replace('$FILES', $this->inputFiles())
            ->replace('$METHOD', $this->method())
            ->replace('$INSERTS', $this->inserts())
            ->replace('$BEGINDATA', $this->beginData())
            ->toString();
    }

    protected function getStub(): string
    {
        return __DIR__.'/stubs/control.stub';
    }

    protected function options(): string
    {
        return implode(', ', $this->loader->options);
    }

    protected function inputFiles(): string
    {
        $inputFiles = implode(PHP_EOL, $this->loader->inputFiles);

        return Str::replace("'*'", '*', $inputFiles);
    }

    protected function method(): string
    {
        return in_array($this->loader->mode, [
            Mode::INSERT,
            Mode::TRUNCATE,
        ]) ? Mode::TRUNCATE->value : $this->loader->mode->value;
    }

    protected function inserts(): string
    {
        return implode(PHP_EOL, $this->loader->tables);
    }

    protected function beginData(): string
    {
        $sql = '';
        if ($this->loader->beginData) {
            $sql .= 'BEGINDATA'.PHP_EOL;
            $sql .= $this->arrayToCsv($this->loader->beginData).PHP_EOL;
        }

        return $sql;
    }

    protected function arrayToCsv(
        array $data,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escape_char = '\\'
    ): string {
        $f = fopen('php://memory', 'r+');

        if (! $f) {
            throw new LogicException('Failed to open memory stream');
        }

        foreach ($data as $item) {
            fputcsv($f, $item, $delimiter, $enclosure, $escape_char);
        }
        rewind($f);

        $contents = stream_get_contents($f);

        if (! $contents) {
            throw new LogicException('Failed to read memory stream');
        }

        return $contents;
    }
}
