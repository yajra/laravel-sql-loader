<?php

declare(strict_types=1);

namespace Yajra\SQLLoader;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ControlFileBuilder
{
    public function __construct(public SQLLoader $loader)
    {
    }

    public function build(): string
    {
        $template = File::get($this->getStub());

        return Str::of($template)
            ->replace('$OPTIONS', $this->options())
            ->replace('$FILE', $this->inputFile())
            ->replace('$BADFILE', $this->badFile())
            ->replace('$DISCARDFILE', $this->discardFile())
            ->replace('$METHOD', $this->method())
            ->replace('$DELIMITER', $this->delimiter())
            ->replace('$ENCLOSURE', $this->enclosure())
            ->replace('$INSERTS', $this->inserts())
            ->toString();
    }

    public function getStub(): string
    {
        return __DIR__.'/stubs/control.stub';
    }

    public function options(): string
    {
        return implode(' ', $this->loader->options);
    }

    protected function inputFile(): string
    {
        return "INFILE '{$this->loader->file}'";
    }

    public function badFile(): string
    {
        if (! $this->loader->controlFile) {
            return '';
        }

        if (! $this->loader->badFile) {
            $this->loader->badFile = str_replace('.ctl', '.bad', $this->getControlFilePath());
        }

        return "BADFILE '{$this->loader->badFile}'";
    }

    protected function getControlFilePath(): string
    {
        if (is_null($this->loader->controlFile)) {
            $this->loader->controlFile = Str::uuid().'.ctl';
        }

        return $this->loader->getDisk()->path($this->loader->controlFile);
    }

    public function discardFile(): string
    {
        if (! $this->loader->controlFile) {
            return '';
        }

        if (! $this->loader->discardFile) {
            $this->loader->discardFile = str_replace('.ctl', '.dis', $this->getControlFilePath());
        }

        return "DISCARDFILE '{$this->loader->discardFile}'";
    }

    public function method(): string
    {
        return in_array($this->loader->method, [
            Method::INSERT,
            Method::TRUNCATE,
        ]) ? Method::TRUNCATE->value : $this->loader->method->value;
    }

    protected function delimiter(): string
    {
        return $this->loader->delimiter;
    }

    protected function enclosure(): string
    {
        return $this->loader->enclosure;
    }

    public function inserts(): string
    {
        $inserts = '';
        foreach ($this->loader->tables as $table) {
            $inserts .= "INTO TABLE {$table['table']}".PHP_EOL;
            $inserts .= "FIELDS TERMINATED BY '{$this->delimiter()}' OPTIONALLY ENCLOSED BY '{$this->enclosure()}'".PHP_EOL;
            // $inserts .= "TRAILING NULLCOLS".PHP_EOL;
            $inserts .= "({$this->buildColumns($table['columns'])})".PHP_EOL;
        }

        return $inserts;
    }

    public function buildColumns(array $columns): string
    {
        return implode(', ', $columns);
    }
}
