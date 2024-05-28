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
use LogicException;

class SQLLoader
{
    protected ?string $file = null;

    protected Method $method = Method::APPEND;

    protected array $tables = [];

    protected string $enclosure = '"';

    protected string $delimiter = ',';

    protected ?string $controlFile = null;

    protected ?string $disk = null;

    protected ?string $logPath = null;

    protected ?ProcessResult $result = null;

    protected ?string $badFile = null;

    protected ?string $discardFile = null;

    protected bool $deleteFiles = true;

    protected string $logs = '';

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

        $this->result = Process::command($this->buildCommand())->run();

        if ($this->logPath && File::exists($this->logPath)) {
            $this->logs = File::get($this->logPath);
        }

        if ($this->deleteFiles) {
            $this->deleteGeneratedFiles();
        }

        return $this->result; // @phpstan-ignore-line
    }

    protected function buildCommand(): string
    {
        $filesystem = $this->getDisk();

        $file = $this->getFile();
        $filesystem->put($file, $this->buildControlFile());
        $tns = $this->buildTNS();
        $binary = $this->getSqlLoaderBinary();
        $filePath = $filesystem->path($file);

        $command = "$binary userid=$tns control={$filePath}";
        if (! $this->logPath) {
            $this->logPath = str_replace('.ctl', '.log', (string) $filePath);
            $command .= " log={$this->logPath}";
        }

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

    protected function getFile(): string
    {
        if (! $this->controlFile) {
            $this->controlFile = Str::uuid().'.ctl';
        }

        return $this->controlFile;
    }

    protected function buildControlFile(): string
    {
        $template = File::get($this->getStub());

        return Str::of($template)
            ->replace('$OPTIONS', $this->buildOptions())
            ->replace('$FILE', "INFILE '{$this->file}'")
            ->replace('$BADFILE', $this->buildBadFile())
            ->replace('$DISCARDFILE', $this->buildDiscardFile())
            ->replace('$METHOD', $this->buildMethod())
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
        if (! $this->controlFile) {
            return '';
        }

        if (! $this->badFile) {
            $this->badFile = str_replace('.ctl', '.bad', $this->getDisk()->path($this->controlFile));
        }

        return "BADFILE '{$this->badFile}'";
    }

    protected function buildDiscardFile(): string
    {
        if (! $this->controlFile) {
            return '';
        }

        if (! $this->discardFile) {
            $this->discardFile = str_replace('.ctl', '.dis', $this->getDisk()->path($this->controlFile));
        }

        return "DISCARDFILE '{$this->discardFile}'";
    }

    protected function buildMethod(): string
    {
        return in_array($this->method, [
            Method::INSERT,
            Method::TRUNCATE,
        ]) ? Method::TRUNCATE->value : $this->method->value;
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

    protected function deleteGeneratedFiles(): void
    {
        if ($this->logPath && File::exists($this->logPath)) {
            File::delete($this->logPath);
        }

        if ($this->badFile && File::exists($this->badFile)) {
            File::delete($this->badFile);
        }

        if ($this->discardFile && File::exists($this->discardFile)) {
            File::delete($this->discardFile);
        }

        $filesystem = $this->getDisk();
        if ($this->controlFile && $filesystem->exists($this->controlFile)) {
            $filesystem->delete($this->controlFile);
        }
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
        if (is_null($this->result)) {
            return false;
        }

        return $this->result->successful();
    }

    public function debug(): string
    {
        $debug = 'Command:'.PHP_EOL.$this->buildCommand().PHP_EOL.PHP_EOL;
        $debug .= 'Control File:'.PHP_EOL.$this->buildControlFile().PHP_EOL;

        if ($this->result) {
            $debug .= 'Output:'.$this->result->output().PHP_EOL.PHP_EOL;
            $debug .= 'Error Output:'.PHP_EOL.$this->result->errorOutput().PHP_EOL;
            $debug .= 'Exit Code: '.$this->result->exitCode().PHP_EOL.PHP_EOL;
        }

        return $debug;
    }

    public function output(): string
    {
        if (is_null($this->result)) {
            return 'No output available';
        }

        return $this->result->output();
    }

    public function errorOutput(): string
    {
        if (is_null($this->result)) {
            return 'No error output available';
        }

        return $this->result->errorOutput();
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

    public function deleteFilesAfterRun(bool $delete = true): static
    {
        $this->deleteFiles = $delete;

        return $this;
    }

    public function logs(): string
    {
        return $this->logs;
    }

    public function result(): ProcessResult
    {
        if (! $this->result) {
            throw new LogicException('Please run execute method first.');
        }

        return $this->result;
    }
}
