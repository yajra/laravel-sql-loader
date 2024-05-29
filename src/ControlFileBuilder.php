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
            ->replace('$FILES', $this->inputFiles())
            ->replace('$METHOD', $this->method())
            ->replace('$INSERTS', $this->inserts())
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
        return implode(PHP_EOL, $this->loader->inputFiles);
    }

    protected function method(): string
    {
        return in_array($this->loader->method, [
            Method::INSERT,
            Method::TRUNCATE,
        ]) ? Method::TRUNCATE->value : $this->loader->method->value;
    }

    protected function inserts(): string
    {
        $inserts = '';
        foreach ($this->loader->tables as $table) {
            $inserts .= "INTO TABLE {$table->table}".PHP_EOL;
            if ($table->terminatedBy) {
                $inserts .= "FIELDS TERMINATED BY '{$table->terminatedBy}' ";
            }

            if ($table->enclosedBy) {
                $inserts .= "OPTIONALLY ENCLOSED BY '{$table->enclosedBy}'".PHP_EOL;
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

    protected function buildColumns(array $columns): string
    {
        return implode(','.PHP_EOL, array_map(fn ($column) => str_repeat(' ', 2).$column, $columns));
    }
}
