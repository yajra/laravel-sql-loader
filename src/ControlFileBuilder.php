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
        return in_array($this->loader->mode, [
            Mode::INSERT,
            Mode::TRUNCATE,
        ]) ? Mode::TRUNCATE->value : $this->loader->mode->value;
    }

    protected function inserts(): string
    {
        return implode(PHP_EOL, $this->loader->tables);
    }
}
