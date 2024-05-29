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
            ->replace('$INSERTS', $this->inserts())
            ->toString();
    }

    public function getStub(): string
    {
        return __DIR__.'/stubs/control.stub';
    }

    public function options(): string
    {
        return implode(', ', $this->loader->options);
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

    public function inserts(): string
    {
        $inserts = '';
        foreach ($this->loader->tables as $table) {
            $inserts .= "INTO TABLE {$table->table}".PHP_EOL;
            if ($table->terminatedBy) {
                $inserts .= "FIELDS TERMINATED BY '{$table->terminatedBy}' ";
            }

            if ($table->optionally) {
                $inserts .= 'OPTIONALLY ';
            }

            if ($table->enclosedBy) {
                $inserts .= "ENCLOSED BY '{$table->enclosedBy}'".PHP_EOL;
            }

            if ($table->trailing) {
                $inserts .= 'TRAILING NULLCOLS'.PHP_EOL;
            }

            $inserts .= '('.PHP_EOL;
            $inserts .= $this->buildColumns($table->columns).PHP_EOL;
            $inserts .= ')'.PHP_EOL;
        }

        return $inserts;
    }

    public function buildColumns(array $columns): string
    {
        return implode(','.PHP_EOL, array_map(fn($column) => str_repeat(' ', 2).$column, $columns));
    }
}
