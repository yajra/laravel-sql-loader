<?php

declare(strict_types=1);

namespace Yajra\SQLLoader;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SQLLoader
{
    protected string $file;

    protected Method $method = Method::INSERT;

    protected array $tables = [];

    protected string $enclosure = '"';

    protected string $delimiter = ',';

    protected string $controlFile = '';

    protected string $disk;

    protected string $logPath = '';

    protected ProcessResult $output;

    protected string $badFile;

    protected string $discardFile;

    public function __construct(
        protected array $options = []
    ) {
    }

    public static function make(array $options = []): SQLLoader
    {
        return new self($options);
    }

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function inFile(string $file): static
    {
        if (! File::exists($file)) {
            throw new InvalidArgumentException("File [{$file}] does not exist.");
        }

        $this->file = $file;

        return $this;
    }

    public function method(Method $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function into(string $table, array $columns): static
    {
        $this->tables[] = [
            'table' => $table,
            'columns' => $columns,
        ];

        return $this;
    }

    public function execute(): ProcessResult
    {
        if (! $this->tables) {
            throw new InvalidArgumentException('At least one table definition is required.');
        }

        if (! $this->file) {
            throw new InvalidArgumentException('Input file is required.');
        }

        return $this->output = Process::command($this->buildCommand())->run();
    }

    protected function buildCommand(): string
    {
        $file = ($this->controlFile ?: Str::uuid()).'.ctl';
        $this->getDisk()->put($file, $this->buildControlFile());

        $tns = $this->buildTNS();
        $binary = $this->getSqlLoaderBinary();
        $filePath = $this->getDisk()->path($file);

        $command = "$binary userid=$tns control={$filePath}";
        if (empty($this->logPath)) {
            $this->logPath = str_replace('.ctl', '.log', (string) $this->getDisk()->path($file));
        }

        $command .= " log={$this->logPath}";

        return $command;
    }

    protected function getDisk(): Filesystem
    {
        if ($this->disk) {
            return Storage::disk($this->disk);
        }

        return Storage::disk(config('sql-loader.disk', 'local'));
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    protected function buildControlFile(): string
    {
        $template = File::get($this->getStub());

        return Str::of($template)
            ->replace('$OPTIONS', $this->buildOptions())
            ->replace('$BADFILE', $this->buildBadFile())
            ->replace('$DISCARDFILE', $this->buildDiscardFile())
            ->replace('$FILE', "INFILE '{$this->file}'")
            ->replace('$METHOD', $this->method->value)
            ->replace('$DELIMITER', $this->delimiter)
            ->replace('$ENCLOSURE', $this->enclosure)
            ->replace('$INSERTS', $this->buildInserts())
            ->toString();
    }

    protected function getStub(): string
    {
        return __DIR__.'/stubs/control.stub';
    }

    protected function buildOptions(): string
    {
        return implode(' ', $this->options);
    }

    protected function buildBadFile(): string
    {
        if (isset($this->badFile)) {
            return "BADFILE '{$this->badFile}'";
        }

        return '';
    }

    protected function buildDiscardFile(): string
    {
        if (isset($this->discardFile)) {
            return "DISCARDFILE '{$this->discardFile}'";
        }

        return '';
    }

    protected function buildInserts(): string
    {
        $inserts = '';
        foreach ($this->tables as $table) {
            $inserts .= "INTO TABLE {$table['table']}".PHP_EOL;
            $inserts .= "FIELDS TERMINATED BY '{$this->delimiter}' OPTIONALLY ENCLOSED BY '{$this->enclosure}'".PHP_EOL;
            // $inserts .= "TRAILING NULLCOLS".PHP_EOL;
            $inserts .= "({$this->buildColumns($table['columns'])})".PHP_EOL;
        }

        return $inserts;
    }

    protected function buildColumns(array $columns): string
    {
        return implode(', ', $columns);
    }

    protected function buildTNS(): string
    {
        $connection = config('sql-loader.connection', 'oracle');
        $username = config('database.connections.'.$connection.'.username');
        $password = config('database.connections.'.$connection.'.password');
        $host = config('database.connections.'.$connection.'.host');
        $port = config('database.connections.'.$connection.'.port');
        $database = config('database.connections.'.$connection.'.database');

        return $username.'/'.$password.'@'.$host.':'.$port.'/'.$database;
    }

    public function getSqlLoaderBinary(): string
    {
        return config('sql-loader.sqlldr', 'sqlldr');
    }

    public function as(string $controlFile): static
    {
        $this->controlFile = $controlFile;

        return $this;
    }

    public function logsTo(string $path): static
    {
        $this->logPath = $path;

        return $this;
    }

    public function successful(): bool
    {
        return $this->output->exitCode() === 0;
    }

    public function debug(): string
    {
        $debug = 'Command:'.PHP_EOL.$this->buildCommand().PHP_EOL.PHP_EOL;
        $debug .= 'Output:'.$this->output().PHP_EOL.PHP_EOL;
        $debug .= 'Error Output:'.PHP_EOL.$this->errorOutput().PHP_EOL;
        $debug .= 'Exit Code: '.$this->output->exitCode().PHP_EOL.PHP_EOL;
        $debug .= 'Control File:'.PHP_EOL.$this->buildControlFile().PHP_EOL;

        return $debug;
    }

    public function output(): string
    {
        return $this->output->output();
    }

    public function errorOutput(): string
    {
        return $this->output->errorOutput();
    }

    public function delimiter(string $delimiter): static
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    public function enclosure(string $enclosure): static
    {
        $this->enclosure = $enclosure;

        return $this;
    }
}
